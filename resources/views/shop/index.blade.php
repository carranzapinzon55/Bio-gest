@extends('layouts.app')

@section('title', 'Tenebrios Gold | Venta de Larvas y Pie de Cría Tenebrio Molitor')

@section('content')
    <!-- CARRUSEL 1 - Principal -->
    <section class="carousel-container main-carousel" id="carousel1">
        <div class="carousel-track">
            <div class="carousel-slide active">
                <div class="banner-content logo-banner">
                    <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="Tenebrios Gold Logo">
                </div>
            </div>
            <div class="carousel-slide">
                <div class="placeholder-box banner">
                    <img src="{{ asset('assets/img/Tenebrios_img.png') }}" alt="Larvas de Tenebrio Molitor">
                </div>
            </div>
            <div class="carousel-slide">
                <div class="placeholder-box banner">
                    <img src="{{ asset('assets/img/Dubias_img.png') }}" alt="Alimento Vivo para Reptiles y Aves">
                </div>
            </div>
        </div>
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </section>

    <!-- CARACTERÍSTICAS Y GARANTÍAS -->
    <section class="features">
        <div class="feature-card">
            <i class="fas fa-leaf"></i>
            <p>Pie de cría y larvas<br>100% vivas y frescas</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-truck"></i>
            <p>Envíos acondicionados<br>con garantía de llegada viva</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-snowflake"></i>
            <p>Hibernación en nevera<br>hasta por 2.5 meses</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <p>Política de reembolso<br>ante mortalidad en transporte</p>
        </div>
    </section>

    <!-- CARRUSEL 2 - Secundario -->
    <section class="carousel-container secondary-carousel" id="carousel2">
        <div class="carousel-track">
            <div class="carousel-slide active"><div class="placeholder-box banner"><img src="{{ asset('assets/img/oferta_2.jfif') }}" alt="Ofertas Tenebrios Gold"></div></div>
            <div class="carousel-slide"><div class="placeholder-box banner"><img src="{{ asset('assets/img/oferta_1.jfif') }}" alt="Ofertas Tenebrios Gold"></div></div>
            <div class="carousel-slide"><div class="placeholder-box banner"><img src="{{ asset('assets/img/oferta_3.jfif') }}" alt="Ofertas Tenebrios Gold"></div></div>
        </div>
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </section>

    <!-- PRODUCTOS DESTACADOS DESDE BASE DE DATOS -->
    <section class="categories">
        <h2>Nuestros Paquetes Destacados</h2>
        <div class="categories-grid">
            @forelse($productosDestacados as $producto)
                <div class="cat-card" style="display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <img src="{{ asset('assets/img/' . ($producto->imagen ?? 'Tenebrios_img.png')) }}"
                             alt="{{ $producto->nombre_producto }}"
                             class="cat-img"
                             onerror="this.src='{{ asset('assets/img/Tenebrios_img.png') }}'">
                        <h3>{{ $producto->nombre_producto }}</h3>
                        <p style="text-align:center; color:#777; font-size:0.9rem; margin:8px 0;">
                            {{ \Illuminate\Support\Str::limit($producto->descripcion, 85) }}
                        </p>
                    </div>
                    <div style="text-align:center; margin-top:12px;">
                        <p style="font-size:1.25rem; font-weight:bold; color:#151515; margin-bottom:10px;">
                            ${{ number_format($producto->precio, 0, ',', '.') }}
                        </p>
                        <a href="{{ route('catalogo.show', $producto->id_producto) }}"
                           style="display:inline-block; background:#e4b353; color:#151515; font-weight:bold; padding:8px 18px; border-radius:20px;">
                            Ver detalle
                        </a>
                    </div>
                </div>
            @empty
                <p style="text-align:center; grid-column: 1 / -1;">No hay productos activos en este momento.</p>
            @endforelse
        </div>
    </section>

    <!-- CATEGORÍAS POPULARES -->
    <section class="categories">
        <h2>Categorías populares</h2>
        <div class="categories-grid">
            <a href="{{ route('catalogo.index') }}" class="cat-card">
                <img src="{{ asset('assets/img/Tenebrios_img.png') }}" alt="Tenebrios" class="cat-img">
                <h3>Tenebrios (Larvas y Semilla)</h3>
            </a>
            <a href="{{ route('catalogo.index') }}" class="cat-card">
                <img src="{{ asset('assets/img/Zophobas_img.png') }}" alt="Zophobas" class="cat-img">
                <h3>Zophobas</h3>
            </a>
            <a href="{{ route('catalogo.index') }}" class="cat-card">
                <img src="{{ asset('assets/img/Dubias_img.png') }}" alt="Cucarachas Dubia" class="cat-img">
                <h3>Cucarachas Dubia</h3>
            </a>
            <a href="{{ route('catalogo.index') }}" class="cat-card">
                <img src="{{ asset('assets/img/Combos_img.png') }}" alt="Combo alimentos" class="cat-img">
                <h3>Kits y Combos de Cría</h3>
            </a>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="newsletter">
        <div class="news-left">
            <div class="placeholder-box small-square">
                <img src="{{ asset('assets/img/pro.jfif') }}" alt="Vuélvete pro">
            </div>
            <div class="news-text">
                <h3>Recibe novedades y promociones</h3>
                <p>Suscríbete para recibir ofertas exclusivas y guías de cuidado</p>
            </div>
        </div>
        <div class="news-form">
            <input type="email" placeholder="Tu correo electrónico">
            <button type="button">Suscribirme</button>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/index_script.js') }}"></script>
@endpush
