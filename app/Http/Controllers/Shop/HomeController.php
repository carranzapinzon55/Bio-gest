<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $productosDestacados = Producto::activos()
            ->orderBy('precio', 'asc')
            ->take(5)
            ->get();

        $promocionesActivas = Promocion::where('estado', 'Activo')
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('id_promocion')
            ->get();

        return view('shop.index', compact('productosDestacados', 'promocionesActivas'));
    }
}
