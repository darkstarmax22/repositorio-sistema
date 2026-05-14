<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Maestro de Comunidades</h2>

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
                        <th width="10%">RIF</th>
                        <th width="25%">Contacto (Correo / Tlf)</th>
                        <th width="10%">Estado</th>
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
                                @if($c->estado == 'Activa')
                                    <span style="color: #155724; font-weight: bold;">ACTIVA</span>
                                @else
                                    <span style="color: #721c24; font-weight: bold;">CLAUSURADA</span>
                                @endif
                            </td>
                            <td align="center">
                                @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                                    <a href="#" wire:click.prevent="edit({{ $c->id }})" style="color: #0000EE; text-decoration: none;">[Editar]</a> &nbsp;
                                    @if($c->estado == 'Activa')
                                        <a href="#" wire:click.prevent="toggleEstado({{ $c->id }})" wire:confirm="¿Desea clausurar esta comunidad?" style="color: #FF0000; text-decoration: none;">[Clausurar]</a>
                                    @else
                                        <a href="#" wire:click.prevent="toggleEstado({{ $c->id }})" wire:confirm="¿Desea reactivar esta comunidad?" style="color: #4CAF50; text-decoration: none;">[Reactivar]</a>
                                    @endif
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
                <tr>
                    <td width="20%"><b>Estado:</b></td>
                    <td colspan="3">
                        <select wire:model="estado" style="width: 30%;">
                            <option value="Activa">Activa</option>
                            <option value="Clausurada">Clausurada</option>
                        </select>
                    </td>
                </tr>
            </table>

            <hr style="border: 1px dotted #ccc; margin:15px 0;">



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
