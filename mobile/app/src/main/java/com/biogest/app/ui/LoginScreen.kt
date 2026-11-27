package com.biogest.app.ui

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.ui.theme.BioGestTheme
import com.biogest.app.viewmodel.BioGestViewModel

/**
 * Pantalla de Inicio de Sesión de BIO-GEST (Tenebrios Gold).
 *
 * Características Responsive y UX:
 * 1. [BoxWithConstraints]: detecta el ancho disponible para aplicar márgenes proporcionales.
 * 2. [Modifier.widthIn(max = 480.dp)]: previene que el formulario se estire de forma antiestética en tabletas.
 * 3. [verticalScroll] + [imePadding]: permite desplazarse cuando el teclado en pantalla se abre o el móvil rota a horizontal.
 * 4. [rememberSaveable]: preserva el texto ingresado por el usuario al rotar el dispositivo.
 * 5. Áreas táctiles mínimas de 48 dp y un único botón de acción primario.
 */
@Composable
fun LoginScreen(
    viewModel: BioGestViewModel,
    onLoginSuccess: (token: String, nombre: String) -> Unit,
    modifier: Modifier = Modifier
) {
    // rememberSaveable asegura que el texto sobreviva al giro de pantalla (recreación de Activity)
    var correo by rememberSaveable { mutableStateOf("") }
    var clave by rememberSaveable { mutableStateOf("") }
    val focusManager = LocalFocusManager.current

    BoxWithConstraints(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .imePadding(), // Evita que el teclado virtual tape los campos o el botón
        contentAlignment = Alignment.Center
    ) {
        // Breakpoints de diseño responsive
        val paddingHorizontal = if (maxWidth > 600.dp) 32.dp else 24.dp

        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState()) // Permite scroll en modo apaisado (landscape)
                .padding(horizontal = paddingHorizontal, vertical = 24.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            // Tarjeta contenedora con ancho máximo para pantallas grandes
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .widthIn(max = 460.dp),
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.surface
                ),
                elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 24.dp, vertical = 32.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    // Logotipo / Título de la Aplicación
                    Text(
                        text = "BIO-GEST",
                        style = MaterialTheme.typography.headlineLarge,
                        color = MaterialTheme.colorScheme.primary,
                        textAlign = TextAlign.Center
                    )

                    // Subtítulo institucional de la empresa
                    Text(
                        text = "TENEBRIOS GOLD",
                        style = MaterialTheme.typography.bodySmall.copy(letterSpacing = 4.sp),
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.padding(top = 4.dp, bottom = 24.dp)
                    )

                    // Campo de Texto: Correo Electrónico
                    OutlinedTextField(
                        value = correo,
                        onValueChange = {
                            correo = it
                            if (viewModel.mensaje != null) viewModel.mensaje = null
                        },
                        label = { Text("Correo electrónico", style = MaterialTheme.typography.bodyMedium) },
                        singleLine = true,
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Email,
                            imeAction = ImeAction.Next
                        ),
                        keyboardActions = KeyboardActions(
                            onNext = { focusManager.moveFocus(FocusDirection.Down) }
                        ),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = MaterialTheme.colorScheme.primary,
                            focusedLabelColor = MaterialTheme.colorScheme.primary,
                            cursorColor = MaterialTheme.colorScheme.primary,
                            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
                            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
                        ),
                        shape = RoundedCornerShape(10.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(bottom = 16.dp)
                    )

                    // Campo de Texto: Contraseña
                    OutlinedTextField(
                        value = clave,
                        onValueChange = {
                            clave = it
                            if (viewModel.mensaje != null) viewModel.mensaje = null
                        },
                        label = { Text("Contraseña", style = MaterialTheme.typography.bodyMedium) },
                        singleLine = true,
                        visualTransformation = PasswordVisualTransformation(),
                        keyboardOptions = KeyboardOptions(
                            keyboardType = KeyboardType.Password,
                            imeAction = ImeAction.Done
                        ),
                        keyboardActions = KeyboardActions(
                            onDone = {
                                focusManager.clearFocus()
                                viewModel.login(correo, clave, onLoginSuccess)
                            }
                        ),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = MaterialTheme.colorScheme.primary,
                            focusedLabelColor = MaterialTheme.colorScheme.primary,
                            cursorColor = MaterialTheme.colorScheme.primary,
                            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
                            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
                        ),
                        shape = RoundedCornerShape(10.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(bottom = 16.dp)
                    )

                    // Mensaje de Error Visible con contraste y texto explícito
                    viewModel.mensaje?.let { msg ->
                        Text(
                            text = msg,
                            color = MaterialTheme.colorScheme.error,
                            style = MaterialTheme.typography.bodyMedium,
                            fontWeight = FontWeight.Medium,
                            textAlign = TextAlign.Center,
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(bottom = 16.dp)
                        )
                    }

                    Spacer(modifier = Modifier.height(8.dp))

                    // Único Botón Primario de la pantalla (Área táctil >= 48 dp)
                    Button(
                        onClick = {
                            focusManager.clearFocus()
                            viewModel.login(correo, clave, onLoginSuccess)
                        },
                        enabled = !viewModel.cargando,
                        shape = RoundedCornerShape(10.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = MaterialTheme.colorScheme.primary,
                            contentColor = MaterialTheme.colorScheme.onPrimary,
                            disabledContainerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.5f),
                            disabledContentColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.5f)
                        ),
                        modifier = Modifier
                            .fillMaxWidth()
                            .heightIn(min = 50.dp) // Cumple pauta de accesibilidad (>= 48 dp)
                    ) {
                        if (viewModel.cargando) {
                            CircularProgressIndicator(
                                color = MaterialTheme.colorScheme.onPrimary,
                                strokeWidth = 2.dp,
                                modifier = Modifier
                                    .size(20.dp)
                                    .padding(end = 8.dp)
                            )
                            Text(
                                text = "Ingresando...",
                                style = MaterialTheme.typography.labelLarge
                            )
                        } else {
                            Text(
                                text = "Ingresar",
                                style = MaterialTheme.typography.labelLarge
                            )
                        }
                    }
                }
            }
        }
    }
}

// =========================================================================
// VISTAS PREVIAS MULTIDISPOSITIVO (RESPONSIVE PREVIEWS)
// =========================================================================

@Preview(name = "1. Celular Pequeño", widthDp = 360, heightDp = 640, showBackground = true)
@Composable
private fun LoginScreenPreviewSmall() {
    BioGestTheme {
        LoginScreen(
            viewModel = BioGestViewModel(),
            onLoginSuccess = { _, _ -> }
        )
    }
}

@Preview(name = "2. Celular Grande Vertical", device = "spec:width=411dp,height=891dp", showBackground = true)
@Composable
private fun LoginScreenPreviewLargePortrait() {
    BioGestTheme {
        LoginScreen(
            viewModel = BioGestViewModel(),
            onLoginSuccess = { _, _ -> }
        )
    }
}

@Preview(name = "3. Celular Grande Horizontal", device = "spec:width=891dp,height=411dp", showBackground = true)
@Composable
private fun LoginScreenPreviewLargeLandscape() {
    BioGestTheme {
        LoginScreen(
            viewModel = BioGestViewModel(),
            onLoginSuccess = { _, _ -> }
        )
    }
}

@Preview(name = "4. Tableta", device = "spec:width=1280dp,height=800dp", showBackground = true)
@Composable
private fun LoginScreenPreviewTablet() {
    BioGestTheme {
        LoginScreen(
            viewModel = BioGestViewModel(),
            onLoginSuccess = { _, _ -> }
        )
    }
}
