<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Pedido #{{ $pedido->numero_pedido }} | Panel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        .sidebar { background-color: #151515; color: #ffffff; border-right: 2px solid #e4b353; }
        .sidebar .brand h2 { color: #e4b353; }
        .sidebar .menu li a { color: #e2e8f0; }
        .sidebar .menu li.active a { background-color: #e4b353; color: #151515; font-weight: 700; }
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
        </ul>
    </aside>

    <main class="main-content">
        <div style="max-width:900px; margin:0 auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:12px;">
                <h2 style="margin:0; color:#151515;">Pedido {{ $pedido->numero_pedido }}</h2>
                <a href="{{ route('admin.pedidos.index') }}" style="color:#666; text-decoration:none;"><i class="fas fa-arrow-left"></i> Volver a Pedidos</a>
            </div>

            <!-- Información General -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px; padding-bottom:20px; border-bottom:1px solid #eee;">
                <div>
                    <h4 style="margin:0 0 6px 0; color:#888;">Cliente</h4>
                    <div style="font-weight:700; font-size:1.05rem;">{{ optional($pedido->usuario)->nombre_completo }}</div>
                    <div style="color:#666; font-size:0.9rem;">{{ optional($pedido->usuario)->correo }}</div>
                    <div style="color:#666; font-size:0.9rem;">Tel: {{ $pedido->telefono_contacto ?? optional($pedido->usuario)->telefono }}</div>
                </div>
                <div>
                    <h4 style="margin:0 0 6px 0; color:#888;">Entrega</h4>
                    <div style="font-weight:600;">{{ $pedido->direccion_entrega }}</div>
                    <div style="color:#666; font-size:0.9rem;">{{ $pedido->ciudad_entrega }}</div>
                    <div style="margin-top:6px;"><span class="badge badge-warning">{{ $pedido->estado_pedido }}</span></div>
                </div>
            </div>

            <!-- Items -->
            <h3 style="color:#151515; margin-bottom:15px;">Productos del Pedido</h3>
            <table class="custom-table" style="width:100%; margin-bottom:20px;">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="text-align:center;">Cantidad</th>
                        <th style="text-align:right;">Precio Unitario</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->detalles as $det)
                        <tr>
                            <td>{{ optional($det->producto)->nombre_producto }}</td>
                            <td style="text-align:center;">{{ $det->cantidad }}</td>
                            <td style="text-align:right;">${{ number_format($det->precio_unitario, 0, ',', '.') }}</td>
                            <td style="text-align:right; font-weight:700;">${{ number_format($det->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align:right; font-size:1.25rem; font-weight:800; color:#151515; border-top:2px solid #eee; padding-top:15px;">
                Total: ${{ number_format($pedido->total, 0, ',', '.') }} COP
            </div>
        </div>
    </main>
</body>
</html>
