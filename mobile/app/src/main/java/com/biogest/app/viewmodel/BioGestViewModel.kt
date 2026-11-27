package com.biogest.app.viewmodel

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.biogest.app.data.ErrorValidacion
import com.biogest.app.data.LoginRequest
import com.biogest.app.data.Producto
import com.biogest.app.data.ProductoRequest
import com.biogest.app.data.RetrofitClient
import com.google.gson.Gson
import kotlinx.coroutines.launch

/**
 * ViewModel principal de la aplicación BIO-GEST (Patrón MVVM).
 * Administra el estado de la interfaz de usuario de manera reactiva mediante State de Compose,
 * sobrevive a cambios de configuración (como rotación de pantalla) y ejecuta las operaciones
 * de red en corrutinas ligadas al ciclo de vida con viewModelScope.
 */
class BioGestViewModel : ViewModel() {

    // -------------------------------------------------------------------------
    // ESTADOS REACTIVOS (Jetpack Compose State)
    // -------------------------------------------------------------------------

    // Controla qué pantalla está visible actualmente: "login", "lista" o "formulario"
    var pantalla by mutableStateOf("login")

    // Lista en memoria de productos obtenidos del catálogo de la API
    var productos by mutableStateOf<List<Producto>>(emptyList())

    // Mensaje de retroalimentación para el usuario (errores o advertencias)
    var mensaje by mutableStateOf<String?>(null)

    // Mensaje de éxito temporal para mostrar en la lista tras una acción favorable
    var mensajeExito by mutableStateOf<String?>(null)

    // Indica si hay una llamada de red en curso (para mostrar spinners o deshabilitar botones)
    var cargando by mutableStateOf(false)

    // Indica si se está guardando un producto para deshabilitar botones en el formulario
    var guardando by mutableStateOf(false)

    // -------------------------------------------------------------------------
    // ACCIONES / MÉTODOS DE NEGOCIO
    // -------------------------------------------------------------------------

    /**
     * Inicia sesión contra el backend Laravel.
     * @param correo Correo electrónico ingresado por el usuario.
     * @param clave Contraseña en texto plano.
     * @param alExito Callback que recibe el token y el nombre para persistirlos en TokenStorage.
     */
    fun login(correo: String, clave: String, alExito: (token: String, nombre: String) -> Unit) {
        // 1. Validación previa en el cliente
        if (correo.isBlank() || clave.isBlank()) {
            mensaje = "Escribe tu correo y tu contraseña"
            return
        }

        // 2. Ejecución asíncrona mediante corrutinas de ViewModel
        viewModelScope.launch {
            cargando = true
            mensaje = null

            try {
                val request = LoginRequest(
                    correo = correo.trim(),
                    contrasena = clave
                )

                // Llamada a la API mediante Retrofit
                val response = RetrofitClient.api.login(request)

                if (response.isSuccessful && response.body() != null) {
                    val loginData = response.body()!!.data

                    // Actualizar token en memoria para que el interceptor lo use
                    RetrofitClient.token = loginData.token

                    // Callback para guardar en SharedPreferences (TokenStorage)
                    val nombreCompleto = loginData.usuario.nombreCompleto ?: loginData.usuario.nombre
                    alExito(loginData.token, nombreCompleto)

                    // Navegar a la pantalla de lista y cargar los productos reales
                    pantalla = "lista"
                    cargarProductos()
                } else if (response.code() == 401 || response.code() == 422) {
                    // 401 Credenciales inválidas o 422 Validación rechazada
                    mensaje = "Correo o contraseña incorrectos"
                } else {
                    mensaje = "Error del servidor (Código ${response.code()})"
                }

            } catch (e: Exception) {
                // Fallo de red: servidor apagado, sin internet o timeout
                mensaje = "No se pudo conectar con el servidor"
            } finally {
                cargando = false
            }
        }
    }

