# BIO-GEST Mobile (Tenebrios Gold) 📱🦗

Componente móvil oficial del sistema **BIO-GEST** desarrollado para la empresa **TENEBRIOS GOLD**. Esta aplicación Android nativa permite la gestión de inventario, consulta en tiempo real del catálogo de productos con monitoreo de existencias críticas y registro ágil de productos para el personal administrativo, integrándose de forma segura con el backend en Laravel mediante tokens Bearer (Laravel Sanctum) y base de datos relacional MySQL/MariaDB.

---

## 🚀 Características Principales

1. **Autenticación Segura (Sanctum)**: Inicio de sesión tipado con validación de credenciales y persistencia en dos niveles (disco local con `SharedPreferences` y memoria con interceptor de red).
2. **Catálogo Reactivo con LazyColumn**: Listado en tiempo real con cálculo dinámico de existencias, formateo oficial en Pesos Colombianos (COP) y alerta visual en color rojo ante `Stock bajo` (`stock <= stock_minimo`).
3. **Formulario de Registro Administrativo**: Captura y validación en cliente (nombre obligatorio, categoría válida, precio `> 0` y stock `>= 0`) con retroalimentación inmediata antes de despachar la petición `POST`.
4. **Manejo Integral de Estados y Errores**: Tratamiento explícito de códigos HTTP (200, 201, 401 sesión expirada con redirección a login, 403 permisos insuficientes, 422 mensajes de validación de Laravel y fallas de red).
5. **Diseño e Identidad Corporativa**: Paleta oficial de Tenebrios Gold basada en Material 3 con fondos carbón (`#151515`) y acentos dorados (`#e4b353`).

---

## 🛠️ Requisitos del Sistema

* **Android Studio**: Iguana, Koala, Ladybug o superior con soporte para Jetpack Compose.
* **JDK**: Java Development Kit versión 17 o 21 (configurado como Gradle JDK en Android Studio).
* **Android SDK**: `minSdk = 24` (Android 7.0 Nougat) y `targetSdk = 37`.
* **Backend BIO-GEST en ejecución**: API REST de Laravel corriendo y accesible en red local con la base de datos `bio_gest` migrada y alimentada con seeders.

---

## 🌐 Configuración de la URL de Conexión (`BASE_URL`)

La URL base del backend se configura en un único punto centralizado:  
Archivo: `app/src/main/java/com/biogest/app/data/RetrofitClient.kt`

```kotlin
private const val BASE_URL = "http://10.0.2.2:8000/api/"
```

### ¿Cuál URL debo usar según mi entorno?

1. **Si usas el Emulador Oficial de Android Studio**:
   * Mantén: `"http://10.0.2.2:8000/api/"`
   * *Justificación*: `10.0.2.2` es la IP virtual reservada por el emulador para redirigir peticiones al `localhost` de tu computador anfitrión.

2. **Si usas un Teléfono Celular Físico**:
   * Conecta tu celular y tu computador a la **misma red Wi-Fi**.
   * Averigua la dirección IP local de tu computador en la terminal de Linux:
     ```bash
     hostname -I
     # o también: ip -4 addr show
     ```
     *(Ejemplo: `192.168.1.15`).*
   * Modifica `BASE_URL` en `RetrofitClient.kt`:
     ```kotlin
     private const val BASE_URL = "http://192.168.1.15:8000/api/"
     ```
   * Asegúrate de iniciar Laravel en tu computador vinculándolo a todas las interfaces:
     ```bash
     php artisan serve --host=0.0.0.0 --port=8000
     ```

---

## 📲 Cómo Ejecutar la Aplicación

### Opción A: Desde Android Studio (Recomendado)
1. Abre Android Studio y selecciona **Open Project**.
2. Navega hasta la carpeta del proyecto y selecciona el directorio `mobile/`.
3. Espera que Gradle finalice la indexación y sincronización de dependencias (`Sync Project with Gradle Files`).
4. Selecciona tu emulador o tu celular físico con depuración USB habilitada en la barra superior.
5. Haz clic en el botón verde **Run 'app'** (o atajo `Shift + F10`).

### Opción B: Desde la Línea de Comandos
Con tu dispositivo o emulador encendido y conectado a `adb`:
```bash
cd mobile
./gradlew installDebug
```

---

## 🔌 Endpoints de la API Consumidos

| Método | Endpoint | Middleware | Función en la App |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/login` | Público (`api`) | Autenticación con `correo` y `contrasena`. Devuelve Bearer Token Sanctum y rol. |
| **GET** | `/api/productos` | Público (`api`) | Consulta del catálogo completo con cálculo dinámico de `estado_stock`. |
| **POST** | `/api/productos` | `auth:sanctum`, `role:Administrador` | Registro de producto validando restricciones CHECK (`precio > 0` y `stock >= 0`). |
| **GET** | `/api/pedidos` | `auth:sanctum` | Listado de pedidos (definido en interfaz `BioGestApi`). |
| **PUT** | `/api/pedidos/{id}` | `auth:sanctum` | Actualización de estado logístico de pedido (definido en interfaz `BioGestApi`). |

---

## 🔑 Cuenta de Demostración para Pruebas

Para las pruebas funcionales de la sustentación, se utiliza la cuenta con rol de **Administrador**:

* **Correo**: `admin@tenebriosgold.com`
* **Contraseña**: Consultar el README principal de la raíz del proyecto o el archivo `DatabaseSeeder.php` (`Admin123*`).
