<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app', ['title' => 'Sucursales'])]
    class extends Component {
    public function with(): array
    {
        return [
            'branches' => Branch::where('company_id', Auth::user()->company_id)->get(),
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="flex justify-between items-end p-4 pb-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sucursales</h1>
            <p class="text-sm text-gray-500">Administra tus ubicaciones físicas.</p>
        </div>
        <a href="{{ route('branches.create') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium text-sm">
            + Nueva Sucursal
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4">
        @forelse($branches as $branch)
            <div
                class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl p-6 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div
                        class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <a href="{{ route('branches.edit', $branch) }}" wire:navigate
                        class="text-gray-400 hover:text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                            </path>
                        </svg>
                    </a>
                </div>
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">{{ $branch->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $branch->address ?? 'Sin dirección registrada' }}</p>
                <div
                    class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                    {{ $branch->phone ?? 'Sin teléfono' }}
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-gray-500">
                No tienes sucursales registradas. Crea la primera para empezar a operar.
            </div>
        @endforelse
    </div>
</div>