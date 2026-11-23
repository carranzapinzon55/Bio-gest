<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto | Panel Admin</title>
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
            <li class="active"><a href="{{ route('admin.productos.index') }}"><i class="fa-solid fa-box"></i> Productos e Inventario</a></li>
            <li><a href="{{ route('admin.pedidos.index') }}"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div style="max-width:800px; margin:0 auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:12px;">
                <h2 style="margin:0; color:#151515;">Registrar Nuevo Producto</h2>
                <a href="{{ route('admin.productos.index') }}" style="color:#666; text-decoration:none;"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            @if($errors->any())
                <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:5px; margin-bottom:15px;">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.productos.store') }}" method="POST">
                @csrf
                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Categoría *</label>
                    <select name="id_categoria" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre_categoria }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Nombre del Producto *</label>
                    <input type="text" name="nombre_producto" value="{{ old('nombre_producto') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Precio (COP) * (mayor a 0)</label>
                        <input type="number" step="0.01" name="precio" value="{{ old('precio') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Stock Inicial * (mínimo 0)</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Estado *</label>
                        <select name="estado" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Descripción</label>
                    <textarea name="descripcion" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">{{ old('descripcion') }}</textarea>
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; color:#333;">Beneficios</label>
                    <textarea name="beneficios" rows="2" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">{{ old('beneficios') }}</textarea>
                </div>

                <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:12px 25px; border-radius:6px; font-weight:700; cursor:pointer;">
                    Guardar Producto
                </button>
            </form>
        </div>
    </main>
</body>
</html>
