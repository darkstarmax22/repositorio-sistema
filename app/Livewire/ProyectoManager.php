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
use Livewire\WithFileUploads;

class ProyectoManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Form fields
    public $titulo = '';
    public $resumen = '';
    public $fecha_subida = '';
    public $asignacion_ct = false;
    public $calificacion = '';
    public $fecha_aprobacion = '';
    public $linea_investigacion_id = '';
    public $metodologia_id = '';
    public $tipo_publicacion_id = '';
    public $tipo_investigacion_id = '';
    public $lapso_academico_id = '';
    public $coordinacion_id = '';
    public $comunidad_id = '';
    public $trayecto = '';
    public $archivo;
    public $current_archivo_path;

    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    public function rules()
    {
        return [
            'titulo' => 'required|min:5|max:255',
            'resumen' => 'required|min:10',
            'fecha_subida' => 'required|date',
            'asignacion_ct' => 'boolean',
            'calificacion' => 'required|integer|min:1|max:20',
            'fecha_aprobacion' => 'required|date',
            'linea_investigacion_id' => 'required|exists:linea_investigacions,id',
            'metodologia_id' => 'required|exists:metodologia_investigacions,id',
            'tipo_publicacion_id' => 'required|exists:tipo_publicacions,id',
            'tipo_investigacion_id' => 'required|exists:tipo_investigacions,id',
            'lapso_academico_id' => 'required|exists:lapso_academicos,id',
            'coordinacion_id' => 'required|exists:coordinaciones,id',
            'comunidad_id' => 'required|exists:comunidades,id',
            'trayecto' => 'required|string|max:100',
            'archivo' => ($this->editingId && !$this->archivo) ? 'nullable' : 'required|mimes:pdf|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título del proyecto es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'resumen.required' => 'El resumen es obligatorio.',
            'resumen.min' => 'El resumen debe tener al menos 10 caracteres.',
            'fecha_subida.required' => 'La fecha de subida es obligatoria.',
            'calificacion.required' => 'La calificación es obligatoria.',
            'calificacion.integer' => 'La calificación debe ser un número entero.',
            'calificacion.min' => 'La calificación mínima es 1.',
            'calificacion.max' => 'La calificación máxima es 20.',
            'fecha_aprobacion.required' => 'La fecha de aprobación es obligatoria.',
            'linea_investigacion_id.required' => 'Debe seleccionar una línea de investigación.',
            'metodologia_id.required' => 'Debe seleccionar una metodología.',
            'tipo_publicacion_id.required' => 'Debe seleccionar un tipo de publicación.',
            'tipo_investigacion_id.required' => 'Debe seleccionar un tipo de investigación.',
            'lapso_academico_id.required' => 'Debe seleccionar un lapso académico.',
            'coordinacion_id.required' => 'Debe seleccionar una Coordinación.',
            'comunidad_id.required' => 'Debe elegir una Comunidad.',
            'trayecto.required' => 'El trayecto es obligatorio.',
            'trayecto.in' => 'El trayecto seleccionado no es válido.',
            'archivo.required' => 'El archivo PDF es obligatorio.',
            'archivo.mimes' => 'El archivo debe ser un formato PDF válido.',
            'archivo.max' => 'El archivo no debe pesar más de 10MB.',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->fecha_subida = now()->format('Y-m-d');
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingId = $id;
        $item = Proyecto::find($id);
        $this->titulo = $item->titulo;
        $this->resumen = $item->resumen;
        $this->fecha_subida = $item->fecha_subida->format('Y-m-d');
        $this->asignacion_ct = (bool)$item->asignacion_ct;
        $this->calificacion = $item->calificacion;
        $this->fecha_aprobacion = $item->fecha_aprobacion ? $item->fecha_aprobacion->format('Y-m-d') : '';
        $this->linea_investigacion_id = $item->linea_investigacion_id;
        $this->metodologia_id = $item->metodologia_id;
        $this->tipo_publicacion_id = $item->tipo_publicacion_id;
        $this->tipo_investigacion_id = $item->tipo_investigacion_id;
        $this->lapso_academico_id = $item->lapso_academico_id;
        $this->coordinacion_id = $item->coordinacion_id;
        $this->comunidad_id = $item->comunidad_id;
        $this->trayecto = $item->trayecto;
        $this->current_archivo_path = $item->archivo_path;
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->titulo = '';
        $this->resumen = '';
        $this->fecha_subida = '';
        $this->asignacion_ct = false;
        $this->calificacion = '';
        $this->fecha_aprobacion = '';
        $this->linea_investigacion_id = '';
        $this->metodologia_id = '';
        $this->tipo_publicacion_id = '';
        $this->tipo_investigacion_id = '';
        $this->lapso_academico_id = '';
        $this->coordinacion_id = '';
        $this->comunidad_id = '';
        $this->trayecto = '';
        $this->archivo = null;
        $this->current_archivo_path = null;
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        Proyecto::updateOrCreate(
            ['id' => $this->editingId],
            [
                'titulo' => $this->titulo,
                'resumen' => $this->resumen,
                'fecha_subida' => $this->fecha_subida,
                'asignacion_ct' => $this->asignacion_ct,
                'calificacion' => $this->calificacion ?: null,
                'fecha_aprobacion' => $this->fecha_aprobacion ?: null,
                'linea_investigacion_id' => $this->linea_investigacion_id,
                'metodologia_id' => $this->metodologia_id,
                'tipo_publicacion_id' => $this->tipo_publicacion_id,
                'tipo_investigacion_id' => $this->tipo_investigacion_id,
                'lapso_academico_id' => $this->lapso_academico_id,
                'coordinacion_id' => $this->coordinacion_id,
                'comunidad_id' => $this->comunidad_id,
                'trayecto' => $this->trayecto,
                'archivo_path' => $this->archivo ? $this->archivo->store('proyectos', 'public') : $this->current_archivo_path,
                'estado_validacion' => 'pendiente',
                'persona_id' => $this->editingId ? Proyecto::find($this->editingId)->persona_id : auth()->id(),
            ]
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Proyecto actualizado con éxito.' : 'Proyecto registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = Proyecto::find($id);
        $item->update(['estado_logico' => !$item->estado_logico]);
        session()->flash('message', 'Estado del proyecto actualizado.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Proyecto::find($id)->delete();
        session()->flash('message', 'Proyecto eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $canRegister = false;
        $comunidades_disp = collect();

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('administrador')) {
                 $canRegister = true;
                 $comunidades_disp = \App\Models\Comunidad::orderBy('nombre')->get();
            } else {
                 $comunidades_disp = $user->comunidades()->wherePivot('role_id', 1)->get(); // Role 1 = lider
                 if($comunidades_disp->count() > 0) {
                      $canRegister = true;
                 }
            }
        }

        return [
            'canRegister' => $canRegister,
            'comunidades_disp' => $comunidades_disp,
            'proyectos' => Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion', 'validador', 'user', 'coordinacion', 'comunidad.estudiantes'])
                        ->where('titulo', 'like', '%' . $this->search . '%')
                        ->when(!auth()->user()->hasRole('administrador'), function($query) {
                            $query->where('persona_id', auth()->id());
                        })
                        ->latest()
                        ->paginate(10),
            'lineas' => LineaInvestigacion::where('activo', true)->get(),
            'metodologias' => MetodologiaInvestigacion::where('estado_logico', true)->get(),
            'tipos_publicacion' => TipoPublicacion::where('estado_logico', true)->get(),
            'tipos_investigacion' => TipoInvestigacion::where('estado_logico', true)->get(),
            'lapsos' => LapsoAcademico::where('estado_lapso', true)->get(),
            'coordinaciones' => Coordinacion::where('activo', true)->get(),
        ];
    }
    public function render()
    {
        return view('livewire.proyecto-manager');
    }
}