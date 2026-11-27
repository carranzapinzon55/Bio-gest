package com.biogest.app.ui

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
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
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.data.ProductoRequest
import com.biogest.app.ui.theme.BioGestTheme
import com.biogest.app.viewmodel.BioGestViewModel

/**
 * Pantalla de Formulario para Registrar Producto (BIO-GEST / Tenebrios Gold).
 *
 * Características Responsive y UX:
 * 1. [rememberSaveable]: preserva todos los campos al rotar la pantalla.
 * 2. [BoxWithConstraints]:
 *    - En pantallas compactas (< 600 dp): Formulario en una columna vertical.
 *    - En pantallas medianas o apaisadas (>= 600 dp): Distribución en dos columnas para aprovechar el ancho.
 * 3. [imePadding] + [verticalScroll]: el teclado no tapa los campos ni el botón; el formulario se desplaza fluidamente.
 * 4. [widthIn(max = 720.dp)]: evita que los campos se estiren de forma desproporcionada en tablets.
 * 5. Un solo botón primario ("Guardar"), botón secundario ("Cancelar") con áreas táctiles mínimas de 48 dp.
 * 6. Mensajes de error claros visibles con color y texto debajo de cada campo ([OutlinedTextField.supportingText]).
 */
@Composable
fun FormularioScreen(
    viewModel: BioGestViewModel,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier
) {
    // -------------------------------------------------------------------------
    // ESTADOS DE LOS CAMPOS (rememberSaveable preserva datos durante la rotación)
    // -------------------------------------------------------------------------
    var nombre by rememberSaveable { mutableStateOf("") }
    var descripcion by rememberSaveable { mutableStateOf("") }
    var precioTexto by rememberSaveable { mutableStateOf("") }
    var stockTexto by rememberSaveable { mutableStateOf("") }
    var stockMinimoTexto by rememberSaveable { mutableStateOf("10") }
    var categoriaId by rememberSaveable { mutableIntStateOf(1) }
    var menuExpandido by rememberSaveable { mutableStateOf(false) }

    // Categorías oficiales de BIO-GEST
    val categorias = listOf(
        1 to "Paquetes de Larvas",
        2 to "Abono Orgánico",
        3 to "Kits de Cría"
    )

    // Estados de validación en cliente
    var errorNombre by rememberSaveable { mutableStateOf<String?>(null) }
    var errorPrecio by rememberSaveable { mutableStateOf<String?>(null) }
    var errorStock by rememberSaveable { mutableStateOf<String?>(null) }
    var errorStockMinimo by rememberSaveable { mutableStateOf<String?>(null) }
    var errorGeneral by rememberSaveable { mutableStateOf<String?>(null) }

    Scaffold(
        modifier = modifier.fillMaxSize(),
        containerColor = MaterialTheme.colorScheme.background
    ) { innerPadding ->
        BoxWithConstraints(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .imePadding(), // Asegura espacio libre cuando el teclado está abierto
            contentAlignment = Alignment.TopCenter
        ) {
            val esPantallaAncha = maxWidth >= 600.dp
            val paddingHorizontal = if (esPantallaAncha) 32.dp else 20.dp

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .widthIn(max = 760.dp) // Ancho máximo ergonómico para formularios
                    .verticalScroll(rememberScrollState())
                    .padding(horizontal = paddingHorizontal, vertical = 20.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                // Título y Subtítulo de la Pantalla
                Column(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalAlignment = if (esPantallaAncha) Alignment.CenterHorizontally else Alignment.Start
                ) {
                    Text(
                        text = "Nuevo Producto",
                        style = MaterialTheme.typography.headlineMedium,
                        color = MaterialTheme.colorScheme.primary
                    )
                    Text(
                        text = "Completa los datos del catálogo de Tenebrios Gold",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.padding(top = 4.dp, bottom = 20.dp)
                    )
                }

                // -------------------------------------------------------------
                // CAMPOS: Disposición adaptable (1 columna en celular, 2 en tablet/horizontal)
                // -------------------------------------------------------------
                if (esPantallaAncha) {
                    // MODO 2 COLUMNAS (Tablet o Apaisado)
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(20.dp)
                    ) {
                        // Columna Izquierda: Información básica
                        Column(modifier = Modifier.weight(1f)) {
                            // 1. Nombre
                            CampoNombre(
                                nombre = nombre,
                                errorNombre = errorNombre,
                                onValueChange = {
                                    nombre = it
                                    errorNombre = null
                                    errorGeneral = null
                                    viewModel.mensaje = null
                                }
                            )

                            Spacer(modifier = Modifier.height(12.dp))

                            // 2. Categoría
                            CampoCategoria(
                                categoriaId = categoriaId,
                                categorias = categorias,
                                menuExpandido = menuExpandido,
                                onExpandChange = { menuExpandido = it },
                                onCategoriaSelect = {
                                    categoriaId = it
                                    menuExpandido = false
                                }
                            )

                            Spacer(modifier = Modifier.height(12.dp))

                            // 3. Descripción
                            CampoDescripcion(
                                descripcion = descripcion,
                                onValueChange = { descripcion = it }
                            )
                        }

                        // Columna Derecha: Precios e Inventario
                        Column(modifier = Modifier.weight(1f)) {
                            // 4. Precio
                            CampoPrecio(
                                precioTexto = precioTexto,
                                errorPrecio = errorPrecio,
                                onValueChange = {
                                    precioTexto = it
                                    errorPrecio = null
                                    errorGeneral = null
                                    viewModel.mensaje = null
                                }
                            )

                            Spacer(modifier = Modifier.height(12.dp))

                            // 5. Stock inicial
                            CampoStock(
                                stockTexto = stockTexto,
                                errorStock = errorStock,
                                onValueChange = {
                                    stockTexto = it
                                    errorStock = null
                                    errorGeneral = null
                                    viewModel.mensaje = null
                                }
                            )

                            Spacer(modifier = Modifier.height(12.dp))

                            // 6. Stock mínimo
                            CampoStockMinimo(
                                stockMinimoTexto = stockMinimoTexto,
                                errorStockMinimo = errorStockMinimo,
                                onValueChange = {
                                    stockMinimoTexto = it
                                    errorStockMinimo = null
                                    errorGeneral = null
                                    viewModel.mensaje = null
                                }
                            )
                        }
                    }
                } else {
                    // MODO 1 COLUMNA (Celular vertical estándar)
                    Column(modifier = Modifier.fillMaxWidth()) {
                        CampoNombre(
                            nombre = nombre,
                            errorNombre = errorNombre,
                            onValueChange = {
                                nombre = it
                                errorNombre = null
                                errorGeneral = null
                                viewModel.mensaje = null
                            }
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        CampoCategoria(
                            categoriaId = categoriaId,
                            categorias = categorias,
                            menuExpandido = menuExpandido,
                            onExpandChange = { menuExpandido = it },
                            onCategoriaSelect = {
                                categoriaId = it
                                menuExpandido = false
                            }
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        CampoDescripcion(
                            descripcion = descripcion,
                            onValueChange = { descripcion = it }
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        CampoPrecio(
                            precioTexto = precioTexto,
                            errorPrecio = errorPrecio,
                            onValueChange = {
                                precioTexto = it
                                errorPrecio = null
                                errorGeneral = null
                                viewModel.mensaje = null
                            }
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            Box(modifier = Modifier.weight(1f)) {
                                CampoStock(
                                    stockTexto = stockTexto,
                                    errorStock = errorStock,
                                    onValueChange = {
                                        stockTexto = it
                                        errorStock = null
                                        errorGeneral = null
                                        viewModel.mensaje = null
                                    }
                                )
                            }
                            Box(modifier = Modifier.weight(1f)) {
                                CampoStockMinimo(
                                    stockMinimoTexto = stockMinimoTexto,
                                    errorStockMinimo = errorStockMinimo,
                                    onValueChange = {
                                        stockMinimoTexto = it
                                        errorStockMinimo = null
                                        errorGeneral = null
                                        viewModel.mensaje = null
                                    }
                                )
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(16.dp))

                // Mensajes de error globales o devueltos por el servidor
                val mensajeAMostrar = errorGeneral ?: viewModel.mensaje
                if (mensajeAMostrar != null) {
                    Text(
                        text = mensajeAMostrar,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodyMedium,
                        fontWeight = FontWeight.Medium,
                        textAlign = TextAlign.Center,
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(bottom = 16.dp)
                    )
                }

                // -------------------------------------------------------------
                // BOTONES DE ACCIÓN: Cancelar (secundario) y Guardar (único primario)
                // -------------------------------------------------------------
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 32.dp),
                    horizontalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    // Botón Secundario: Cancelar
                    OutlinedButton(
                        onClick = {
                            viewModel.mensaje = null
                            viewModel.cambiarPantalla("lista")
                        },
                        enabled = !viewModel.guardando,
                        shape = RoundedCornerShape(10.dp),
                        colors = ButtonDefaults.outlinedButtonColors(
                            contentColor = MaterialTheme.colorScheme.onSurfaceVariant
                        ),
                        modifier = Modifier
                            .weight(1f)
                            .heightIn(min = 50.dp) // Área táctil mínima accesible
                    ) {
                        Text(
                            text = "Cancelar",
                            style = MaterialTheme.typography.labelLarge
                        )
                    }

                    // Único Botón Primario: Guardar
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

                            // Validación A: Nombre no vacío
                            if (nombre.trim().isBlank()) {
                                errorNombre = "El nombre es obligatorio"
                                hayErrores = true
                            }

                            // Validación B: Precio numérico mayor a 0
                            val precioParsed = precioTexto.trim().toDoubleOrNull()
                            if (precioTexto.trim().isBlank()) {
                                errorPrecio = "El precio es obligatorio"
                                hayErrores = true
                            } else if (precioParsed == null || precioParsed <= 0.0) {
                                errorPrecio = "El precio debe ser mayor que 0"
                                hayErrores = true
                            }

                            // Validación C: Stock entero >= 0
                            val stockParsed = stockTexto.trim().toIntOrNull()
                            if (stockTexto.trim().isBlank()) {
                                errorStock = "El stock es obligatorio"
                                hayErrores = true
                            } else if (stockParsed == null || stockParsed < 0) {
                                errorStock = "El stock no puede ser negativo"
                                hayErrores = true
                            }

                            // Validación D: Stock mínimo entero >= 0
                            val stockMinimoParsed = if (stockMinimoTexto.trim().isNotBlank()) {
                                stockMinimoTexto.trim().toIntOrNull()
                            } else {
                                10
                            }
                            if (stockMinimoParsed == null || stockMinimoParsed < 0) {
                                errorStockMinimo = "El stock mínimo no puede ser negativo"
                                hayErrores = true
                            }

                            if (hayErrores) {
                                errorGeneral = "Corrige los campos marcados antes de continuar"
                                return@Button
                            }

                            // Construcción de la petición DTO
                            val nuevoProducto = ProductoRequest(
                                idCategoria = categoriaId,
                                nombreProducto = nombre.trim(),
                                descripcion = descripcion.trim().ifBlank { null },
                                precio = precioParsed!!,
                                stock = stockParsed!!,
                                stockMinimo = stockMinimoParsed ?: 10,
                                estado = "Activo"
                            )

                            viewModel.crearProducto(
                                request = nuevoProducto,
                                alExpirar = onLogout
                            )
                        },
                        enabled = !viewModel.guardando,
                        shape = RoundedCornerShape(10.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = MaterialTheme.colorScheme.primary,
                            contentColor = MaterialTheme.colorScheme.onPrimary,
                            disabledContainerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.5f),
                            disabledContentColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.5f)
                        ),
                        modifier = Modifier
                            .weight(1f)
                            .heightIn(min = 50.dp) // Área táctil mínima accesible
                    ) {
                        if (viewModel.guardando) {
                            CircularProgressIndicator(
                                color = MaterialTheme.colorScheme.onPrimary,
                                strokeWidth = 2.dp,
                                modifier = Modifier
                                    .size(18.dp)
                                    .padding(end = 6.dp)
                            )
                            Text(
                                text = "Guardando...",
                                style = MaterialTheme.typography.labelLarge
                            )
                        } else {
                            Text(
                                text = "Guardar",
                                style = MaterialTheme.typography.labelLarge
                            )
                        }
                    }
                }
            }
        }
    }
}

