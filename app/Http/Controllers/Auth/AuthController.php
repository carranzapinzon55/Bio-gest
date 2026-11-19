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
            'nombre'     => ['required', 'string', 'max:100'],
            'apellido'   => ['required', 'string', 'max:100'],
            'correo'     => ['required', 'email', 'max:150', 'unique:usuario,correo'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'perfil_uso' => ['required', 'in:Inicio de criadero,Alimentación continua,Proyecto comercial / Gran escala,Mascota única'],
            'contrasena' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $usuario = Usuario::create([
            'id_rol'     => 2, // Rol Cliente por defecto
            'nombre'     => $datos['nombre'],
            'apellido'   => $datos['apellido'],
            'correo'     => $datos['correo'],
            'telefono'   => $datos['telefono'] ?? null,
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
