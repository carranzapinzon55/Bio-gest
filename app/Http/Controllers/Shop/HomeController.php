<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $productosDestacados = Producto::activos()
            ->orderBy('precio', 'asc')
            ->take(5)
            ->get();

        return view('shop.index', compact('productosDestacados'));
    }
}
