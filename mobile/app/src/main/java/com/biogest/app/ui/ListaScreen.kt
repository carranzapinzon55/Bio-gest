package com.biogest.app.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.data.Producto
import com.biogest.app.ui.theme.BioGestTheme
import com.biogest.app.ui.theme.StatusActiveBg
import com.biogest.app.ui.theme.StatusInactiveBg
import com.biogest.app.ui.theme.StatusInactiveText
import com.biogest.app.ui.theme.SuccessGreen
import com.biogest.app.ui.theme.SuccessGreenDark
import com.biogest.app.viewmodel.BioGestViewModel
import java.text.NumberFormat
import java.util.Locale

/**
 * Función utilitaria para formatear valores monetarios a Pesos Colombianos (COP).
 * Ejemplo: 34900.0 -> "$ 34.900"
 */
fun formatearCOP(valor: Double): String {
    val formato = NumberFormat.getCurrencyInstance(Locale.forLanguageTag("es-CO")).apply {
        maximumFractionDigits = 0
    }
    return formato.format(valor)
}

/**
 * Pantalla principal del Catálogo de Productos (BIO-GEST / Tenebrios Gold).
 *
 * Características Responsive y UX:
 * 1. [BoxWithConstraints]:
 *    - Celulares (< 600 dp): Vista vertical en una sola columna con [LazyColumn].
 *    - Pantallas medianas y tabletas (>= 600 dp): Cuadrícula adaptable con [LazyVerticalGrid] (mínimo 280 dp por tarjeta).
 * 2. Un único botón primario: el FloatingActionButton "+" para agregar productos.
 * 3. Áreas táctiles mínimas de 48 dp en todos los botones y elementos de acción.
 * 4. Textos en unidades 'sp' y colores corporativos desde [MaterialTheme.colorScheme].
 * 5. Accesibilidad completa: etiquetas semánticas y [contentDescription] en elementos no textuales.
 */
