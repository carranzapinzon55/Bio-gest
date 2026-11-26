package com.biogest.app.ui

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.data.ProductoRequest
import com.biogest.app.ui.theme.DarkBackground
import com.biogest.app.ui.theme.DarkSurfaceVariant
import com.biogest.app.ui.theme.GoldPrimary
import com.biogest.app.ui.theme.TextMuted
import com.biogest.app.ui.theme.TextWhite
import com.biogest.app.viewmodel.BioGestViewModel

/**
 * Pantalla de Formulario para Registrar Producto (BIO-GEST / TENEBRIOS GOLD).
 * Valida todas las reglas de negocio en el cliente antes de enviar la petición POST:
 * - Nombre obligatorio.
 * - Categoría válida (1, 2 o 3).
 * - Precio estrictamente mayor a 0 (CHECK precio > 0).
 * - Stock mayor o igual a 0 (CHECK stock >= 0).
 * - Stock mínimo mayor o igual a 0.
 */
@Composable
fun FormularioScreen(
    viewModel: BioGestViewModel,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier
) {
    // -------------------------------------------------------------------------
    // ESTADOS DE LOS CAMPOS DEL FORMULARIO
    // -------------------------------------------------------------------------
    var nombre by remember { mutableStateOf("") }
    var descripcion by remember { mutableStateOf("") }
    var precioTexto by remember { mutableStateOf("") }
    var stockTexto by remember { mutableStateOf("") }
    var stockMinimoTexto by remember { mutableStateOf("10") }

    // Las 3 categorías oficiales de BIO-GEST
    val categorias = listOf(
        1 to "Paquetes de Larvas",
        2 to "Abono Orgánico",
        3 to "Kits de Cría"
    )
    var categoriaId by remember { mutableIntStateOf(1) }
    var menuExpandido by remember { mutableStateOf(false) }

    // -------------------------------------------------------------------------
    // ESTADOS DE VALIDACIÓN EN CLIENTE
    // -------------------------------------------------------------------------
    var errorNombre by remember { mutableStateOf<String?>(null) }
    var errorPrecio by remember { mutableStateOf<String?>(null) }
    var errorStock by remember { mutableStateOf<String?>(null) }
    var errorStockMinimo by remember { mutableStateOf<String?>(null) }
    var errorGeneral by remember { mutableStateOf<String?>(null) }

    Scaffold(
        modifier = modifier.fillMaxSize(),
        containerColor = DarkBackground
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(horizontal = 20.dp)
                .verticalScroll(rememberScrollState())
        ) {
            Spacer(modifier = Modifier.height(16.dp))

            // Título de la Pantalla
            Text(
                text = "Nuevo Producto",
                fontSize = 26.sp,
                fontWeight = FontWeight.Bold,
                color = GoldPrimary
            )
            Text(
                text = "Completa los datos del catálogo de Tenebrios Gold",
                fontSize = 13.sp,
                color = TextMuted,
                modifier = Modifier.padding(bottom = 20.dp)
            )

            // 1. Campo: Nombre del Producto (Obligatorio)
            OutlinedTextField(
                value = nombre,
                onValueChange = {
                    nombre = it
                    errorNombre = null
                    errorGeneral = null
                    viewModel.mensaje = null
                },
                label = { Text("Nombre del producto *") },
                isError = errorNombre != null,
                supportingText = {
                    errorNombre?.let { Text(it, color = MaterialTheme.colorScheme.error) }
                },
                singleLine = true,
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = GoldPrimary,
                    focusedLabelColor = GoldPrimary,
                    cursorColor = GoldPrimary
                ),
                modifier = Modifier.fillMaxWidth()
            )

            Spacer(modifier = Modifier.height(8.dp))

            // 2. Campo: Categoría (Menú desplegable con las 3 categorías)
            val nombreCategoriaActual = categorias.find { it.first == categoriaId }?.second ?: ""
            Box(modifier = Modifier.fillMaxWidth()) {
                OutlinedTextField(
                    value = nombreCategoriaActual,
                    onValueChange = {},
                    readOnly = true,
                    label = { Text("Categoría *") },
                    trailingIcon = {
                        Text(
                            text = if (menuExpandido) "▲" else "▼",
                            color = GoldPrimary,
                            fontSize = 14.sp,
                            modifier = Modifier.padding(end = 12.dp)
                        )
                    },
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = GoldPrimary,
                        focusedLabelColor = GoldPrimary
                    ),
                    modifier = Modifier.fillMaxWidth()
                )

                // Capa transparente para detectar el clic en todo el campo
                Box(
                    modifier = Modifier
                        .matchParentSize()
                        .clickable { menuExpandido = true }
                )

                DropdownMenu(
                    expanded = menuExpandido,
                    onDismissRequest = { menuExpandido = false },
                    modifier = Modifier.background(DarkSurfaceVariant)
                ) {
                    categorias.forEach { (id, etiqueta) ->
                        DropdownMenuItem(
                            text = { Text(etiqueta, color = TextWhite) },
                            onClick = {
                                categoriaId = id
                                menuExpandido = false
                            }
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(14.dp))

            // 3. Campo: Descripción (Opcional)
            OutlinedTextField(
                value = descripcion,
                onValueChange = { descripcion = it },
                label = { Text("Descripción (opcional)") },
                maxLines = 3,
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = GoldPrimary,
                    focusedLabelColor = GoldPrimary,
                    cursorColor = GoldPrimary
                ),
                modifier = Modifier.fillMaxWidth()
            )

            Spacer(modifier = Modifier.height(8.dp))

            // 4. Campo: Precio (Obligatorio, > 0)
            OutlinedTextField(
                value = precioTexto,
                onValueChange = {
                    precioTexto = it
                    errorPrecio = null
                    errorGeneral = null
                    viewModel.mensaje = null
                },
                label = { Text("Precio en COP * (Ej: 34900)") },
                isError = errorPrecio != null,
                supportingText = {
                    errorPrecio?.let { Text(it, color = MaterialTheme.colorScheme.error) }
                },
                singleLine = true,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = GoldPrimary,
                    focusedLabelColor = GoldPrimary,
                    cursorColor = GoldPrimary
                ),
                modifier = Modifier.fillMaxWidth()
            )

            Spacer(modifier = Modifier.height(8.dp))

            // 5. Fila: Stock y Stock Mínimo
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                // Campo: Stock (Obligatorio, >= 0)
                OutlinedTextField(
                    value = stockTexto,
                    onValueChange = {
                        stockTexto = it
                        errorStock = null
                        errorGeneral = null
                        viewModel.mensaje = null
                    },
                    label = { Text("Stock inicial *") },
                    isError = errorStock != null,
                    supportingText = {
                        errorStock?.let { Text(it, color = MaterialTheme.colorScheme.error) }
                    },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = GoldPrimary,
                        focusedLabelColor = GoldPrimary,
                        cursorColor = GoldPrimary
                    ),
                    modifier = Modifier.weight(1f)
                )

                // Campo: Stock Mínimo (Opcional/Default 10, >= 0)
                OutlinedTextField(
                    value = stockMinimoTexto,
                    onValueChange = {
                        stockMinimoTexto = it
                        errorStockMinimo = null
                        errorGeneral = null
                        viewModel.mensaje = null
                    },
                    label = { Text("Stock mín.") },
                    isError = errorStockMinimo != null,
                    supportingText = {
                        errorStockMinimo?.let { Text(it, color = MaterialTheme.colorScheme.error) }
                    },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = GoldPrimary,
                        focusedLabelColor = GoldPrimary,
                        cursorColor = GoldPrimary
                    ),
                    modifier = Modifier.weight(1f)
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Mensajes de Error (Validación del Cliente o Respuesta del Servidor)
            val mensajeAMostrar = errorGeneral ?: viewModel.mensaje
            if (mensajeAMostrar != null) {
                Text(
                    text = mensajeAMostrar,
                    color = MaterialTheme.colorScheme.error,
                    fontSize = 14.sp,
                    fontWeight = FontWeight.Medium,
                    textAlign = TextAlign.Center,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 12.dp)
                )
            }

            // 6. Botones de Acción: Guardar y Cancelar
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 24.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                // Botón Cancelar
                OutlinedButton(
                    onClick = {
                        viewModel.mensaje = null
                        viewModel.cambiarPantalla("lista")
                    },
                    enabled = !viewModel.guardando,
                    shape = RoundedCornerShape(10.dp),
                    colors = ButtonDefaults.outlinedButtonColors(
                        contentColor = TextMuted
                    ),
                    modifier = Modifier
                        .weight(1f)
                        .height(50.dp)
                ) {
                    Text(text = "Cancelar", fontWeight = FontWeight.SemiBold)
                }

                // Botón Guardar
                Button(
                    onClick = {
                        // Limpiar estados de error previos
                        errorNombre = null
                        errorPrecio = null
                        errorStock = null
                        errorStockMinimo = null
                        errorGeneral = null
                        viewModel.mensaje = null

                        var hayErrores = false

                        // A. Validación: Nombre no vacío
                        if (nombre.trim().isBlank()) {
                            errorNombre = "El nombre es obligatorio"
                            hayErrores = true
                        }

                        // B. Validación: Precio mayor que 0
                        val precioParsed = precioTexto.trim().toDoubleOrNull()
                        if (precioTexto.trim().isBlank()) {
                            errorPrecio = "El precio es obligatorio"
                            hayErrores = true
                        } else if (precioParsed == null || precioParsed <= 0.0) {
                            errorPrecio = "El precio debe ser mayor que 0"
                            hayErrores = true
                        }

                        // C. Validación: Stock entero mayor o igual a 0
                        val stockParsed = stockTexto.trim().toIntOrNull()
                        if (stockTexto.trim().isBlank()) {
                            errorStock = "El stock es obligatorio"
                            hayErrores = true
                        } else if (stockParsed == null || stockParsed < 0) {
                            errorStock = "El stock no puede ser negativo"
                            hayErrores = true
                        }

                        // D. Validación: Stock mínimo entero mayor o igual a 0
                        val stockMinimoParsed = if (stockMinimoTexto.trim().isNotBlank()) {
                            stockMinimoTexto.trim().toIntOrNull()
                        } else {
                            10
                        }
                        if (stockMinimoParsed == null || stockMinimoParsed < 0) {
                            errorStockMinimo = "El stock no puede ser negativo"
                            hayErrores = true
                        }

                        // Si falló alguna validación en el cliente, detenemos el envío
                        if (hayErrores) {
                            errorGeneral = "Todos los campos obligatorios deben estar llenos"
                            return@Button
                        }

                        // Construcción del DTO de petición real
                        val nuevoProducto = ProductoRequest(
                            idCategoria = categoriaId,
                            nombreProducto = nombre.trim(),
                            descripcion = descripcion.trim().ifBlank { null },
                            precio = precioParsed!!,
                            stock = stockParsed!!,
                            stockMinimo = stockMinimoParsed ?: 10,
                            estado = "Activo"
                        )

                        // Enviar al ViewModel
                        viewModel.crearProducto(
                            request = nuevoProducto,
                            alExpirar = onLogout
                        )
                    },
                    enabled = !viewModel.guardando,
                    shape = RoundedCornerShape(10.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = GoldPrimary,
                        contentColor = DarkBackground,
                        disabledContainerColor = GoldPrimary.copy(alpha = 0.5f),
                        disabledContentColor = DarkBackground.copy(alpha = 0.5f)
                    ),
                    modifier = Modifier
                        .weight(1f)
                        .height(50.dp)
                ) {
                    if (viewModel.guardando) {
                        CircularProgressIndicator(
                            color = DarkBackground,
                            strokeWidth = 2.dp,
                            modifier = Modifier
                                .size(18.dp)
                                .padding(end = 6.dp)
                        )
                        Text(text = "Guardando...", fontWeight = FontWeight.Bold)
                    } else {
                        Text(text = "Guardar", fontWeight = FontWeight.Bold)
                    }
                }
            }
        }
    }
}
