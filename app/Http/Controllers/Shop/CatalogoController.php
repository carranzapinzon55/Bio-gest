<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Producto::with('categoria')->activos();

        if ($request->filled('q')) {
            $busqueda = $request->input('q');
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_producto', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->integer('categoria'));
        }

        $productos  = $query->orderBy('precio', 'asc')->get();
        $categorias = Categoria::all();

        $promocionesActivas = Promocion::where('estado', 'Activo')
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('id_promocion')
            ->get();

        return view('shop.catalogo', compact('productos', 'categorias', 'promocionesActivas'));
    }

    public function show(int $id_producto): View
    {
        $producto = Producto::with('categoria')->activos()->findOrFail($id_producto);

        return view('shop.detalle_producto', compact('producto'));
    }
}
