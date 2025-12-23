<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new
    #[Layout('components.layouts.app', ['title' => 'Inventario'])]
    class extends Component {

    #[Url]
    public $search = '';

    public function with(): array
    {
        return [
            'products' => Product::where('company_id', Auth::user()->company_id)
                ->where(function ($query) {
                    $query->where('name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('sku', 'ilike', '%' . $this->search . '%');
                })
                // 👇 CARGAMOS LA RELACIÓN para poder mostrar el desglose en el tooltip sin lentitud
                ->with(['category', 'unit', 'productBranches.branch'])
                ->withSum('productBranches as total_stock', 'quantity')
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-end p-4 pb-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Inventario</h1>
            <p class="text-sm text-gray-500">Gestión de productos y existencias</p>
        </div>

        <a href="{{ route('products.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium text-sm flex items-center gap-2 shadow-md">
            <span>+ Nuevo Producto</span>
        </a>
    </div>

    <div class="px-4">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-neutral-800 dark:border-neutral-700 dark:placeholder-gray-400 dark:text-white"
                placeholder="Buscar por nombre o SKU..." autofocus>
        </div>
    </div>

    <div
        class="relative overflow-x-auto border border-neutral-200 dark:border-neutral-700 sm:rounded-lg mx-4 mb-4 shadow-sm">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-neutral-800 dark:text-gray-300">
                <tr>
                    <th scope="col" class="px-6 py-3">Producto</th>
                    <th scope="col" class="px-6 py-3">SKU</th>
                    <th scope="col" class="px-6 py-3">Categoría</th>
                    <th scope="col" class="px-6 py-3 text-center">Stock Total</th>
                    <th scope="col" class="px-6 py-3 text-right">Precio</th>
                    <th scope="col" class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr
                        class="bg-white border-b dark:bg-neutral-900 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800 transition">

                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $product->name }}
                            @if($product->weight)
                                <span class="block text-xs text-gray-400 font-normal">
                                    {{ 0 + $product->weight }} {{ $product->unit->abbreviation ?? '' }}
                                </span>
                            @endif
                        </th>

                        <td class="px-6 py-4 font-mono text-xs">{{ $product->sku }}</td>

                        <td class="px-6 py-4">
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                {{ $product->category->name }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center overflow-visible z-50">
                            @php
                                $stock = $product->total_stock ?? 0;
                                // Lógica de colores (Semáforo)
                                $color = $stock == 0 ? 'bg-red-100 text-red-800' : ($stock <= $product->min_stock ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800');
                                $darkColor = $stock == 0 ? 'dark:bg-red-900 dark:text-red-300' : ($stock <= $product->min_stock ? 'dark:bg-orange-900 dark:text-orange-300' : 'dark:bg-green-900 dark:text-green-300');
                            @endphp

                            <div class="group relative inline-block">
                                <span
                                    class="{{ $color }} {{ $darkColor }} text-sm font-bold px-3 py-1 rounded-full cursor-help transition-transform hover:scale-105 inline-block">
                                    {{ $stock }}
                                </span>

                                <div
                                    class="invisible group-hover:visible absolute z-50 w-48 -translate-x-1/2 left-1/2 bottom-full mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-xl dark:bg-gray-700 transition-opacity duration-200 opacity-0 group-hover:opacity-100 pointer-events-none">
                                    <p class="font-bold border-b border-gray-600 pb-1 mb-1 text-center text-gray-300">
                                        Ubicación</p>
                                    <ul class="space-y-1">
                                        @forelse($product->productBranches as $pb)
                                            <li class="flex justify-between items-center">
                                                <span class="truncate pr-2">{{ $pb->branch->name }}</span>
                                                <span class="font-bold text-green-400">{{ $pb->quantity }}</span>
                                            </li>
                                        @empty
                                            <li class="text-center italic text-gray-400">Sin existencias</li>
                                        @endforelse
                                    </ul>
                                    <div
                                        class="w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45 absolute left-1/2 -translate-x-1/2 -bottom-1">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                            ${{ number_format($product->price, 2) }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('products.edit', $product) }}" wire:navigate
                                class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">
                                Editar
                            </a>
                            <a href="{{ route('products.kardex', $product) }}" wire:navigate
                                class="text-gray-500 hover:text-indigo-600 mr-3" title="Ver Historial">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p>No se encontraron productos.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>