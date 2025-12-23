<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed; // 👈 Importante para cálculos automáticos
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Movement;
use App\Models\ProductBranch;

new #[Layout('components.layouts.app', ['title' => 'Registrar Venta'])]
    class extends Component {

    // Variables del formulario
    public $branch_id = '';
    public $product_id = '';
    public $quantity = 1;
    public $price = ''; // Precio UNITARIO

    // Variables de control
    public $available_stock = 0;

    // 🧮 PROPIEDAD COMPUTADA: Calcula el Total Automáticamente
    #[Computed]
    public function total()
    {
        // Si no son números válidos, retorna 0
        $qty = is_numeric($this->quantity) ? $this->quantity : 0;
        $prc = is_numeric($this->price) ? $this->price : 0;

        return $qty * $prc;
    }

    public function updatedBranchId()
    {
        $this->reset(['product_id', 'available_stock', 'quantity', 'price']);
    }

    public function updatedProductId()
    {
        if ($this->branch_id && $this->product_id) {
            $stock = ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)
                ->first();

            $this->available_stock = $stock ? $stock->quantity : 0;

            // Sugerimos el precio de lista del producto
            $product = Product::find($this->product_id);
            $this->price = $product->price;
        }
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1|max:' . $this->available_stock,
            'price' => 'required|numeric|min:0',
        ], [
            'quantity.max' => 'No hay suficiente stock (Máximo: ' . $this->available_stock . ').',
        ]);

        DB::transaction(function () {
            // Buscamos el producto para obtener su COSTO actual
            $product = Product::find($this->product_id);

            // 1. Crear Movimiento
            Movement::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'branch_id' => $this->branch_id,
                'product_id' => $this->product_id,
                'type' => 'sale',
                'quantity' => $this->quantity,
                'price_at_movement' => $this->price,

                // 👇 AQUÍ ESTABA EL ERROR: Ahora guardamos el costo (o 0 si no tiene)
                'cost_at_movement' => $product->cost ?? 0,
            ]);

            // 2. Descontar Stock
            ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)
                ->decrement('quantity', $this->quantity);
        });

        session()->flash('message', 'Venta registrada correctamente.');
        return $this->redirect(route('products.index'), navigate: true);
    }

    public function with(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'branches' => Branch::where('company_id', $companyId)->get(),
            'products' => Product::where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        
        <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Registrar Salida / Venta</h2>
            <p class="text-sm text-gray-500">Descuenta productos del inventario y registra ingresos.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal de Origen *</label>
                <select wire:model.live="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    <option value="">Selecciona sucursal...</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Producto *</label>
                        <select wire:model.live="product_id" 
                                @if(!$branch_id) disabled @endif
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">
                                @if(!$branch_id) Primero selecciona una sucursal @else Buscar producto... @endif
                            </option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        
                        @if($product_id)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">En existencia:</span>
                                <span class="px-2 py-1 text-xs font-bold rounded 
                                    {{ $available_stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $available_stock }} unidades
                                </span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                        <input wire:model.live="quantity" type="number" step="1" max="{{ $available_stock }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Unitario *</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input wire:model.live="price" type="number" step="0.01" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>
                        @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 p-4 bg-gray-50 dark:bg-neutral-700/30 rounded-lg border border-gray-200 dark:border-neutral-700 flex flex-col items-center justify-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Total a Cobrar</span>
                    <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                        ${{ number_format($this->total, 2) }}
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        ({{ $quantity ?: 0 }} u. x ${{ number_format((float) ($price ?: 0), 2) }})
                    </p>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" 
                        wire:loading.attr="disabled"
                        {{ $available_stock <= 0 ? 'disabled' : '' }}
                        class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition flex items-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>✅ Confirmar Venta</span>
                    <span wire:loading>Procesando...</span>
                </button>
            </div>
        </form>
    </div>
</div>