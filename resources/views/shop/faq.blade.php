@extends('layouts.app')

@section('title', 'Preguntas Frecuentes | Tenebrios Gold')

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-question-circle" style="color:#e4b353;"></i> Preguntas Frecuentes (FAQ)
    </h1>

    <div style="display:flex; flex-direction:column; gap:20px;">
        @forelse($faqs as $faq)
            <div style="background:#fff; border-radius:8px; box-shadow:0 3px 10px rgba(0,0,0,0.06); padding:22px; border-left:4px solid #e4b353;">
                <span style="font-size:0.8rem; background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:12px; font-weight:700;">{{ $faq->categoria ?? 'General' }}</span>
                <h3 style="color:#151515; margin:10px 0 8px 0; font-size:1.15rem;">{{ $faq->pregunta }}</h3>
                <p style="color:#555; line-height:1.6; margin:0;">{{ $faq->respuesta }}</p>
            </div>
        @empty
            <p style="color:#777;">No hay preguntas frecuentes registradas por el momento.</p>
        @endforelse
    </div>
</div>
@endsection
