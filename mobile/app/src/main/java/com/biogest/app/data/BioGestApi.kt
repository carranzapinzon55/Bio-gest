package com.biogest.app.data

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query

/**
 * Interfaz de endpoints HTTP de Retrofit para BIO-GEST.
 * Debido a que BASE_URL en RetrofitClient termina en "/api/",
 * las rutas relativas NO deben comenzar con "api/" ni con "/" inicial.
 */
interface BioGestApi {

    /**
     * 1. Iniciar Sesión (Público)
     * POST /api/login
     */
    @POST("login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    /**
     * 2. Catálogo de Productos (Público)
     * GET /api/productos
     * Soporta filtros opcionales de búsqueda, categoría y estado.
     */
    @GET("productos")
    suspend fun getProductos(
        @Query("buscar") buscar: String? = null,
        @Query("id_categoria") idCategoria: Int? = null,
        @Query("estado") estado: String? = null
    ): Response<ApiResponse<List<Producto>>>

    /**
     * 3. Registrar Producto (Exclusivo Administrador)
     * POST /api/productos
     * Requiere que el interceptor inyecte el Bearer Token con rol de Administrador.
     */
    @POST("productos")
    suspend fun crearProducto(
        @Body request: ProductoRequest
    ): Response<ApiResponse<Producto>>

    /**
     * 4. Listar Pedidos (Protegido: auth:sanctum)
     * GET /api/pedidos
     * Admin ve todos los pedidos; Cliente ve únicamente los propios.
     */
    @GET("pedidos")
    suspend fun getPedidos(
        @Query("estado_pedido") estadoPedido: String? = null
    ): Response<ApiResponse<List<Pedido>>>

    /**
     * 5. Actualizar Estado de un Pedido (Exclusivo Administrador)
     * PUT /api/pedidos/{id}
     * Permite cambiar el estado logístico ('Despachado', 'Entregado', etc.) y de pago.
     */
    @PUT("pedidos/{id}")
    suspend fun actualizarEstadoPedido(
        @Path("id") id: Int,
        @Body request: ActualizarEstadoPedidoRequest
    ): Response<ApiResponse<Pedido>>
}
