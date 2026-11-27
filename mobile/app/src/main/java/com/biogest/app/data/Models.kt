package com.biogest.app.data

import com.google.gson.annotations.SerializedName

/**
 * =========================================================================
 * MODELOS DE DATOS (DTOs) PARA LA API REST DE BIO-GEST
 * Mapean con fidelidad absoluta los nombres de campos devueltos por Laravel.
 * =========================================================================
 */

// -------------------------------------------------------------------------
// 1. Envoltura Genérica de Respuesta de la API
// -------------------------------------------------------------------------
/**
 * Envoltura estándar que usa Laravel en BIO-GEST:
 * - Para listados: { "success": true, "total": 5, "data": [...] }
 * - Para creaciones o detalles: { "success": true, "message": "...", "data": { ... } }
 */
data class ApiResponse<T>(
    @SerializedName("success") val success: Boolean? = null,
    @SerializedName("message") val message: String? = null,
    @SerializedName("total") val total: Int? = null,
    @SerializedName("data") val data: T? = null
)

// -------------------------------------------------------------------------
// 2. Autenticación y Usuario
// -------------------------------------------------------------------------
/**
 * Cuerpo JSON requerido por POST /api/login.
 * IMPORTANTE: Laravel espera "contrasena" con 'n', NO "password".
 */
data class LoginRequest(
    @SerializedName("correo") val correo: String,
    @SerializedName("contrasena") val contrasena: String
)

/**
 * Respuesta devuelta por POST /api/login (HTTP 200).
 */
data class LoginResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String?,
    @SerializedName("data") val data: LoginData
)

/**
 * Contenido interno de 'data' en LoginResponse.
 */
data class LoginData(
    @SerializedName("token") val token: String,
    @SerializedName("token_type") val tokenType: String = "Bearer",
    @SerializedName("usuario") val usuario: Usuario
)

/**
 * Modelo del Usuario autenticado según la tabla 'usuario' y el controlador AuthApiController.
 */
data class Usuario(
    @SerializedName("id_usuario") val idUsuario: Int,
    @SerializedName("nombre") val nombre: String,
    @SerializedName("apellido") val apellido: String,
    @SerializedName("nombre_completo") val nombreCompleto: String? = null,
    @SerializedName("correo") val correo: String,
    @SerializedName("telefono") val telefono: String? = null,
    @SerializedName("id_rol") val idRol: Int,
    @SerializedName("rol") val rol: String,
    @SerializedName("perfil_uso") val perfilUso: String? = null,
    @SerializedName("estado") val estado: String
)

// -------------------------------------------------------------------------
// 3. Categorías y Productos
// -------------------------------------------------------------------------
/**
 * Modelo de Categoría según la tabla 'categoria'.
 */
data class Categoria(
    @SerializedName("id_categoria") val idCategoria: Int,
    @SerializedName("nombre_categoria") val nombreCategoria: String,
    @SerializedName("descripcion") val descripcion: String? = null
)

/**
 * Modelo de Producto según la tabla 'producto' y ProductoApiController.
 * Laravel devuelve 'precio' como string decimal ("34900.00"), el cual Gson
 * convierte de forma transparente a Double.
 */
data class Producto(
    @SerializedName("id_producto") val idProducto: Int,
    @SerializedName("id_categoria") val idCategoria: Int,
    @SerializedName("nombre_producto") val nombreProducto: String,
    @SerializedName("descripcion") val descripcion: String? = null,
    @SerializedName("beneficios") val beneficios: String? = null,
    @SerializedName("instrucciones_conservacion") val instruccionesConservacion: String? = null,
    @SerializedName("especies_recomendadas") val especiesRecomendadas: String? = null,
    @SerializedName("politica_reembolso") val politicaReembolso: String? = null,
    @SerializedName("precio") val precio: Double,
    @SerializedName("stock") val stock: Int,
    @SerializedName("stock_minimo") val stockMinimo: Int? = 10,
    @SerializedName("imagen") val imagen: String? = null,
    @SerializedName("estado") val estado: String,
    @SerializedName("categoria") val categoria: Categoria? = null,
    @SerializedName("estado_stock") val estadoStock: String? = null
)

