<?php

use Livewire\Volt\Component;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public bool $creating = false;

    public function newCompany()
    {
        $this->creating = true;
    }

    public function cancelNewCompany()
    {
        $this->creating = false;
        $this->reset(['company_name', 'admin_name', 'admin_email', 'admin_password']);
    }

    // Propiedades para EDITAR
    public ?int $editing_id = null;
    public ?int $editing_admin_id = null;
    public string $edit_company_name = '';
    public string $edit_admin_name = '';
    public string $edit_admin_email = '';
    public string $edit_admin_password = ''; // Opcional al editar
    
    // Props para confirmaciones modales
    public ?int $confirming_company_delete = null;
    public ?int $confirming_admin_delete = null;

    public function save()
    {
        $this->validate([
            'company_name' => 'required|string|max:255|unique:companies,name',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:users,email',
            'admin_password' => ['required', Password::defaults()],
        ]);

        $company = Company::create([
            'name' => $this->company_name,
        ]);

        User::create([
            'name' => $this->admin_name,
            'email' => $this->admin_email,
            'password' => Hash::make($this->admin_password),
            'role' => 'company_admin',
            'company_id' => $company->id,
        ]);

        $this->reset(['company_name', 'admin_name', 'admin_email', 'admin_password']);
        session()->flash('success', 'Empresa y administrador creados correctamente.');
    }

    public function edit(int $id)
    {
        $company = Company::with(['users' => function ($query) {
            $query->where('role', 'company_admin')->first();
        }])->findOrFail($id);

        $this->editing_id = $company->id;
        $this->edit_company_name = $company->name;

        $admin = $company->users->first();

        if ($admin) {
            $this->editing_admin_id = $admin->id;
            $this->edit_admin_name = $admin->name;
            $this->edit_admin_email = $admin->email;
        } else {
            // Si por alguna razón no hay admin, preparamos para crear uno si es necesario
            $this->editing_admin_id = null;
            $this->edit_admin_name = '';
            $this->edit_admin_email = '';
        }
    }

    // Abre el modal para editar directamente un administrador
    public function editAdmin(int $adminId)
    {
        $admin = User::findOrFail($adminId);
        $this->editing_admin_id = $admin->id;
        $this->edit_admin_name = $admin->name;
        $this->edit_admin_email = $admin->email;
        $this->editing_id = $admin->company_id; // contexto: mostrar empresa relacionada en modal
    }

    // Elimina un administrador individual sin borrar la empresa
    public function deleteAdmin(int $adminId)
    {
        $admin = User::findOrFail($adminId);

        // Evitar que el propietario del SaaS o roles sensibles se borren accidentalmente
        if (in_array($admin->role, ['owner', 'super_admin'])) {
            session()->flash('success', 'No se puede eliminar un administrador con rol propietario.');
            return;
        }

        $admin->delete();

        // Si estábamos editando a este admin, limpiamos los campos del modal
        if ($this->editing_admin_id === $adminId) {
            $this->reset(['editing_admin_id', 'edit_admin_name', 'edit_admin_email', 'edit_admin_password']);
        }

        session()->flash('success', 'Administrador eliminado correctamente.');
    }

    // Mostrar modal de confirmación para eliminar empresa
    public function confirmDeleteCompany(int $companyId)
    {
        $this->confirming_company_delete = $companyId;
    }

    public function cancelDeleteCompany()
    {
        $this->confirming_company_delete = null;
    }

    // Mostrar modal de confirmación para eliminar admin
    public function confirmDeleteAdmin(int $adminId)
    {
        $this->confirming_admin_delete = $adminId;
    }

    public function cancelDeleteAdmin()
    {
        $this->confirming_admin_delete = null;
    }

    public function update()
    {
        if (!$this->editing_id) {
            return;
        }

        $rules = [
            'edit_company_name' => 'required|string|max:255|unique:companies,name,' . $this->editing_id,
            'edit_admin_name' => 'required|string|max:255',
            'edit_admin_password' => ['nullable', Password::defaults()],
        ];

        if ($this->editing_admin_id) {
            $rules['edit_admin_email'] = 'required|string|email|max:255|unique:users,email,' . $this->editing_admin_id;
        } else {
            $rules['edit_admin_email'] = 'required|string|email|max:255|unique:users,email';
        }

        $this->validate($rules);

        // Actualizar Empresa
        $company = Company::findOrFail($this->editing_id);
        $company->update(['name' => $this->edit_company_name]);

        // Actualizar o Crear Administrador
        $adminData = [
            'name' => $this->edit_admin_name,
            'email' => $this->edit_admin_email,
            'company_id' => $company->id,
            'role' => 'company_admin',
        ];

        if (!empty($this->edit_admin_password)) {
            $adminData['password'] = Hash::make($this->edit_admin_password);
        }

        User::updateOrCreate(['id' => $this->editing_admin_id], $adminData);

        $this->cancelEdit();
        session()->flash('success', 'Datos actualizados correctamente.');
    }

    public function cancelEdit()
    {
        $this->reset(['editing_id', 'editing_admin_id', 'edit_company_name', 'edit_admin_name', 'edit_admin_email', 'edit_admin_password']);
    }

    public function delete(int $id)
    {
        $company = Company::findOrFail($id);
        
        // Primero eliminamos todos los usuarios asociados a la empresa para evitar errores de llave foránea
        User::where('company_id', $company->id)->delete();
        
        // Luego eliminamos la empresa
        Log::info('Empresa eliminada por superadmin', ['company_id' => $id, 'by' => auth()->id() ?? null]);
        $company->delete();
        
        session()->flash('success', 'Empresa y sus usuarios eliminados permanentemente.');
    }

    public function with(): array
    {
        return [
            'companies' => Company::with('users')->latest()->get(),
        ];
    }
}; ?>

