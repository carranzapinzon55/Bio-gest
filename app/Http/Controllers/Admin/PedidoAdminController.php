<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoAdminController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with(['usuario', 'detalles.producto'])
            ->orderByDesc('fecha_pedido')
            ->get();

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function show(int $id_pedido): View
    {
        $pedido = Pedido::with(['usuario', 'detalles.producto', 'direccion'])
            ->findOrFail($id_pedido);

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function actualizarEstado(Request $request, int $id_pedido): RedirectResponse
    {
        $datos = $request->validate([
            'estado_pedido' => [
                'required',
                'in:Pendiente,Procesando,Despachado,Listo para retiro,Entregado,Completado,Cancelado'
            ],
        ]);

        $pedido = Pedido::findOrFail($id_pedido);
        $pedido->update(['estado_pedido' => $datos['estado_pedido']]);

        return back()->with('success', "Estado del pedido {$pedido->numero_pedido} actualizado a {$datos['estado_pedido']}.");
    }
}
