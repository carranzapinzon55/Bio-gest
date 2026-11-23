<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioAdminController extends Controller
{
    /**
     * Garantiza que existan los roles del sistema y retorna la vista o el listado JSON.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->ensureRolesExist();

        $usuarios = Usuario::with('rol')
            ->orderByDesc('id_usuario')
            ->get()
            ->map(fn (Usuario $u) => $this->formatUser($u))
            ->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'       => true,
                'usuarios' => $usuarios,
            ]);
        }

        return view('admin.usuarios.index', [
            'usuariosIniciales' => $usuarios,
        ]);
    }

    /**
     * Crea un nuevo usuario directamente en la tabla `usuario`.
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureRolesExist();

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'max:190'],
            'correo'     => ['required', 'email', 'max:150', 'unique:usuario,correo'],
            'rol'        => ['required', 'string', 'in:Administrador,Vendedor,Cliente,Inventario'],
            'estado'     => ['required', 'in:Activo,Inactivo'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'notas'      => ['nullable', 'string', 'max:150'],
            'contrasena' => ['nullable', 'string', 'min:6'],
        ]);

        [$nombre, $apellido] = $this->splitFullName($datos['nombre']);
        $rol = Rol::where('nombre_rol', $datos['rol'])->firstOrFail();

        $perfilesValidos = [
            'Inicio de criadero',
            'Alimentación continua',
            'Proyecto comercial / Gran escala',
            'Mascota única',
        ];

        $perfilUso = in_array($datos['notas'] ?? '', $perfilesValidos, true)
            ? $datos['notas']
            : ($datos['rol'] === 'Administrador' ? 'Proyecto comercial / Gran escala' : 'Alimentación continua');

        $usuario = Usuario::create([
            'id_rol'         => $rol->id_rol,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'correo'         => strtolower(trim($datos['correo'])),
            'telefono'       => $datos['telefono'] ?: null,
            'contrasena'     => Hash::make($datos['contrasena'] ?: 'BioGest123*'),
            'perfil_uso'     => $perfilUso,
            'estado'         => $datos['estado'],
            'fecha_registro' => now(),
        ]);

        $usuario->load('rol');

        return response()->json([
            'ok'      => true,
            'message' => "Usuario {$usuario->nombre_completo} creado correctamente en la base de datos.",
            'usuario' => $this->formatUser($usuario),
        ], 201);
    }

    /**
     * Actualiza los datos de un usuario existente en la tabla `usuario`.
     */
    public function update(Request $request, int $id_usuario): JsonResponse
    {
        $this->ensureRolesExist();

        $usuario = Usuario::findOrFail($id_usuario);

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'max:190'],
            'correo'     => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'rol'        => ['required', 'string', 'in:Administrador,Vendedor,Cliente,Inventario'],
            'estado'     => ['required', 'in:Activo,Inactivo'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'notas'      => ['nullable', 'string', 'max:150'],
            'contrasena' => ['nullable', 'string', 'min:6'],
        ]);

        [$nombre, $apellido] = $this->splitFullName($datos['nombre']);
        $rol = Rol::where('nombre_rol', $datos['rol'])->firstOrFail();

        $perfilesValidos = [
            'Inicio de criadero',
            'Alimentación continua',
            'Proyecto comercial / Gran escala',
            'Mascota única',
        ];

        $usuario->id_rol   = $rol->id_rol;
        $usuario->nombre   = $nombre;
        $usuario->apellido = $apellido;
        $usuario->correo   = strtolower(trim($datos['correo']));
        $usuario->telefono = $datos['telefono'] ?: null;
        $usuario->estado   = $datos['estado'];

        if (!empty($datos['notas']) && in_array($datos['notas'], $perfilesValidos, true)) {
            $usuario->perfil_uso = $datos['notas'];
        }

        if (!empty($datos['contrasena'])) {
            $usuario->contrasena = Hash::make($datos['contrasena']);
        }

        $usuario->save();
        if ($usuario->estado !== 'Activo') {
            $usuario->tokens()->delete();
            $usuario->remember_token = null;
            $usuario->save();
        }
        $usuario->load('rol');

        return response()->json([
            'ok'      => true,
            'message' => "Datos de {$usuario->nombre_completo} actualizados correctamente.",
            'usuario' => $this->formatUser($usuario),
        ]);
    }

    /**
     * Alterna el estado entre Activo e Inactivo sin recargar la página.
     */
    public function toggleEstado(int $id_usuario): JsonResponse
    {
        $usuario = Usuario::with('rol')->findOrFail($id_usuario);

        if (auth()->check() && (int) auth()->id() === (int) $usuario->id_usuario && $usuario->estado === 'Activo') {
            return response()->json([
                'ok'      => false,
                'message' => 'No puedes desactivar tu propia cuenta de administrador con sesión activa.',
            ], 422);
        }

        $usuario->estado = $usuario->estado === 'Activo' ? 'Inactivo' : 'Activo';
        if ($usuario->estado !== 'Activo') {
            $usuario->tokens()->delete();
            $usuario->remember_token = null;
        }
        $usuario->save();

        return response()->json([
            'ok'      => true,
            'message' => "Estado de {$usuario->nombre_completo} actualizado a {$usuario->estado}.",
            'usuario' => $this->formatUser($usuario),
        ]);
    }

    /**
     * Elimina o desactiva un usuario de la base de datos.
     */
    public function destroy(int $id_usuario): JsonResponse
    {
        $usuario = Usuario::findOrFail($id_usuario);

        if (auth()->check() && (int) auth()->id() === (int) $usuario->id_usuario) {
            return response()->json([
                'ok'      => false,
                'message' => 'No puedes eliminar tu propia cuenta con sesión activa.',
            ], 422);
        }

        try {
            $nombre = $usuario->nombre_completo;
            $usuario->delete();

            return response()->json([
                'ok'      => true,
                'deleted' => true,
                'message' => "Usuario {$nombre} eliminado de la base de datos.",
            ]);
        } catch (\Throwable $e) {
            // Si tiene pedidos asociados por clave foránea, se marca como Inactivo
            $usuario->estado = 'Inactivo';
            $usuario->save();
            $usuario->load('rol');

            return response()->json([
                'ok'      => true,
                'deleted' => false,
                'message' => 'El usuario tiene pedidos asociados; se ha marcado como Inactivo.',
                'usuario' => $this->formatUser($usuario),
            ]);
        }
    }

    /**
     * Formatea el modelo Usuario al contrato esperado por la tabla en usuarios.js.
     */
    private function formatUser(Usuario $u): array
    {
        $fecha = $u->fecha_registro
            ? Carbon::parse($u->fecha_registro)->format('d/m/Y h:i a')
            : now()->format('d/m/Y h:i a');

        $fechaFormateada = str_replace(['am', 'pm', 'AM', 'PM'], ['a. m.', 'p. m.', 'a. m.', 'p. m.'], $fecha);

        return [
            'id_usuario'   => $u->id_usuario,
            'id'           => 'USR-' . str_pad((string) $u->id_usuario, 3, '0', STR_PAD_LEFT),
            'nombre'       => $u->nombre_completo,
            'correo'       => $u->correo,
            'rol'          => optional($u->rol)->nombre_rol ?? 'Cliente',
            'estado'       => $u->estado ?? 'Activo',
            'ultimoAcceso' => $fechaFormateada,
            'telefono'     => $u->telefono ?: 'No registrado',
            'notas'        => $u->perfil_uso ?: 'Alimentación continua',
        ];
    }

    /**
     * Separa un nombre completo en nombre y apellido para las columnas de la tabla `usuario`.
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        if (count($parts) <= 1) {
            return [$parts[0] ?? 'Usuario', 'BIO-GEST'];
        }

        $nombre = array_shift($parts);
        $apellido = implode(' ', $parts);

        return [$nombre, $apellido];
    }

    /**
     * Asegura que los 4 roles del selector existan en la tabla `rol`.
     */
    private function ensureRolesExist(): void
    {
        $roles = [
            'Administrador' => 'Acceso total a la administración y gestión del sistema',
            'Cliente'       => 'Acceso al catálogo, compras, carrito y seguimiento de pedidos',
            'Vendedor'      => 'Gestión comercial, clientes y seguimiento de pedidos',
            'Inventario'    => 'Control de existencias, kardex y movimientos de almacén',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Rol::firstOrCreate(
                ['nombre_rol' => $nombre],
                ['descripcion' => $descripcion]
            );
        }
    }
}
