<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Volt::route('products', 'products.index')->name('products.index');
    Route::redirect('settings', 'settings/profile');
    Volt::route('products/{product}/edit', 'products.edit')->name('products.edit');
    Volt::route('products/create', 'products.create')->name('products.create');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('movements/purchase', 'movements.purchase')->name('movements.purchase');
    Volt::route('movements/sale', 'movements.sale')->name('movements.sale');
    Volt::route('movements/transfer', 'movements.transfer')->name('movements.transfer');
    Volt::route('movements/adjustment', 'movements.adjustment')->name('movements.adjustment');
    Volt::route('products/{product}/kardex', 'products.kardex')->name('products.kardex');

    // RUTAS DE SUCURSALES
    Volt::route('branches', 'branches.index')->name('branches.index');
    Volt::route('branches/create', 'branches.create')->name('branches.create');
    Volt::route('branches/{branch}/edit', 'branches.edit')->name('branches.edit');

    Volt::route('suppliers', 'suppliers.index')->name('suppliers.index');
    Volt::route('suppliers/create', 'suppliers.create')->name('suppliers.create');
    Volt::route('suppliers/{supplier}/edit', 'suppliers.edit')->name('suppliers.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// 👇 NUEVO: RUTAS DE TU PANEL DE DUEÑO (SUPER ADMIN)
Route::middleware(['auth', 'superadmin'])
    ->prefix('admin')
    ->name('superadmin.')
    ->group(function () {
        // Creamos un componente Volt para gestionar las empresas
        Volt::route('empresas', 'superadmin.companies')->name('companies');
    });