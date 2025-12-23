<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.app', ['title' => 'Nuevo Producto'])]
    class extends Component {

    // Variables del formulario
    public $name = '';
    public $sku = '';
    public $description = '';
    public $price = '';
    public $weight = ''; // <--- Nueva variable para el peso/litros
    public $category_id = '';
    public $unit_id = '';

    public function save()
    {
        // 1. Validar
        $validated = $this->validate([
            'name' => 'required|min:3',
            'sku' => 'required|unique:products,sku,NULL,id,company_id,' . Auth::user()->company_id,
            'price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0', // <--- Validación opcional
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        // 2. Crear Producto
        Product::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => $this->price,
            'weight' => $this->weight === '' ? null : $this->weight, // <--- Guardamos el peso
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ]);

        // 3. Redirigir
        return $this->redirect(route('products.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'categories' => Category::where('company_id', Auth::user()->company_id)->get(),
            'units' => Unit::where('company_id', Auth::user()->company_id)->get(),
        ];
    }
}; ?>

<div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Registrar Nuevo Producto</h2>
            <p class="text-sm text-gray-500">Ingresa los detalles básicos del ítem.</p>
        </div>

        <form wire:submit="save" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto *</label>
                <input wire:model="name" type="text" placeholder="Ej. Cemento Tolteca, Coca Cola 600ml"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Código) *</label>
                    <input wire:model="sku" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta *</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input wire:model="price" type="number" step="0.01"
                            class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                            placeholder="0.00">
                    </div>
                    @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría *</label>
                    <select wire:model="category_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad de Medida *</label>
                    <select wire:model="unit_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona (pza, kg, lt)...</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                    @error('unit_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Peso o Contenido <span class="text-gray-400 font-normal">(Opcional)</span>
                </label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <input wire:model="weight" type="number" step="0.01"
                        class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                        placeholder="Ej. 50 (para kg) o 1.5 (para litros)">
                </div>
                <p class="mt-1 text-xs text-gray-500">Úsalo solo si necesitas especificar cuánto pesa o mide (ej.
                    Constructora).</p>
                @error('weight') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t dark:border-neutral-700">
                <a href="{{ route('products.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancelar</a>

                <button type="submit" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center disabled:opacity-50">

                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>

                    <span wire:loading.remove wire:target="save">Guardar Producto</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>