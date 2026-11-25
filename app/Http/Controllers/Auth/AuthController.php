<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'correo'     => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $usuario = Usuario::with('rol')
            ->where('correo', $credenciales['correo'])
            ->where('estado', 'Activo')
            ->first();

        if ($usuario && Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
            Auth::login($usuario, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($usuario->esAdmin()) {
                \App\Models\Configuracion::set('admin_last_seen', (string) now()->timestamp, 'sistema', 'Última actividad del administrador');
                return redirect()->intended(route('admin.inicio'));
            }

            return redirect()->intended(route('home'))
                ->with('success', "¡Bienvenido/a de nuevo, {$usuario->nombre}!");
        }

        return back()->withErrors([
            'correo' => 'Credenciales incorrectas o cuenta inactiva.',
        ])->onlyInput('correo');
    }

    public function showRegistro(): View
    {
        return view('auth.registro');
    }

    public function registro(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido'   => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'correo'     => ['required', 'email', 'max:150', 'unique:usuario,correo'],
            'telefono'   => ['nullable', 'string', 'max:10', 'regex:/^[0-9]{7,10}$/'],
            'perfil_uso' => ['required', 'in:Inicio de criadero,Alimentación continua,Proyecto comercial / Gran escala,Mascota única'],
            'contrasena' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).+$/', 'confirmed'],
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 letras.',
            'nombre.regex'         => 'El nombre solo puede contener letras y espacios (no se permiten números ni símbolos).',
            'apellido.required'    => 'El apellido es obligatorio.',
            'apellido.min'         => 'El apellido debe tener al menos 2 letras.',
            'apellido.regex'       => 'El apellido solo puede contener letras y espacios (no se permiten números ni símbolos).',
            'correo.required'      => 'El correo electrónico es obligatorio.',
            'correo.email'         => 'Ingresa un correo electrónico válido.',
            'correo.unique'        => 'Ya existe una cuenta registrada con este correo electrónico.',
            'telefono.regex'       => 'El teléfono debe contener únicamente números (entre 7 y 10 dígitos).',
            'contrasena.required'  => 'La contraseña es obligatoria.',
            'contrasena.min'       => 'La contraseña debe tener mínimo 8 caracteres.',
            'contrasena.regex'     => 'La contraseña debe incluir al menos una letra y al menos un número.',
            'contrasena.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $usuario = Usuario::create([
            'id_rol'     => 2, // Rol Cliente por defecto
            'nombre'     => trim($datos['nombre']),
            'apellido'   => trim($datos['apellido']),
            'correo'     => strtolower(trim($datos['correo'])),
            'telefono'   => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
            'perfil_uso' => $datos['perfil_uso'],
            'contrasena' => Hash::make($datos['contrasena']),
            'estado'     => 'Activo',
        ]);

        Auth::login($usuario);

        return redirect()->route('home')
            ->with('success', '¡Tu cuenta en Tenebrios-Gold ha sido creada con éxito!');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()->esAdmin()) {
            \App\Models\Configuracion::set('admin_last_seen', '0', 'sistema', 'Última actividad del administrador');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
