package com.biogest.app.ui

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.ui.theme.DarkBackground
import com.biogest.app.ui.theme.GoldPrimary
import com.biogest.app.ui.theme.TextMuted
import com.biogest.app.viewmodel.BioGestViewModel

/**
 * Pantalla de Inicio de Sesión de BIO-GEST en Jetpack Compose (Material 3).
 * Sigue la paleta corporativa de Tenebrios Gold (Negro #151515 y Dorado #e4b353).
 */
@Composable
fun LoginScreen(
    viewModel: BioGestViewModel,
    onLoginSuccess: (token: String, nombre: String) -> Unit,
    modifier: Modifier = Modifier
) {
    var correo by remember { mutableStateOf("") }
    var clave by remember { mutableStateOf("") }
    val focusManager = LocalFocusManager.current

    Box(
        modifier = modifier
            .fillMaxSize()
            .background(DarkBackground)
            .padding(24.dp),
        contentAlignment = Alignment.Center
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .verticalScroll(rememberScrollState()),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            // Título Principal de la App
            Text(
                text = "BIO-GEST",
                fontSize = 36.sp,
                fontWeight = FontWeight.Bold,
                color = GoldPrimary,
                letterSpacing = 2.sp
            )

            // Subtítulo de marca de la empresa
            Text(
                text = "TENEBRIOS GOLD",
                fontSize = 13.sp,
                fontWeight = FontWeight.Medium,
                color = TextMuted,
                letterSpacing = 4.sp,
                modifier = Modifier.padding(bottom = 32.dp)
            )

            // Campo de Texto: Correo Electrónico
            OutlinedTextField(
                value = correo,
                onValueChange = {
                    correo = it
                    if (viewModel.mensaje != null) viewModel.mensaje = null
                },
                label = { Text("Correo electrónico") },
                singleLine = true,
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Email,
                    imeAction = ImeAction.Next
                ),
                keyboardActions = KeyboardActions(
                    onNext = { focusManager.moveFocus(FocusDirection.Down) }
                ),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = GoldPrimary,
                    focusedLabelColor = GoldPrimary,
                    cursorColor = GoldPrimary
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 16.dp)
            )

            // Campo de Texto: Contraseña Oculta
            OutlinedTextField(
                value = clave,
                onValueChange = {
                    clave = it
                    if (viewModel.mensaje != null) viewModel.mensaje = null
                },
                label = { Text("Contraseña") },
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
                    focusedBorderColor = GoldPrimary,
                    focusedLabelColor = GoldPrimary,
                    cursorColor = GoldPrimary
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 16.dp)
            )

            // Mensaje de Error (si el ViewModel reporta credenciales inválidas o falta de conexión)
            viewModel.mensaje?.let { msg ->
                Text(
                    text = msg,
                    color = MaterialTheme.colorScheme.error,
                    fontSize = 14.sp,
                    textAlign = TextAlign.Center,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 16.dp)
                )
            }

            // Botón de Ingreso
            Button(
                onClick = {
                    focusManager.clearFocus()
                    viewModel.login(correo, clave, onLoginSuccess)
                },
                enabled = !viewModel.cargando,
                shape = RoundedCornerShape(10.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = GoldPrimary,
                    contentColor = Color(0xFF151515),
                    disabledContainerColor = GoldPrimary.copy(alpha = 0.5f),
                    disabledContentColor = Color(0xFF151515).copy(alpha = 0.5f)
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .height(50.dp)
            ) {
                if (viewModel.cargando) {
                    CircularProgressIndicator(
                        color = Color(0xFF151515),
                        strokeWidth = 2.dp,
                        modifier = Modifier
                            .size(20.dp)
                            .padding(end = 8.dp)
                    )
                    Text(
                        text = "Ingresando...",
                        fontWeight = FontWeight.Bold,
                        fontSize = 16.sp
                    )
                } else {
                    Text(
                        text = "Ingresar",
                        fontWeight = FontWeight.Bold,
                        fontSize = 16.sp
                    )
                }
            }
        }
    }
}
