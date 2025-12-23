<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url; // Para que la búsqueda se guarde en la URL

new
    #[Layout('components.layouts.app', ['title' => 'Inventario'])]
    class extends Component {

    #[Url] // Esto hace que si recargas la página, no pierdas tu búsqueda
    public $search = '';

    public function with(): array
    {
        return [
            'products' => Product::where('company_id', Auth::user()->company_id)
                ->where(function ($query) {
                    // Buscamos por Nombre O por SKU (insensible a mayúsculas 'ilike')
                    $query->where('name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('sku', 'ilike', '%' . $this->search . '%');
                })
                ->with(['category', 'unit'])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-end p-4 pb-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Inventario</h1>
            <p class="text-sm text-gray-500">Gestión de tus existencias</p>
        </div>

        <a href="{{ route('products.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium text-sm flex items-center gap-2">
            <span>+ Nuevo</span>
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
                placeholder="Buscar por nombre o código SKU..." autofocus>
        </div>
    </div>

    <div class="relative overflow-x-auto border border-neutral-200 dark:border-neutral-700 sm:rounded-lg mx-4 mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-neutral-800 dark:text-gray-300">
                <tr>
                    <th scope="col" class="px-6 py-3">Producto</th>
                    <th scope="col" class="px-6 py-3">SKU</th>
                    <th scope="col" class="px-6 py-3">Categoría</th>
                    <th scope="col" class="px-6 py-3">Contenido</th>
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
                        </th>
                        <td class="px-6 py-4">{{ $product->sku }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($product->weight)
                                <span
                                    class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                    {{ 0 + $product->weight }} {{ $product->unit->abbreviation }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                            ${{ number_format($product->price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('products.edit', $product) }}" wire:navigate
                                class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No encontramos productos que coincidan con tu búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>