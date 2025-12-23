<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app', ['title' => 'Nueva Sucursal'])]
    class extends Component {
    public $name = '';
    public $address = '';
    public $phone = '';

    public function save()
    {
        $this->validate(['name' => 'required|min:3']);

        Branch::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
        ]);

        return $this->redirect(route('branches.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-white">Registrar Sucursal</h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Sucursal
                    *</label>
                <input wire:model="name" type="text" placeholder="Ej. Bodega Central, Tienda Centro..."
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

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Guardar
                    Sucursal</button>
            </div>
        </form>
    </div>
</div>