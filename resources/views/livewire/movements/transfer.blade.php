<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Movement;
use App\Models\ProductBranch;

new #[Layout('components.layouts.app', ['title' => 'Traspaso de Mercancía'])]
    class extends Component {

    public $from_branch_id = '';
    public $to_branch_id = '';
    public $product_id = '';
    public $quantity = 1;

    public $available_stock = 0;

    public function updatedFromBranchId()
    {
        $this->resetProduct();
    }
    public function updatedProductId()
    {
        $this->checkStock();
    }

    public function resetProduct()
    {
        $this->product_id = '';
        $this->available_stock = 0;
    }

    public function checkStock()
    {
        if ($this->from_branch_id && $this->product_id) {
            $stock = ProductBranch::where('branch_id', $this->from_branch_id)
                ->where('product_id', $this->product_id)->first();
            $this->available_stock = $stock ? $stock->quantity : 0;
        }
    }

    public function save()
    {
        $this->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id', // No traspasar a la misma
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1|max:' . $this->available_stock,
        ], [
            'to_branch_id.different' => 'La sucursal de destino debe ser diferente al origen.',
            'quantity.max' => 'No tienes suficiente stock en el origen para traspasar.',
        ]);

        DB::transaction(function () {
            $user = Auth::id();
            $company = Auth::user()->company_id;
            // Obtenemos costo para el registro (informativo)
            $product = Product::find($this->product_id);

            // 1. SALIDA del Origen
            Movement::create([
                'company_id' => $company,
                'user_id' => $user,
                'branch_id' => $this->from_branch_id,
                'product_id' => $this->product_id,
                'type' => 'transfer_out', // Tipo Salida por Traspaso
                'quantity' => $this->quantity,
                'cost_at_movement' => $product->cost ?? 0,
            ]);
            ProductBranch::where('branch_id', $this->from_branch_id)
                ->where('product_id', $this->product_id)
                ->decrement('quantity', $this->quantity);

            // 2. ENTRADA al Destino
            Movement::create([
                'company_id' => $company,
                'user_id' => $user,
                'branch_id' => $this->to_branch_id,
                'product_id' => $this->product_id,
                'type' => 'transfer_in', // Tipo Entrada por Traspaso
                'quantity' => $this->quantity,
                'cost_at_movement' => $product->cost ?? 0,
            ]);

            // Actualizar/Crear Stock Destino
            $destStock = ProductBranch::firstOrCreate(
                ['branch_id' => $this->to_branch_id, 'product_id' => $this->product_id],
                ['quantity' => 0]
            );
            $destStock->increment('quantity', $this->quantity);
        });

        session()->flash('message', 'Traspaso realizado con éxito.');
        return $this->redirect(route('products.index'), navigate: true);
    }

    public function with(): array
    {
        $cid = Auth::user()->company_id;
        return [
            'branches' => Branch::where('company_id', $cid)->get(),
            'products' => Product::where('company_id', $cid)->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        <div class="mb-6 border-b pb-4 dark:border-neutral-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Traspaso entre Sucursales</h2>
            <p class="text-sm text-gray-500">Mueve inventario de una ubicación a otra.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-gray-50 dark:bg-neutral-900 p-4 rounded-xl">
                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">📤 Desde
                        (Origen)</label>
                    <select wire:model.live="from_branch_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona origen...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">📥 Hacia
                        (Destino)</label>
                    <select wire:model="to_branch_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona destino...</option>
                        @foreach($branches as $branch)
                            {{-- Ocultar la misma sucursal seleccionada en origen --}}
                            @if($branch->id != $from_branch_id)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Producto a Mover</label>
                    <select wire:model.live="product_id" @if(!$from_branch_id) disabled @endif
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white disabled:opacity-50">
                        <option value="">Buscar producto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </select>

                    @if($product_id)
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Stock en Origen: <span
                                class="font-bold {{ $available_stock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $available_stock }}</span>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad</label>
                    <input wire:model="quantity" type="number" min="1" max="{{ $available_stock }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100 dark:border-gray-700">
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow-md">
                    Confirmar Traspaso 🚚
                </button>
            </div>
        </form>
    </div>
</div>