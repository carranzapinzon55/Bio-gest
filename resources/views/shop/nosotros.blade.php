@extends('layouts.app')

@section('title', '¿Quiénes Somos? | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/quienes_somos_style.css') }}">
@endpush

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px; line-height:1.8; color:#444;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:10px; margin-bottom:20px;">
        Acerca de Tenebrios Gold
    </h1>
    <p style="font-size:1.1rem;">
        En <strong>Tenebrios Gold</strong> somos productores especializados en la cría zootécnica de <em>Tenebrio Molitor</em> (gusano de la harina), ofreciendo pie de cría selecto y alimento vivo de óptimo valor nutricional para aves, peces ornamentales, reptiles, anfibios y proyectos agropecuarios sustentables.
    </p>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin:30px 0;">
        <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:3px solid #e4b353;">
            <h3 style="color:#151515; margin-top:0;"><i class="fas fa-seedling" style="color:#e4b353;"></i> Nuestra Misión</h3>
            <p style="margin:0; font-size:0.95rem;">Proveer biomasa viva de máxima digestibilidad, garantizando siempre frescura, bioseguridad y trazabilidad en cada envío nacional.</p>
        </div>
        <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:3px solid #151515;">
            <h3 style="color:#151515; margin-top:0;"><i class="fas fa-award" style="color:#e4b353;"></i> Compromiso BIO-GEST</h3>
            <p style="margin:0; font-size:0.95rem;">Acompañamiento zootécnico integral, capacitación en manejo de criaderos y soporte continuo a nuestros clientes.</p>
        </div>
    </div>
</div>
@endsection
