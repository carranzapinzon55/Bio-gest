<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos | Panel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        .sidebar { background-color: #151515; color: #ffffff; border-right: 2px solid #e4b353; }
        .sidebar .brand { border-bottom: 1px solid #2d2d2d; }
        .sidebar .brand h2 { color: #e4b353; }
        .sidebar .menu li a { color: #e2e8f0; }
        .sidebar .menu li.active a, .sidebar .menu li a:hover {
            background-color: #e4b353; color: #151515; font-weight: 700;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <h2>Tenebrios Gold</h2>
            <p>panel de administrador</p>
        </div>
        <ul class="menu">
            <li><a href="{{ route('admin.inicio') }}"><i class="fa-solid fa-house"></i> Inicio</a></li>
            <li><a href="{{ route('admin.productos.index') }}"><i class="fa-solid fa-box"></i> Productos e Inventario</a></li>
            <li class="active"><a href="{{ route('admin.pedidos.index') }}"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a></li>
            <li><a href="{{ route('home') }}"><i class="fa-solid fa-store"></i> Ver Tienda Pública</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header" style="margin-bottom:25px;">
            <div style="font-size:1.4rem; font-weight:700; color:#151515;">Gestión y Despacho de Pedidos</div>
        </header>

        @if(session('success'))
            <div style="background:#dcfce7; color:#15803d; padding:12px; border-radius:6px; margin-bottom:20px; font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        <div class="widget-card" style="background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <table class="custom-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado Actual</th>
                        <th>Cambiar Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $ped)
                        <tr>
                            <td><strong>{{ $ped->numero_pedido }}</strong></td>
                            <td>{{ optional($ped->usuario)->nombre_completo }}</td>
                            <td>${{ number_format($ped->total, 0, ',', '.') }}</td>
                            <td>{{ $ped->estado_pago }} ({{ $ped->metodo_pago }})</td>
                            <td>
                                <span class="badge badge-warning">{{ $ped->estado_pedido }}</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="display:flex; gap:6px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado_pedido" style="padding:4px; font-size:0.85rem; border-radius:4px; border:1px solid #ccc;">
                                        @foreach(['Pendiente', 'Procesando', 'Despachado', 'Listo para retiro', 'Entregado', 'Completado', 'Cancelado'] as $st)
                                            <option value="{{ $st }}" {{ $ped->estado_pedido === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:0.8rem; font-weight:700;">
                                        OK
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}" style="color:#0284c7; text-decoration:none; font-weight:600;">
                                    <i class="fas fa-eye"></i> Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:20px;">Sin pedidos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:20px;">
                {{ $pedidos->links() }}
            </div>
        </div>
    </main>
</body>
</html>
