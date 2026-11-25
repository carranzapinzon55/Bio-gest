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
use Illuminate\Http\RedirectResponse;
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

        $usuarios = Usuario::with(['rol', 'direcciones', 'pedidos.detalles'])
            ->orderByDesc('id_usuario')
            ->get()
            ->map(fn (Usuario $u) => $this->formatUserWithRelations($u))
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
     * Retorna datos personales, rol, estado, fechas, direcciones y resumen de pedidos.
     */
    public function show(int $id_usuario): JsonResponse
    {
        $usuario = Usuario::with(['rol', 'direcciones', 'pedidos.detalles'])->findOrFail($id_usuario);
        $formatted = $this->formatUserWithRelations($usuario);

        return response()->json([
            'ok'              => true,
            'usuario'         => $formatted,
            'direcciones'     => $formatted['direcciones'],
            'pedidos'         => $formatted['pedidos'],
            'resumen_pedidos' => $formatted['resumen_pedidos'],
        ]);
    }

    /**
     * Regla de negocio estricta: desde el panel el administrador SOLO puede
     * crear nuevas cuentas con rol exclusivo de Administrador (id_rol = 1).
     * Soporta peticiones AJAX (JSON) y envíos de formulario estándar con @csrf.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureRolesExist();

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'min:3', 'max:190', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido'   => ['nullable', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'correo'     => ['required', 'email', 'max:150', 'unique:usuario,correo'],
            'telefono'   => ['nullable', 'string', 'regex:/^[0-9]{7,10}$/'],
            'contrasena' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).+$/', 'confirmed'],
            'estado'     => ['nullable', 'in:Activo,Inactivo'],
        ], [
            'nombre.required'      => 'El nombre del nuevo administrador es obligatorio.',
            'nombre.min'           => 'El nombre debe contener al menos 3 letras.',
            'nombre.regex'         => 'El nombre y apellido solo pueden contener letras y espacios (no se permiten números).',
            'apellido.regex'       => 'El apellido solo puede contener letras y espacios (no se permiten números).',
            'correo.required'      => 'El correo corporativo es obligatorio.',
            'correo.email'         => 'Ingresa un formato de correo electrónico válido.',
            'correo.unique'        => 'Ya existe una cuenta registrada con este correo electrónico.',
            'telefono.regex'       => 'El teléfono debe contener únicamente números (entre 7 y 10 dígitos).',
            'contrasena.required'  => 'Debes asignar una contraseña de acceso inicial (mínimo 8 caracteres, al menos 1 letra y 1 número).',
            'contrasena.min'       => 'La contraseña debe contener al menos 8 caracteres.',
            'contrasena.regex'     => 'La contraseña debe incluir al menos una letra y al menos un número.',
            'contrasena.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        if (!empty($datos['apellido'])) {
            $nombre = trim($datos['nombre']);
            $apellido = trim($datos['apellido']);
        } else {
            [$nombre, $apellido] = $this->splitFullName($datos['nombre']);
        }

        // El rol está ESTRICTAMENTE fijado a Administrador (id_rol = 1)
        $rolAdmin = Rol::where('id_rol', 1)->orWhere('nombre_rol', 'Administrador')->firstOrFail();

        $usuario = Usuario::create([
            'id_rol'         => $rolAdmin->id_rol,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'correo'         => strtolower(trim($datos['correo'])),
            'telefono'       => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
            'contrasena'     => Hash::make($datos['contrasena']),
            'perfil_uso'     => 'Proyecto comercial / Gran escala',
            'estado'         => $datos['estado'] ?? 'Activo',
            'fecha_registro' => now(),
        ]);

        $usuario->load(['rol', 'direcciones', 'pedidos.detalles']);

        // Registro de auditoría en Bitácora
        try {
            BitacoraSeguridad::registrar(
                auth()->id(),
                'Creación de Administrador',
                "Se creó la cuenta administrativa para {$usuario->nombre_completo} ({$usuario->correo})."
            );
        } catch (\Throwable $e) {
            // Continuar aunque la tabla de bitácora no esté disponible
        }

        $mensajeExito = "Administrador {$usuario->nombre_completo} registrado exitosamente.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'message' => $mensajeExito,
                'usuario' => $this->formatUserWithRelations($usuario),
            ], 201);
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Alterna el estado entre Activo e Inactivo en MySQL.
     * Soporta peticiones AJAX (PATCH/POST) y formularios tradicionales.
     */
    public function toggleEstado(Request $request, int $id_usuario): JsonResponse|RedirectResponse
    {
        $usuario = Usuario::with(['rol', 'direcciones', 'pedidos.detalles'])->findOrFail($id_usuario);

        if (auth()->check() && (int) auth()->id() === (int) $usuario->id_usuario && $usuario->estado === 'Activo') {
            $msgError = 'No puedes desactivar tu propia cuenta de administrador con sesión activa.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $msgError,
                ], 422);
            }
            return redirect()->route('admin.usuarios.index')->with('error', $msgError);
        }

        $estadoAnterior = $usuario->estado;
        $usuario->estado = $usuario->estado === 'Activo' ? 'Inactivo' : 'Activo';

        if ($usuario->estado !== 'Activo') {
            if (method_exists($usuario, 'tokens')) {
                $usuario->tokens()->delete();
            }
            $usuario->remember_token = null;
        }

        $usuario->save();

        // Registro de auditoría en Bitácora
        try {
            BitacoraSeguridad::registrar(
                auth()->id(),
                'Cambio de Estado de Cuenta',
                "Cuenta de {$usuario->nombre_completo} ({$usuario->correo}) cambió de '{$estadoAnterior}' a '{$usuario->estado}'."
            );
        } catch (\Throwable $e) {
            // Continuar aunque la tabla de bitácora no esté disponible
        }

        $mensaje = "Estado de {$usuario->nombre_completo} actualizado a {$usuario->estado}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'message' => $mensaje,
                'usuario' => $this->formatUserWithRelations($usuario),
            ]);
        }

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
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
     * Formatea el modelo Usuario con todas sus direcciones y resumen de pedidos.
     */
    private function formatUserWithRelations(Usuario $u): array
    {
        $fecha = $u->fecha_registro
            ? Carbon::parse($u->fecha_registro)->format('d/m/Y h:i a')
            : now()->format('d/m/Y h:i a');

        $fechaFormateada = str_replace(['am', 'pm', 'AM', 'PM'], ['a. m.', 'p. m.', 'a. m.', 'p. m.'], $fecha);

        $direcciones = $u->direcciones
            ->sortByDesc('es_principal')
            ->values()
            ->map(function (DireccionUsuario $d) {
                return [
                    'id_direccion'     => $d->id_direccion,
                    'nombre_ubicacion' => $d->nombre_ubicacion ?: 'Principal',
                    'direccion'        => $d->direccion,
                    'ciudad'           => $d->ciudad,
                    'departamento'     => $d->departamento,
                    'codigo_postal'    => $d->codigo_postal ?: 'N/A',
                    'es_principal'     => (bool) $d->es_principal,
                ];
            })
            ->all();

        $pedidosCollection = $u->pedidos->sortByDesc('id_pedido')->values();

        $pedidos = $pedidosCollection->map(function (Pedido $p) {
            $estadoPedido = $p->estado_pedido ?? $p->estado ?? 'Pendiente';
            $codigoPedido = $p->numero_pedido ?: ('PED-' . str_pad((string) $p->id_pedido, 4, '0', STR_PAD_LEFT));
            $fechaPed = $p->fecha_pedido
                ? str_replace(['am', 'pm', 'AM', 'PM'], ['a. m.', 'p. m.', 'a. m.', 'p. m.'], Carbon::parse($p->fecha_pedido)->format('d/m/Y h:i a'))
                : '-';

            return [
                'id_pedido'        => $p->id_pedido,
                'codigo'           => $codigoPedido,
                'fecha'            => $fechaPed,
                'estado'           => $estadoPedido,
                'estado_pedido'    => $estadoPedido,
                'estado_pago'      => $p->estado_pago ?? 'Pendiente',
                'total'            => (float) $p->total,
                'total_formateado' => '$' . number_format((float) $p->total, 0, ',', '.'),
                'items_count'      => $p->detalles ? $p->detalles->sum('cantidad') : 0,
                'metodo_pago'      => $p->metodo_pago ?? 'Contraentrega',
            ];
        })->all();

        $totalGastado = (float) $pedidosCollection
            ->whereNotIn('estado_pedido', ['Cancelado'])
            ->sum('total');

        $ultimoPedido = $pedidos[0] ?? null;

        return [
            'id_usuario'      => $u->id_usuario,
            'id'              => 'USR-' . str_pad((string) $u->id_usuario, 3, '0', STR_PAD_LEFT),
            'nombre'          => $u->nombre_completo,
            'nombre_pila'     => $u->nombre,
            'apellido'        => $u->apellido,
            'correo'          => $u->correo,
            'rol'             => optional($u->rol)->nombre_rol ?? ($u->id_rol === 1 ? 'Administrador' : 'Cliente'),
            'id_rol'          => $u->id_rol,
            'estado'          => $u->estado ?? 'Activo',
            'ultimoAcceso'    => $ultimoPedido ? $ultimoPedido['fecha'] : $fechaFormateada,
            'fecha_registro'  => $fechaFormateada,
            'telefono'        => $u->telefono ?: 'No registrado',
            'notas'           => $u->perfil_uso ?: 'Alimentación continua',
            'perfil_uso'      => $u->perfil_uso ?: 'Alimentación continua',
            'direcciones'     => $direcciones,
            'pedidos'         => $pedidos,
            'resumen_pedidos' => [
                'total_pedidos'            => count($pedidos),
                'total_comprado'           => $totalGastado,
                'total_comprado_formateado'=> '$' . number_format($totalGastado, 0, ',', '.'),
                'ultimo_pedido_fecha'      => $ultimoPedido ? $ultimoPedido['fecha'] : 'Sin pedidos',
                'ultimo_pedido_codigo'     => $ultimoPedido ? $ultimoPedido['codigo'] : '-',
            ],
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

        Rol::whereNotIn('id_rol', [1, 2])->delete();
    }
}
