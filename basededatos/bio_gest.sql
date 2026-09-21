CREATE DATABASE IF NOT EXISTS BIO_GEST;
USE BIO_GEST;

-- ========================================================
-- 1. ROLES, PERMISOS, USUARIOS Y DIRECCIONES
-- ========================================================

CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion TEXT,
    estado VARCHAR(20) DEFAULT 'Activo'
);

-- Matriz de permisos por módulo (Panel Administrador)
CREATE TABLE permiso_rol (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    modulo VARCHAR(50) NOT NULL, -- Ej: Usuarios, Productos, Pedidos, Inventario
    puede_ver BOOLEAN DEFAULT FALSE,
    puede_crear BOOLEAN DEFAULT FALSE,
    puede_editar BOOLEAN DEFAULT FALSE,
    puede_eliminar BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(150) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    estado VARCHAR(20) DEFAULT 'Activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME NULL,
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- Direcciones guardadas del cliente (direcciones.html / editar-perfil.html)
CREATE TABLE direccion_usuario (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    alias_direccion VARCHAR(50) NOT NULL DEFAULT 'Principal', -- Ej: Casa, Trabajo, Finca
    nombre_destinatario VARCHAR(150) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    barrio VARCHAR(100),
    ciudad VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(20),
    es_principal BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- ========================================================
-- 2. CATÁLOGO, INVENTARIO Y CATEGORÍAS
-- ========================================================

CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_categoria VARCHAR(255)
);

CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(100) NOT NULL,
    descripcion_producto TEXT,
    precio DECIMAL(10,2) NOT NULL CHECK (precio > 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    stock_minimo INT NOT NULL DEFAULT 10 CHECK (stock_minimo >= 0),
    imagen_url VARCHAR(255),
    estado VARCHAR(20) DEFAULT 'Activo',
    caracteristicas VARCHAR(250), -- Información nutricional / técnica
    beneficios VARCHAR(250),
    instrucciones VARCHAR(250),
    uso_recomendado VARCHAR(250),
    id_categoria INT NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

-- Auditoría de stock (Vista Admin: Inventario)
CREATE TABLE movimiento_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL, 
    tipo_movimiento ENUM('Entrada', 'Salida', 'Ajuste') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255),
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- ========================================================
-- 3. PROMOCIONES
-- ========================================================

CREATE TABLE promocion (
    id_promocion INT AUTO_INCREMENT PRIMARY KEY,
    nombre_promocion VARCHAR(100) NOT NULL,
    tipo_promocion VARCHAR(50) NOT NULL, -- Por producto, Por envío, Por cantidad, General
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    descuento DECIMAL(5,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Activa',
    id_producto INT NULL,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
);

-- ========================================================
-- 4. CARRITO, PEDIDOS Y CHECKOUT
-- ========================================================

CREATE TABLE carrito (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'Activo',
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE carrito_detalle (
    id_carrito_detalle INT AUTO_INCREMENT PRIMARY KEY,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    id_carrito INT NOT NULL,
    id_producto INT NOT NULL,
    FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
);

CREATE TABLE pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL, -- Ej: PED-0018
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado_pedido VARCHAR(20) NOT NULL DEFAULT 'Pendiente', -- Pendiente, Confirmado, En camino, Entregado, Cancelado
    metodo_pago VARCHAR(50) NOT NULL,                      -- Nequi, Daviplata, PSE, Contraentrega
    estado_pago VARCHAR(20) NOT NULL DEFAULT 'Pendiente',    -- Pendiente, Aprobado, Rechazado
    subtotal DECIMAL(10,2) NOT NULL,
    costo_envio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_pedido DECIMAL(10,2) NOT NULL,
    direccion_entrega VARCHAR(200) NOT NULL,
    ciudad_entrega VARCHAR(100) NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    empresa_transporte VARCHAR(100) NULL,                   -- Ej: Servientrega, Interrapidísimo
    numero_guia VARCHAR(100) NULL,                          -- Número de rastreo
    id_usuario INT NOT NULL,
    id_direccion INT NULL,                                  -- Opcional: enlace a la dirección guardada
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_direccion) REFERENCES direccion_usuario(id_direccion) ON DELETE SET NULL
);

CREATE TABLE pedido_detalle (
    id_pedido_detalle INT AUTO_INCREMENT PRIMARY KEY,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
);

-- ========================================================
-- 5. SOPORTE, CHAT Y MENSAJERÍA
-- ========================================================

-- Módulo de Chat en Vivo (Panel Admin <-> Cliente)
CREATE TABLE chat_mensaje (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor INT NOT NULL,    
    id_receptor INT NOT NULL,  
    mensaje TEXT NOT NULL,
    archivo_adjunto VARCHAR(255) NULL, 
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_emisor) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_receptor) REFERENCES usuario(id_usuario)
);

-- Formulario de Contacto (contacto.html)
CREATE TABLE mensaje_contacto (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_remitente VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente', -- Pendiente, Atendido
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Preguntas Frecuentes dinámicas (faq.html)
CREATE TABLE faq (
    id_faq INT AUTO_INCREMENT PRIMARY KEY,
    pregunta VARCHAR(255) NOT NULL,
    respuesta TEXT NOT NULL,
    categoria_faq VARCHAR(50) DEFAULT 'General', -- Ej: Envíos, Cuidados, Pagos
    orden INT DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'Activo'
);