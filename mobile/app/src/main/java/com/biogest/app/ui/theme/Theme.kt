package com.biogest.app.ui.theme

import android.app.Activity
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

// Esquema Oscuro Tenebrios Gold (Negro #151515 y Dorado #e4b353)
private val DarkColorScheme = darkColorScheme(
    primary = GoldPrimary,
    onPrimary = DarkBackground,
    primaryContainer = GoldDark,
    onPrimaryContainer = TextWhite,
    secondary = GoldLight,
    onSecondary = DarkBackground,
    background = DarkBackground,
    onBackground = TextWhite,
    surface = DarkSurface,
    onSurface = TextWhite,
    surfaceVariant = DarkSurfaceVariant,
    onSurfaceVariant = TextMuted,
    error = ErrorRed,
    onError = DarkBackground
)

// Esquema Claro con acentos Dorados y fondo oscuro elegante
private val LightColorScheme = lightColorScheme(
    primary = GoldPrimary,
    onPrimary = DarkBackground,
    secondary = GoldDark,
    background = DarkBackground,
    onBackground = TextWhite,
    surface = DarkSurface,
    onSurface = TextWhite,
    error = ErrorRed
)

@Composable
fun BioGestTheme(
    darkTheme: Boolean = true, // Predeterminado en tema oscuro corporativo
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme

    val view = LocalView.current
    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as? Activity)?.window
            window?.statusBarColor = colorScheme.background.toArgb()
            if (window != null) {
                WindowCompat.getInsetsController(window, view).isAppearanceLightStatusBars = !darkTheme
            }
        }
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}