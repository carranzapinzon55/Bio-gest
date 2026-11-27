# Matriz de Pruebas de Interoperabilidad - BIO-GEST 🧪📲💻

**Proyecto**: BIO-GEST (TENEBRIOS GOLD)  
**Componentes Evaluados**: Aplicación Móvil Android (Kotlin + Jetpack Compose) ↔ API REST Laravel (Sanctum) ↔ Base de Datos MySQL / MariaDB ↔ Panel Web Administrativo.

---

## 📋 Registro de Casos de Prueba de Integración

| # | Caso de Prueba / Paso Realizado | Resultado Esperado | Resultado Obtenido | Evidencia / Captura |
| :-: | :--- | :--- | :---: | :---: |
| **1** | **Login con Credenciales Incorrectas**<br>1. Abrir la app en pantalla de Login.<br>2. Ingresar `admin@tenebriosgold.com` con clave errónea.<br>3. Presionar *"Ingresar"*. | La API responde HTTP 401 (`"Credenciales inválidas."`). La app no crashea, no avanza de pantalla y muestra en color de error: *"Correo o contraseña incorrectos"*. | *(En blanco para diligenciar)* | *(En blanco)* |
| **2** | **Login Exitoso (Administrador)**<br>1. Ingresar correo y contraseña válidos de la cuenta de prueba.<br>2. Presionar *"Ingresar"*. | La API responde HTTP 200 con Token Bearer Sanctum. El token se almacena en memoria y en `SharedPreferences`. La app transiciona a la pantalla *"Productos"* y carga el catálogo automáticamente. | *(En blanco para diligenciar)* | *(En blanco)* |
| **3** | **Consulta de Catálogo (Interoperabilidad App vs Web)**<br>1. Observar la lista de productos en la app móvil.<br>2. Abrir el navegador web en el módulo de catálogo del backend.<br>3. Comparar nombres, precios en COP y stock. | Los mismos productos existentes en la base de datos MySQL se muestran idénticos tanto en la app Android como en la interfaz web de Laravel. | *(En blanco para diligenciar)* | *(En blanco)* |
| **4** | **Registro desde Celular y Reflejo en la Web**<br>1. En la app presionar el botón flotante `+`.<br>2. Llenar el formulario con un nuevo producto (ej. *"Harina de Tenebrio 500g"*, precio 45000, stock 15).<br>3. Presionar *"Guardar"*. | La API valida y responde HTTP 201 Created. La app regresa al catálogo mostrando el banner verde *"✓ Guardado correctamente"*. Al recargar la página web de Laravel, el nuevo producto ya aparece registrado en la base de datos. | *(En blanco para diligenciar)* | *(En blanco)* |
| **5** | **Modificación desde Web y Reflejo en la App**<br>1. En el panel administrativo web, editar el stock de un producto (ej. bajarlo a 3 unidades).<br>2. En la app móvil, presionar el botón *"Actualizar"*. | La app Android realiza una nueva petición `GET /api/productos`. La tarjeta del producto actualizado refleja inmediatamente el nuevo stock y muestra la alerta en rojo *"• Stock bajo"*. | *(En blanco para diligenciar)* | *(En blanco)* |
| **6** | **Cierre de Sesión y Persistencia**<br>1. En la app móvil presionar el botón *"Salir"*.<br>2. Cerrar la app por completo desde la multitarea del teléfono y volver a abrirla. | Al presionar *"Salir"*, se elimina el token de `SharedPreferences` y de memoria. La app vuelve a la pantalla de Login. Al reabrir la app, permanece en el Login exigiendo credenciales. | *(En blanco para diligenciar)* | *(En blanco)* |
| **7** | **Control de Acceso por Roles (Cliente vs Admin)**<br>1. Iniciar sesión con una cuenta de rol Cliente (`cliente@tenebriosgold.com`).<br>2. Intentar registrar un producto en el backend o enviar un `POST /api/productos` con el token de Cliente. | La API de Laravel intercepta la petición con el middleware `role:Administrador` y responde HTTP 403 Forbidden. La app captura el código 403 y muestra: *"No tienes permiso para registrar productos"*. | *(En blanco para diligenciar)* | *(En blanco)* |
