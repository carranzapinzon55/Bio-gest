<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Faq;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Muestra el catálogo de productos con filtros de categoría, precio, disponibilidad y ordenamiento.
     */
    public function index(Request $request): View
    {
        $query = Producto::with('categoria')->activos();

        // 1. Búsqueda por texto (nombre o descripción)
        if ($request->filled('q')) {
            $busqueda = trim((string) $request->input('q'));
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_producto', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        // 2. Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->integer('categoria'));
        }

        // 3. Filtro por rango de precio
        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', (float) $request->input('precio_min'));
        }
        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', (float) $request->input('precio_max'));
        }

        // 4. Filtro por disponibilidad
        if ($request->filled('disponibilidad')) {
            $disp = $request->input('disponibilidad');
            if ($disp === 'en_stock' || $disp === 'disponible') {
                $query->where('stock', '>', 0);
            } elseif ($disp === 'agotado') {
                $query->where('stock', '<=', 0);
            }
        }

        // 5. Ordenamiento (Menor precio, Mayor precio, Más recientes, Nombre A-Z)
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'nombre_asc':
                $query->orderBy('nombre_producto', 'asc');
                break;
            case 'recientes':
            default:
                $query->orderBy('id_producto', 'desc');
                break;
        }

        $productos  = $query->get();
        $categorias = Categoria::all();

        $promocionesActivas = Promocion::where('estado', 'Activo')
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('id_promocion')
            ->get();

        return view('shop.catalogo', compact('productos', 'categorias', 'promocionesActivas'));
    }

    /**
     * Muestra la vista detallada de un producto.
     */
    public function show(int $id_producto): View
    {
        $producto = Producto::with('categoria')->activos()->findOrFail($id_producto);

        return view('shop.detalle_producto', compact('producto'));
    }

    /**
     * Buscador general en tiempo real (JSON para sugerencias).
     * Busca simultáneamente en: Productos activos, Preguntas Frecuentes, Políticas de envío e Institucional.
     */
    public function sugerencias(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query'         => $q,
                'total'         => 0,
                'productos'     => [],
                'faqs'          => [],
                'envios'        => [],
                'institucional' => [],
            ]);
        }

        // 1. Productos activos
        $productos = Producto::activos()
            ->where(function ($query) use ($q) {
                $query->where('nombre_producto', 'like', "%{$q}%")
                      ->orWhere('descripcion', 'like', "%{$q}%");
            })
            ->take(5)
            ->get()
            ->map(function ($prod) {
                return [
                    'id'        => $prod->id_producto,
                    'titulo'    => $prod->nombre_producto,
                    'subtitulo' => '$' . number_format($prod->precio, 0, ',', '.') . ' COP' . ($prod->stock > 0 ? '' : ' (Agotado)'),
                    'imagen'    => asset('assets/img/' . ($prod->imagen ?? 'Tenebrios_img.png')),
                    'url'       => url('/productos/' . $prod->id_producto),
                    'stock'     => $prod->stock,
                ];
            });

        // 2. Preguntas Frecuentes (FAQ)
        $faqs = Faq::where(function ($query) use ($q) {
                $query->where('pregunta', 'like', "%{$q}%")
                      ->orWhere('respuesta', 'like', "%{$q}%");
            })
            ->take(4)
            ->get()
            ->map(function ($faq) {
                return [
                    'id'        => $faq->id_faq,
                    'titulo'    => $faq->pregunta,
                    'subtitulo' => \Illuminate\Support\Str::limit(strip_tags($faq->respuesta), 90),
                    'url'       => route('faq') . '#faq-' . $faq->id_faq,
                ];
            });

        // 3. Políticas de Envío (/envios)
        $seccionesEnvios = [
            [
                'titulo'    => 'Garantía 100% de Llegada Viva',
                'subtitulo' => 'Contenedores térmicos ventilados con sustrato. Garantizamos larvas vivas en todo el territorio nacional.',
                'url'       => route('envios'),
                'keywords'  => 'garantia llegada viva contenedores termicos larvas vivas transporte empaque envios',
            ],
            [
                'titulo'    => 'Envío Gratuito Nacional',
                'subtitulo' => 'A partir del paquete recomendado (2.000 larvas) o compras superiores a $122.900 COP.',
                'url'       => route('envios'),
                'keywords'  => 'envio gratis flete gratuito promocion flete costo nacional 2000 larvas paquetes',
            ],
            [
                'titulo'    => 'Reembolsos y Reposiciones en Envíos',
                'subtitulo' => 'Solicita reposición dentro de 24 horas enviando video de desempaque ante cualquier incidencia.',
                'url'       => route('envios'),
                'keywords'  => 'reembolso reposicion garantias devolucion reclamo desempaque video 24 horas',
            ],
        ];

        $envios = collect($seccionesEnvios)->filter(function ($item) use ($q) {
            return mb_stripos($item['titulo'], $q) !== false
                || mb_stripos($item['subtitulo'], $q) !== false
                || mb_stripos($item['keywords'], $q) !== false;
        })->values();

        // 4. Información institucional (/nosotros y soporte)
        $seccionesInstitucional = [
            [
                'titulo'    => 'Acerca de Tenebrios Gold',
                'subtitulo' => 'Productores especializados en la cría zootécnica de Tenebrio Molitor (gusano de la harina).',
                'url'       => route('nosotros'),
                'keywords'  => 'nosotros quienes somos tenebrios gold historia crianza insectos zootecnica',
            ],
            [
                'titulo'    => 'Nuestra Misión Institucional',
                'subtitulo' => 'Proveer biomasa viva de máxima digestibilidad, frescura, bioseguridad y trazabilidad nacional.',
                'url'       => route('nosotros'),
                'keywords'  => 'mision vision valores calidad frescura biomasa viva digestibilidad',
            ],
            [
                'titulo'    => 'Compromiso y Soporte BIO-GEST',
                'subtitulo' => 'Acompañamiento zootécnico integral, capacitación en manejo de criaderos y asesoría técnica.',
                'url'       => route('nosotros'),
                'keywords'  => 'bio-gest biogest compromiso criaderos soporte capacitacion asesoria',
            ],
            [
                'titulo'    => 'Contacto y Atención al Cliente',
                'subtitulo' => 'Canales de soporte, consultas personalizadas y atención de pedidos.',
                'url'       => route('contacto.index'),
                'keywords'  => 'contacto atencion soporte ayuda telefono correo soporte cliente',
            ],
        ];

        $institucional = collect($seccionesInstitucional)->filter(function ($item) use ($q) {
            return mb_stripos($item['titulo'], $q) !== false
                || mb_stripos($item['subtitulo'], $q) !== false
                || mb_stripos($item['keywords'], $q) !== false;
        })->values();

        $total = $productos->count() + $faqs->count() + $envios->count() + $institucional->count();

        return response()->json([
            'query'         => $q,
            'total'         => $total,
            'productos'     => $productos,
            'faqs'          => $faqs,
            'envios'        => $envios,
            'institucional' => $institucional,
        ]);
    }
}
