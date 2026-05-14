<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $error = '';

    public function login()
    {
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            return redirect()->intended('/dashboard');
        }

        $this->error = 'Usuario o contraseña incorrectos.';
    }
    public function render()
    {
        return view('livewire.login');
    }
}