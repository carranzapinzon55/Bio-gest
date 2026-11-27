package com.biogest.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.consumeWindowInsets
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
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
 * Actividad Principal de BIO-GEST (Tenebrios Gold).
 * Punto de entrada de la aplicación Android.
 *
 * Configuración de Pantalla y UX Móvil:
 * - [enableEdgeToEdge]: Activa el modo de borde a borde para que la interfaz aproveche toda la pantalla
 *   (detrás de la barra de estado y de navegación).
 * - [Scaffold] con [innerPadding]: Respeta las áreas seguras del sistema (insets: notch, status bar, navigation bar).
 * - [consumeWindowInsets]: Evita doble padding en pantallas hijas que tengan su propio Scaffold o manejo de insets.
 */
class MainActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        // Activa la experiencia moderna Edge-to-Edge recomendada por Android
        enableEdgeToEdge()

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

                Scaffold(
                    modifier = Modifier.fillMaxSize(),
                    containerColor = MaterialTheme.colorScheme.background
                ) { innerPadding ->
                    // Enrutador de pantallas basado en estado reactivo del ViewModel
                    when (vm.pantalla) {
                        "login" -> LoginScreen(
                            viewModel = vm,
                            onLoginSuccess = { token, nombre ->
                                tokenStorage.guardar(token, nombre)
                            },
                            modifier = Modifier
                                .padding(innerPadding)
                                .consumeWindowInsets(innerPadding)
                        )

                        "lista" -> ListaScreen(
                            viewModel = vm,
                            onLogout = {
                                tokenStorage.limpiar()
                            },
                            modifier = Modifier
                                .padding(innerPadding)
                                .consumeWindowInsets(innerPadding)
                        )

                        "formulario" -> FormularioScreen(
                            viewModel = vm,
                            onLogout = {
                                tokenStorage.limpiar()
                            },
                            modifier = Modifier
                                .padding(innerPadding)
                                .consumeWindowInsets(innerPadding)
                        )
                    }
                }
            }
        }
    }
}
