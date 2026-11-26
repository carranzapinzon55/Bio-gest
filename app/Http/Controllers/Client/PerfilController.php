<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DireccionUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user()->load(['direcciones']);
        if (!$usuario->esAdmin()) {
            $usuario->load(['pedidos']);
        }
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
            'nombre'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido'   => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'correo'     => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'telefono'   => ['nullable', 'string', 'regex:/^[0-9]{7,10}$/'],
            'perfil_uso' => ['nullable', 'in:Inicio de criadero,Alimentación continua,Proyecto comercial / Gran escala,Mascota única'],
            'contrasena' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).+$/', 'confirmed'],
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 letras.',
            'nombre.regex'         => 'El nombre solo puede contener letras y espacios (no se permiten números).',
            'apellido.required'    => 'El apellido es obligatorio.',
            'apellido.min'         => 'El apellido debe tener al menos 2 letras.',
            'apellido.regex'       => 'El apellido solo puede contener letras y espacios (no se permiten números).',
            'correo.required'      => 'El correo electrónico es obligatorio.',
            'correo.email'         => 'Ingresa un correo electrónico válido.',
            'correo.unique'        => 'Este correo electrónico ya está siendo usado por otra cuenta.',
            'telefono.regex'       => 'El teléfono debe contener únicamente números (entre 7 y 10 dígitos).',
            'contrasena.min'       => 'La nueva contraseña debe tener mínimo 8 caracteres.',
            'contrasena.regex'     => 'La nueva contraseña debe incluir al menos una letra y al menos un número.',
            'contrasena.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $updatePayload = [
            'nombre'     => trim($datos['nombre']),
            'apellido'   => trim($datos['apellido']),
            'correo'     => strtolower(trim($datos['correo'])),
            'telefono'   => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
            'perfil_uso' => $datos['perfil_uso'] ?? $usuario->perfil_uso,
        ];

        if (!empty($datos['contrasena'])) {
            $updatePayload['contrasena'] = $datos['contrasena'];
        }

        $usuario->update($updatePayload);

        return redirect()->route('cliente.cuenta')
            ->with('success', 'Tus datos de perfil fueron actualizados exitosamente.');
    }

    public function direcciones(): View
    {
        $direcciones = auth()->user()->direcciones;
        return view('client.direcciones', compact('direcciones'));
    }

    public function guardarDireccion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_ubicacion' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'direccion'        => ['required', 'string', 'min:5', 'max:255', 'regex:/[a-zA-ZÁÉÍÓÚÜÑáéíóúüñ]/u'],
            'ciudad'           => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'departamento'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'codigo_postal'    => ['nullable', 'string', 'regex:/^[0-9]{4,10}$/'],
            'es_principal'     => ['nullable', 'boolean'],
        ], [
            'nombre_ubicacion.regex' => 'El nombre de la ubicación solo debe contener letras (Ej: Casa, Finca, Oficina).',
            'direccion.min'          => 'Ingresa una dirección válida y completa.',
            'direccion.regex'        => 'La dirección debe incluir texto válido (ej. Calle, Carrera), no solo números.',
            'ciudad.regex'           => 'La ciudad solo puede contener letras y espacios (no números).',
            'departamento.regex'     => 'El departamento solo puede contener letras y espacios (no números).',
            'codigo_postal.regex'    => 'El código postal solo puede contener dígitos numéricos.',
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
