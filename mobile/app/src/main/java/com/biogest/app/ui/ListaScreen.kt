package com.biogest.app.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.biogest.app.data.Producto
import com.biogest.app.ui.theme.DarkBackground
import com.biogest.app.ui.theme.DarkSurface
import com.biogest.app.ui.theme.DarkSurfaceVariant
import com.biogest.app.ui.theme.GoldLight
import com.biogest.app.ui.theme.GoldPrimary
import com.biogest.app.ui.theme.TextMuted
import com.biogest.app.ui.theme.TextWhite
import com.biogest.app.viewmodel.BioGestViewModel
import java.text.NumberFormat
import java.util.Locale

/**
 * Función utilitaria para formatear valores a Pesos Colombianos (COP).
 * Ejemplo: 34900.0 -> "$ 34.900"
 */
fun formatearCOP(valor: Double): String {
    val formato = NumberFormat.getCurrencyInstance(Locale.forLanguageTag("es-CO")).apply {
        maximumFractionDigits = 0
    }
    return formato.format(valor)
}

/**
 * Pantalla principal del Catálogo de Productos (BIO-GEST / TENEBRIOS GOLD).
 * Muestra el listado en una LazyColumn eficiente, controla estados de carga,
 * errores de red y alertas de inventario (stock bajo).
 */
