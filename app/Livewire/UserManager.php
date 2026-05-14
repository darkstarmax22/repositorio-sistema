<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManager extends Component
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
    public function render()
    {
        return view('livewire.user-manager');
    }
}