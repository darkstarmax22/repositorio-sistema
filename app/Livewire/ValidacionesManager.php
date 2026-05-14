<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Models\Coordinacion;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\TipoPublicacion;
use App\Models\TipoInvestigacion;
use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

class ValidacionesManager extends Component
{
    use WithPagination;

    public $search = '';
    public $motivo_rechazo = '';
    public $selectedProjectId = null;
    public $selectedProject = null;
    public $viewMode = 'list';

    public function approve($id)
    {
        $proyecto = Proyecto::find($id);
        $proyecto->update([
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
            'validador_id' => auth()->id()
        ]);

        session()->flash('message', 'Proyecto aprobado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function openRejectModal($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function openDetails($id)
    {
        $this->selectedProject = Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion', 'user', 'coordinacion', 'metodologia', 'tipo_investigacion'])->find($id);
        $this->viewMode = 'details';
        $this->dispatch('refresh-icons');
    }

    public function backToList()
    {
        $this->viewMode = 'list';
        $this->selectedProjectId = null;
        $this->selectedProject = null;
        $this->motivo_rechazo = '';
    }

    public function reject()
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10'
        ]);

        $proyecto = Proyecto::find($this->selectedProjectId);
        $proyecto->update([
            'estado_validacion' => 'rechazado',
            'motivo_rechazo' => $this->motivo_rechazo,
            'estado_logico' => false,
            'validador_id' => auth()->id()
        ]);

        $this->backToList();
        session()->flash('message', 'Proyecto rechazado.');
        $this->dispatch('refresh-icons');
    }

    public function approveFromDetails($id)
    {
        $this->approve($id);
        $this->backToList();
    }

    public function rejectFromDetails($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function with()
    {
        return [
            'proyectos' => Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion'])
                ->where('estado_validacion', 'pendiente')
                ->where('titulo', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10)
        ];
    }
    public function render()
    {
        return view('livewire.validaciones-manager');
    }
}