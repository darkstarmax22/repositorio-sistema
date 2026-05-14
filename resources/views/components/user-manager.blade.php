<?php

use App\Models\User;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    // Campos del formulario
    public $nombre = '';
    public $apellido = '';
    public $sexo = '';
    public $fecha_nacimiento = '';
    public $email = '';
    public $password = '';
    public $selectedRoles = []; // Ids de los roles seleccionados
    
    public $search = '';
    public $editingUserId = null;
    public $viewMode = 'list';

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|min:3|max:100',
            'apellido' => 'required|min:3|max:100',
            'sexo' => 'required|in:M,F',
            'fecha_nacimiento' => 'required|date|before:today',
            'email' => 'required|email|unique:persona,email,' . $this->editingUserId,
            'password' => $this->editingUserId ? 'nullable|min:8' : 'required|min:8',
            'selectedRoles' => 'array',
        ];
    }

    // Mensajes de error en español
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'apellido.required' => 'El apellido es obligatorio.',
        'sexo.required' => 'El sexo es obligatorio.',
        'sexo.in' => 'El valor seleccionado para sexo es inválido.',
        'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
        'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una válida.',
        'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El formato del correo es inválido.',
        'email.unique' => 'Este correo ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
    ];

    public function create()
    {
        $this->resetFields();
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingUserId = $id;
        $user = User::with('roles')->find($id);
        $this->nombre = $user->nombre;
        $this->apellido = $user->apellido;
        $this->sexo = $user->sexo;
        $this->fecha_nacimiento = $user->fecha_nacimiento;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->apellido = '';
        $this->sexo = '';
        $this->fecha_nacimiento = '';
        $this->email = '';
        $this->password = '';
        $this->selectedRoles = [];
        $this->editingUserId = null;
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function() {
            $userData = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'sexo' => $this->sexo,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'email' => $this->email,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            $user = User::updateOrCreate(
                ['id' => $this->editingUserId],
                $userData
            );

            // Sincronizar roles
            $user->roles()->sync($this->selectedRoles);
        });

        $this->viewMode = 'list';
        session()->flash('message', $this->editingUserId ? 'Usuario actualizado con éxito.' : 'Usuario creado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            session()->flash('error', 'No puedes eliminarte a ti mismo.');
            return;
        }
        User::find($id)->delete();
        session()->flash('message', 'Usuario eliminado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'users' => User::with('roles')->where(function($query) {
                        $query->where('nombre', 'like', '%' . $this->search . '%')
                              ->orWhere('apellido', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->latest()
                    ->paginate(10),
            'availableRoles' => Rol::whereIn('nombre', ['profesor', 'estudiante'])->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Usuarios</h2>

    <!-- Mensajes de Estado -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('error') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <!-- Acciones de Cabecera -->
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Usuario:</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="Término...">
            </div>
            
            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                Nuevo Usuario
            </button>
        </div>

        <!-- Tabla -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Directorio de Usuarios</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="30%">Usuario (Nombre, Apellido, Correo)</th>
                        <th padding="5" width="30%">Roles Asignados</th>
                        <th padding="5" width="20%">Sexo</th>
                        <th padding="5" width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($users as $user)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="middle">
                            <td align="left" style="padding: 5px;">
                                <span style="font-weight: bold;">{{ mb_strtoupper($user->nombre) }} {{ mb_strtoupper($user->apellido) }}</span>
                                <br>
                                <span style="font-size: 10px; color: #555;">{{ strtolower($user->email) }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($user->roles->isEmpty())
                                    <span style="font-size: 10px; color: #999;">Ninguno asignado</span>
                                @else
                                    @foreach($user->roles as $role)
                                        <span style="background-color: {{ $role->nombre == 'administrador' ? '#FFD700' : '#FFF' }}; border: 1px solid #CCC; padding: 1px 4px; font-size: 9px; font-weight: bold; margin: 1px; display: inline-block;">
                                            {{ mb_strtoupper($role->nombre) }}
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $user->sexo == 'M' ? 'MASCULINO' : 'FEMENINO' }}
                            </td>
                            <td align="center" style="padding: 5px;">
                                <a href="#" wire:click.prevent="edit({{ $user->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-right: 10px;">
                                    [Editar]
                                </a>
                                <a href="#" wire:click.prevent="delete({{ $user->id }})" wire:confirm="¿Desea eliminar este usuario permanentemente?" title="Eliminar" style="color: #FF0000; text-decoration: none;">
                                    [Eliminar]
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($users->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No se encontraron usuarios
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                {{ $users->links() }}
            </div>
        </fieldset>

    @else
        <!-- Formulario (Nueva Página) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingUserId ? 'Actualizar Información de Usuario' : 'Registro de Nuevo Usuario' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Datos Personales</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="25%"><b>Nombres:</b></td>
                            <td width="75%">
                                <input wire:model="nombre" type="text" style="width: 90%;">
                                <span class="obligatorio">*</span>
                                @error('nombre') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Apellidos:</b></td>
                            <td>
                                <input wire:model="apellido" type="text" style="width: 90%;">
                                <span class="obligatorio">*</span>
                                @error('apellido') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Sexo:</b></td>
                            <td>
                                <select wire:model="sexo" style="width: 50%;">
                                    <option value="">Seleccione...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <span class="obligatorio">*</span>
                                @error('sexo') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Fch. Nacimiento:</b></td>
                            <td>
                                <input wire:model="fecha_nacimiento" type="date" style="width: 50%;">
                                <span class="obligatorio">*</span>
                                @error('fecha_nacimiento') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Datos de Acceso</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="25%"><b>Correo Electrónico:</b></td>
                            <td width="75%">
                                <input wire:model="email" type="email" style="width: 90%;" placeholder="ejemplo@correo.com">
                                <span class="obligatorio">*</span>
                                @error('email') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Contraseña:</b></td>
                            <td>
                                <input wire:model="password" type="password" style="width: 90%;">
                                @if(!$editingUserId) <span class="obligatorio">*</span> @endif
                                @if($editingUserId) <br><span style="font-size: 10px; color: #555;">(Deje en blanco para mantener la actual)</span> @endif
                                @error('password') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Roles en el Sistema</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td valign="top">
                                @foreach($availableRoles as $role)
                                    <label style="margin-right: 15px; display: inline-block; margin-bottom: 5px;">
                                        <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}">
                                        {{ mb_strtoupper($role->nombre) }}
                                    </label>
                                @endforeach
                                @error('selectedRoles') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <div style="margin-top: 15px; font-size: 11px;">
                    Los campos con <span class="obligatorio">*</span> son obligatorios
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">{{ $editingUserId ? 'Guardar Cambios' : 'Ingresar Registro' }}</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
