<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraSeguridad;
use App\Models\DireccionUsuario;
use App\Models\Pedido;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioAdminController extends Controller
{
    /**
     * Listado principal de usuarios del sistema (solo roles Administrador y Cliente).
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
     * Consulta la ficha detallada de información de cualquier cuenta.
     * Retorna datos personales, rol, estado, fechas, direcciones y pedidos.
     */
    public function show(int $id_usuario): JsonResponse
    {
        $usuario = Usuario::with('rol')->findOrFail($id_usuario);

        $direcciones = DireccionUsuario::where('id_usuario', $usuario->id_usuario)
            ->orderByDesc('es_principal')
            ->orderBy('id_direccion')
            ->get()
            ->map(function ($d) {
                return [
                    'id_direccion'     => $d->id_direccion,
                    'nombre_ubicacion' => $d->nombre_ubicacion ?: 'Principal',
                    'direccion'        => $d->direccion,
                    'ciudad'           => $d->ciudad,
                    'departamento'     => $d->departamento,
                    'codigo_postal'    => $d->codigo_postal,
                    'es_principal'     => (bool) $d->es_principal,
                ];
            });

        $pedidos = Pedido::where('id_usuario', $usuario->id_usuario)
            ->withCount('detalles')
            ->orderByDesc('id_pedido')
            ->get()
            ->map(function ($p) {
                return [
                    'id_pedido'    => $p->id_pedido,
                    'codigo'       => 'PED-' . str_pad((string) $p->id_pedido, 5, '0', STR_PAD_LEFT),
                    'fecha'        => $p->fecha_pedido ? Carbon::parse($p->fecha_pedido)->format('d/m/Y h:i a') : '-',
                    'estado'       => $p->estado,
                    'total'        => (float) $p->total,
                    'total_formateado' => '$ ' . number_format($p->total, 0, ',', '.'),
                    'items_count'  => $p->detalles_count,
                    'metodo_pago'  => $p->metodo_pago,
                ];
            });

        return response()->json([
            'ok'          => true,
            'usuario'     => $this->formatUser($usuario),
            'direcciones' => $direcciones,
            'pedidos'     => $pedidos,
        ]);
    }

    /**
     * Regla de negocio estricta: desde el panel el administrador SOLO puede
     * crear nuevas cuentas con rol exclusivo de Administrador.
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureRolesExist();

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'max:190'],
            'correo'     => ['required', 'email', 'max:150', 'unique:usuario,correo'],
            'telefono'   => ['nullable', 'string', 'max:20'],
            'contrasena' => ['required', 'string', 'min:6'],
            'estado'     => ['nullable', 'in:Activo,Inactivo'],
        ], [
            'nombre.required'     => 'El nombre completo del nuevo administrador es obligatorio.',
            'correo.required'     => 'El correo corporativo es obligatorio.',
            'correo.unique'       => 'Ya existe una cuenta registrada con este correo electrónico.',
            'contrasena.required' => 'Debes asignar una contraseña de acceso inicial (mínimo 6 caracteres).',
            'contrasena.min'      => 'La contraseña debe contener al menos 6 caracteres.',
        ]);

        [$nombre, $apellido] = $this->splitFullName($datos['nombre']);

        // El rol está ESTRICTAMENTE fijado a Administrador (id_rol = 1)
        $rolAdmin = Rol::where('nombre_rol', 'Administrador')->firstOrFail();

        $usuario = Usuario::create([
            'id_rol'         => $rolAdmin->id_rol,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'correo'         => strtolower(trim($datos['correo'])),
            'telefono'       => $datos['telefono'] ?: null,
            'contrasena'     => Hash::make($datos['contrasena']),
            'perfil_uso'     => 'Proyecto comercial / Gran escala',
            'estado'         => $datos['estado'] ?? 'Activo',
            'fecha_registro' => now(),
        ]);

        $usuario->load('rol');

        // Registro de auditoría en Bitácora
        BitacoraSeguridad::registrar(
            auth()->id(),
            'Creación de Administrador',
            "Se creó la cuenta administrativa para {$usuario->nombre_completo} ({$usuario->correo})."
        );

        return response()->json([
            'ok'      => true,
            'message' => "Administrador {$usuario->nombre_completo} registrado exitosamente.",
            'usuario' => $this->formatUser($usuario),
        ], 201);
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

        $estadoAnterior = $usuario->estado;
        $usuario->estado = $usuario->estado === 'Activo' ? 'Inactivo' : 'Activo';

        if ($usuario->estado !== 'Activo') {
            $usuario->tokens()->delete();
            $usuario->remember_token = null;
        }

        $usuario->save();

        // Registro de auditoría en Bitácora
        BitacoraSeguridad::registrar(
            auth()->id(),
            'Cambio de Estado de Cuenta',
            "Cuenta de {$usuario->nombre_completo} ({$usuario->correo}) cambió de '{$estadoAnterior}' a '{$usuario->estado}'."
        );

        return response()->json([
            'ok'      => true,
            'message' => "Estado de {$usuario->nombre_completo} actualizado a {$usuario->estado}.",
            'usuario' => $this->formatUser($usuario),
        ]);
    }

    /**
     * REGLA DE NEGOCIO: Prohibida la edición de datos personales de terceros.
     */
    public function update(Request $request, int $id_usuario): JsonResponse
    {
        return response()->json([
            'ok'      => false,
            'message' => 'Por normativa de protección de datos personales y privacidad, la edición de datos de terceros está deshabilitada. Los clientes gestionan su información desde su perfil de compra.',
        ], 403);
    }

    /**
     * REGLA DE NEGOCIO: Prohibida la eliminación de usuarios; se utiliza desactivación de cuenta.
     */
    public function destroy(int $id_usuario): JsonResponse
    {
        return response()->json([
            'ok'      => false,
            'message' => 'Por trazabilidad de auditoría y facturación comercial, las cuentas no se eliminan. Por favor use el interruptor para Desactivar la cuenta.',
        ], 403);
    }

    /**
     * Formatea el modelo Usuario al contrato esperado por la vista.
     */
    private function formatUser(Usuario $u): array
    {
        $fecha = $u->fecha_registro
            ? Carbon::parse($u->fecha_registro)->format('d/m/Y h:i a')
            : now()->format('d/m/Y h:i a');

        $fechaFormateada = str_replace(['am', 'pm', 'AM', 'PM'], ['a. m.', 'p. m.', 'a. m.', 'p. m.'], $fecha);

        return [
            'id_usuario'     => $u->id_usuario,
            'id'             => 'USR-' . str_pad((string) $u->id_usuario, 3, '0', STR_PAD_LEFT),
            'nombre'         => $u->nombre_completo,
            'nombre_pila'    => $u->nombre,
            'apellido'       => $u->apellido,
            'correo'         => $u->correo,
            'rol'            => optional($u->rol)->nombre_rol ?? 'Cliente',
            'estado'         => $u->estado ?? 'Activo',
            'ultimoAcceso'   => $fechaFormateada,
            'fecha_registro' => $fechaFormateada,
            'telefono'       => $u->telefono ?: 'No registrado',
            'notas'          => $u->perfil_uso ?: 'Alimentación continua',
        ];
    }

    /**
     * Separa un nombre completo en nombre y apellido.
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        if (count($parts) <= 1) {
            return [$parts[0] ?? 'Administrador', 'BIO-GEST'];
        }

        $nombre = array_shift($parts);
        $apellido = implode(' ', $parts);

        return [$nombre, $apellido];
    }

    /**
     * Asegura estrictamente los 2 roles vigentes: Administrador (1) y Cliente (2).
     */
    private function ensureRolesExist(): void
    {
        Rol::updateOrCreate(
            ['id_rol' => 1],
            ['nombre_rol' => 'Administrador', 'descripcion' => 'Acceso total a la administración y gestión del sistema']
        );

        Rol::updateOrCreate(
            ['id_rol' => 2],
            ['nombre_rol' => 'Cliente', 'descripcion' => 'Acceso al catálogo, compras, carrito y seguimiento de pedidos']
        );

        // Eliminar cualquier otro rol no autorizado
        Rol::whereNotIn('id_rol', [1, 2])->delete();
    }
}
