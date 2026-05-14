<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Proyectos</h2>

    <!-- Notification -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Proyecto (Título):</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>
            
            @if($canRegister)
            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                Registrar Nuevo Proyecto
            </button>
            @endif
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Proyectos Institucionales</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="25%">Título del Proyecto</th>
                        <th padding="5" width="20%">Comunidad / Línea Inv.</th>
                        <th padding="5" width="15%">Lapso / Coordinación</th>
                        <th padding="5" width="15%">Validación / C&T</th>
                        <th padding="5" width="10%">Estado</th>
                        <th padding="5" width="15%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($proyectos as $p)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$p->estado_logico ? 'color: #888;' : 'color: #000;' }}" valign="top">
                            <td align="left" style="padding: 5px; font-weight: bold;">
                                {{ $p->titulo }}
                                <br>
                                <span style="font-size: 9px; font-weight: normal; color: {{ !$p->estado_logico ? '#888' : '#555' }};">Subido: {{ $p->fecha_subida->format('d/m/Y') }}</span>
                                @if($p->archivo_path)
                                    <br><a href="{{ Storage::url($p->archivo_path) }}" target="_blank" style="color: #0000EE; font-size: 10px;">[Ver Documento PDF]</a>
                                @endif
                            </td>
                            <td align="left" style="padding: 5px;">
                                <span style="font-size: 11px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#8b0000' }};">Comunidad: {{ $p->comunidad->nombre ?? 'N/A' }}</span>
                                <br>
                                <span style="font-size: 9px; color: #333;">
                                    @if($p->comunidad && $p->comunidad->estudiantes->count() > 0)
                                        <b>Integrantes:</b> 
                                        @foreach($p->comunidad->estudiantes as $estu)
                                            {{ $estu->nombre }} {{ mb_substr($estu->apellido, 0, 1) }}. ({{ $estu->pivot->role_id == 1 ? 'Líder' : 'Autor' }}){{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    @else
                                        <i>- Sin integrantes vinculados -</i>
                                    @endif
                                </span>
                                <br>
                                <span style="font-size: 10px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#666' }};">Línea Inv: {{ $p->linea_investigacion->nombre_investigacion ?? '' }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $p->lapso_academico->nombre }}
                                <br>
                                <span style="font-size: 10px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#666' }};">
                                    Coordinación: {{ $p->coordinacion->nombre ?? '' }} 
                                    @if($p->trayecto) (Tr. {{ $p->trayecto }}) @endif
                                </span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($p->estado_validacion == 'pendiente')
                                    <span style="color: #d4a017; font-weight: bold;">En Revisión</span>
                                @elseif($p->estado_validacion == 'rechazado')
                                    <span style="color: #FF0000; font-weight: bold;" title="Por: {{ $p->validador?->nombre ?? 'N/A' }}">Rechazado</span>
                                @else
                                    <span style="color: #008000; font-weight: bold;" title="Por: {{ $p->validador?->nombre ?? 'N/A' }}">Aprobado</span>
                                @endif
                                <br>
                                @if($p->asignacion_ct)
                                    <span style="background-color: #FFFF00; padding: 2px; border: 1px solid #CCC; font-size: 9px; color: #000;">Asig. C&T</span>
                                @endif
                                @if($p->calificacion)
                                    <br><span style="font-size: 9px;">Nota: {{ $p->calificacion }}</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($p->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                <a href="#" wire:click.prevent="edit({{ $p->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-bottom: 2px; display: inline-block;">
                                    [Editar]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="toggleStatus({{ $p->id }})" title="{{ $p->estado_logico ? 'Deshabilitar' : 'Habilitar' }}" style="color: #0000EE; text-decoration: none; font-size: 10px; margin-bottom: 2px; display: inline-block;">
                                    [{{ $p->estado_logico ? 'Inhabilitar' : 'Habilitar' }}]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="delete({{ $p->id }})" wire:confirm="¿Desea eliminar este proyecto permanentemente?" title="Eliminar" style="color: #FF0000; text-decoration: none; font-size: 10px; display: inline-block;">
                                    [Eliminar]
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($proyectos->isEmpty())
                        <tr>
                            <td colspan="6" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay expedientes registrados
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                {{ $proyectos->links() }}
            </div>
        </fieldset>

    @else
        <!-- Formulario (Nueva Página) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Actualizar Expediente' : 'Nuevo Registro de Proyecto' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Datos Principales</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Título del Proyecto:</b></td>
                            <td width="80%" colspan="3">
                                <input wire:model="titulo" type="text" style="width: 95%;">
                                <span class="obligatorio">*</span>
                                @error('titulo') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" valign="top"><b>Resumen / Abstract:</b></td>
                            <td width="80%" colspan="3">
                                <textarea wire:model="resumen" rows="3" style="width: 95%;"></textarea>
                                <span class="obligatorio">*</span>
                                @error('resumen') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Fecha de Subida:</b></td>
                            <td width="30%">
                                <input wire:model="fecha_subida" type="date" style="width: 120px;">
                                <span class="obligatorio">*</span>
                                @error('fecha_subida') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Fecha Aprobación:</b></td>
                            <td width="30%">
                                <input wire:model="fecha_aprobacion" type="date" style="width: 120px;">
                                <span class="obligatorio">*</span>
                                @error('fecha_aprobacion') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Nota Final (1-20):</b></td>
                            <td width="30%">
                                <input wire:model="calificacion" type="number" min="1" max="20" style="width: 60px;">
                                <span class="obligatorio">*</span>
                                @error('calificacion') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Asignación C&T:</b></td>
                            <td width="30%">
                                <label><input type="checkbox" wire:model="asignacion_ct"> ¿Aplica?</label>
                                @error('asignacion_ct') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" valign="top"><b>Subir Archivo (PDF):</b></td>
                            <td width="80%" colspan="3">
                                <input type="file" wire:model="archivo" accept=".pdf" style="font-size: 11px;">
                                @if($current_archivo_path)
                                    <div style="font-size: 10px; margin-top: 5px; color: #008000; font-weight: bold;">(Archivo actual subido. Seleccione otro para reemplazar)</div>
                                @endif
                                <span class="obligatorio" style="font-size: 11px;"> (Máx 10MB) *</span>
                                <div wire:loading wire:target="archivo" style="font-size: 11px; color: #0000EE;">Cargando archivo...</div>
                                @error('archivo') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Comunidad Beneficiada</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Comunidad Asignada:</b></td>
                            <td width="80%">
                                <select wire:model="comunidad_id" style="width: 100%;">
                                    <option value="">Seleccione Comunidad Válida...</option>
                                    @foreach($comunidades_disp as $cm) <option value="{{ $cm->id }}">{{ mb_strtoupper($cm->nombre) }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('comunidad_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Clasificación de la Investigación</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Lapso Académico:</b></td>
                            <td width="30%">
                                <select wire:model="lapso_academico_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($lapsos as $lap) <option value="{{ $lap->id }}">{{ $lap->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('lapso_academico_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Coordinación:</b></td>
                            <td width="30%">
                                <select wire:model="coordinacion_id" style="width: 90%;">
                                    <option value="">Seleccione Coordinación...</option>
                                    @foreach($coordinaciones as $coordinacion) <option value="{{ $coordinacion->id }}">{{ $coordinacion->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('coordinacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Trayecto:</b></td>
                            <td width="80%" colspan="3">
                                <input wire:model="trayecto" type="text" placeholder="Ej: Trayecto I..." style="width: 85%;">
                                <span class="obligatorio">*</span>
                                @error('trayecto') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Línea de Inv.:</b></td>
                            <td width="30%">
                                <select wire:model="linea_investigacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($lineas as $l) <option value="{{ $l->id }}">{{ substr($l->nombre_investigacion, 0, 30) }}...</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('linea_investigacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Metodología Aplic.:</b></td>
                            <td width="30%">
                                <select wire:model="metodologia_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($metodologias as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('metodologia_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Tipo Publicación:</b></td>
                            <td width="30%">
                                <select wire:model="tipo_publicacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos_publicacion as $tp) <option value="{{ $tp->id }}">{{ $tp->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('tipo_publicacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Tipo Investigación:</b></td>
                            <td width="30%">
                                <select wire:model="tipo_investigacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos_investigacion as $ti) <option value="{{ $ti->id }}">{{ $ti->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('tipo_investigacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <div style="margin-top: 15px; font-size: 11px;">
                    Los campos con <span class="obligatorio">*</span> son obligatorios
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">{{ $editingId ? 'Guardar Cambios' : 'Finalizar Registro' }}</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
