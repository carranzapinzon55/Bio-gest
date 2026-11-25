@extends('layouts.app')

@section('title', 'Mi Cuenta | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mi_cuenta_style.css') }}">
@endpush

@section('content')
<div style="max-width:1000px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-user-circle" style="color:#e4b353;"></i> Mi Cuenta en BIO-GEST
    </h1>

    <div style="display:grid; grid-template-columns:1fr 2fr; gap:30px;">
        <!-- Tarjeta de Perfil -->
        <div style="background:#fff; border-radius:10px; padding:25px; box-shadow:0 3px 12px rgba(0,0,0,0.06); text-align:center;">
            <div style="width:80px; height:80px; border-radius:50%; background:#e4b353; color:#151515; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800; margin:0 auto 15px auto;">
                {{ substr($usuario->nombre, 0, 1) }}{{ substr($usuario->apellido, 0, 1) }}
            </div>
            <h2 style="color:#151515; margin:0 0 5px 0;">{{ $usuario->nombre_completo }}</h2>
            <p style="color:#666; margin:0 0 15px 0; font-size:0.9rem;">{{ $usuario->correo }}</p>
            <span style="background:#fef3c7; color:#92400e; padding:4px 12px; border-radius:15px; font-size:0.8rem; font-weight:700;">
                Perfil: {{ $usuario->perfil_uso }}
            </span>

            <div style="margin-top:25px; display:flex; flex-direction:column; gap:10px;">
                <a href="{{ route('cliente.perfil.editar') }}" style="background:#151515; color:#e4b353; padding:10px; border-radius:6px; text-decoration:none; font-weight:700;">
                    <i class="fas fa-user-edit"></i> Editar Datos
                </a>
                <a href="{{ route('cliente.direcciones.index') }}" style="background:#f3f4f6; color:#374151; padding:10px; border-radius:6px; text-decoration:none; font-weight:600;">
                    <i class="fas fa-map-marker-alt"></i> Mis Direcciones
                </a>
                <a href="{{ route('cliente.pedidos.index') }}" style="background:#f3f4f6; color:#374151; padding:10px; border-radius:6px; text-decoration:none; font-weight:600;">
                    <i class="fas fa-box"></i> Mis Pedidos
                </a>
                <a href="{{ route('cliente.chat.index') }}" style="background:#e4b353; color:#151515; padding:10px; border-radius:6px; text-decoration:none; font-weight:700;">
                    <i class="fas fa-comments"></i> Chat en Vivo con Soporte
                </a>
            </div>
        </div>

        <!-- Resumen de Actividad -->
        <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:12px;">Información General</h3>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px;">
                <div style="background:#fafafa; padding:15px; border-radius:8px;">
                    <span style="color:#888; font-size:0.85rem; display:block;">Teléfono:</span>
                    <strong style="color:#151515;">{{ $usuario->telefono ?? 'Sin registrar' }}</strong>
                </div>
                <div style="background:#fafafa; padding:15px; border-radius:8px;">
                    <span style="color:#888; font-size:0.85rem; display:block;">Fecha de Registro:</span>
                    <strong style="color:#151515;">{{ $usuario->fecha_registro ? \Carbon\Carbon::parse($usuario->fecha_registro)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
                </div>
            </div>

            <h4 style="color:#151515; margin-bottom:10px;">Últimos Pedidos</h4>
            @if($usuario->pedidos->isEmpty())
                <p style="color:#777; font-size:0.9rem;">No tienes pedidos recientes.</p>
            @else
                <ul style="list-style:none; padding:0; margin:0;">
                    @foreach($usuario->pedidos->take(3) as $ped)
                        <li style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:0.9rem;">
                            <span>{{ $ped->numero_pedido }}</span>
                            <span style="font-weight:700;">${{ number_format($ped->total, 0, ',', '.') }}</span>
                            <span style="color:#0284c7; font-weight:600;">{{ $ped->estado_pedido }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
