<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; // 1. Importamos el atributo Layout

new
    #[Layout('components.layouts.app', ['title' => 'Inventario'])] // 2. Definimos el diseño AQUÍ, no en el HTML
    class extends Component {
    public function with(): array
    {
        return [
            'products' => Product::where('company_id', Auth::user()->company_id)
                ->with(['category', 'unit'])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-center p-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Inventario</h1>
            <p class="text-sm text-gray-500">Gestión de existencias</p>
        </div>
        <a href="{{ route('products.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium text-sm">
            + Nuevo Producto
        </a>
    </div>

    <div class="relative overflow-x-auto border border-neutral-200 dark:border-neutral-700 sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-neutral-800 dark:text-gray-300">
                <tr>
                    <th class="px-6 py-3">Producto</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Categoría</th>
                    <th class="px-6 py-3 text-right">Precio</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr
                        class="bg-white border-b dark:bg-neutral-900 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        <th class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $product->name }}
                        </th>
                        <td class="px-6 py-4">{{ $product->sku }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                            ${{ number_format($product->price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            No hay productos registrados aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>