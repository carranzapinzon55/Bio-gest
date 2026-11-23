@extends('layouts.app')

@section('title', 'Calculadora de Alimento Vivo | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/calculadora_style.css') }}">
@endpush

@section('content')
<div style="max-width:850px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:35px; border-top:4px solid #e4b353;">
        <h1 style="color:#151515; font-size:1.8rem; margin-bottom:10px;"><i class="fas fa-calculator" style="color:#e4b353;"></i> Calculadora de Alimento Vivo</h1>
        <p style="color:#666; margin-bottom:30px;">Calcula el requerimiento mensual de larvas de Tenebrio Molitor para tus animales y recibe el paquete recomendado.</p>

        <form id="calcForm" onsubmit="event.preventDefault(); calcularAlimento();">
            @csrf
            <div style="margin-bottom:20px;">
                <label for="especie" style="display:block; font-weight:700; margin-bottom:8px; color:#333;">Tipo de Animal / Proyecto:</label>
                <select id="especie" name="especie" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem;">
                    <option value="reptil">Reptiles (Geckos, Pogonas, Tortugas acuáticas)</option>
                    <option value="ave">Aves (Canarios, Diamantes, Pollos de engorde / Finca)</option>
                    <option value="pez">Peces de estanque / Acuarios carnívoros</option>
                    <option value="erizo">Erizos africanos / Petauros</option>
                    <option value="criadero">Pie de cría intensivo / Granja de reproducción</option>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label for="cantidad_animales" style="display:block; font-weight:700; margin-bottom:8px; color:#333;">Cantidad de Animales:</label>
                <input type="number" id="cantidad_animales" name="cantidad_animales" value="2" min="1" max="5000" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem;">
            </div>

            <div style="margin-bottom:25px;">
                <label for="frecuencia_semanal" style="display:block; font-weight:700; margin-bottom:8px; color:#333;">Frecuencia de alimentación semanal (días por semana):</label>
                <input type="number" id="frecuencia_semanal" name="frecuencia_semanal" value="3" min="1" max="7" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem;">
            </div>

            <button type="submit" style="width:100%; background:#151515; color:#e4b353; border:none; padding:14px; border-radius:8px; font-size:1.1rem; font-weight:700; cursor:pointer;">
                <i class="fas fa-chart-line"></i> Calcular Consumo y Paquete
            </button>
        </form>

        <div id="resultadoCalc" style="display:none; margin-top:30px; background:#fafafa; border-radius:8px; padding:25px; border:1px solid #e5e7eb;">
            <h3 style="color:#151515; margin-top:0;">Resultado del Cálculo</h3>
            <p id="consumoText" style="font-size:1.1rem; color:#333; font-weight:600;"></p>
            <div id="paqueteRecomendado" style="background:#fff; border:2px solid #e4b353; border-radius:8px; padding:15px; margin:15px 0;"></div>
            <p id="consejoText" style="color:#0284c7; font-size:0.9rem; margin-bottom:0;"><i class="fas fa-info-circle"></i> </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calcularAlimento() {
    const data = {
        _token: '{{ csrf_token() }}',
        especie: document.getElementById('especie').value,
        cantidad_animales: parseInt(document.getElementById('cantidad_animales').value),
        frecuencia_semanal: parseInt(document.getElementById('frecuencia_semanal').value),
    };

    fetch('{{ route("calculadora.calcular") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            document.getElementById('resultadoCalc').style.display = 'block';
            document.getElementById('consumoText').innerText = `Tu consumo mensual estimado es de ${res.consumo_mensual.toLocaleString()} larvas al mes.`;
            if (res.producto_recomendado) {
                document.getElementById('paqueteRecomendado').innerHTML = `
                    <h4 style="margin:0 0 8px 0; color:#151515;">Paquete Sugerido: ${res.producto_recomendado.nombre_producto}</h4>
                    <p style="margin:0 0 10px 0; color:#555;">${res.producto_recomendado.descripcion}</p>
                    <div style="font-size:1.3rem; font-weight:800; color:#151515;">$${parseFloat(res.producto_recomendado.precio).toLocaleString()} COP</div>
                    <a href="{{ url('catalogo/producto') }}/${res.producto_recomendado.id_producto}" style="display:inline-block; margin-top:10px; background:#e4b353; color:#151515; padding:8px 16px; border-radius:5px; text-decoration:none; font-weight:700;">Ver Detalle del Paquete</a>
                `;
            }
            document.getElementById('consejoText').innerHTML = `<i class="fas fa-snowflake"></i> ${res.consejo_conservacion}`;
        }
    });
}
</script>
@endpush
@endsection
