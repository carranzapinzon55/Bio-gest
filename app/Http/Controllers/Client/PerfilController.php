<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DireccionUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user()->load(['direcciones', 'pedidos']);
        return view('client.mi_cuenta', compact('usuario'));
    }

    public function editar(): View
    {
        $usuario = auth()->user();
        return view('client.editar_perfil', compact('usuario'));
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $usuario = auth()->user();

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'max:100'],
            'apellido'   => ['required', 'string', 'max:100'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'perfil_uso' => ['nullable', 'in:Inicio de criadero,Alimentación continua,Proyecto comercial / Gran escala,Mascota única'],
        ]);

        $usuario->update($datos);

        return redirect()->route('cliente.cuenta')
            ->with('success', 'Tus datos de perfil fueron actualizados.');
    }

    public function direcciones(): View
    {
        $direcciones = auth()->user()->direcciones;
        return view('client.direcciones', compact('direcciones'));
    }

    public function guardarDireccion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_ubicacion' => ['required', 'string', 'max:100'],
            'direccion'        => ['required', 'string', 'max:255'],
            'ciudad'           => ['required', 'string', 'max:100'],
            'departamento'     => ['required', 'string', 'max:100'],
            'codigo_postal'    => ['nullable', 'string', 'max:10'],
            'es_principal'     => ['nullable', 'boolean'],
        ]);

        $datos['id_usuario'] = auth()->id();
        DireccionUsuario::create($datos);

        return back()->with('success', 'Dirección agregada correctamente.');
    }

    public function eliminarDireccion(int $id_direccion): RedirectResponse
    {
        DireccionUsuario::where('id_usuario', auth()->id())
            ->where('id_direccion', $id_direccion)
            ->delete();

        return back()->with('success', 'Dirección eliminada.');
    }
}
