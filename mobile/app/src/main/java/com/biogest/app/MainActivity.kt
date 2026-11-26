package com.biogest.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Modifier
import androidx.lifecycle.viewmodel.compose.viewModel
import com.biogest.app.data.RetrofitClient
import com.biogest.app.data.TokenStorage
import com.biogest.app.ui.FormularioScreen
import com.biogest.app.ui.ListaScreen
import com.biogest.app.ui.LoginScreen
import com.biogest.app.ui.theme.BioGestTheme
import com.biogest.app.viewmodel.BioGestViewModel

/**
 * Actividad Principal de BIO-GEST.
 * Punto de entrada de la aplicación Android. Controla el flujo de navegación
 * mediante un patrón de estado simple con "when" en Compose, sin librerías externas.
 */
class MainActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        // Gestor de persistencia local con SharedPreferences
        val tokenStorage = TokenStorage(applicationContext)

        setContent {
            BioGestTheme {
                val vm: BioGestViewModel = viewModel()

                // Comprobación de sesión guardada al iniciar la app:
                // Si ya existe un token en SharedPreferences, se asigna al cliente de red
                // y se navega directamente al catálogo sin pedir login de nuevo.
                LaunchedEffect(Unit) {
                    val savedToken = tokenStorage.token()
                    if (!savedToken.isNullOrBlank()) {
                        RetrofitClient.token = savedToken
                        vm.pantalla = "lista"
                        vm.cargarProductos()
                    } else {
                        vm.pantalla = "login"
                    }
                }

                Scaffold(modifier = Modifier.fillMaxSize()) { innerPadding ->
                    // Enrutador de pantallas basado en estado reactivo
                    when (vm.pantalla) {
                        "login" -> LoginScreen(
                            viewModel = vm,
                            onLoginSuccess = { token, nombre ->
                                tokenStorage.guardar(token, nombre)
                            },
                            modifier = Modifier.padding(innerPadding)
                        )

                        "lista" -> ListaScreen(
                            viewModel = vm,
                            onLogout = {
                                tokenStorage.limpiar()
                            },
                            modifier = Modifier.padding(innerPadding)
                        )

                        "formulario" -> FormularioScreen(
                            viewModel = vm,
                            onLogout = {
                                tokenStorage.limpiar()
                            },
                            modifier = Modifier.padding(innerPadding)
                        )
                    }
                }
            }
        }
    }
}
