@extends('layouts.app')

@section('title', 'Mi Cuenta | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mi_cuenta_style.css') }}?v={{ file_exists(public_path('assets/css/mi_cuenta_style.css')) ? filemtime(public_path('assets/css/mi_cuenta_style.css')) : time() }}">
@endpush

@section('content')
<div class="mi-cuenta-wrapper">
    <h1 class="mi-cuenta-titulo">
        <i class="fas fa-user-circle" aria-hidden="true"></i>
        <span>Mi Cuenta en BIO-GEST</span>
    </h1>

    <div class="mi-cuenta-grid">
        <!-- Tarjeta de Perfil -->
        <aside class="mi-cuenta-perfil-card">
            <div class="mi-cuenta-avatar" aria-hidden="true">
                {{ substr($usuario->nombre, 0, 1) }}{{ substr($usuario->apellido, 0, 1) }}
            </div>
            <h2 class="mi-cuenta-nombre">{{ $usuario->nombre_completo }}</h2>
            <p class="mi-cuenta-correo">{{ $usuario->correo }}</p>
            <span class="mi-cuenta-perfil-badge">
                Perfil: {{ $usuario->perfil_uso }}
            </span>

            <nav class="mi-cuenta-acciones" aria-label="Acciones de cuenta">
                <a href="{{ route('cliente.perfil.editar') }}" class="mi-cuenta-btn mi-cuenta-btn--dark">
                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                    <span>Editar Datos</span>
                </a>
                <a href="{{ route('cliente.direcciones.index') }}" class="mi-cuenta-btn mi-cuenta-btn--neutral">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span>Mis Direcciones</span>
                </a>
                @if(!$usuario->esAdmin())
                    <a href="{{ route('cliente.pedidos.index') }}" class="mi-cuenta-btn mi-cuenta-btn--neutral">
                        <i class="fas fa-box" aria-hidden="true"></i>
                        <span>Mis Pedidos</span>
                    </a>
                @else
                    <a href="{{ route('admin.inicio') }}" class="mi-cuenta-btn mi-cuenta-btn--admin">
                        <i class="fas fa-gauge" aria-hidden="true"></i>
                        <span>Ir al Panel Admin</span>
                    </a>
                @endif
                <a href="{{ route('cliente.chat.index') }}" class="mi-cuenta-btn mi-cuenta-btn--gold">
                    <i class="fas fa-comments" aria-hidden="true"></i>
                    <span>Chat en Vivo con Soporte</span>
                </a>
            </nav>
        </aside>

        <!-- Resumen de Actividad -->
        <section class="mi-cuenta-resumen-card">
            <h3 class="mi-cuenta-subtitulo">Información General</h3>

            <div class="mi-cuenta-info-grid">
                <div class="mi-cuenta-info-item">
                    <span class="mi-cuenta-info-label">Teléfono:</span>
                    <strong class="mi-cuenta-info-valor">{{ $usuario->telefono ?? 'Sin registrar' }}</strong>
                </div>
                <div class="mi-cuenta-info-item">
                    <span class="mi-cuenta-info-label">Fecha de Registro:</span>
                    <strong class="mi-cuenta-info-valor">{{ $usuario->fecha_registro ? \Carbon\Carbon::parse($usuario->fecha_registro)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
                </div>
            </div>

            @if(!$usuario->esAdmin())
                <h4 class="mi-cuenta-pedidos-titulo">Últimos Pedidos</h4>
                @if($usuario->pedidos && $usuario->pedidos->isEmpty())
                    <p class="mi-cuenta-pedidos-vacio">No tienes pedidos recientes.</p>
                @elseif($usuario->pedidos)
                    <ul class="mi-cuenta-pedidos-lista">
                        @foreach($usuario->pedidos->take(3) as $ped)
                            <li class="mi-cuenta-pedido-item">
                                <span class="mi-cuenta-pedido-numero">{{ $ped->numero_pedido }}</span>
                                <span class="mi-cuenta-pedido-total">${{ number_format($ped->total, 0, ',', '.') }}</span>
                                <span class="mi-cuenta-pedido-estado">{{ $ped->estado_pedido }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="mi-cuenta-admin-aviso">
                    <h4><i class="fas fa-user-shield" aria-hidden="true"></i> Cuenta de Administrador</h4>
                    <p>
                        Esta cuenta posee privilegios administrativos completos en <strong>BIO-GEST</strong>. Las compras comerciales están reservadas a cuentas de clientes para mantener la integridad contable y de inventario de Tenebrios Gold.
                    </p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
