@extends('layouts.admin')

@section('title', 'Ajustes del Sistema | BIO-GEST Tenebrios Gold')
@section('header_title', 'Configuración y Ajustes del Sistema')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Ajustes del Sistema
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Parámetros generales de la plataforma <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
    </div>

    <div class="tg-card" style="max-width:760px;">
        <div class="card-header-flex">
            <h2><i class="fa-solid fa-sliders" style="color:#e6a100;"></i> Parámetros Operativos</h2>
        </div>
        <form onsubmit="event.preventDefault(); alert('Ajustes guardados correctamente.');">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                <div class="form-group-filter">
                    <label>Nombre de la Empresa</label>
                    <input type="text" class="form-input-filter" value="Tenebrios Gold S.A.S." readonly>
                </div>
                <div class="form-group-filter">
                    <label>Moneda Oficial</label>
                    <input type="text" class="form-input-filter" value="COP (Pesos Colombianos)" readonly>
                </div>
                <div class="form-group-filter">
                    <label>Umbral General de Stock Mínimo</label>
                    <input type="number" class="form-input-filter" value="10" min="1">
                </div>
                <div class="form-group-filter">
                    <label>Zona Horaria</label>
                    <input type="text" class="form-input-filter" value="America/Bogota (UTC-5)" readonly>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="tg-btn-gold">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
@endsection
