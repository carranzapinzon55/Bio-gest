package com.biogest.app.data

import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

/**
 * Cliente HTTP y generador de servicios Retrofit para el proyecto BIO-GEST.
 * Se implementa como 'object' (patrón Singleton) para que exista una única instancia
 * de la configuración de red y del cliente OkHttpClient en toda la aplicación.
 */
object RetrofitClient {

    // BASE_URL de la API REST de BIO-GEST.
    // 10.0.2.2 es la dirección IP virtual especial que el emulador de Android usa para
    // conectarse al 'localhost' del computador anfitrión (host machine).
    // Para probar en un celular físico conectado a la misma red Wi-Fi, se debe cambiar
    // por la IP local del computador, por ejemplo "http://192.168.1.15:8000/api/".
    private const val BASE_URL = "http://10.0.2.2:8000/api/"

    // Variable mutable en memoria para almacenar el Bearer Token de Laravel Sanctum.
    // Cuando el usuario inicia sesión, este valor se actualiza y los interceptores
    // lo inyectan automáticamente en cada petición subsiguiente.
    var token: String? = null

    /**
     * Interceptor de autenticación y formato.
     * Intercepta cada petición HTTP antes de salir hacia el servidor:
     * 1) Agrega SIEMPRE la cabecera "Accept: application/json" para que Laravel
     *    sepa que debe responder en formato JSON y no redireccionar a vistas web HTML.
     * 2) Si existe un token Sanctum activo, agrega la cabecera "Authorization: Bearer <token>".
     */
    private val authInterceptor = Interceptor { chain ->
        val originalRequest = chain.request()
        val requestBuilder = originalRequest.newBuilder()
            .header("Accept", "application/json") // Obligatorio para APIs Laravel

        // Inyectar token de autorización si está presente
        token?.let { currentToken ->
            requestBuilder.header("Authorization", "Bearer $currentToken")
        }

        val requestWithHeaders = requestBuilder.build()
        chain.proceed(requestWithHeaders)
    }

    /**
     * Interceptor de registro (Logging) de OkHttp.
     * Nivel BODY: imprime en el Logcat de Android Studio las URLs, cabeceras, códigos HTTP
     * y los cuerpos JSON completos enviados y recibidos. Esencial solo para desarrollo.
     */
    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    /**
     * Cliente HTTP OkHttpClient configurado con los dos interceptores.
     */
    private val okHttpClient: OkHttpClient = OkHttpClient.Builder()
        .addInterceptor(authInterceptor)
        .addInterceptor(loggingInterceptor)
        .build()

    /**
     * Instancia global de Retrofit configurada con:
     * - baseUrl: Raíz de todos los endpoints de la API.
     * - client: OkHttpClient con autenticación y logging.
     * - addConverterFactory: GsonConverterFactory para convertir automáticamente
     *   el JSON del servidor a data classes de Kotlin.
     */
    val retrofit: Retrofit = Retrofit.Builder()
        .baseUrl(BASE_URL)
        .client(okHttpClient)
        .addConverterFactory(GsonConverterFactory.create())
        .build()

    /**
     * Instancia lista para usar de la interfaz BioGestApi.
     * 'by lazy' garantiza que la interfaz se inicialice solo la primera vez que se use en la app.
     */
    val api: BioGestApi by lazy {
        retrofit.create(BioGestApi::class.java)
    }

    /**
     * Función genérica para instanciar cualquier interfaz de servicio de Retrofit.
     * Ejemplo de uso: RetrofitClient.createService(AuthApiService::class.java)
     */
    fun <T> createService(serviceClass: Class<T>): T {
        return retrofit.create(serviceClass)
    }
}
