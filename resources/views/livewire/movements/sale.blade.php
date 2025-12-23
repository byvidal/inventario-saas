<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Movement;
use App\Models\ProductBranch;
use App\Models\Client; // Opcional, si quisieras registrar cliente

new #[Layout('components.layouts.app', ['title' => 'Registrar Venta'])]
    class extends Component {

    // Variables del formulario
    public $branch_id = '';
    public $product_id = '';
    public $quantity = 1;
    public $price = '';

    // Variables de control (Estado)
    public $available_stock = 0; // Para mostrar al usuario cuánto hay

    // Ciclo de vida: Cuando cambia la Sucursal
    public function updatedBranchId()
    {
        $this->reset(['product_id', 'available_stock', 'quantity', 'price']);
    }

    // Ciclo de vida: Cuando cambia el Producto
    public function updatedProductId()
    {
        if ($this->branch_id && $this->product_id) {
            // Buscamos cuánto stock hay exactamente en esa sucursal
            $stock = ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)
                ->first();

            // Si hay registro tomamos la cantidad, si no, es 0
            $this->available_stock = $stock ? $stock->quantity : 0;

            // Sugerimos el precio de venta actual del producto
            $product = Product::find($this->product_id);
            $this->price = $product->price;
        }
    }

    public function save()
    {
        // 1. Validaciones
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            // La cantidad debe ser numérica, mayor a 0 y MENOR O IGUAL al stock disponible
            'quantity' => 'required|numeric|min:1|max:' . $this->available_stock,
            'price' => 'required|numeric|min:0',
        ], [
            'quantity.max' => 'No tienes suficiente stock para realizar esta venta.',
        ]);

        DB::transaction(function () {
            // 2. Crear Movimiento (Historial)
            Movement::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'branch_id' => $this->branch_id,
                'product_id' => $this->product_id,
                'type' => 'sale', // 👈 TIPO VENTA
                'quantity' => $this->quantity, // Se guarda positivo, la lógica sabe que es salida por el tipo
                'price_at_movement' => $this->price,
                'cost_at_movement' => null, // O podrías buscar el costo promedio si quisieras
            ]);

            // 3. Descontar Stock (Decrement)
            ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)
                ->decrement('quantity', $this->quantity);
        });

        session()->flash('message', 'Venta registrada correctamente.');
        return $this->redirect(route('products.index'), navigate: true);
    }

    // Cargar datos para los Selects
    public function with(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'branches' => Branch::where('company_id', $companyId)->get(),

            // Cargamos productos. Podríamos filtrar solo los que tienen stock > 0
            // pero dejémoslo abierto para que el usuario vea que "no hay stock".
            'products' => Product::where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        
        <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Registrar Salida / Venta</h2>
            <p class="text-sm text-gray-500">Descuenta productos del inventario.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">¿De dónde sale la mercancía? *</label>
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
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Disponible:</span>
                                <span class="px-2 py-1 text-xs font-bold rounded 
                                    {{ $available_stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $available_stock }} unidades
                                </span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad a Vender *</label>
                        <input wire:model="quantity" type="number" step="1" max="{{ $available_stock }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta (Total) *</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input wire:model="price" type="number" step="0.01" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Precio unitario sugerido.</p>
                        @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-center pt-6 mt-4">
                    <div class="text-lg font-bold text-gray-800 dark:text-white">
                        Total a Cobrar: <span class="text-blue-600">${{ number_format((float) $quantity * (float) ($price ?: 0), 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" 
                        wire:loading.attr="disabled"
                        {{ $available_stock <= 0 ? 'disabled' : '' }}
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove>📉 Registrar Venta</span>
                    <span wire:loading>Procesando...</span>
                </button>
            </div>
        </form>
    </div>
</div>