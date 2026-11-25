<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    /**
     * Iniciar sesión y generar token de Sanctum.
     * POST /api/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo'     => 'required|email',
            'contrasena' => 'required|string',
        ], [
            'correo.required'     => 'El correo electrónico es obligatorio.',
            'correo.email'        => 'Ingrese un correo electrónico válido.',
            'contrasena.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::with('rol')->where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        if ($usuario->estado !== 'Activo') {
            return response()->json([
                'success' => false,
                'message' => 'La cuenta se encuentra inactiva. Comuníquese con soporte.'
            ], 403);
        }

        // Crear token Sanctum
        $token = $usuario->createToken('bio-gest-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'usuario'    => [
                    'id_usuario'      => $usuario->id_usuario,
                    'nombre'          => $usuario->nombre,
                    'apellido'        => $usuario->apellido,
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo'          => $usuario->correo,
                    'telefono'        => $usuario->telefono,
                    'id_rol'          => $usuario->id_rol,
                    'rol'             => optional($usuario->rol)->nombre_rol ?? ($usuario->id_rol === 1 ? 'Administrador' : 'Cliente'),
                    'perfil_uso'      => $usuario->perfil_uso,
                    'estado'          => $usuario->estado,
                ]
            ]
        ], 200);
    }

    /**
     * Registrar un nuevo cliente y retornar token Sanctum.
     * POST /api/registro
     */
    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido'   => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'correo'     => ['required', 'string', 'email', 'max:150', 'unique:usuario,correo'],
            'contrasena' => ['required', 'string', 'min:6'],
            'telefono'   => ['nullable', 'string', 'regex:/^[0-9]{7,10}$/'],
            'perfil_uso' => ['nullable', 'in:Inicio de criadero,Alimentación continua,Proyecto comercial / Gran escala,Mascota única'],
        ], [
            'nombre.required'     => 'El nombre es obligatorio.',
            'nombre.min'          => 'El nombre debe tener al menos 2 letras.',
            'nombre.regex'        => 'El nombre solo puede contener letras y espacios (no se permiten números).',
            'apellido.required'   => 'El apellido es obligatorio.',
            'apellido.min'        => 'El apellido debe tener al menos 2 letras.',
            'apellido.regex'      => 'El apellido solo puede contener letras y espacios (no se permiten números).',
            'correo.required'     => 'El correo electrónico es obligatorio.',
            'correo.unique'       => 'Este correo ya se encuentra registrado.',
            'telefono.regex'      => 'El teléfono debe contener únicamente números (entre 7 y 10 dígitos).',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min'      => 'La contraseña debe tener mínimo 6 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'id_rol'         => 2, // Rol de Cliente por defecto
            'nombre'         => $request->nombre,
            'apellido'       => $request->apellido,
            'correo'         => $request->correo,
            'telefono'       => $request->telefono,
            'contrasena'     => Hash::make($request->contrasena),
            'perfil_uso'     => $request->perfil_uso ?? 'Alimentación continua',
            'estado'         => 'Activo',
            'fecha_registro' => now(),
        ]);

        $usuario->load('rol');
        $token = $usuario->createToken('bio-gest-api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'usuario'    => [
                    'id_usuario'      => $usuario->id_usuario,
                    'nombre'          => $usuario->nombre,
                    'apellido'        => $usuario->apellido,
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo'          => $usuario->correo,
                    'telefono'        => $usuario->telefono,
                    'id_rol'          => $usuario->id_rol,
                    'rol'             => optional($usuario->rol)->nombre_rol ?? 'Cliente',
                    'perfil_uso'      => $usuario->perfil_uso,
                    'estado'          => $usuario->estado,
                ]
            ]
        ], 201);
    }

    /**
     * Obtener el perfil del usuario autenticado.
     * GET /api/perfil
     */
    public function perfil(Request $request)
    {
        $usuario = $request->user();
        $usuario->load(['rol', 'direcciones']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id_usuario'      => $usuario->id_usuario,
                'nombre'          => $usuario->nombre,
                'apellido'        => $usuario->apellido,
                'nombre_completo' => $usuario->nombre_completo,
                'correo'          => $usuario->correo,
                'telefono'        => $usuario->telefono,
                'id_rol'          => $usuario->id_rol,
                'rol'             => optional($usuario->rol)->nombre_rol ?? ($usuario->id_rol === 1 ? 'Administrador' : 'Cliente'),
                'perfil_uso'      => $usuario->perfil_uso,
                'estado'          => $usuario->estado,
                'fecha_registro'  => $usuario->fecha_registro,
                'direcciones'     => $usuario->direcciones,
            ]
        ], 200);
    }

    /**
     * Cerrar sesión (revocar el token actual).
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ], 200);
    }
}
