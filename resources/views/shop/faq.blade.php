@extends('layouts.app')

@section('title', 'Preguntas Frecuentes | Tenebrios Gold')

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-question-circle" style="color:#e4b353;"></i> Preguntas Frecuentes
    </h1>

    <!-- Buscador / Asistente Rápido conectado a GET /api/chatbot-faq -->
    <div style="background:#151515; color:#fff; padding:22px; border-radius:10px; margin-bottom:25px; border:1px solid #e4b353;">
        <h3 style="margin:0 0 8px 0; color:#e4b353; font-size:1.1rem;">
            <i class="fas fa-robot"></i> Asistente de Consultas Rápidas BIO-GEST
        </h3>
        <p style="margin:0 0 12px 0; font-size:0.88rem; color:#cbd5e1;">
            Escribe tu duda sobre conservación en nevera, envíos o garantía de llegada viva:
        </p>
        <div style="display:flex; gap:10px;">
            <input type="text" id="faqSearchInput" placeholder="Ej. nevera, garantía, envío gratis..."
                   onkeyup="consultarChatbotFaq(this.value)"
                   style="flex:1; padding:10px 14px; border-radius:6px; border:1px solid #e4b353; background:#242424; color:#fff;">
            <button type="button" onclick="consultarChatbotFaq(document.getElementById('faqSearchInput').value)"
                    style="background:#e4b353; color:#151515; border:none; padding:10px 18px; border-radius:6px; font-weight:700; cursor:pointer;">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
        <div id="faqSearchResults" style="margin-top:12px; display:none;"></div>
    </div>

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

@push('scripts')
<script>
function consultarChatbotFaq(query) {
    const resultsBox = document.getElementById('faqSearchResults');
    if (!query || query.trim().length < 2) {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        return;
    }

    fetch(`{{ route('faq.chatbot') }}?q=${encodeURIComponent(query.trim())}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        resultsBox.style.display = 'block';
        if (!data.results || data.results.length === 0) {
            resultsBox.innerHTML = '<div style="padding:10px; color:#fca5a5; font-size:0.88rem;">No encontramos coincidencias exactas. Envíanos tu consulta en la sección de Contacto.</div>';
            return;
        }
        resultsBox.innerHTML = data.results.map(item => `
            <div style="background:#242424; border-left:3px solid #e4b353; padding:12px; border-radius:6px; margin-top:8px;">
                <div style="font-weight:700; color:#e4b353; font-size:0.95rem;">${item.pregunta}</div>
                <div style="color:#e2e8f0; font-size:0.88rem; margin-top:4px;">${item.respuesta}</div>
            </div>
        `).join('');
    });
}
</script>
@endpush
@endsection
