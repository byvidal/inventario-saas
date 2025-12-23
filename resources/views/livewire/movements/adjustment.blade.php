<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Movement;
use App\Models\ProductBranch;

new #[Layout('components.layouts.app', ['title' => 'Registrar Merma'])]
    class extends Component {

    public $branch_id = '';
    public $product_id = '';
    public $quantity = 1;
    public $notes = ''; // Motivo del ajuste

    public $available_stock = 0;

    public function updatedBranchId()
    {
        $this->reset(['product_id', 'available_stock', 'quantity']);
    }

    public function updatedProductId()
    {
        if ($this->branch_id && $this->product_id) {
            $stock = ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)->first();
            $this->available_stock = $stock ? $stock->quantity : 0;
        }
    }

    public function save()
    {
        $this->validate([
            'branch_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|numeric|min:1|max:' . $this->available_stock,
            'notes' => 'required|string|min:5', // Obligatorio explicar la merma
        ]);

        DB::transaction(function () {
            $product = Product::find($this->product_id);

            Movement::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'branch_id' => $this->branch_id,
                'product_id' => $this->product_id,
                'type' => 'adjustment', // 👈 TIPO AJUSTE (SALIDA)
                'quantity' => $this->quantity,
                'price_at_movement' => 0, // No hay venta
                'cost_at_movement' => $product->cost ?? 0, // Perdemos el costo
                'notes' => $this->notes, // Guardamos el motivo
            ]);

            ProductBranch::where('branch_id', $this->branch_id)
                ->where('product_id', $this->product_id)
                ->decrement('quantity', $this->quantity);
        });

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

<div class="max-w-2xl mx-auto p-4 sm:p-6">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6 border-l-4 border-red-500">
        <div class="mb-6 border-b pb-4 dark:border-neutral-700">
            <h2 class="text-xl font-bold text-red-600 dark:text-red-400">Registrar Merma / Pérdida</h2>
            <p class="text-sm text-gray-500">Salida de inventario por daño, caducidad o robo.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Sucursal</label>
                    <select wire:model.live="branch_id"
                        class="w-full rounded-md border-gray-300 dark:bg-neutral-900 dark:text-white">
                        <option value="">Selecciona...</option>
                        @foreach($branches as $branch) <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Producto</label>
                    <select wire:model.live="product_id" @if(!$branch_id) disabled @endif
                        class="w-full rounded-md border-gray-300 dark:bg-neutral-900 dark:text-white disabled:opacity-50">
                        <option value="">Buscar...</option>
                        @foreach($products as $product) <option value="{{ $product->id }}">{{ $product->sku }} -
                        {{ $product->name }}</option> @endforeach
                    </select>
                </div>
            </div>

            @if($product_id)
                <div class="bg-red-50 text-red-800 p-2 rounded text-sm text-center">
                    Stock actual para dar de baja: <strong>{{ $available_stock }}</strong>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Cantidad Perdida</label>
                    <input wire:model="quantity" type="number" min="1" max="{{ $available_stock }}"
                        class="w-full rounded-md border-gray-300 dark:bg-neutral-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Motivo (Obligatorio)</label>
                <textarea wire:model="notes" rows="3" placeholder="Ej. El producto se cayó de la estantería..."
                    class="w-full rounded-md border-gray-300 dark:bg-neutral-900 dark:text-white"></textarea>
                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition">
                    Confirmar Baja 🗑️
                </button>
            </div>
        </form>
    </div>
</div>