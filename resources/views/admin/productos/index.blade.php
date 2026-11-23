<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos e Inventario | Panel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        .sidebar { background-color: #151515; color: #ffffff; border-right: 2px solid #e4b353; }
        .sidebar .brand { border-bottom: 1px solid #2d2d2d; }
        .sidebar .brand h2 { color: #e4b353; }
        .sidebar .brand p { color: #aaaaaa; }
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
            <li class="active"><a href="{{ route('admin.productos.index') }}"><i class="fa-solid fa-box"></i> Productos e Inventario</a></li>
            <li><a href="{{ route('admin.pedidos.index') }}"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a></li>
            <li><a href="{{ route('home') }}"><i class="fa-solid fa-store"></i> Ver Tienda Pública</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST" style="padding: 12px 20px;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#f87171; cursor:pointer; font-weight:600;">
                        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header" style="margin-bottom:25px;">
            <div style="font-size:1.4rem; font-weight:700; color:#151515;">Gestión de Productos e Inventario</div>
            <div class="user-profile">
                <span>{{ auth()->user()->nombre_completo ?? 'Administrador' }}</span>
            </div>
        </header>

        @if(session('success'))
            <div style="background:#dcfce7; color:#15803d; padding:12px; border-radius:6px; margin-bottom:20px; font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; color:#151515;">Listado de Productos</h2>
            <a href="{{ route('admin.productos.create') }}" style="background:#151515; color:#e4b353; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:700;">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>

        <div class="widget-card" style="background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <table class="custom-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $prod)
                        <tr>
                            <td>PROD-{{ str_pad($prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                            <td><strong>{{ $prod->nombre_producto }}</strong></td>
                            <td>{{ optional($prod->categoria)->nombre_categoria }}</td>
                            <td>${{ number_format($prod->precio, 0, ',', '.') }}</td>
                            <td>
                                <strong>{{ $prod->stock }}</strong>
                                <form action="{{ route('admin.productos.stock', $prod->id_producto) }}" method="POST" style="display:inline-flex; align-items:center; gap:5px; margin-left:10px;">
                                    @csrf
                                    <input type="number" name="cantidad" value="10" min="1" style="width:50px; padding:3px; font-size:0.8rem;">
                                    <button type="submit" name="tipo_movimiento" value="ENTRADA" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:2px 6px; cursor:pointer;" title="Sumar Stock">+</button>
                                    <button type="submit" name="tipo_movimiento" value="SALIDA" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; border-radius:3px; padding:2px 6px; cursor:pointer;" title="Restar Stock">-</button>
                                </form>
                            </td>
                            <td>
                                <span class="badge {{ $prod->estado === 'Activo' ? 'badge-active' : 'badge-danger' }}">{{ $prod->estado }}</span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('admin.productos.edit', $prod->id_producto) }}" style="color:#0284c7; margin-right:12px; text-decoration:none; font-weight:600;"><i class="fas fa-edit"></i> Editar</a>
                                <form action="{{ route('admin.productos.destroy', $prod->id_producto) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-weight:600;">
                                        <i class="fas fa-trash"></i> Desactivar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
