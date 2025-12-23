<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.app', ['title' => 'Editar Producto'])]
    class extends Component {

    public Product $product; // El producto que estamos editando

    // Variables del formulario
    public $name = '';
    public $sku = '';
    public $price = '';
    public $weight = '';
    public $category_id = '';
    public $unit_id = '';

    // Cargar datos al iniciar
    public function mount(Product $product)
    {
        // Seguridad: Verificar que el producto sea de mi empresa
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->product = $product;

        // Llenamos el formulario con los datos actuales
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->weight = $product->weight;
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|min:3',
            // En SKU ignoramos el ID actual para que no nos de error de "ya existe" si no lo cambiamos
            'sku' => 'required|unique:products,sku,' . $this->product->id . ',id,company_id,' . Auth::user()->company_id,
            'price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        // Actualizar
        $this->product->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'weight' => $this->weight === '' ? null : $this->weight,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ]);

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

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Editar Producto</h2>
                <p class="text-sm text-gray-500">Modifica los detalles del ítem.</p>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto</label>
                <input wire:model="name" type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Código)</label>
                    <input wire:model="sku" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta</label>
                    <input wire:model="price" type="number" step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad</label>
                    <select wire:model="unit_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        <option value="">Selecciona...</option>
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
                <input wire:model="weight" type="number" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                @error('weight') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t dark:border-neutral-700">
                <a href="{{ route('products.index') }}"
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