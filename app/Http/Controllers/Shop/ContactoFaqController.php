<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
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
        return view('shop.contacto');
    }

    public function enviarContacto(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre'  => ['required', 'string', 'max:100'],
            'correo'  => ['required', 'email', 'max:150'],
            'asunto'  => ['nullable', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        MensajeContacto::create($datos);

        return back()->with('success', 'Hemos recibido tu mensaje. Te responderemos pronto.');
    }
}
