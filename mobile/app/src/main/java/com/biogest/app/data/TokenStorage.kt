package com.biogest.app.data

import android.content.Context
import android.content.SharedPreferences

/**
 * Gestor de persistencia local para credenciales ligeras usando SharedPreferences.
 * Guarda en el almacenamiento privado del dispositivo Android el token Sanctum
 * y el nombre del usuario para mantener la sesión abierta al cerrar la aplicación.
 */
class TokenStorage(context: Context) {

    // Archivo XML privado de preferencias compartidas
    private val preferences: SharedPreferences = context.getSharedPreferences(
        PREFS_NAME,
        Context.MODE_PRIVATE
    )

    companion object {
        private const val PREFS_NAME = "bio_gest_prefs"
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_NOMBRE = "user_nombre"
    }

    /**
     * Guarda el token Sanctum y el nombre del usuario en disco (SharedPreferences)
     * y actualiza de inmediato la variable en memoria de RetrofitClient.
     */
    fun guardar(token: String, nombre: String) {
        preferences.edit()
            .putString(KEY_TOKEN, token)
            .putString(KEY_NOMBRE, nombre)
            .apply()

        // Sincronizar el token con la capa de red
        RetrofitClient.token = token
    }

    /**
     * Recupera el token Sanctum almacenado.
     * Retorna null si no hay sesión iniciada.
     */
    fun token(): String? {
        return preferences.getString(KEY_TOKEN, null)
    }

    /**
     * Recupera el nombre del usuario autenticado.
     * Retorna null si no hay sesión iniciada.
     */
    fun nombre(): String? {
        return preferences.getString(KEY_NOMBRE, null)
    }

    /**
     * Borra los datos almacenados en SharedPreferences (Cierre de sesión)
     * y limpia el token en memoria dentro de RetrofitClient.
     */
    fun limpiar() {
        preferences.edit()
            .clear()
            .apply()

        // Limpiar el token en memoria
        RetrofitClient.token = null
    }
}
