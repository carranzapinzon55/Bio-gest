<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Faq;
use App\Models\MensajeContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactoFaqController extends Controller
{
    public function faq(): View
    {
        $faqs = Faq::all();
        return view('shop.faq', compact('faqs'));
    }

    public function chatbotQuery(Request $request): JsonResponse
    {
        $pregunta = trim((string) $request->query('q', ''));

        $coincidencias = Faq::query()
            ->when($pregunta !== '', function ($query) use ($pregunta) {
                $query->where('pregunta', 'like', "%{$pregunta}%")
                      ->orWhere('respuesta', 'like', "%{$pregunta}%")
                      ->orWhere('categoria', 'like', "%{$pregunta}%");
            })
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'results' => $coincidencias,
        ]);
    }

    public function contactoForm(): View
    {
        $whatsappRaw = Configuracion::get('whatsapp_soporte', '+57 310 000 0000');
        $whatsappDigits = preg_replace('/\D+/', '', $whatsappRaw);
        if (!str_starts_with($whatsappDigits, '57') && strlen($whatsappDigits) === 10) {
            $whatsappDigits = '57' . $whatsappDigits;
        }
        $whatsappUrl = 'https://wa.me/' . ($whatsappDigits ?: '573100000000');

        $misMensajes = collect();
        if (auth()->check()) {
            $misMensajes = MensajeContacto::where('correo', auth()->user()->correo)
                ->orderByDesc('fecha_envio')
                ->get();
        }

        return view('shop.contacto', compact('whatsappRaw', 'whatsappUrl', 'misMensajes'));
    }

    /**
     * Solo usuarios registrados y autenticados pueden enviar mensajes al administrador.
     */
    public function enviarContacto(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'Debes iniciar sesión o registrarte para poder enviar mensajes al administrador.']);
        }

        $user = auth()->user();

        $datos = $request->validate([
            'asunto'  => ['required', 'string', 'min:3', 'max:150', 'regex:/[a-zA-ZÁÉÍÓÚÜÑáéíóúüñ]/u'],
            'mensaje' => ['required', 'string', 'min:5', 'max:2000', 'regex:/[a-zA-ZÁÉÍÓÚÜÑáéíóúüñ]/u'],
        ], [
            'asunto.required'  => 'El asunto es obligatorio.',
            'asunto.regex'     => 'El asunto debe contener texto válido y no solo números.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.min'      => 'El mensaje debe contener al menos 5 caracteres.',
            'mensaje.regex'    => 'El mensaje debe contener texto descriptivo válido.',
        ]);

        MensajeContacto::create([
            'nombre'      => $user->nombre_completo,
            'correo'      => $user->correo,
            'asunto'      => trim($datos['asunto']),
            'mensaje'     => trim($datos['mensaje']),
            'estado'      => 'Pendiente',
            'fecha_envio' => now(),
        ]);

        return back()->with('success', '¡Hemos recibido tu mensaje correctamente! El administrador te responderá a la brevedad y podrás ver su respuesta en esta misma página o por el Chat en Vivo.');
    }
}
