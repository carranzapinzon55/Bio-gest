@extends('layouts.admin')

@section('title', 'Promociones y Cupones | BIO-GEST Tenebrios Gold')
@section('header_title', 'Promociones y Descuentos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Promociones y Cupones
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Administración de códigos promocionales y campañas comerciales de <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
    </div>

    <div class="tg-card">
        <div class="card-header-flex">
            <h2><i class="fa-solid fa-tags" style="color:#e6a100;"></i> Listado de Promociones</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Código Cupón</th>
                        <th style="text-align:center;">Descuento</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promociones as $promo)
                        <tr>
                            <td>
                                <strong style="font-size:0.95rem; color:#111111; letter-spacing:0.5px;">
                                    <i class="fa-solid fa-ticket" style="color:#e6a100; margin-right:6px;"></i>
                                    {{ $promo->codigo_cupon }}
                                </strong>
                            </td>
                            <td style="text-align:center; font-weight:800; color:#047857; font-size:1rem;">
                                {{ number_format($promo->descuento_porcentaje, 0) }}% OFF
                            </td>
                            <td style="color:#6b7280;">
                                {{ optional($promo->fecha_inicio)->format('d/m/Y H:i') ?? 'Inmediato' }}
                            </td>
                            <td style="color:#6b7280;">
                                {{ optional($promo->fecha_fin)->format('d/m/Y H:i') ?? 'Permanente' }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $promo->estado === 'Activo' ? 'badge-activo' : 'badge-inactivo' }}">
                                    {{ $promo->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:#6b7280;">
                                No hay promociones activas registradas en la base de datos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