// -----------------------------------------------------------------------------
// COMPONENTES AUXILIARES DEL FORMULARIO
// -----------------------------------------------------------------------------

@Composable
private fun CampoNombre(
    nombre: String,
    errorNombre: String?,
    onValueChange: (String) -> Unit
) {
    OutlinedTextField(
        value = nombre,
        onValueChange = onValueChange,
        label = { Text("Nombre del producto *", style = MaterialTheme.typography.bodyMedium) },
        isError = errorNombre != null,
        supportingText = {
            errorNombre?.let { Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall) }
        },
        singleLine = true,
        shape = RoundedCornerShape(10.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = MaterialTheme.colorScheme.primary,
            focusedLabelColor = MaterialTheme.colorScheme.primary,
            cursorColor = MaterialTheme.colorScheme.primary,
            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
        ),
        modifier = Modifier.fillMaxWidth()
    )
}

@Composable
private fun CampoCategoria(
    categoriaId: Int,
    categorias: List<Pair<Int, String>>,
    menuExpandido: Boolean,
    onExpandChange: (Boolean) -> Unit,
    onCategoriaSelect: (Int) -> Unit
) {
    val nombreCategoriaActual = categorias.find { it.first == categoriaId }?.second ?: ""

    Box(modifier = Modifier.fillMaxWidth()) {
        OutlinedTextField(
            value = nombreCategoriaActual,
            onValueChange = {},
            readOnly = true,
            label = { Text("Categoría *", style = MaterialTheme.typography.bodyMedium) },
            trailingIcon = {
                Text(
                    text = if (menuExpandido) "▲" else "▼",
                    color = MaterialTheme.colorScheme.primary,
                    fontSize = 14.sp,
                    modifier = Modifier.padding(end = 12.dp)
                )
            },
            shape = RoundedCornerShape(10.dp),
            colors = OutlinedTextFieldDefaults.colors(
                focusedBorderColor = MaterialTheme.colorScheme.primary,
                focusedLabelColor = MaterialTheme.colorScheme.primary,
                unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
                focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
            ),
            modifier = Modifier.fillMaxWidth()
        )

        // Capa táctil accesible (mínimo 48 dp) para abrir el menú
        Box(
            modifier = Modifier
                .matchParentSize()
                .clickable { onExpandChange(true) }
                .semantics { contentDescription = "Desplegar lista de categorías" }
        )

        DropdownMenu(
            expanded = menuExpandido,
            onDismissRequest = { onExpandChange(false) },
            modifier = Modifier.background(MaterialTheme.colorScheme.surfaceVariant)
        ) {
            categorias.forEach { (id, etiqueta) ->
                DropdownMenuItem(
                    text = {
                        Text(
                            text = etiqueta,
                            style = MaterialTheme.typography.bodyLarge,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                    },
                    onClick = { onCategoriaSelect(id) },
                    modifier = Modifier.heightIn(min = 48.dp) // Accesibilidad en menú
                )
            }
        }
    }
}

@Composable
private fun CampoDescripcion(
    descripcion: String,
    onValueChange: (String) -> Unit
) {
    OutlinedTextField(
        value = descripcion,
        onValueChange = onValueChange,
        label = { Text("Descripción (opcional)", style = MaterialTheme.typography.bodyMedium) },
        maxLines = 3,
        shape = RoundedCornerShape(10.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = MaterialTheme.colorScheme.primary,
            focusedLabelColor = MaterialTheme.colorScheme.primary,
            cursorColor = MaterialTheme.colorScheme.primary,
            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
        ),
        modifier = Modifier.fillMaxWidth()
    )
}

@Composable
private fun CampoPrecio(
    precioTexto: String,
    errorPrecio: String?,
    onValueChange: (String) -> Unit
) {
    OutlinedTextField(
        value = precioTexto,
        onValueChange = onValueChange,
        label = { Text("Precio en COP * (Ej: 34900)", style = MaterialTheme.typography.bodyMedium) },
        isError = errorPrecio != null,
        supportingText = {
            errorPrecio?.let { Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall) }
        },
        singleLine = true,
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
        shape = RoundedCornerShape(10.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = MaterialTheme.colorScheme.primary,
            focusedLabelColor = MaterialTheme.colorScheme.primary,
            cursorColor = MaterialTheme.colorScheme.primary,
            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
        ),
        modifier = Modifier.fillMaxWidth()
    )
}