<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Administración Global de Empresas</h2>
            <button wire:click="newCompany" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Nueva Empresa
            </button>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800/50 dark:border dark:border-gray-700 rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($companies as $company)
                    <li class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ $company->name }}
                                    <span class="text-xs font-mono text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full border border-gray-200 dark:border-gray-600">ID: {{ $company->id }}</span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $company->users->count() }} {{ Str::plural('usuario registrado', $company->users->count()) }}
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <button wire:click="edit({{ $company->id }})" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg text-xs font-medium transition-colors border border-blue-200 dark:border-blue-800">
                                    ✏️ Empresa
                                </button>

                                @php $admin = $company->users->first(); @endphp
                                @if($admin)
                                    <button wire:click="editAdmin({{ $admin->id }})" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 rounded-lg text-xs font-medium transition-colors border border-indigo-200 dark:border-indigo-800">
                                        👤 Admin
                                    </button>

                                    <button wire:click="confirmDeleteAdmin({{ $admin->id }})" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg text-xs font-medium transition-colors border border-red-200 dark:border-red-800">
                                        🗑️ Admin
                                    </button>
                                @endif

                                <button 
                                    wire:click="confirmDeleteCompany({{ $company->id }})" 
                                    class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg text-xs font-medium transition-colors border border-red-200 dark:border-red-800"
                                >
                                    🗑️ Empresa
                                </button>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-12 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-lg font-semibold mb-2">No hay empresas creadas todavía.</p>
                        <p>Usa el botón "Nueva Empresa" para registrar la primera.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Modal: Crear Empresa -->
    @if($creating)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/90 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border shadow-xl dark:bg-gray-800 rounded-xl dark:border-gray-700">
                <div class="flex justify-between items-center pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nueva Empresa</h3>
                    <button wire:click="cancelNewCompany" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit="save">
                    <div class="space-y-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Empresa</label>
                            <input type="text" wire:model="company_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('company_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Administrador (Dueño)</label>
                            <input type="text" wire:model="admin_name" placeholder="Nombre completo" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('admin_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <input type="email" wire:model="admin_email" placeholder="Correo electrónico" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('admin_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <input type="text" wire:model="admin_password" placeholder="Contraseña temporal" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('admin_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Registrar Cliente
                        </button>
                        <button type="button" wire:click="cancelNewCompany" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: Editar Empresa y Administrador -->
    @if($editing_id)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/90 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border shadow-xl dark:bg-gray-800 rounded-xl dark:border-gray-700">
                <div class="flex justify-between items-center pb-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                        Editar Empresa y Administrador
                    </h3>
                    <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="update">
                    <div class="space-y-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Empresa</label>
                            <input type="text" wire:model="edit_company_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('edit_company_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Administrador</label>
                            <input type="text" wire:model="edit_admin_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('edit_admin_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                            <input type="email" wire:model="edit_admin_email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('edit_admin_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva Contraseña <span class="text-gray-400 font-normal">(Opcional)</span></label>
                            <input type="text" wire:model="edit_admin_password" placeholder="Dejar en blanco para no cambiar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:text-white">
                            @error('edit_admin_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Cambios
                        </button>
                        <button type="button" wire:click="cancelEdit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: Confirmar eliminación de Empresa -->
    @if($confirming_company_delete)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/90 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-50 flex items-center justify-center min-h-screen px-4 text-center">
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border shadow-xl dark:bg-gray-800 rounded-xl dark:border-gray-700">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Confirmar eliminación</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar esta empresa y todos sus usuarios? Esta acción no se puede deshacer.</p>
            </div>
            <div class="mt-6 sm:flex sm:flex-row-reverse">
                <button wire:click="delete({{ $confirming_company_delete }})" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                <button wire:click="cancelDeleteCompany" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal: Confirmar eliminación de Admin -->
    @if($confirming_admin_delete)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/90 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-50 flex items-center justify-center min-h-screen px-4 text-center">
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border shadow-xl dark:bg-gray-800 rounded-xl dark:border-gray-700">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Confirmar eliminación de administrador</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar este administrador? Esta acción no se puede deshacer.</p>
            </div>
            <div class="mt-6 sm:flex sm:flex-row-reverse">
                <button wire:click="deleteAdmin({{ $confirming_admin_delete }})" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Eliminar Admin</button>
                <button wire:click="cancelDeleteAdmin" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
            </div>
        </div>
    </div>
    @endif
</div>