/**
 * Payload requerido para registrar un producto en POST /api/productos.
 * Restricciones del backend: precio > 0 y stock >= 0.
 */
data class ProductoRequest(
    @SerializedName("id_categoria") val idCategoria: Int,
    @SerializedName("nombre_producto") val nombreProducto: String,
    @SerializedName("precio") val precio: Double,
    @SerializedName("stock") val stock: Int,
    @SerializedName("descripcion") val descripcion: String? = null,
    @SerializedName("beneficios") val beneficios: String? = null,
    @SerializedName("instrucciones_conservacion") val instruccionesConservacion: String? = null,
    @SerializedName("especies_recomendadas") val especiesRecomendadas: String? = null,
    @SerializedName("politica_reembolso") val politicaReembolso: String? = null,
    @SerializedName("stock_minimo") val stockMinimo: Int? = 10,
    @SerializedName("imagen") val imagen: String? = null,
    @SerializedName("estado") val estado: String? = "Activo"
)

// -------------------------------------------------------------------------
// 4. Pedidos y Detalles
// -------------------------------------------------------------------------
/**
 * Modelo de Pedido devuelto por GET /api/pedidos y GET /api/pedidos/{id}.
 */
data class Pedido(
    @SerializedName("id_pedido") val idPedido: Int,
    @SerializedName("numero_pedido") val numeroPedido: String? = null,
    @SerializedName("id_usuario") val idUsuario: Int,
    @SerializedName("id_direccion") val idDireccion: Int? = null,
    @SerializedName("subtotal") val subtotal: Double? = null,
    @SerializedName("costo_envio") val costoEnvio: Double? = null,
    @SerializedName("total") val total: Double,
    @SerializedName("metodo_pago") val metodoPago: String? = null,
    @SerializedName("estado_pago") val estadoPago: String? = null,
    @SerializedName("estado_pedido") val estadoPedido: String,
    @SerializedName("direccion_entrega") val direccionEntrega: String? = null,
    @SerializedName("ciudad_entrega") val ciudadEntrega: String? = null,
    @SerializedName("telefono_contacto") val telefonoContacto: String? = null,
    @SerializedName("fecha_pedido") val fechaPedido: String? = null,
    @SerializedName("detalles") val detalles: List<PedidoDetalle>? = null,
    @SerializedName("usuario") val usuario: Usuario? = null
)

/**
 * Detalle individual de un ítem dentro de un pedido.
 */
data class PedidoDetalle(
    @SerializedName("id_detalle") val idDetalle: Int,
    @SerializedName("id_pedido") val idPedido: Int,
    @SerializedName("id_producto") val idProducto: Int,
    @SerializedName("cantidad") val cantidad: Int,
    @SerializedName("precio_unitario") val precioUnitario: Double,
    @SerializedName("subtotal") val subtotal: Double,
    @SerializedName("producto") val producto: Producto? = null
)

/**
 * Payload para PUT /api/pedidos/{id} (Actualización de estado por Admin).
 */
data class ActualizarEstadoPedidoRequest(
    @SerializedName("estado_pedido") val estadoPedido: String,
    @SerializedName("estado_pago") val estadoPago: String? = null
)

// -------------------------------------------------------------------------
// 5. Modelo de Error de Validación de Laravel (HTTP 422)
// -------------------------------------------------------------------------
/**
 * Formato devuelto por Laravel ante fallos de validación o reglas de negocio:
 * {
 *   "success": false,
 *   "message": "Errores de validación.",
 *   "errors": {
 *     "correo": ["El correo electrónico es obligatorio."],
 *     "precio": ["El precio debe ser estrictamente mayor a 0."]
 *   }
 * }
 */
data class ErrorValidacion(
    @SerializedName("success") val success: Boolean? = null,
    @SerializedName("message") val message: String? = null,
    @SerializedName("errors") val errors: Map<String, List<String>>? = null
)