    /**
     * Consulta el catálogo de productos activos desde GET /api/productos.
     * Maneja todos los estados: éxito con datos, lista vacía, 401 sesión expirada,
     * 403 permisos insuficientes, otros errores HTTP y fallas de conexión.
     *
     * @param alExpirar Callback opcional para limpiar el TokenStorage si la sesión vence (401).
     */
    fun cargarProductos(alExpirar: (() -> Unit)? = null) {
        viewModelScope.launch {
            cargando = true
            mensaje = null

            try {
                val response = RetrofitClient.api.getProductos()

                if (response.isSuccessful && response.body() != null) {
                    val lista = response.body()!!.data ?: emptyList()
                    if (lista.isEmpty()) {
                        productos = emptyList()
                        mensaje = "No hay productos registrados"
                    } else {
                        productos = lista
                        mensaje = null
                    }
                } else when (response.code()) {
                    401 -> {
                        // 401: Token inválido o revocado en el backend
                        RetrofitClient.token = null
                        productos = emptyList()
                        alExpirar?.invoke()
                        pantalla = "login"
                        mensaje = "Tu sesión expiró, ingresa de nuevo"
                    }
                    403 -> {
                        // 403: No autorizado para acceder a este recurso
                        mensaje = "No tienes permiso para ver esta información"
                    }
                    else -> {
                        // Otros códigos de error (500, 404, etc.)
                        mensaje = "Error al cargar los datos"
                    }
                }
            } catch (e: Exception) {
                // Falla de red: servidor apagado, timeout o sin conexión a internet
                mensaje = "No se pudo conectar con el servidor"
            } finally {
                cargando = false
            }
        }
    }

    /**
     * Registra un nuevo producto en la API de Laravel (POST /api/productos).
     * Requiere rol de Administrador. Maneja los códigos 200/201 (Éxito),
     * 422 (Validación de Laravel), 403 (Prohibido), 401 (Expiración) y fallas de red.
     */
    fun crearProducto(request: ProductoRequest, alExpirar: (() -> Unit)? = null) {
        viewModelScope.launch {
            guardando = true
            mensaje = null

            try {
                val response = RetrofitClient.api.crearProducto(request)

                if (response.isSuccessful && response.body() != null) {
                    // Éxito: 200 o 201
                    mensajeExito = "Guardado correctamente"
                    pantalla = "lista"
                    cargarProductos(alExpirar)
                } else when (response.code()) {
                    422 -> {
                        // Laravel devuelve estructura: { "message": "...", "errors": { "campo": ["error..."] } }
                        val errorBodyStr = response.errorBody()?.string()
                        val errorParsed = try {
                            Gson().fromJson(errorBodyStr, ErrorValidacion::class.java)
                        } catch (e: Exception) {
                            null
                        }
                        // Extraer el primer mensaje específico de validación
                        val primerError = errorParsed?.errors?.values?.firstOrNull()?.firstOrNull()
                            ?: errorParsed?.message
                            ?: "Datos inválidos según el servidor"
                        mensaje = primerError
                    }
                    403 -> {
                        mensaje = "No tienes permiso para registrar productos"
                    }
                    401 -> {
                        RetrofitClient.token = null
                        productos = emptyList()
                        alExpirar?.invoke()
                        pantalla = "login"
                        mensaje = "Tu sesión expiró, ingresa de nuevo"
                    }
                    else -> {
                        mensaje = "Error al guardar el producto (Código ${response.code()})"
                    }
                }
            } catch (e: Exception) {
                // Fallo de red: servidor apagado, timeout o sin conexión
                mensaje = "No se pudo guardar, revisa tu conexión"
            } finally {
                guardando = false
            }
        }
    }

    /**
     * Cambia de pantalla limpiando mensajes anteriores.
     */
    fun cambiarPantalla(nuevaPantalla: String) {
        pantalla = nuevaPantalla
        mensaje = null
    }

    /**
     * Cierra la sesión activa: limpia estados en memoria y ejecuta el callback de limpieza.
     */
    fun cerrarSesion(alCerrar: () -> Unit) {
        RetrofitClient.token = null
        productos = emptyList()
        mensaje = null
        alCerrar()
        pantalla = "login"
    }
}
