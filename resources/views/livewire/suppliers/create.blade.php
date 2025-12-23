<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app', ['title' => 'Nuevo Proveedor'])]
    class extends Component {
    public $name = '';
    public $email = '';
    public $phone = '';
    public $tax_id = '';

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'nullable|email',
            'tax_id' => 'nullable|string|max:20',
        ]);

        Supplier::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
        ]);

        return $this->redirect(route('suppliers.index'), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-white">Registrar Proveedor</h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Empresa *</label>
                <input wire:model="name" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                    <input wire:model="email" type="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input wire:model="phone" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Fiscal (RFC/NIT)</label>
                <input wire:model="tax_id" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            </div>
        </form>
    </div>
</div>