@Composable
private fun CampoStock(
    stockTexto: String,
    errorStock: String?,
    onValueChange: (String) -> Unit
) {
    OutlinedTextField(
        value = stockTexto,
        onValueChange = onValueChange,
        label = { Text("Stock inicial *", style = MaterialTheme.typography.bodyMedium) },
        isError = errorStock != null,
        supportingText = {
            errorStock?.let { Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall) }
        },
        singleLine = true,
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
        shape = RoundedCornerShape(10.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = MaterialTheme.colorScheme.primary,
            focusedLabelColor = MaterialTheme.colorScheme.primary,
            cursorColor = MaterialTheme.colorScheme.primary,
            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
        ),
        modifier = Modifier.fillMaxWidth()
    )
}

@Composable
private fun CampoStockMinimo(
    stockMinimoTexto: String,
    errorStockMinimo: String?,
    onValueChange: (String) -> Unit
) {
    OutlinedTextField(
        value = stockMinimoTexto,
        onValueChange = onValueChange,
        label = { Text("Stock mín.", style = MaterialTheme.typography.bodyMedium) },
        isError = errorStockMinimo != null,
        supportingText = {
            errorStockMinimo?.let { Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall) }
        },
        singleLine = true,
        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
        shape = RoundedCornerShape(10.dp),
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = MaterialTheme.colorScheme.primary,
            focusedLabelColor = MaterialTheme.colorScheme.primary,
            cursorColor = MaterialTheme.colorScheme.primary,
            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant,
            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant
        ),
        modifier = Modifier.fillMaxWidth()
    )
}

