<?php

namespace App\Livewire;

use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

class LapsoAcademicoManager extends Component
{
    use WithPagination;

    public $nombre = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del lapso es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
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
        $this->editingId = $id;
        $item = LapsoAcademico::find($id);
        $this->nombre = $item->nombre;
        $this->fecha_inicio = $item->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $item->fecha_fin->format('Y-m-d');
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
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        LapsoAcademico::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nombre' => $this->nombre,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
            ]
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Lapso Académico actualizado con éxito.' : 'Lapso Académico registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = LapsoAcademico::find($id);
        $item->update(['estado_lapso' => !$item->estado_lapso]);
        
        session()->flash('message', $item->estado_lapso ? 'Lapso habilitado correctamente.' : 'Lapso deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        LapsoAcademico::find($id)->delete();
        session()->flash('message', 'Lapso Académico eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => LapsoAcademico::where('nombre', 'like', '%' . $this->search . '%')
                        ->latest()
                        ->paginate(10)
        ];
    }
    public function render()
    {
        return view('livewire.lapso-academico-manager');
    }
}