<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMensaje;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatClienteController extends Controller
{
    /**
     * Determina si hay al menos un administrador activo y con sesión en línea recientemente (últimos 45 segundos).
     */
    private function isAdminOnline(): bool
    {
        $hayAdminActivo = Usuario::where('id_rol', 1)->where('estado', 'Activo')->exists();
        if (!$hayAdminActivo) {
            return false;
        }

        $lastSeen = (int) Configuracion::get('admin_last_seen', '0');
        if ($lastSeen <= 0) {
            return false;
        }

        return (now()->timestamp - $lastSeen) <= 45;
    }

    /**
     * Vista de Chat en Vivo del cliente autenticado con el administrador.
     */
    public function index(Request $request): View|JsonResponse
    {
        $usuario = auth()->user();
        $adminEnLinea = $this->isAdminOnline();

        $mensajes = ChatMensaje::where('id_usuario', $usuario->id_usuario)
            ->with('admin')
            ->orderBy('fecha_envio')
            ->orderBy('id_mensaje')
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'           => true,
                'admin_online' => $adminEnLinea,
                'mensajes'     => $mensajes->map(fn ($m) => [
                    'id_mensaje'  => $m->id_mensaje,
                    'mensaje'     => $m->mensaje,
                    'es_admin'    => (bool) $m->es_admin,
                    'autor'       => $m->es_admin ? (optional($m->admin)->nombre ?? 'Administrador BIO-GEST') : $usuario->nombre,
                    'fecha_envio' => $m->fecha_envio ? $m->fecha_envio->format('d/m/Y h:i a') : now()->format('d/m/Y h:i a'),
                ]),
            ]);
        }

        return view('client.chat', compact('usuario', 'mensajes', 'adminEnLinea'));
    }

    /**
     * Envía un nuevo mensaje del cliente al administrador en tiempo real.
     */
    public function enviar(Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'min:1', 'max:2000'],
        ], [
            'mensaje.required' => 'Escribe un mensaje antes de enviar.',
        ]);

        $usuario = auth()->user();

        $msg = ChatMensaje::create([
            'id_usuario'  => $usuario->id_usuario,
            'mensaje'     => trim($datos['mensaje']),
            'es_admin'    => false,
            'id_admin'    => null,
            'fecha_envio' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => [
                    'id_mensaje'  => $msg->id_mensaje,
                    'mensaje'     => $msg->mensaje,
                    'es_admin'    => false,
                    'autor'       => $usuario->nombre,
                    'fecha_envio' => $msg->fecha_envio->format('d/m/Y h:i a'),
                ],
            ], 201);
        }

        return redirect()->route('cliente.chat.index')
            ->with('success', 'Mensaje enviado al administrador.');
    }
}
