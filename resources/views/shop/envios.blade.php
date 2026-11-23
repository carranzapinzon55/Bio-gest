@extends('layouts.app')

@section('title', 'Envíos y Garantías | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/envios_style.css') }}">
@endpush

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px; line-height:1.7; color:#444;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:10px; margin-bottom:20px;">
        <i class="fas fa-truck-fast" style="color:#e4b353;"></i> Políticas de Envío y Garantías
    </h1>

    <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06); margin-bottom:20px;">
        <h3 style="color:#151515; margin-top:0;">Garantía 100% de Llegada Viva</h3>
        <p>Todos nuestros paquetes viajan en contenedores térmicos ventilados con sustrato alimenticio de salvado. Garantizamos la llegada de larvas vivas a todo el territorio nacional.</p>
    </div>

    <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06); margin-bottom:20px;">
        <h3 style="color:#151515; margin-top:0;">Envío Gratuito</h3>
        <p>A partir del <strong>Paquete Recomendado (2.000 larvas en adelante)</strong> o compras iguales o superiores a <strong>$122.900 COP</strong>, el costo de flete es completamente asumido por Tenebrios Gold.</p>
    </div>

    <div style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
        <h3 style="color:#151515; margin-top:0;">Reembolsos y Reposiciones</h3>
        <p>En caso de incidencias en el transporte, solicita la reposición dentro de las 24 horas posteriores a la recepción enviando un video del desempaque a nuestras líneas de atención.</p>
    </div>
</div>
@endsection
