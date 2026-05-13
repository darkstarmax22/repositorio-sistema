<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/repositorio', function () {
    return view('repositorio');
})->name('repositorio');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::middleware(['auth'])->group(function () {
    
    // Todos los autenticados ven el dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulos de Gestión Académica (Solo Administrador)
    Route::middleware(['role:administrador'])->group(function() {
        Route::get('/coordinaciones', function () {
            return view('coordinaciones.index');
        })->name('coordinaciones');

        Route::get('/lapsos-academicos', function () {
            return view('lapso_academico.index');
        })->name('lapsos-academicos');
    });

    // Módulos Compartidos (Estudiantes y Administrador)
    Route::middleware(['role:administrador,estudiante'])->group(function() {
        Route::get('/lineas-investigacion', function () {
            return view('lineas.index');
        })->name('lineas-investigacion');

        Route::get('/tipos-investigacion', function () {
            return view('tipo_investigacion.index');
        })->name('tipos-investigacion');

        Route::get('/metodologia-investigacion', function () {
            return view('metodologia_investigacion.index');
        })->name('metodologia-investigacion');

        Route::get('/tipos-publicacion', function () {
            return view('tipo_publicacion.index');
        })->name('tipos-publicacion');
    });







    // Módulos de Proyectos
    Route::get('/proyectos', function () {
        return view('proyectos.index');
    })->name('proyectos.index');

    // Módulo Metodologías
    Route::get('/metodologias', function () {
        return view('metodologias.index');
    })->name('metodologias.index');

    // Módulo Comunidades
    Route::get('/comunidades', function () {
        return view('comunidades.index');
    })->name('comunidades.index');

    Route::get('/proyectos/buscar', function () {
        return view('proyectos.buscar');
    })->name('proyectos.buscar');

    Route::middleware(['role:administrador,profesor proyecto'])->group(function() {
        Route::get('/validaciones', function () {
            return view('validaciones.index');
        })->name('validaciones.index');
    });

    Route::middleware(['role:administrador,estudiante'])->group(function() {
        Route::get('/proyectos/crear', function () {
            return view('proyectos.index'); 
        })->name('proyectos.crear');
    });


    // Módulos de Validación (Docentes y Admin)
    // ELIMINADO: Módulo de Validaciones (Dependencia de Proyectos)


    // Módulos de Sistema (Administrador y COORDINADOR_Coordinación_TITLE_TEMP_PLACEHOLDER para Profesores Proy.)
    Route::middleware(['role:administrador,COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->group(function() {
        Route::get('/configuracion/profesores-proyecto', function () {
            return view('configuracion.profesores-proyecto');
        })->name('profesores-proyecto.index');
    });

    Route::get('/configuracion', function () {
        return view('configuracion.index');
    })->name('configuracion');

    // Módulos de Sistema (Solo Administrador)
    Route::middleware(['role:administrador'])->group(function() {
        Route::get('/auditoria', function () {
            return view('dashboard'); // Placeholder
        })->name('auditoria');

        Route::get('/configuracion/coordinadores', function () {
            return view('configuracion.coordinadores');
        })->name('coordinadores.index');

        Route::get('/usuarios', function () {
            return view('usuarios.index');
        })->name('usuarios.index');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
