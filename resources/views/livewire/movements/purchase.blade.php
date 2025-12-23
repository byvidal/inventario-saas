<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Movement;
use App\Models\ProductBranch;

new #[Layout('components.layouts.app', ['title' => 'Registrar Compra'])]
    class extends Component {

    // Campos del formulario
    public $branch_id = '';
    public $supplier_id = '';
    public $product_id = '';
    public $quantity = 1;
    public $cost = '';

    // Guardar la Compra
    public function save()
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            // 1. Crear el Movimiento (Historial)
            Movement::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'branch_id' => $this->branch_id,
                'supplier_id' => $this->supplier_id,
                'product_id' => $this->product_id,
                'type' => 'purchase', // Tipo COMPRA
                'quantity' => $this->quantity,
                'cost_at_movement' => $this->cost,
                'price_at_movement' => null, // En compra no hay precio de venta
            ]);

            // 2. Actualizar o Crear Stock en la Sucursal
            $stock = ProductBranch::where('product_id', $this->product_id)
                ->where('branch_id', $this->branch_id)
                ->first();

            if ($stock) {
                // Si ya existe, sumamos
                $stock->increment('quantity', $this->quantity);
            } else {
                // Si es nuevo en esta sucursal, lo creamos
                ProductBranch::create([
                    'product_id' => $this->product_id,
                    'branch_id' => $this->branch_id,
                    'quantity' => $this->quantity,
                ]);
            }

            // Opcional: Actualizar el "Costo Último" en el producto maestro
            $product = Product::find($this->product_id);
            $product->update(['cost' => $this->cost]);
        });

        // Mensaje de éxito y limpiar
        session()->flash('message', 'Compra registrada y stock actualizado.');
        return $this->redirect(route('products.index'), navigate: true);
    }

    public function with(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'branches' => Branch::where('company_id', $companyId)->get(),
            'suppliers' => Supplier::where('company_id', $companyId)->get(),
            'products' => Product::where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        
        <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Registrar Entrada de Mercancía</h2>
            <p class="text-sm text-gray-500">Ingresa los productos comprados para aumentar tu stock.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal de Entrada *</label>
                    <select wire:model="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona sucursal...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor *</label>
                    <select wire:model="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona proveedor...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-xs">
                        <a href="{{ route('suppliers.create') }}" class="text-indigo-600 hover:underline">+ Crear nuevo proveedor</a>
                    </div>
                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Producto *</label>
                        <select wire:model="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                            <option value="">Buscar producto...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                        <input wire:model="quantity" type="number" step="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Costo Unitario *</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input wire:model="cost" type="number" step="0.01" class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>
                        @error('cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-center pt-6">
                        <div class="text-lg font-bold text-gray-800 dark:text-white">
                            Total: <span class="text-indigo-600">${{ number_format((float) $quantity * (float) ($cost ?: 0), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition flex items-center shadow-md">
                    <span wire:loading.remove>✅ Registrar Entrada</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>