// =========================================================================
// VISTAS PREVIAS MULTIDISPOSITIVO (RESPONSIVE PREVIEWS)
// =========================================================================

@Preview(name = "1. Celular Pequeño", widthDp = 360, heightDp = 640, showBackground = true)
@Composable
private fun FormularioScreenPreviewSmall() {
    BioGestTheme {
        FormularioScreen(
            viewModel = BioGestViewModel(),
            onLogout = {}
        )
    }
}

@Preview(name = "2. Celular Grande Vertical", device = "spec:width=411dp,height=891dp", showBackground = true)
@Composable
private fun FormularioScreenPreviewLargePortrait() {
    BioGestTheme {
        FormularioScreen(
            viewModel = BioGestViewModel(),
            onLogout = {}
        )
    }
}

@Preview(name = "3. Celular Grande Horizontal", device = "spec:width=891dp,height=411dp", showBackground = true)
@Composable
private fun FormularioScreenPreviewLargeLandscape() {
    BioGestTheme {
        FormularioScreen(
            viewModel = BioGestViewModel(),
            onLogout = {}
        )
    }
}

@Preview(name = "4. Tableta", device = "spec:width=1280dp,height=800dp", showBackground = true)
@Composable
private fun FormularioScreenPreviewTablet() {
    BioGestTheme {
        FormularioScreen(
            viewModel = BioGestViewModel(),
            onLogout = {}
        )
    }
}
