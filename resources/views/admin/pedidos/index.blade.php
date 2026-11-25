@extends('layouts.admin')

@section('title', 'Gestión de Pedidos | BIO-GEST Tenebrios Gold')
@section('header_title', 'Gestión y Seguimiento de Pedidos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#151515;">Pedidos de Clientes</h1>
            <p style="margin:4px 0 0 0; color:#64748b; font-size:0.9rem;">
                Consulta los pedidos recibidos desde la tienda, revisa sus productos y actualiza su estado logístico
            </p>
        </div>
        <a href="{{ route('admin.estadisticas') }}" class="tg-btn-gold">
            <i class="fa-solid fa-chart-line"></i> Estadísticas y Reportes
        </a>
    </div>

    <!-- Filtros por Estado y Búsqueda -->
    <div class="tg-card" style="padding:16px 20px;">
        <form action="{{ route('admin.pedidos.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div style="flex:1; min-width:220px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#151515; margin-bottom:4px;">Buscar pedido o cliente</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ej. PED-1002, nombre o correo..."
                       style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div style="min-width:200px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#151515; margin-bottom:4px;">Filtrar por Estado</label>
                <select name="estado" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    <option value="">Todos los estados</option>
                    @foreach(['Pendiente', 'Procesando', 'Despachado', 'Listo para retiro', 'Entregado', 'Completado', 'Cancelado'] as $est)
                        <option value="{{ $est }}" {{ request('estado') === $est ? 'selected' : '' }}>
                            {{ $est === 'Despachado' ? 'Enviado / Despachado' : $est }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="tg-btn-dark" style="padding:9px 16px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                @if(request()->hasAny(['q', 'estado']))
                    <a href="{{ route('admin.pedidos.index') }}" style="padding:9px 14px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; font-weight:600; font-size:0.88rem;">
                        Ver todos
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="tg-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>N.º Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Productos y Cantidades</th>
                        <th style="text-align:center;">Cant. Total</th>
                        <th>Valor Total</th>
                        <th>Estado Actual</th>
                        <th>Actualizar Estado</th>
                        <th style="text-align:right;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $ped)
                        @php
                            $badgeClass = match($ped->estado_pedido) {
                                'Entregado', 'Completado' => 'badge-active',
                                'Cancelado' => 'badge-danger',
                                'Despachado', 'Procesando', 'Listo para retiro' => 'badge-info',
                                default => 'badge-warning',
                            };
                            $totalUnidades = $ped->detalles->sum('cantidad');
                        @endphp
                        <tr>
                            <td style="font-weight:800; color:#151515; white-space:nowrap;">
                                {{ $ped->numero_pedido ?? 'PED-' . $ped->id_pedido }}
                            </td>
                            <td>
                                <div style="font-weight:700; color:#151515;">{{ optional($ped->usuario)->nombre_completo ?? 'Cliente' }}</div>
                                <div style="font-size:0.78rem; color:#64748b;">{{ optional($ped->usuario)->correo }}</div>
                            </td>
                            <td style="white-space:nowrap; font-size:0.84rem;">
                                {{ optional($ped->fecha_pedido)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <ul style="margin:0; padding-left:16px; font-size:0.83rem; color:#334155;">
                                    @foreach($ped->detalles as $det)
                                        <li>
                                            {{ optional($det->producto)->nombre_producto ?? 'Producto #' . $det->id_producto }}
                                            <strong>(x{{ $det->cantidad }})</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="text-align:center; font-weight:700;">
                                {{ $totalUnidades }} uds.
                            </td>
                            <td style="white-space:nowrap;">
                                <div style="font-weight:800; color:#151515;">${{ number_format($ped->total, 0, ',', '.') }}</div>
                                <div style="font-size:0.75rem; color:#64748b;">{{ $ped->metodo_pago }} ({{ $ped->estado_pago }})</div>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $ped->estado_pedido === 'Despachado' ? 'Enviado / Despachado' : $ped->estado_pedido }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="display:flex; gap:6px; align-items:center;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado_pedido" style="padding:6px 8px; font-size:0.82rem; border-radius:6px; border:1px solid #cbd5e1; background:#fff;">
                                        <option value="Pendiente" {{ $ped->estado_pedido === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Procesando" {{ $ped->estado_pedido === 'Procesando' ? 'selected' : '' }}>Procesando</option>
                                        <option value="Enviado" {{ $ped->estado_pedido === 'Despachado' ? 'selected' : '' }}>Enviado / Despachado</option>
                                        <option value="Entregado" {{ $ped->estado_pedido === 'Entregado' ? 'selected' : '' }}>Entregado</option>
                                        <option value="Completado" {{ $ped->estado_pedido === 'Completado' ? 'selected' : '' }}>Completado</option>
                                        <option value="Cancelado" {{ $ped->estado_pedido === 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                    <button type="submit" class="tg-btn-dark" style="padding:6px 10px; font-size:0.8rem;">
                                        Guardar
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}"
                                   class="tg-btn-gold" style="padding:6px 12px; font-size:0.8rem;">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:28px; color:#64748b;">
                                No se encontraron pedidos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $pedidos->links() }}
        </div>
    </div>
@endsection