@Composable
fun ListaScreen(
    viewModel: BioGestViewModel,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier
) {
    // 1. Carga automática de los productos al entrar por primera vez a la pantalla
    LaunchedEffect(Unit) {
        viewModel.cargarProductos(alExpirar = onLogout)
    }

    Scaffold(
        modifier = modifier.fillMaxSize(),
        containerColor = DarkBackground,
        // Botón Flotante "+" para ir al formulario de creación
        floatingActionButton = {
            FloatingActionButton(
                onClick = { viewModel.cambiarPantalla("formulario") },
                containerColor = GoldPrimary,
                contentColor = DarkBackground,
                shape = RoundedCornerShape(16.dp)
            ) {
                Text(
                    text = "+",
                    fontSize = 28.sp,
                    fontWeight = FontWeight.Bold
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
            // Cabecera: Título "Productos", Botón "Actualizar" y Botón "Salir"
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(vertical = 16.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Productos",
                    fontSize = 28.sp,
                    fontWeight = FontWeight.Bold,
                    color = GoldPrimary
                )

                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    // Botón para refrescar la lista manualmente
                    OutlinedButton(
                        onClick = { viewModel.cargarProductos(alExpirar = onLogout) },
                        enabled = !viewModel.cargando,
                        colors = ButtonDefaults.outlinedButtonColors(
                            contentColor = GoldPrimary
                        ),
                        border = ButtonDefaults.outlinedButtonBorder(!viewModel.cargando).copy(
                            brush = SolidColor(GoldPrimary)
                        ),
                        shape = RoundedCornerShape(8.dp),
                        contentPadding = PaddingValues(horizontal = 12.dp, vertical = 6.dp)
                    ) {
                        Text(text = "Actualizar", fontSize = 13.sp, fontWeight = FontWeight.SemiBold)
                    }

                    // Botón Salir (Cierra sesión y limpia tokens)
                    Button(
                        onClick = { viewModel.cerrarSesion(alCerrar = onLogout) },
                        colors = ButtonDefaults.buttonColors(
                            containerColor = DarkSurfaceVariant,
                            contentColor = TextWhite
                        ),
                        shape = RoundedCornerShape(8.dp),
                        contentPadding = PaddingValues(horizontal = 12.dp, vertical = 6.dp)
                    ) {
                        Text(text = "Salir", fontSize = 13.sp, fontWeight = FontWeight.SemiBold)
                    }
                }
            }

            // Banner visual de éxito tras crear o actualizar un registro
            viewModel.mensajeExito?.let { exitoMsg ->
                Card(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(bottom = 12.dp),
                    shape = RoundedCornerShape(8.dp),
                    colors = CardDefaults.cardColors(
                        containerColor = Color(0xFF1B5E20).copy(alpha = 0.85f)
                    )
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 14.dp, vertical = 10.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = "✓ $exitoMsg",
                            color = Color(0xFFA5D6A7),
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.weight(1f)
                        )
                        IconButton(
                            onClick = { viewModel.mensajeExito = null },
                            modifier = Modifier.size(24.dp)
                        ) {
                            Text(
                                text = "✕",
                                color = Color(0xFFA5D6A7),
                                fontSize = 12.sp,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }
                }
            }

            // Contenedor principal con gestión reactiva de estados
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .weight(1f),
                contentAlignment = Alignment.Center
            ) {
                when {
                    // ESTADO 1: Cargando datos desde la API
                    viewModel.cargando -> {
                        Column(
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.Center
                        ) {
                            CircularProgressIndicator(
                                color = GoldPrimary,
                                strokeWidth = 3.dp,
                                modifier = Modifier.size(44.dp)
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Text(
                                text = "Consultando catálogo...",
                                color = TextMuted,
                                fontSize = 14.sp
                            )
                        }
                    }

                    // ESTADO 2: Error de conexión o Lista Vacía
                    viewModel.mensaje != null && viewModel.productos.isEmpty() -> {
                        Column(
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.Center,
                            modifier = Modifier.padding(24.dp)
                        ) {
                            Text(
                                text = viewModel.mensaje ?: "Error al cargar los datos",
                                color = if (viewModel.mensaje == "No hay productos registrados") GoldLight else MaterialTheme.colorScheme.error,
                                fontSize = 16.sp,
                                textAlign = TextAlign.Center,
                                fontWeight = FontWeight.Medium
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            // Botón de reintento en caso de fallo
                            Button(
                                onClick = { viewModel.cargarProductos(alExpirar = onLogout) },
                                colors = ButtonDefaults.buttonColors(
                                    containerColor = GoldPrimary,
                                    contentColor = DarkBackground
                                ),
                                shape = RoundedCornerShape(8.dp)
                            ) {
                                Text(text = "Reintentar", fontWeight = FontWeight.Bold)
                            }
                        }
                    }

                    // ESTADO 3: Éxito con catálogo cargado
                    else -> {
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
                    }
                }
            }
        }
    }
}

/**
 * Tarjeta individual para mostrar la información del producto.
 */
@Composable
fun ProductoCard(
    producto: Producto,
    modifier: Modifier = Modifier
) {
    val stockMinimo = producto.stockMinimo ?: 10
    val esStockBajo = producto.stock <= stockMinimo

    Card(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(
            containerColor = DarkSurface
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp)
        ) {
            // Nombre y Estado (Activo / Inactivo)
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top
            ) {
                Text(
                    text = producto.nombreProducto,
                    fontSize = 17.sp,
                    fontWeight = FontWeight.Bold,
                    color = TextWhite,
                    modifier = Modifier.weight(1f)
                )

                // Etiqueta visual del estado
                Surface(
                    shape = RoundedCornerShape(6.dp),
                    color = if (producto.estado == "Activo") Color(0xFF1B5E20).copy(alpha = 0.4f) else Color(0xFFB71C1C).copy(alpha = 0.4f),
                    modifier = Modifier.padding(start = 8.dp)
                ) {
                    Text(
                        text = producto.estado,
                        color = if (producto.estado == "Activo") Color(0xFF81C784) else Color(0xFFE57373),
                        fontSize = 11.sp,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp)
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            // Precio en Pesos Colombianos
            Text(
                text = formatearCOP(producto.precio),
                fontSize = 20.sp,
                fontWeight = FontWeight.ExtraBold,
                color = GoldPrimary
            )

            Spacer(modifier = Modifier.height(6.dp))

            // Stock disponible y Alerta de Stock Bajo
            Row(
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Stock disponible: ",
                    fontSize = 13.sp,
                    color = TextMuted
                )
                Text(
                    text = "${producto.stock} unidades",
                    fontSize = 13.sp,
                    fontWeight = FontWeight.Bold,
                    color = if (esStockBajo) MaterialTheme.colorScheme.error else TextWhite
                )

                // Indicador especial cuando el stock es menor o igual al mínimo
                if (esStockBajo) {
                    Text(
                        text = " • Stock bajo",
                        fontSize = 13.sp,
                        fontWeight = FontWeight.ExtraBold,
                        color = MaterialTheme.colorScheme.error,
                        modifier = Modifier.padding(start = 4.dp)
                    )
                }
            }
        }
    }
}
