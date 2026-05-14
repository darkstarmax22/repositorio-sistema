<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Coordinacion;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;

class CoordinacionCoordinatorManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCoordinacion = [];

    public function mount()
    {
        // Cargar los Coordinaciones configurados previamente para los coordinadores
        $coordinators = User::whereHas('roles', function($q){
            $q->where('rol.id', 2); // 2 = coordinador
        })->with(['roles' => function($q){
            $q->where('rol.id', 2);
        }])->get();
        
        foreach($coordinators as $c) {
            $pivotInfo = $c->roles->first()->pivot;
            if($pivotInfo && $pivotInfo->coordinacion_id) {
                $this->selectedCoordinacion[$c->id] = $pivotInfo->coordinacion_id;
            }
        }
    }

    public function toggleCoordinator($userId)
    {
        $user = User::find($userId);
        $coordinatorRoleId = 2; // COORDINADOR_Coordinación_TEMP_PLACEHOLDER
        
        if ($user->roles->contains($coordinatorRoleId)) {
            $user->roles()->detach($coordinatorRoleId);
            $this->selectedCoordinacion[$userId] = '';
            session()->flash('message', "Rol de Coordinador revocado de {$user->nombre}.");
        } else {
            if(empty($this->selectedCoordinacion[$userId])) {
                session()->flash('message_error', "Debe seleccionar primero a qué Coordinación será asignado para este docente.");
                return;
            }

            $user->roles()->attach($coordinatorRoleId, [
                'coordinacion_id' => $this->selectedCoordinacion[$userId],
                'estado_logico' => true,
                'id_asignador' => auth()->id() ?? 1
            ]);
            session()->flash('message', "Rol de Coordinador asignado a {$user->nombre}.");
        }
        
        $this->dispatch('refresh-icons');
    }

    public function updateCoordinatorCoordinacion($userId)
    {
        if (empty($this->selectedCoordinacion[$userId])) {
            session()->flash('message_error', 'Debe seleccionar una Coordinación válida antes de actualizar.');
            return;
        }

        $user = User::findOrFail($userId);
        $coordinatorRoleId = 2;

        if ($user->roles->contains($coordinatorRoleId)) {
            $user->roles()->updateExistingPivot($coordinatorRoleId, [
                'coordinacion_id' => $this->selectedCoordinacion[$userId],
                'id_asignador' => auth()->id() ?? 1
            ]);
            session()->flash('message', "Coordinación actualizado correctamente para el Coordinador {$user->nombre}.");
        }
    }

    public function with()
    {
        // Buscamos usuarios que tengan el rol de profesor (ID 4)
        $users = User::with(['roles' => function($query) {
            $query->whereIn('rol.id', [2, 4]);
        }])
        ->whereHas('roles', function($query) {
            $query->where('rol.id', 4); // Profesor
        })
        ->where(function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(10);

        return [
            'users' => $users,
            'coordinaciones' => Coordinacion::where('activo', true)->get(),
        ];
    }
    public function render()
    {
        return view('livewire.coordinacion-coordinator-manager');
    }
}