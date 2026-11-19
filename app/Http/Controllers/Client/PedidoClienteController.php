<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\View\View;

class PedidoClienteController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with('detalles.producto')
            ->where('id_usuario', auth()->id())
            ->orderByDesc('fecha_pedido')
            ->get();

        return view('client.mis_pedidos', compact('pedidos'));
    }

    public function show(int $id_pedido): View
    {
        $pedido = Pedido::with(['detalles.producto', 'direccion'])
            ->where('id_usuario', auth()->id())
            ->findOrFail($id_pedido);

        return view('client.detalle_pedido', compact('pedido'));
    }
}
