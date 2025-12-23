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

    public Product $product;

    // Variables
    public $name = '';
    public $sku = '';
    public $price = '';
    public $cost = '';          // 👈 NUEVO
    public $min_stock = '';     // 👈 NUEVO
    public $weight = '';
    public $category_id = '';
    public $unit_id = '';

    public function mount(Product $product)
    {
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->product = $product;

        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->price = $product->price;
        $this->cost = $product->cost;          // 👈 Cargar Costo
        $this->min_stock = $product->min_stock; // 👈 Cargar Stock Min
        $this->weight = $product->weight;
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|min:3',
            'sku' => 'required|unique:products,sku,' . $this->product->id . ',id,company_id,' . Auth::user()->company_id,
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',      // 👈 Validar Costo
            'min_stock' => 'required|integer|min:0', // 👈 Validar Stock Min
            'weight' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        $this->product->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'cost' => $this->cost,          // 👈 Actualizar
            'min_stock' => $this->min_stock,// 👈 Actualizar
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

<div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-neutral-800 shadow rounded-lg p-6">

        <div class="mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Editar Producto</h2>
            <p class="text-sm text-gray-500">Modifica la información técnica del producto.</p>
        </div>

        <form wire:submit="save" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto
                        *</label>
                    <input wire:model="name" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Código) *</label>
                    <input wire:model="sku" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

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
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Economía e Inventario</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Costo Base *</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input wire:model="cost" type="number" step="0.01"
                                class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>
                        @error('cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta *</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input wire:model="price" type="number" step="0.01"
                                class="block w-full rounded-md border-gray-300 pl-7 focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>
                        @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alerta Stock
                            Mínimo</label>
                        <input wire:model="min_stock" type="number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        @error('min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad de Medida
                            *</label>
                        <select wire:model="unit_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                            <option value="">Selecciona...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                            @endforeach
                        </select>
                        @error('unit_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contenido /
                            Peso</label>
                        <input wire:model="weight" type="number" step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-gray-700">
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