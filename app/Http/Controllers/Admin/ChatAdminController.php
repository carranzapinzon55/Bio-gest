<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMensaje;
use App\Models\Faq;
use App\Models\MensajeContacto;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatAdminController extends Controller
{
    /**
     * Centro de Mensajes y Chatbot en interfaz unificada de 3 columnas:
     * Columna 1: Directorio de Clientes y Formularios de Contacto.
     * Columna 2: Historial del Chat activo y envío de respuestas del administrador.
     * Columna 3: Gestor de Preguntas Frecuentes (FAQ) del Chatbot BIO-GEST.
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Clientes con conversaciones activas en chat_mensaje
        $usuariosConMensajesIds = ChatMensaje::select('id_usuario')
            ->distinct()
            ->pluck('id_usuario');

        $clientesConversaciones = Usuario::whereIn('id_usuario', $usuariosConMensajesIds)
            ->with(['rol'])
            ->get()
            ->map(function ($u) {
                $ultimoMensaje = ChatMensaje::where('id_usuario', $u->id_usuario)
                    ->orderByDesc('fecha_envio')
                    ->first();

                $conteoTotal = ChatMensaje::where('id_usuario', $u->id_usuario)->count();

                return [
                    'id_usuario'     => $u->id_usuario,
                    'nombre'         => $u->nombre_completo,
                    'correo'         => $u->correo,
                    'telefono'       => $u->telefono ?: 'No registrado',
                    'ultimo_mensaje' => $ultimoMensaje?->mensaje ?? 'Sin mensajes',
                    'es_admin_last'  => (bool) ($ultimoMensaje?->es_admin ?? false),
                    'fecha_ultimo'   => $ultimoMensaje?->fecha_envio?->diffForHumans() ?? '',
                    'conteo'         => $conteoTotal,
                ];
            });

        // Si hay otros clientes sin mensajes aún, listarlos todos para iniciar chat nuevo
        $otrosClientes = Usuario::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosConMensajesIds)
            ->orderByDesc('id_usuario')
            ->get();

        // Determinar usuario seleccionado para la columna central
        $usuarioActivoId = $request->input('usuario');
        if (!$usuarioActivoId && $clientesConversaciones->isNotEmpty()) {
            $usuarioActivoId = $clientesConversaciones->first()['id_usuario'];
        } elseif (!$usuarioActivoId && $otrosClientes->isNotEmpty()) {
            $usuarioActivoId = $otrosClientes->first()->id_usuario;
        }

        $usuarioActivo = $usuarioActivoId ? Usuario::find($usuarioActivoId) : null;

        $mensajesHilo = $usuarioActivoId
            ? ChatMensaje::where('id_usuario', $usuarioActivoId)
                ->with(['admin'])
                ->orderBy('fecha_envio')
                ->get()
            : collect();

        // 2. Solicitudes de contacto web (mensaje_contacto)
        $contactos = MensajeContacto::orderByDesc('fecha_envio')->get();

        // 3. Base de Conocimiento FAQ del Chatbot
        $faqs = Faq::orderBy('categoria')->orderBy('id_faq')->get();
        $categoriasFaq = Faq::select('categoria')->distinct()->pluck('categoria')->filter()->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'            => true,
                'usuarioActivo' => $usuarioActivo ? [
                    'id_usuario' => $usuarioActivo->id_usuario,
                    'nombre'     => $usuarioActivo->nombre_completo,
                    'correo'     => $usuarioActivo->correo,
                ] : null,
                'mensajes'      => $mensajesHilo->map(fn ($m) => [
                    'id_mensaje'  => $m->id_mensaje,
                    'mensaje'     => $m->mensaje,
                    'es_admin'    => (bool) $m->es_admin,
                    'autor'       => $m->es_admin ? (optional($m->admin)->nombre ?? 'Administrador BIO-GEST') : (optional($usuarioActivo)->nombre ?? 'Cliente'),
                    'fecha_envio' => $m->fecha_envio ? $m->fecha_envio->format('d/m/Y h:i a') : '',
                ]),
            ]);
        }

        return view('admin.chats.index', compact(
            'clientesConversaciones',
            'otrosClientes',
            'usuarioActivo',
            'mensajesHilo',
            'contactos',
            'faqs',
            'categoriasFaq'
        ));
    }

    /**
     * Envía una respuesta del Administrador al cliente por el Chat en vivo.
     */
    public function responderChat(Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'id_usuario' => ['required', 'exists:usuario,id_usuario'],
            'mensaje'    => ['required', 'string', 'max:3000'],
        ], [
            'mensaje.required' => 'Debes escribir un mensaje para responder al cliente.',
        ]);

        $chat = ChatMensaje::create([
            'id_usuario'  => $datos['id_usuario'],
            'mensaje'     => trim($datos['mensaje']),
            'es_admin'    => true,
            'id_admin'    => auth()->id(),
            'fecha_envio' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Respuesta enviada correctamente.',
                'chat'    => $chat,
            ]);
        }

        return redirect()->route('admin.chats.index', ['usuario' => $datos['id_usuario']])
            ->with('success', 'Respuesta enviada al cliente en tiempo real.');
    }

    /**
     * Marca una solicitud de contacto web como Atendida.
     */
    public function atenderContacto(Request $request, int $id_mensaje): RedirectResponse|JsonResponse
    {
        $contacto = MensajeContacto::findOrFail($id_mensaje);

        $datos = $request->validate([
            'respuesta_admin' => ['nullable', 'string', 'max:3000'],
        ]);

        $contacto->estado = 'Atendido';
        $contacto->respuesta_admin = $datos['respuesta_admin'] ?? 'Solicitud resuelta por el equipo de soporte.';
        $contacto->fecha_atencion = now();
        $contacto->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Solicitud de contacto marcada como Atendida.',
                'contacto'=> $contacto,
            ]);
        }

        return back()->with('success', "Solicitud de {$contacto->nombre} marcada como Atendida.");
    }

    /**
     * Crea una nueva Pregunta Frecuente (FAQ) para el Chatbot BIO-GEST.
     */
    public function storeFaq(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'pregunta'  => ['required', 'string', 'max:500'],
            'respuesta' => ['required', 'string', 'max:2000'],
            'categoria' => ['required', 'string', 'max:50'],
        ], [
            'pregunta.required'  => 'La pregunta frecuente es obligatoria.',
            'respuesta.required' => 'La respuesta del chatbot es obligatoria.',
            'categoria.required' => 'Selecciona o escribe una categoría temática.',
        ]);

        Faq::create([
            'pregunta'  => trim($datos['pregunta']),
            'respuesta' => trim($datos['respuesta']),
            'categoria' => trim($datos['categoria']),
        ]);

        return back()->with('success', 'Nueva pregunta frecuente registrada en el Chatbot BIO-GEST.');
    }

    /**
     * Actualiza una Pregunta Frecuente (FAQ).
     */
    public function updateFaq(Request $request, int $id_faq): RedirectResponse
    {
        $faq = Faq::findOrFail($id_faq);

        $datos = $request->validate([
            'pregunta'  => ['required', 'string', 'max:500'],
            'respuesta' => ['required', 'string', 'max:2000'],
            'categoria' => ['required', 'string', 'max:50'],
        ]);

        $faq->update($datos);

        return back()->with('success', 'Pregunta frecuente actualizada correctamente.');
    }

    /**
     * Elimina una Pregunta Frecuente (FAQ).
     */
    public function destroyFaq(int $id_faq): RedirectResponse
    {
        $faq = Faq::findOrFail($id_faq);
        $faq->delete();

        return back()->with('success', 'Pregunta frecuente eliminada del catálogo del Chatbot.');
    }
}
