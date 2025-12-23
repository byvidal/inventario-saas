<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Movement; // 👈 Importante
use App\Models\ProductBranch; // 👈 Importante

new 
#[Layout('components.layouts.app', ['title' => 'Dashboard'])] 
class extends Component {
    public function with(): array
    {
        $companyId = Auth::user()->company_id;

        // 1. Calcular Stock Físico Total (Suma de quantity en la tabla pivote)
        // Nota: Filtramos por productos de la compañía para asegurar seguridad
        $totalStock = ProductBranch::whereHas('product', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->sum('quantity');

        return [
            // Valor Total del Inventario (Precio * Cantidad en stock)
            // Esto es un cálculo aproximado rápido. Para exactitud milimétrica se requiere query compleja.
            // Por rendimiento ahora, mostraremos el Valor Potencial del Catálogo (como antes)
            // o si prefieres, podemos dejar solo la suma de precios base.
            'catalogValue' => Product::where('company_id', $companyId)->sum('price'),

            'totalStock' => $totalStock,
            
            'totalSuppliers' => Supplier::where('company_id', $companyId)->count(),
            
            'totalBranches' => Branch::where('company_id', $companyId)->count(),

            // 2. Traer los últimos movimientos reales (Compras, etc.)
            'recentMovements' => Movement::where('company_id', $companyId)
                                ->with(['product', 'branch', 'user']) // Carga inteligente
                                ->latest()
                                ->take(5)
                                ->get()
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    
    <div class="grid gap-6 md:grid-cols-4">
        
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Stock Total (Unidades)</dt>
            <dd class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalStock) }}</dd>
            <div class="absolute right-4 top-4 text-blue-100 dark:text-blue-900/30">
                <svg class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Valor Catálogo</dt>
            <dd class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">${{ number_format($catalogValue, 2) }}</dd>
            <div class="absolute right-4 top-4 text-emerald-100 dark:text-emerald-900/30">
                <svg class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Proveedores</dt>
            <dd class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalSuppliers }}</dd>
            <div class="absolute right-4 top-4 text-purple-100 dark:text-purple-900/30">
                <svg class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800">
            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Sucursales Activas</dt>
            <dd class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalBranches }}</dd>
            <div class="absolute right-4 top-4 text-orange-100 dark:text-orange-900/30">
                <svg class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>
    </div>

    <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700 flex justify-between items-center">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Movimientos Recientes</h3>
            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">En vivo</span>
        </div>
        <ul role="list" class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @forelse($recentMovements as $movement)
                <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition">
                    <div class="flex items-center gap-x-4">
                        <div class="h-10 w-10 flex-none rounded-full flex items-center justify-center
                            {{ $movement->type === 'purchase' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }}">
                            @if($movement->type === 'purchase')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                            @endif
                        </div>
                        
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">
                                {{ $movement->type === 'purchase' ? 'Compra registrada' : 'Movimiento' }} 
                                <span class="font-normal text-gray-500">de</span> {{ $movement->product->name }}
                            </p>
                            <p class="truncate text-xs leading-5 text-gray-500">
                                {{ $movement->branch->name }} • Por {{ $movement->user->name }}
                            </p>
                        </div>
                        
                        <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 font-bold {{ $movement->type === 'purchase' ? 'text-emerald-600' : 'text-gray-900' }}">
                                +{{ $movement->quantity }} u.
                            </p>
                            <p class="text-xs leading-5 text-gray-500">{{ $movement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-gray-500 text-sm">
                    <div class="mx-auto h-12 w-12 text-gray-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    </div>
                    <p class="mt-2">No hay movimientos registrados aún.</p>
                </li>
            @endforelse
        </ul>
    </div>

</div>