@Composable
fun ListaScreen(
    viewModel: BioGestViewModel,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier
) {
    // Carga automática inicial de productos
    LaunchedEffect(Unit) {
        viewModel.cargarProductos(alExpirar = onLogout)
    }

    Scaffold(
        modifier = modifier.fillMaxSize(),
        containerColor = MaterialTheme.colorScheme.background,
        // Botón Flotante "+" (Único botón primario de la pantalla)
        floatingActionButton = {
            FloatingActionButton(
                onClick = { viewModel.cambiarPantalla("formulario") },
                containerColor = MaterialTheme.colorScheme.primary,
                contentColor = MaterialTheme.colorScheme.onPrimary,
                shape = RoundedCornerShape(16.dp),
                modifier = Modifier
                    .size(56.dp) // Supera el estándar de accesibilidad de 48 dp
                    .semantics { contentDescription = "Agregar nuevo producto al catálogo" }
            ) {
                Text(
                    text = "+",
                    fontSize = 30.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onPrimary
                )
            }
        }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(horizontal = 16.dp)
        ) {
            // Cabecera: Título "Productos", Botón secundario "Actualizar" y Botón "Salir"
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(vertical = 16.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Productos",
                    style = MaterialTheme.typography.headlineMedium,
                    color = MaterialTheme.colorScheme.primary
                )

                Row(
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    // Botón secundario: Actualizar
                    OutlinedButton(
                        onClick = { viewModel.cargarProductos(alExpirar = onLogout) },
                        enabled = !viewModel.cargando,
                        colors = ButtonDefaults.outlinedButtonColors(
                            contentColor = MaterialTheme.colorScheme.primary
                        ),
                        border = ButtonDefaults.outlinedButtonBorder(!viewModel.cargando).copy(
                            brush = SolidColor(MaterialTheme.colorScheme.primary)
                        ),
                        shape = RoundedCornerShape(8.dp),
                        contentPadding = PaddingValues(horizontal = 12.dp, vertical = 8.dp),
                        modifier = Modifier.heightIn(min = 48.dp) // Área táctil mínima accesible
                    ) {
                        Text(
                            text = "Actualizar",
                            style = MaterialTheme.typography.labelLarge
                        )
                    }

                    // Botón secundario: Salir (Cierre de sesión)
                    Button(
                        onClick = { viewModel.cerrarSesion(alCerrar = onLogout) },
                        colors = ButtonDefaults.buttonColors(
                            containerColor = MaterialTheme.colorScheme.surfaceVariant,
                            contentColor = MaterialTheme.colorScheme.onSurface
                        ),
                        shape = RoundedCornerShape(8.dp),
                        contentPadding = PaddingValues(horizontal = 14.dp, vertical = 8.dp),
                        modifier = Modifier.heightIn(min = 48.dp) // Área táctil mínima accesible
                    ) {
                        Text(
                            text = "Salir",
                            style = MaterialTheme.typography.labelLarge
                        )
                    }
                }
            }

            // Banner visual de éxito tras guardar producto
            viewModel.mensajeExito?.let { exitoMsg ->
                Card(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 12.dp),
                    shape = RoundedCornerShape(10.dp),
                    colors = CardDefaults.cardColors(
                        containerColor = SuccessGreenDark.copy(alpha = 0.85f)
                    )
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 16.dp, vertical = 10.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = "✓ $exitoMsg",
                            color = SuccessGreen,
                            style = MaterialTheme.typography.bodyMedium,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.weight(1f)
                        )
                        IconButton(
                            onClick = { viewModel.mensajeExito = null },
                            modifier = Modifier
                                .size(48.dp) // Área táctil de 48 dp para accesibilidad
                                .semantics { contentDescription = "Cerrar notificación de éxito" }
                        ) {
                            Text(
                                text = "✕",
                                color = SuccessGreen,
                                fontSize = 16.sp,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }
                }
            }

            // Contenedor Responsive que adapta la vista según el ancho de pantalla
            BoxWithConstraints(
                modifier = Modifier
                    .fillMaxSize()
                    .weight(1f),
                contentAlignment = Alignment.Center
            ) {
                val esCompacto = maxWidth < 600.dp

                when {
                    // ESTADO 1: Cargando datos
                    viewModel.cargando -> {
                        Column(
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.Center,
                            modifier = Modifier.padding(16.dp)
                        ) {
                            CircularProgressIndicator(
                                color = MaterialTheme.colorScheme.primary,
                                strokeWidth = 3.dp,
                                modifier = Modifier.size(48.dp)
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Text(
                                text = "Consultando catálogo...",
                                style = MaterialTheme.typography.bodyMedium,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    }

                    // ESTADO 2: Error de red o Catálogo vacío
                    viewModel.mensaje != null && viewModel.productos.isEmpty() -> {
                        Column(
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.Center,
                            modifier = Modifier.padding(24.dp)
                        ) {
                            Text(
                                text = viewModel.mensaje ?: "Error al cargar los datos",
                                color = if (viewModel.mensaje == "No hay productos registrados")
                                    MaterialTheme.colorScheme.secondary
                                else
                                    MaterialTheme.colorScheme.error,
                                style = MaterialTheme.typography.titleMedium,
                                textAlign = TextAlign.Center
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Button(
                                onClick = { viewModel.cargarProductos(alExpirar = onLogout) },
                                colors = ButtonDefaults.buttonColors(
                                    containerColor = MaterialTheme.colorScheme.primary,
                                    contentColor = MaterialTheme.colorScheme.onPrimary
                                ),
                                shape = RoundedCornerShape(8.dp),
                                modifier = Modifier.heightIn(min = 48.dp)
                            ) {
                                Text(
                                    text = "Reintentar",
                                    style = MaterialTheme.typography.labelLarge
                                )
                            }
                        }
                    }

                    // ESTADO 3: Lista poblada - Adaptación Celular vs Tablet/Apaisado
                    else -> {
                        if (esCompacto) {
                            // En celulares (< 600 dp): LazyColumn de una columna
                            LazyColumn(
                                modifier = Modifier.fillMaxSize(),
                                verticalArrangement = Arrangement.spacedBy(12.dp),
                                contentPadding = PaddingValues(bottom = 88.dp, top = 4.dp)
                            ) {
                                items(
                                    items = viewModel.productos,
                                    key = { it.idProducto }
                                ) { producto ->
                                    ProductoCard(producto = producto)
                                }
                            }
                        } else {
                            // En pantallas medianas y tabletas (>= 600 dp): Cuadrícula responsiva
                            LazyVerticalGrid(
                                columns = GridCells.Adaptive(minSize = 280.dp),
                                modifier = Modifier.fillMaxSize(),
                                verticalArrangement = Arrangement.spacedBy(12.dp),
                                horizontalArrangement = Arrangement.spacedBy(12.dp),
                                contentPadding = PaddingValues(bottom = 88.dp, top = 4.dp)
                            ) {
                                items(
                                    items = viewModel.productos,
                                    key = { it.idProducto }
                                ) { producto ->
                                    ProductoCard(producto = producto)
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Tarjeta individual de producto con diseño claro y tipografía escalable.
 */
@Composable
fun ProductoCard(
    producto: Producto,
    modifier: Modifier = Modifier
) {
    val stockMinimo = producto.stockMinimo ?: 10
    val esStockBajo = producto.stock <= stockMinimo
    val esActivo = producto.estado == "Activo"

    Card(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surface
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp)
        ) {
            // Nombre del producto y Badge de Estado
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top
            ) {
                Text(
                    text = producto.nombreProducto,
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier.weight(1f)
                )

                // Etiqueta visual de estado con contraste validado
                Surface(
                    shape = RoundedCornerShape(6.dp),
                    color = if (esActivo) StatusActiveBg else StatusInactiveBg,
                    modifier = Modifier.padding(start = 8.dp)
                ) {
                    Text(
                        text = producto.estado,
                        color = if (esActivo) SuccessGreen else StatusInactiveText,
                        style = MaterialTheme.typography.labelSmall,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp)
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            // Precio en Pesos Colombianos destacado en Dorado corporativo
            Text(
                text = formatearCOP(producto.precio),
                style = MaterialTheme.typography.titleLarge,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )

            Spacer(modifier = Modifier.height(6.dp))

            // Información de Stock con alerta contextual de stock bajo
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(
                    text = "Stock disponible: ",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Text(
                    text = "${producto.stock} unidades",
                    style = MaterialTheme.typography.bodySmall,
                    fontWeight = FontWeight.Bold,
                    color = if (esStockBajo) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.onSurface
                )

                if (esStockBajo) {
                    Text(
                        text = " • Stock bajo",
                        style = MaterialTheme.typography.bodySmall,
                        fontWeight = FontWeight.ExtraBold,
                        color = MaterialTheme.colorScheme.error,
                        modifier = Modifier.padding(start = 4.dp)
                    )
                }
            }
        }
    }
}

// =========================================================================
// VISTAS PREVIAS MULTIDISPOSITIVO (RESPONSIVE PREVIEWS)
// =========================================================================

private fun crearViewModelConDatos(): BioGestViewModel {
    return BioGestViewModel().apply {
        productos = listOf(
            Producto(
                idProducto = 1,
                idCategoria = 1,
                nombreProducto = "Paquete Larva Viva 500g",
                descripcion = "Larva viva seleccionada de Tenebrio molitor",
                precio = 35000.0,
                stock = 45,
                stockMinimo = 10,
                estado = "Activo"
            ),
            Producto(
                idProducto = 2,
                idCategoria = 2,
                nombreProducto = "Abono Orgánico Frass 1kg",
                descripcion = "Fertilizante 100% natural rico en quitina",
                precio = 18000.0,
                stock = 5,
                stockMinimo = 15,
                estado = "Activo"
            ),
            Producto(
                idProducto = 3,
                idCategoria = 3,
                nombreProducto = "Kit Inicial de Cría Tenebrio",
                descripcion = "Incluye caja ventilada, sustrato y cepa",
                precio = 65000.0,
                stock = 0,
                stockMinimo = 5,
                estado = "Inactivo"
            )
        )
    }
}

@Preview(name = "1. Celular Pequeño", widthDp = 360, heightDp = 640, showBackground = true)
@Composable
private fun ListaScreenPreviewSmall() {
    BioGestTheme {
        ListaScreen(
            viewModel = crearViewModelConDatos(),
            onLogout = {}
        )
    }
}

@Preview(name = "2. Celular Grande Vertical", device = "spec:width=411dp,height=891dp", showBackground = true)
@Composable
private fun ListaScreenPreviewLargePortrait() {
    BioGestTheme {
        ListaScreen(
            viewModel = crearViewModelConDatos(),
            onLogout = {}
        )
    }
}

@Preview(name = "3. Celular Grande Horizontal", device = "spec:width=891dp,height=411dp", showBackground = true)
@Composable
private fun ListaScreenPreviewLargeLandscape() {
    BioGestTheme {
        ListaScreen(
            viewModel = crearViewModelConDatos(),
            onLogout = {}
        )
    }
}

@Preview(name = "4. Tableta", device = "spec:width=1280dp,height=800dp", showBackground = true)
@Composable
private fun ListaScreenPreviewTablet() {
    BioGestTheme {
        ListaScreen(
            viewModel = crearViewModelConDatos(),
            onLogout = {}
        )
    }
}
