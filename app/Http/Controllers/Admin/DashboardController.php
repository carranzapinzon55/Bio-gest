<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Usuario;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $kpis = [
            'total_usuarios'     => Usuario::count(),
            'pedidos_pendientes' => Pedido::where('estado_pedido', 'Pendiente')->count(),
            'total_pedidos'      => Pedido::count(),
            'ingresos_totales'   => Pedido::where('estado_pedido', '!=', 'Cancelado')->sum('total'),
        ];

        $usuariosRecientes = Usuario::with('rol')->orderByDesc('id_usuario')->take(5)->get();
        $productos         = Producto::orderBy('id_producto')->take(6)->get();
        $pedidosRecientes  = Pedido::with('usuario')->orderByDesc('fecha_pedido')->take(5)->get();
        $promociones       = Promocion::where('estado', 'Activo')->get();

        return view('admin.inicio', compact(
            'kpis',
            'usuariosRecientes',
            'productos',
            'pedidosRecientes',
            'promociones'
        ));
    }
}
