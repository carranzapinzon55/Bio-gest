package com.biogest.app.ui.theme

import android.app.Activity
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

/**
 * Esquema oficial de Tenebrios Gold para BIO-GEST.
 * Mantiene la estética premium oscura corporativa (Negro #151515 + Dorado #e4b353).
 * Cumple con contraste de accesibilidad WCAG (mínimo 4.5:1).
 */
private val BioGestColorScheme = darkColorScheme(
    primary = GoldPrimary,
    onPrimary = DarkBackground,
    primaryContainer = GoldDark,
    onPrimaryContainer = TextWhite,
    secondary = GoldLight,
    onSecondary = DarkBackground,
    secondaryContainer = DarkSurfaceVariant,
    onSecondaryContainer = GoldLight,
    tertiary = SuccessGreen,
    onTertiary = DarkBackground,
    background = DarkBackground,
    onBackground = TextWhite,
    surface = DarkSurface,
    onSurface = TextWhite,
    surfaceVariant = DarkSurfaceVariant,
    onSurfaceVariant = TextMuted,
    surfaceTint = GoldPrimary,
    outline = GoldDark,
    outlineVariant = DarkSurfaceVariant,
    error = ErrorRed,
    onError = DarkBackground,
    errorContainer = ErrorRedDark,
    onErrorContainer = ErrorRed
)

@Composable
fun BioGestTheme(
    darkTheme: Boolean = true, // Siempre mantiene la identidad oscura oficial
    content: @Composable () -> Unit
) {
    val colorScheme = BioGestColorScheme

    val view = LocalView.current
    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as? Activity)?.window
            if (window != null) {
                // Control de contraste de iconos de la barra de estado y navegación
                WindowCompat.getInsetsController(window, view).apply {
                    isAppearanceLightStatusBars = false
                    isAppearanceLightNavigationBars = false
                }
            }
        }
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}
