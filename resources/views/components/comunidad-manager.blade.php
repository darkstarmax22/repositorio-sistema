<?php

use App\Models\Comunidad;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
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

    // Grupo de proyecto
    public $selectedStudentId = '';
    public $selectedProjectRoleId = '';
    public $estudiantesSeleccionados = []; // list of ['persona_id', 'nombre_completo', 'role_id', 'role_name']

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
        $this->reset(['editingId', 'nombre', 'direccion', 'rif', 'correo', 'numero_telefono', 'estudiantesSeleccionados']);
        $this->resetValidation();
        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $comunidad = Comunidad::with('estudiantes')->findOrFail($id);
        $this->editingId = $id;
        $this->nombre = $comunidad->nombre;
        $this->direccion = $comunidad->direccion;
        $this->rif = $comunidad->rif;
        $this->correo = $comunidad->correo;
        $this->numero_telefono = $comunidad->numero_telefono;
        
        $this->estudiantesSeleccionados = $comunidad->estudiantes->map(function($e) {
            $rol = Role::find($e->pivot->role_id);
            return [
                'persona_id' => $e->id,
                'nombre_completo' => $e->nombre . ' ' . $e->apellido,
                'role_id' => $e->pivot->role_id,
                'role_name' => $rol ? $rol->tipo_de_rol : 'Desconocido'
            ];
        })->toArray();

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
        ];

        if (!$this->editingId && auth()->user()->hasRole('profesor proyecto')) {
            $roleData = auth()->user()->roles()->where('nombre', 'profesor proyecto')->first();
            if ($roleData) {
                $datosPayload['anio'] = $roleData->pivot->anio;
                $datosPayload['profesor_id'] = auth()->id();
                $datosPayload['coordinacion_id'] = $roleData->pivot->coordinacion_id;
            }
        }

        $comunidad = Comunidad::updateOrCreate(
            ['id' => $this->editingId],
            $datosPayload
        );

        $syncData = [];
        foreach($this->estudiantesSeleccionados as $est) {
            $syncData[$est['persona_id']] = ['role_id' => $est['role_id']];
        }
        $comunidad->estudiantes()->sync($syncData);

        session()->flash('message', 'Comunidad guardada exitosamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Comunidad::findOrFail($id)->delete();
        session()->flash('message', 'Comunidad eliminada permanentemente.');
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }



    public function addStudentToGroup()
    {
        $this->validate([
            'selectedStudentId' => 'required',
            'selectedProjectRoleId' => 'required'
        ], [
            'selectedStudentId.required' => 'Seleccione un estudiante.',
            'selectedProjectRoleId.required' => 'Seleccione un rol de proyecto.',
        ]);

        foreach($this->estudiantesSeleccionados as $es) {
            if ($es['persona_id'] == $this->selectedStudentId) {
                session()->flash('modal_error', 'El estudiante ya fue agregado.');
                return;
            }
        }

        $estu = User::find($this->selectedStudentId);
        $rol = Role::find($this->selectedProjectRoleId);

        if($estu && $rol) {
            $this->estudiantesSeleccionados[] = [
                'persona_id' => $estu->id,
                'nombre_completo' => $estu->nombre . ' ' . $estu->apellido,
                'role_id' => $rol->id,
                'role_name' => $rol->tipo_de_rol
            ];
        }
        
        $this->reset(['selectedStudentId', 'selectedProjectRoleId']);
        $this->dispatch('refresh-icons');
    }

    public function removeStudent($persona_id)
    {
        $this->estudiantesSeleccionados = array_filter($this->estudiantesSeleccionados, function($es) use ($persona_id) {
            return $es['persona_id'] != $persona_id;
        });
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
                                              $q->whereHas('estudiantes', function($q2) {
                                                  $q2->where('persona_id', auth()->id());
                                              })->orWhere('profesor_id', auth()->id());

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
            'availableStudents' => User::whereHas('roles', function($q) { 
                $q->where('nombre', 'estudiante')
                  ->where('estado_logico', true); 
            })->get(),
            'projectRoles' => Role::all(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Comunidades y Grupos de Proyecto</h2>

    @if(isset($alertaAsesor) && $alertaAsesor)
        <div style="background-color: #f8d7da; color: #721c24; border: 2px dashed #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:13px; font-weight:bold; text-align:center; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1)); cursor: default;">
            ⚠ ¡ATENCIÓN! La COORDINACION_DE_Coordinación_TITLE_TEMP_PLACEHOLDER solicita urgentemente el registro y actualización de sus Comunidades y Grupos de Proyecto.
        </div>
    @endif

    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y Listado</legend>
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="30%"><b>Buscar Sector/RIF:</b></td>
                    <td width="50%">
                        <input wire:model.live="search" type="text" style="width: 90%; padding: 3px;" placeholder="...">
                    </td>
                    <td width="30%" align="right">
                        @if(isset($alertaCoordinador) && auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'))
                            <button wire:click="toggleAlertaCoordinacion" class="boton" style="border: 2px solid {{ $alertaCoordinador ? '#FF0000' : '#4CAF50' }}; border-radius: 4px; padding: 4px 10px; font-weight: bold; background-color: {{ $alertaCoordinador ? '#FFdddd' : '#ddFFdd' }}; color: #000; height: auto; min-height: 26px; white-space: normal; margin-bottom: 4px;">
                                {{ $alertaCoordinador ? '🔕 Desactivar Alerta a Profesores' : '🔔 Enviar Alerta a Profesores' }}
                            </button>
                        @endif

                        @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: auto; min-height: 26px; white-space: nowrap;">
                                Registrar Nueva Comunidad
                            </button>
                        @endif
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N°</th>
                        <th width="30%">Nombre de la Comunidad</th>
                        <th width="15%">RIF</th>
                        <th width="30%">Contacto (Correo / Tlf)</th>
                        <th width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($comunidades as $index => $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="left">
                                <span style="font-weight: bold; font-size: 11px;">{{ $c->nombre }}</span><br>
                                @if($c->anio || $c->coordinacion_id)
                                    <span style="font-weight: bold; font-size: 9px; color: #8b0000; display:inline-block; margin-bottom:2px;">
                                        [{{ mb_strtoupper($c->coordinacion->nombre ?? 'N/A') }} - {{ mb_strtoupper($c->anio) }}]
                                    </span><br>
                                @endif
                                @if($c->profesor)
                                    <span style="font-size: 9px; color: #000; display:inline-block; margin-bottom: 2px;"><b>Prof. Asesor:</b> {{ mb_strtoupper($c->profesor->nombre . ' ' . $c->profesor->apellido) }}</span><br>
                                @endif
                                <span style="font-size:9px; color:#555;">{{ $c->direccion }}</span>
                            </td>
                            <td align="center">{{ $c->rif ?? 'N/A' }}</td>
                            <td align="center">
                                {{ $c->correo }} <br>
                                <span style="font-weight:bold;">{{ $c->numero_telefono }}</span>
                            </td>
                            <td align="center">
                                @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                                    <a href="#" wire:click.prevent="edit({{ $c->id }})" style="color: #0000EE; text-decoration: none;">[Editar]</a> &nbsp;
                                    <a href="#" wire:click.prevent="delete({{ $c->id }})" wire:confirm="¿Desea eliminar esto permanentemente?" style="color: #FF0000; text-decoration: none;">[Eliminar]</a>
                                @else
                                    <span style="color: #888;">[Solo Lectura]</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($comunidades->isEmpty())
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay comunidades registradas.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $comunidades->links() }}</div>
        </fieldset>

    @else
        <!-- FORMULARIO DE REGISTRO/EDICION -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Modificar Información de Comunidad' : 'Registrar Nueva Comunidad' }}
            </legend>

            <!-- Datos Base -->
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="20%"><b>Nombre de la Comunidad:</b></td>
                    <td width="30%">
                        <input wire:model="nombre" type="text" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('nombre') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="20%"><b>Documento RIF:</b></td>
                    <td width="30%">
                        <input wire:model="rif" type="text" style="width: 80%;" placeholder="J-12345678-9"> <span style="color:red; font-weight:bold;">*</span>
                        @error('rif') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <td width="20%"><b>Correo Electrónico:</b></td>
                    <td width="30%">
                        <input wire:model="correo" type="email" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('correo') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="20%"><b>Número Teléfono:</b></td>
                    <td width="30%">
                        <input wire:model="numero_telefono" type="text" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('numero_telefono') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <td width="20%" valign="top"><b>Dirección Completa:</b></td>
                    <td colspan="3">
                        <textarea wire:model="direccion" style="width: 90%; height:40px;"></textarea>
                    </td>
                </tr>
            </table>

            <hr style="border: 1px dotted #ccc; margin:15px 0;">

            <!-- GRUPO DE PROYECTO -->
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Grupo de Proyecto (Integrantes)</legend>
                
                @if (session()->has('modal_error'))
                    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 5px; margin-bottom: 10px; font-size: 11px;">
                        {{ session('modal_error') }}
                    </div>
                @endif

                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px; background-color: #e9ecef; border: 1px solid #CCC; padding: 5px;">
                    <tr>
                        <td width="30%"><b>Elegir Estudiante:</b><br>
                            <select wire:model="selectedStudentId" style="width: 95%;">
                                <option value="">Seleccione uno...</option>
                                @foreach($availableStudents as $as)
                                    <option value="{{ $as->id }}">{{ mb_strtoupper($as->nombre . ' ' . $as->apellido) }}</option>
                                @endforeach
                            </select>
                            @error('selectedStudentId') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="30%"><b>Rol en el Proyecto:</b><br>
                            <select wire:model="selectedProjectRoleId" style="width: 95%;">
                                <option value="">Seleccione uno...</option>
                                @foreach($projectRoles as $pr)
                                    <option value="{{ $pr->id }}">{{ ucfirst($pr->tipo_de_rol) }}</option>
                                @endforeach
                            </select>
                            @error('selectedProjectRoleId') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="40%" valign="bottom">
                            <button wire:click="addStudentToGroup" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 15px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 26px; margin-bottom: 2px;">
                                + Agregar Integrante
                            </button>
                        </td>
                    </tr>
                </table>

                <table width="100%" border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; margin-top: 10px; font-size: 11px;">
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold; text-align: center;">
                        <td width="50%">Estudiante</td>
                        <td width="30%">Rol de Proyecto</td>
                        <td width="20%">Acción</td>
                    </tr>
                    @foreach($estudiantesSeleccionados as $est)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};">
                            <td align="left" style="padding-left:10px; font-weight:bold;">{{ mb_strtoupper($est['nombre_completo']) }}</td>
                            <td align="center">
                                <span style="font-weight: bold;">{{ mb_strtoupper($est['role_name']) }}</span>
                            </td>
                            <td align="center">
                                <a href="#" wire:click.prevent="removeStudent('{{ $est['persona_id'] }}')" style="color: #FF0000; text-decoration: none;">[Quitar]</a>
                            </td>
                        </tr>
                    @endforeach
                    @if(empty($estudiantesSeleccionados))
                        <tr>
                            <td colspan="3" align="center" style="padding: 10px; background-color: #FFFFFF;">
                                No hay integrantes asignados a este grupo.
                            </td>
                        </tr>
                    @endif
                </table>
            </fieldset>

            <br>
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
                <tr>
                    <td align="center">
                        <button wire:click="save" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 20px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 30px;">
                            {{ $editingId ? 'Actualizar Información' : 'Registrar Comunidad' }}
                        </button>
                        <button wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 30px; margin-left: 10px;">
                            Cancelar
                        </button>
                    </td>
                </tr>
            </table>
        </fieldset>
    @endif


</div>