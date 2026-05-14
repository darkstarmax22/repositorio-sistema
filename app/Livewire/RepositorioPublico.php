<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\Coordinacion;
use Livewire\Component;
use Livewire\WithPagination;

class RepositorioPublico extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCoordinacion = '';
    public $filterLapso = '';

    public function with()
    {
        $query = Proyecto::with(['coordinacion', 'tutor'])
            ->where('estado_validacion', 'aprobado');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('resumen', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterCoordinacion) {
            $query->where('coordinacion_id', $this->filterCoordinacion);
        }

        if ($this->filterLapso) {
            // Note: Currently filterLapso stores string like 2024-II, but we use lapso_academico_id in projects.
            // If the user selects a string, we might need a join or proper column name here.
            // Simplified handling for legacy update based on what the property is mapped to.
            // Since it may need adjusting if lapso is relation:
            // $query->whereHas('lapso_academico', function($q) { $q->where('nombre', $this->filterLapso); });
            // For now assuming we just match ID if they were IDs, but since they are strings, we match related:
            $query->whereHas('lapso_academico', function($q){
                $q->where('nombre', $this->filterLapso);
            });
        }

        return [
            'proyectos' => $query->latest()->paginate(9),
            'coordinaciones' => Coordinacion::all(),
        ];
    }
    public function render()
    {
        return view('livewire.repositorio-publico');
    }
}