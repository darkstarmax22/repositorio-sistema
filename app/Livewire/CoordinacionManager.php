<?php

namespace App\Livewire;

use App\Models\Coordinacion;
use Livewire\Component;
use Livewire\WithPagination;

class CoordinacionManager extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $search = '';
    public $editingCoordinacionId = null;
    public $viewMode = 'list'; // 'list' o 'form'

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la Coordinación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingCoordinacionId = $id;
        $coordinacion = Coordinacion::find($id);
        $this->nombre = $coordinacion->nombre;
        $this->descripcion = $coordinacion->descripcion;
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->editingCoordinacionId = null;
    }

    public function save()
    {
        $this->validate();

        Coordinacion::updateOrCreate(
            ['id' => $this->editingCoordinacionId],
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingCoordinacionId ? 'Coordinación actualizado con éxito.' : 'Coordinación creado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $coordinacion = Coordinacion::find($id);
        $coordinacion->update(['activo' => !$coordinacion->activo]);
        
        session()->flash('message', $coordinacion->activo ? 'Coordinación habilitado correctamente.' : 'Coordinación deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Coordinacion::find($id)->delete();
        session()->flash('message', 'Coordinación eliminado permanentemente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'coordinaciones' => Coordinacion::where('nombre', 'like', '%' . $this->search . '%')
                        ->latest()
                        ->paginate(10)
        ];
    }
    public function render()
    {
        return view('livewire.coordinacion-manager');
    }
}