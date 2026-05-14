<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Coordinacion;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectProfessorManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedYear = [];
    public $activeAdminCoordinacion = null;

    public function mount()
    {
        if (auth()->check()) {
            $currentUser = auth()->user();
            if ($currentUser->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
                $coordRole = $currentUser->roles->where('id', 2)->first();
                if($coordRole && $coordRole->pivot) {
                    $this->activeAdminCoordinacion = $coordRole->pivot->coordinacion_id;
                }
            }
        }

        // Cargar años pre-asignados a profesores de proyecto
        $professors = User::whereHas('roles', function($q) {
            $q->where('rol.id', 3);
        })->with(['roles' => function($q) {
            $q->where('rol.id', 3);
        }])->get();

        foreach($professors as $prof) {
            $pivotInfo = $prof->roles->first()->pivot;
            if($pivotInfo && $pivotInfo->anio) {
                $this->selectedYear[$prof->id] = $pivotInfo->anio;
            }
        }
    }

    public function toggleProjectProfessor($userId)
    {
        $user = User::find($userId);
        $projectProfessorRoleId = 3; // profesor proyecto

        if ($user->roles->contains('id', $projectProfessorRoleId)) {
            // Verificación de Jurisdicción para Revocar
            $roleData = $user->roles->where('id', $projectProfessorRoleId)->first();
            if ($roleData && $roleData->pivot->coordinacion_id && !auth()->user()->hasRole('administrador')) {
                if ($this->activeAdminCoordinacion != $roleData->pivot->coordinacion_id) {
                    session()->flash('message_error', "Acceso denegado: Este profesor corresponde a otro Coordinación y no posee jurisdicción para revocarlo.");
                    return;
                }
            }

            $user->roles()->detach($projectProfessorRoleId);
            $this->selectedYear[$userId] = '';
            session()->flash('message', "Rol de Profesor de Proyecto quitado a {$user->nombre}.");
        } else {
            if(empty($this->selectedYear[$userId])) {
                session()->flash('message_error', "Debe seleccionar un Año asignado para el proyecto.");
                return;
            }

            $user->roles()->attach($projectProfessorRoleId, [
                'id_asignador' => auth()->id(),
                'anio' => $this->selectedYear[$userId],
                'coordinacion_id' => $this->activeAdminCoordinacion,
                'estado_logico' => true
            ]);
            session()->flash('message', "Rol de Profesor de Proyecto asignado a {$user->nombre}.");
        }
        
        $this->dispatch('refresh-icons');
    }


    public function with()
    {
        // Buscamos usuarios que tengan el rol de profesor (ID 4)
        $users = User::with(['roles' => function($query) {
            $query->whereIn('rol.id', [3, 4]); // 3=ProfesorProyecto, 4=Profesor
        }])
        ->whereHas('roles', function($query) {
            $query->where('rol.id', 4); // Profesor base
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
        ];
    }
    public function render()
    {
        return view('livewire.project-professor-manager');
    }
}