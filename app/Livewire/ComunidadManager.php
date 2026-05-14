<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class ComunidadManager extends Component
{
    use WithPagination;

    public $search = '';
    public $viewMode = 'list';
    public $editingId = null;

    // Comunidad fields
    public $nombre = '';
    public $direccion = '';
    public $rif = '';
    public $correo = '';
    public $numero_telefono = '';
    public $estado = 'Activa';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'rif' => 'required|string|max:50',
        'direccion' => 'nullable|string',
        'correo' => 'required|email|max:150',
        'numero_telefono' => 'required|string|max:20',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la comunidad es obligatorio',
        'rif.required' => 'El RIF es obligatorio',
        'correo.required' => 'El correo es obligatorio',
        'numero_telefono.required' => 'El teléfono es obligatorio',
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->reset(['editingId', 'nombre', 'direccion', 'rif', 'correo', 'numero_telefono', 'estado']);
        $this->resetValidation();
        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $comunidad = Comunidad::findOrFail($id);
        $this->editingId = $id;
        $this->nombre = $comunidad->nombre;
        $this->direccion = $comunidad->direccion;
        $this->rif = $comunidad->rif;
        $this->correo = $comunidad->correo;
        $this->numero_telefono = $comunidad->numero_telefono;
        $this->estado = $comunidad->estado ?? 'Activa';

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function save()
    {
        $this->validate();

        $datosPayload = [
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'numero_telefono' => $this->numero_telefono,
            'estado' => $this->estado,
        ];

        if (!$this->editingId && auth()->user()->hasRole('profesor proyecto')) {
            $roleData = auth()->user()->roles()->where('nombre', 'profesor proyecto')->first();
            if ($roleData) {
                $datosPayload['anio'] = $roleData->pivot->anio;
                $datosPayload['profesor_id'] = auth()->id();
                $datosPayload['coordinacion_id'] = $roleData->pivot->coordinacion_id;
            }
        }

        Comunidad::updateOrCreate(
            ['id' => $this->editingId],
            $datosPayload
        );

        session()->flash('message', 'Comunidad guardada exitosamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function toggleEstado($id)
    {
        $comunidad = Comunidad::findOrFail($id);
        if ($comunidad->estado === 'Clausurada') {
            $comunidad->estado = 'Activa';
            session()->flash('message', 'Comunidad reactivada exitosamente.');
        } else {
            $comunidad->estado = 'Clausurada';
            session()->flash('message', 'Comunidad clausurada exitosamente.');
        }
        $comunidad->save();
    }

    public function toggleAlertaCoordinacion()
    {
        $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
        if ($coordRole && $coordRole->pivot->coordinacion_id) {
            $coordinacion = \App\Models\Coordinacion::find($coordRole->pivot->coordinacion_id);
            if ($coordinacion) {
                $coordinacion->alertar_comunidades = !$coordinacion->alertar_comunidades;
                $coordinacion->save();
            }
        }
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $alertaAsesor = false;
        $alertaCoordinador = false;

        if (auth()->user()->hasRole('profesor proyecto')) {
            $profRole = auth()->user()->roles()->where('nombre', 'profesor proyecto')->first();
            if ($profRole && $profRole->pivot->coordinacion_id) {
                $coordinacion = \App\Models\Coordinacion::find($profRole->pivot->coordinacion_id);
                $alertaAsesor = $coordinacion ? $coordinacion->alertar_comunidades : false;
            }
        }

        if (auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
            $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
            if ($coordRole && $coordRole->pivot->coordinacion_id) {
                $coordinacion = \App\Models\Coordinacion::find($coordRole->pivot->coordinacion_id);
                $alertaCoordinador = $coordinacion ? $coordinacion->alertar_comunidades : false;
            }
        }

        return [
            'alertaAsesor' => $alertaAsesor,
            'alertaCoordinador' => $alertaCoordinador,
            'comunidades' => Comunidad::with(['coordinacion', 'profesor'])->where(function($q) {
                                          $q->where('nombre', 'like', "%{$this->search}%")
                                            ->orWhere('rif', 'like', "%{$this->search}%");
                                      })
                                      ->when(!auth()->user()->hasRole('administrador'), function($query) {
                                          $query->where(function($q) {
                                              $q->where('profesor_id', auth()->id());

                                              if (auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
                                                  $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
                                                  if ($coordRole && $coordRole->pivot->coordinacion_id) {
                                                      $q->orWhere('coordinacion_id', $coordRole->pivot->coordinacion_id);
                                                  }
                                              }
                                          });
                                      })
                                      ->latest()
                                      ->paginate(10),
        ];
    }
    public function render()
    {
        return view('livewire.comunidad-manager');
    }
}