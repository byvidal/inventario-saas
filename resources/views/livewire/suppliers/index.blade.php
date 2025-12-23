<?php

use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new
    #[Layout('components.layouts.app', ['title' => 'Proveedores'])]
    class extends Component {

    #[Url]
    public $search = '';

    public function with(): array
    {
        return [
            'suppliers' => Supplier::where('company_id', Auth::user()->company_id)
                ->where('name', 'ilike', '%' . $this->search . '%') // Búsqueda simple
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="flex justify-between items-end p-4 pb-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Proveedores</h1>
            <p class="text-sm text-gray-500">Directorio de socios comerciales</p>
        </div>
        <a href="{{ route('suppliers.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium text-sm flex items-center gap-2">
            + Nuevo Proveedor
        </a>
    </div>

    <div class="px-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
            placeholder="Buscar empresa...">
    </div>

    <div class="relative overflow-x-auto border border-neutral-200 dark:border-neutral-700 sm:rounded-lg mx-4 mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-neutral-800 dark:text-gray-300">
                <tr>
                    <th class="px-6 py-3">Empresa / Nombre</th>
                    <th class="px-6 py-3">Contacto</th>
                    <th class="px-6 py-3">ID Fiscal</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr
                        class="bg-white border-b dark:bg-neutral-900 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-800">
                        <th class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $supplier->name }}
                        </th>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs">{{ $supplier->email ?? '-' }}</span>
                                <span class="text-xs text-gray-400">{{ $supplier->phone ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                {{ $supplier->tax_id ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate
                                class="font-medium text-indigo-600 dark:text-indigo-500 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay proveedores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>