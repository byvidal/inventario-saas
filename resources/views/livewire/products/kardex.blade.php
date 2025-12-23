<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app', ['title' => 'Kárdex'])]
    class extends Component {
    public Product $product;

    public function mount(Product $product)
    {
        if ($product->company_id !== Auth::user()->company_id)
            abort(403);
        $this->product = $product;
    }

    public function with(): array
    {
        return [
            'movements' => Movement::where('product_id', $this->product->id)
                ->with(['branch', 'user', 'supplier'])
                ->latest() // Del más reciente al más antiguo
                ->get()
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div
        class="bg-white dark:bg-neutral-800 p-6 rounded-xl shadow border border-neutral-200 dark:border-neutral-700 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h1>
            <p class="text-gray-500 font-mono text-sm">{{ $product->sku }} • {{ $product->category->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Stock Actual Global</p>
            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ $product->productBranches->sum('quantity') }} u.
            </p>
        </div>
    </div>

    <div
        class="relative overflow-x-auto border border-neutral-200 dark:border-neutral-700 sm:rounded-lg bg-white dark:bg-neutral-800">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-neutral-900 dark:text-gray-300">
                <tr>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Concepto</th>
                    <th class="px-6 py-3">Sucursal</th>
                    <th class="px-6 py-3 text-center">Entrada / Salida</th>
                    <th class="px-6 py-3">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $mov)
                    <tr class="border-b dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $mov->created_at->format('d/m/Y H:i') }}
                            <br><span class="text-xs text-gray-400">{{ $mov->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($mov->type == 'purchase') <span class="text-emerald-600 font-bold">Compra</span>
                            @elseif($mov->type == 'sale') <span class="text-blue-600 font-bold">Venta</span>
                            @elseif($mov->type == 'adjustment') <span class="text-red-600 font-bold">Merma/Ajuste</span>
                            @elseif($mov->type == 'transfer_out') <span class="text-orange-600">Traspaso (Salida)</span>
                            @elseif($mov->type == 'transfer_in') <span class="text-emerald-600">Traspaso (Entrada)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $mov->branch->name }}</td>
                        <td class="px-6 py-4 text-center font-bold text-base">
                            @if(in_array($mov->type, ['purchase', 'transfer_in']))
                                <span class="text-emerald-600">+{{ $mov->quantity }}</span>
                            @else
                                <span class="text-red-600">-{{ $mov->quantity }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($mov->supplier) Prov: {{ $mov->supplier->name }} <br> @endif
                            @if($mov->notes) <span class="italic text-gray-500">"{{ $mov->notes }}"</span> <br> @endif
                            <span class="opacity-75">Por: {{ $mov->user->name }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">Sin movimientos históricos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>