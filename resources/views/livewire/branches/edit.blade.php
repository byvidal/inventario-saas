<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.app', ['title' => 'Editar Sucursal'])]
    class extends Component {

    public Branch $branch; // Guardamos la sucursal que estamos editando

    // Variables del formulario
    public $name = '';
    public $address = '';
    public $phone = '';

    public function mount(Branch $branch)
    {
        // Seguridad: Verificar que la sucursal pertenezca a mi empresa
        if ($branch->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->branch = $branch;

        // Llenamos los inputs con los datos actuales
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->phone = $branch->phone;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Actualizamos en la BD
        $this->branch->update([
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
        ]);

        return $this->redirect(route('branches.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">

        <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Editar Sucursal</h2>
            <p class="text-sm text-gray-500">Modifica los datos de ubicación.</p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Sucursal
                    *</label>
                <input wire:model="name" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                <input wire:model="address" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                <input wire:model="phone" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('branches.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancelar</a>

                <button type="submit" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                    <span wire:loading.remove>Guardar Cambios</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>