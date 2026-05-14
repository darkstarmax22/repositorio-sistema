<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $lapso_id = '';
    public $selectedProject = null;
    public $isDetailsModalOpen = false;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingLapsoId() { $this->resetPage(); }

    public function openDetails($id)
    {
        $this->selectedProject = Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion', 'user', 'coordinacion', 'metodologia', 'tipo_investigacion', 'comunidad.estudiantes'])->find($id);
        $this->isDetailsModalOpen = true;
        $this->dispatch('refresh-icons');
    }

    public function closeDetails()
    {
        $this->isDetailsModalOpen = false;
        $this->selectedProject = null;
    }

    public function with()
    {
        $proyectos = Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion', 'user', 'comunidad'])
            ->where('estado_logico', true) // Solo proyectos habilitados
            ->where('estado_validacion', 'aprobado') // Solo proyectos aprobados
            ->when($this->search, function($query) {
                $query->where('titulo', 'like', '%' . $this->search . '%')
                      ->orWhere('resumen', 'like', '%' . $this->search . '%');
            })
            ->when($this->lapso_id, function($query) {
                $query->where('lapso_academico_id', $this->lapso_id);
            })
            ->latest()
            ->paginate(10);

        return [
            'proyectos' => $proyectos,
            'lapsos' => LapsoAcademico::where('estado_lapso', true)->get(),
        ];
    }
    public function render()
    {
        return view('livewire.project-search');
    }
}