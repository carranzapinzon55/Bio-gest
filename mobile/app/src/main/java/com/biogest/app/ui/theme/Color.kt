package com.biogest.app.ui.theme

import androidx.compose.ui.graphics.Color

// =========================================================================
// PALETA DE COLORES OFICIAL - TENEBRIOS GOLD (BIO-GEST)
// Identidad visual coherente con el portal web: Negro #151515 y Dorado #E4B353.
// Todos los colores cumplen contraste WCAG AA/AAA (> 4.5:1) para accesibilidad.
// =========================================================================

// Tonos Dorados (Marca Corporativa)
val GoldPrimary = Color(0xFFE4B353)        // Dorado principal (#e4b353) - Botones principales y títulos
val GoldDark = Color(0xFFB58928)           // Dorado oscuro - Bordes y contenedores de realce
val GoldLight = Color(0xFFF7D58C)          // Dorado suave - Acentos secundarios y subtítulos

// Tonos Oscuros de Fondo y Superficie
val DarkBackground = Color(0xFF151515)     // Fondo base (#151515) - Negro carbón profundo
val DarkSurface = Color(0xFF1E1E1E)        // Superficie de tarjetas y modales
val DarkSurfaceVariant = Color(0xFF2A2A2A) // Variante para campos de formulario y divisores
val DarkSurfaceHighlight = Color(0xFF333333)// Superficie interactiva al presionar

// Tipografía y Contraste
val TextWhite = Color(0xFFFFFFFF)          // Blanco puro (Contraste 16.5:1 sobre #151515)
val TextMuted = Color(0xFFB0B0B0)          // Gris claro secundario (Contraste 7.4:1 sobre #151515)

// Estados del Sistema (Validación y Alertas)
val ErrorRed = Color(0xFFCF6679)           // Rojo accesible para errores de validación
val ErrorRedDark = Color(0xFF3D1418)       // Fondo sutil para alertas de error
val SuccessGreen = Color(0xFF81C784)       // Verde claro para mensajes de éxito
val SuccessGreenDark = Color(0xFF1B5E20)   // Contenedor verde para banners de confirmación
val StatusActiveBg = Color(0x3381C784)     // Fondo translúcido para badge Activo
val StatusInactiveBg = Color(0x33CF6679)   // Fondo translúcido para badge Inactivo
val StatusInactiveText = Color(0xFFE57373) // Texto para badge Inactivo
