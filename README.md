# BIO-GEST (Tenebrios Gold)

## Descripción del proyecto
**BIO-GEST** es una plataforma integral de comercio electrónico y gestión operativa desarrollada para **Tenebrios Gold**, una empresa dedicada a la producción y comercialización de alimentos vivos para animales, con enfoque especializado en el cultivo y distribución de *Tenebrio molitor* (gusanos de la harina)[cite: 2, 3].

El sistema nace para centralizar y digitalizar los procesos comerciales y administrativos del negocio, superando el manejo disperso de pedidos, existencias y reportes. A través de una interfaz web moderna alineada con la identidad visual de la marca (negro `#151515` y dorado `#e4b353`) y una aplicación móvil conectada en tiempo real, **BIO-GEST** conecta la tienda orientada al cliente con el panel de control del administrador.

De esta manera, los clientes pueden explorar el catálogo de productos, gestionar su carrito de compras, consultar su historial y realizar pedidos de forma ágil, mientras que el equipo administrativo controla el inventario, las entradas y salidas de mercancía, las alertas de bajo stock, las promociones, los estados de cada pedido y la generación de reportes de ventas[cite: 1, 2].

---

## Objetivo general
Optimizar y centralizar la gestión comercial, de inventario y de pedidos de **Tenebrios Gold** mediante una solución tecnológica multiplataforma compuesta por una aplicación web en Laravel, una base de datos relacional en MySQL y una aplicación móvil Android comunicada a través de una API REST[cite: 2, 3].

---

## Módulos y funcionalidades principales

### 1. Tienda Web y Experiencia del Cliente
- **Catálogo de productos:** visualización de presentaciones y productos de alimento vivo disponibles para la compra[cite: 1, 2].
- **Gestión de cuenta y pedidos:** registro e inicio de sesión de clientes, realización de compras en línea y consulta del historial de pedidos con seguimiento de estado[cite: 1, 2].
- **Atención y soporte:** canales de contacto con el administrador y soporte automatizado[cite: 1].

### 2. Panel Administrativo Web
- **Gestión de productos (CRUD):** registro, consulta, edición y desactivación/eliminación de productos con validaciones de datos.
- **Control de inventario:** consulta de existencias en tiempo real, trazabilidad de entradas y salidas, y alertas por bajo nivel de stock[cite: 1, 2].
- **Gestión de pedidos:** visualización detallada de órdenes (número de pedido, cliente, fecha, productos, cantidades, valor total y estado) y actualización de su ciclo de estado (por ejemplo, *Pendiente*, en proceso, entregado).
- **Promociones:** administración de ofertas y promociones vigentes en la plataforma[cite: 1].
- **Módulo de reportes:** filtrado de ventas por rango de fechas, resumen de totales por producto, visualización de estados de pedidos y exportación de reportes en formato **CSV** compatible con Excel.

### 3. API REST y Aplicación Móvil Android
- **API REST en Laravel:** servicios en formato JSON protegidos mediante autenticación por token para consultar y actualizar productos y pedidos de forma segura.
- **Aplicación móvil en Kotlin:** aplicación Android conectada al mismo backend y base de datos MySQL que la web, permitiendo iniciar sesión, consultar registros en tiempo real y ejecutar operaciones administrativas con interoperabilidad inmediata.

---

## Integrantes y responsabilidades
- **Luis:** Scrum Master y coordinación técnica del backend, base de datos MySQL y API REST.
- **Dayanna:** Desarrollo de interfaz web, operaciones CRUD, validaciones y módulo de reportes[cite: 2].
- **Evelyn:** Desarrollo de la aplicación móvil Android y pruebas de interoperabilidad[cite: 2].
- **Ian:** Documentación técnica (`docs/` y `README.md`), arquitectura documental y preparación de la sustentación[cite: 2].

---

## Tecnologías utilizadas
- **Backend y Web:** PHP, Laravel (Blade, rutas web y API, middleware de roles y protección CSRF)[cite: 2, 3]
- **Base de datos:** MySQL (migraciones, seeders, restricciones de integridad, procedimientos, funciones y triggers)[cite: 2]
- **API REST:** Endpoints RESTful en JSON con autenticación por token[cite: 2]
- **Aplicación móvil:** Android Studio, Kotlin, Retrofit y Gson[cite: 2]
- **Control de versiones:** Git y GitHub[cite: 2, 3]

---

## Requisitos para ejecutar el proyecto
- Servidor local con PHP, Composer y MySQL (por ejemplo, Laragon o XAMPP)[cite: 2]
- Git para el control de versiones[cite: 2]
- Android Studio y SDK de Android (para ejecutar el cliente móvil ubicado en `mobile/`)[cite: 2]

   ```bash
   git clone [https://github.com/carranzapinzons55/Bio-gest.git](https://github.com/carranzapinzons55/Bio-gest.git)
   cd Bio-gest
