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
                Parámetros comerciales, cálculo de envíos, perfil administrativo y copias de seguridad de <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.ajustes.respaldo.sql') }}" class="tg-btn-dark" style="background:#0f172a; border-color:#334155;" title="Exportar volcado completo de tablas y registros MySQL">
                <i class="fa-solid fa-database" style="color:#e6a100;"></i> Descargar respaldo (.sql)
            </a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(420px, 1fr)); gap:24px;">

        <!-- TARJETA 1: PARÁMETROS COMERCIALES Y CÁLCULO DE ENVÍOS -->
        <div class="tg-card">
            <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:18px;">
                <h2 style="font-size:1.1rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-sliders" style="color:#e6a100;"></i> Parámetros Comerciales y Envíos
                </h2>
                <span style="font-size:0.75rem; background:#ecfdf5; color:#065f46; padding:3px 8px; border-radius:999px; font-weight:700;">
                    Sincronizado con Checkout
                </span>
            </div>

            <p style="color:#64748b; font-size:0.84rem; margin-bottom:18px;">
                Estos valores determinan el costo de despacho y los impuestos calculados automáticamente en la pasarela de compra de cara al cliente.
            </p>

            <form action="{{ route('admin.ajustes.update') }}" method="POST">
                @csrf

                <!-- Tarifas de Envíos -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Costo Envío Bogotá (COP) *
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#64748b; font-weight:700;">$</span>
                            <input type="number" step="100" min="0" name="costo_envio_bogota" required
                                   value="{{ old('costo_envio_bogota', $ajustes['costo_envio_bogota']) }}"
                                   style="width:100%; padding:9px 12px 9px 28px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem; font-weight:600;">
                        </div>
                        <small style="color:#64748b; font-size:0.75rem;">Aplica para entregas en Bogotá D.C.</small>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Costo Envío Nacional (COP) *
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#64748b; font-weight:700;">$</span>
                            <input type="number" step="100" min="0" name="costo_envio_nacional" required
                                   value="{{ old('costo_envio_nacional', $ajustes['costo_envio_nacional']) }}"
                                   style="width:100%; padding:9px 12px 9px 28px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem; font-weight:600;">
                        </div>
                        <small style="color:#64748b; font-size:0.75rem;">Aplica para cobertura nacional</small>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Monto Mínimo Envío Gratis (COP) *
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#64748b; font-weight:700;">$</span>
                            <input type="number" step="100" min="0" name="monto_envio_gratis" required
                                   value="{{ old('monto_envio_gratis', $ajustes['monto_envio_gratis']) }}"
                                   style="width:100%; padding:9px 12px 9px 28px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem; font-weight:600;">
                        </div>
                        <small style="color:#64748b; font-size:0.75rem;">Compras iguales o superiores no pagan flete</small>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Porcentaje IVA General (%) *
                        </label>
                        <div style="position:relative;">
                            <input type="number" step="0.5" min="0" max="100" name="porcentaje_iva" required
                                   value="{{ old('porcentaje_iva', $ajustes['porcentaje_iva']) }}"
                                   style="width:100%; padding:9px 28px 9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem; font-weight:600;">
                            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#64748b; font-weight:700;">%</span>
                        </div>
                        <small style="color:#64748b; font-size:0.75rem;">Tarifa impositiva oficial en Colombia</small>
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                        <i class="fa-brands fa-whatsapp" style="color:#10b981;"></i> Línea Directa de WhatsApp Soporte *
                    </label>
                    <input type="text" name="whatsapp_soporte" required
                           value="{{ old('whatsapp_soporte', $ajustes['whatsapp_soporte']) }}"
                           placeholder="+57 310 000 0000"
                           style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    <small style="color:#64748b; font-size:0.75rem;">Enlazado al botón flotante de atención en la tienda web</small>
                </div>

                <!-- Switches Notificaciones -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:20px;">
                    <strong style="display:block; font-size:0.8rem; text-transform:uppercase; color:#475569; margin-bottom:10px;">
                        Alertas y Notificaciones Activas
                    </strong>

                    <label style="display:flex; align-items:center; gap:10px; margin-bottom:10px; cursor:pointer; font-size:0.86rem; color:#111111;">
                        <input type="checkbox" name="alerta_stock_bajo" value="1" {{ $ajustes['alerta_stock_bajo'] ? 'checked' : '' }} style="width:18px; height:18px;">
                        <span>Emitir badge rojo de alerta cuando el stock esté en o por debajo del mínimo</span>
                    </label>

                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.86rem; color:#111111;">
                        <input type="checkbox" name="alerta_nuevo_pedido" value="1" {{ $ajustes['alerta_nuevo_pedido'] ? 'checked' : '' }} style="width:18px; height:18px;">
                        <span>Notificar inmediatamente al registrarse un nuevo pedido en la tienda</span>
                    </label>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="tg-btn-dark" style="padding:10px 22px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Ajustes Comerciales
                    </button>
                </div>
            </form>
        </div>

        <!-- TARJETA 2: PERFIL DE ADMINISTRADOR Y RESPALDO BASE DE DATOS -->
        <div style="display:flex; flex-direction:column; gap:24px;">

            <!-- Perfil del Administrador Autenticado -->
            <div class="tg-card">
                <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:16px;">
                    <h2 style="font-size:1.1rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-user-gear" style="color:#e6a100;"></i> Perfil del Administrador Autenticado
                    </h2>
                    <span style="background:#111111; color:#e6a100; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:999px;">
                        <i class="fa-solid fa-shield-halved"></i> {{ $admin->rol?->nombre_rol ?? 'Administrador' }}
                    </span>
                </div>

                <div style="display:flex; align-items:center; gap:16px; margin-bottom:18px;">
                    <div style="width:52px; height:52px; border-radius:50%; background:#111111; color:#e6a100; font-size:1.4rem; font-weight:800; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(230,161,0,0.3);">
                        {{ strtoupper(substr($admin->nombre ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.08rem; font-weight:800; color:#111111;">
                            {{ $admin->nombre_completo ?? 'Administrador BIO-GEST' }}
                        </h3>
                        <div style="color:#64748b; font-size:0.84rem;">
                            {{ $admin->correo ?? 'admin@tenebriosgold.com' }}
                        </div>
                        <div style="font-size:0.76rem; color:#94a3b8; margin-top:2px;">
                            Registrado: {{ $admin->fecha_registro ? \Carbon\Carbon::parse($admin->fecha_registro)->format('d/m/Y') : 'Oficial' }}
                        </div>
                    </div>
                </div>

                <div style="border-top:1px solid #f1f5f9; padding-top:14px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:0.84rem; margin-bottom:14px;">
                        <div>
                            <span style="color:#64748b; display:block; font-size:0.75rem;">Teléfono registrado:</span>
                            <strong style="color:#111111;">{{ $admin->telefono ?: 'No registrado' }}</strong>
                        </div>
                        <div>
                            <span style="color:#64748b; display:block; font-size:0.75rem;">Estado de cuenta:</span>
                            <strong style="color:#059669;"><i class="fa-solid fa-circle-check"></i> Activo</strong>
                        </div>
                    </div>
                    <a href="{{ route('admin.perfil.index') }}" class="tg-btn-gold" style="width:100%; justify-content:center; padding:9px 16px; font-size:0.86rem;">
                        <i class="fa-solid fa-user-pen"></i> Editar Mi Perfil y Contraseña
                    </a>
                </div>
            </div>

            <!-- Herramientas Administrativas para el Administrador No Técnico -->
            <div class="tg-card" style="border-left:4px solid #e6a100;">
                <div class="card-header-flex" style="margin-bottom:12px;">
                    <h2 style="font-size:1.05rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-toolbox" style="color:#e6a100;"></i> Herramientas de Mantenimiento de la Tienda
                    </h2>
                    <span style="font-size:0.75rem; background:#ecfdf5; color:#065f46; padding:2px 8px; border-radius:999px; font-weight:700;">
                        Gestión Intuitiva
                    </span>
                </div>

                <p style="color:#475569; font-size:0.84rem; line-height:1.45; margin-bottom:16px;">
                    Utilidades diseñadas para la administración diaria de <strong>Tenebrios Gold</strong> sin requerir conocimientos técnicos en bases de datos o servidores:
                </p>

                <div style="display:flex; flex-direction:column; gap:12px;">
                    <!-- Botón 1: Verificar estado y limpiar caché -->
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#f8fafc; padding:12px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                        <div>
                            <strong style="font-size:0.86rem; color:#111111; display:block;">Verificar estado de la tienda y limpiar caché</strong>
                            <span style="font-size:0.76rem; color:#64748b;">Refresca el catálogo, optimiza las rutas y asegura la carga inmediata de cambios.</span>
                        </div>
                        <form action="{{ route('admin.ajustes.limpiar.cache') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="tg-btn-dark" style="padding:8px 14px; font-size:0.82rem; white-space:nowrap;">
                                <i class="fa-solid fa-broom" style="color:#e6a100;"></i> Verificar y Limpiar
                            </button>
                        </form>
                    </div>

                    <!-- Botón 2: Descargar resumen de catálogo y precios -->
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#f8fafc; padding:12px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                        <div>
                            <strong style="font-size:0.86rem; color:#111111; display:block;">Descargar resumen general de catálogo y precios</strong>
                            <span style="font-size:0.76rem; color:#64748b;">Exporta la lista de productos vigentes, existencias y precios compatible con Excel.</span>
                        </div>
                        <a href="{{ route('admin.ajustes.catalogo.precios') }}" class="tg-btn-gold" style="padding:8px 14px; font-size:0.82rem; white-space:nowrap; text-decoration:none;">
                            <i class="fa-solid fa-file-excel"></i> Descargar Catálogo
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
