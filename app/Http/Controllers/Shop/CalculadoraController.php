<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalculadoraController extends Controller
{
    public function index(): View
    {
        return view('shop.calculadora');
    }

    public function calcular(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'especie'           => ['required', 'in:reptil,ave,pez,erizo,criadero'],
            'cantidad_animales' => ['required', 'integer', 'min:1', 'max:5000'],
            'frecuencia_semanal'=> ['required', 'integer', 'min:1', 'max:7'],
        ]);

        $larvasPorAnimalPorToma = match ($datos['especie']) {
            'reptil'   => 12,
            'ave'      => 15,
            'pez'      => 8,
            'erizo'    => 10,
            'criadero' => 100,
        };

        $consumoMensual = $datos['cantidad_animales'] * $larvasPorAnimalPorToma * $datos['frecuencia_semanal'] * 4;

        $productoRecomendado = Producto::activos()
            ->orderBy('precio', 'asc')
            ->get()
            ->first(function ($prod) use ($consumoMensual) {
                if ($consumoMensual <= 500) return str_contains($prod->nombre_producto, '500');
                if ($consumoMensual <= 1000) return str_contains($prod->nombre_producto, '1.000') || str_contains($prod->nombre_producto, '1,000');
                if ($consumoMensual <= 2000) return str_contains($prod->nombre_producto, '2.000') || str_contains($prod->nombre_producto, '2,000');
                if ($consumoMensual <= 5000) return str_contains($prod->nombre_producto, '5.000') || str_contains($prod->nombre_producto, '5,000');
                return str_contains($prod->nombre_producto, '10.000') || str_contains($prod->nombre_producto, '10,000');
            }) ?? Producto::activos()->orderByDesc('precio')->first();

        return response()->json([
            'success'              => true,
            'consumo_mensual'      => $consumoMensual,
            'producto_recomendado' => $productoRecomendado,
            'consejo_conservacion' => 'Recuerda que puedes almacenar las larvas en la bandeja inferior de la nevera (8°C - 10°C) donde hibernan hasta 2.5 meses.',
        ]);
    }
}
