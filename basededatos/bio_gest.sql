-- ========================================================
-- PROYECTO: BIO-GEST (TENEBRIOS GOLD)
-- SCRIPT COMPLETO 100% CORREGIDO: DDL + DML
-- Motor: MySQL 8.0+ / MariaDB (XAMPP)
-- ========================================================

DROP DATABASE IF EXISTS bio_gest;
CREATE DATABASE bio_gest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bio_gest;

SET FOREIGN_KEY_CHECKS = 0;

-- ========================================================
-- SECCIÓN 1: DDL (ESTRUCTURA Y TABLAS CORREGIDAS)
-- ========================================================

-- 1. Tabla: ROL
CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabla: USUARIO
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    contrasena VARCHAR(255) NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla: DIRECCION_USUARIO (Corregida con nombre_ubicacion)
CREATE TABLE direccion_usuario (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_ubicacion VARCHAR(100) DEFAULT 'Casa',
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10),
    es_principal TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_direccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla: CATEGORIA
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla: PRODUCTO
CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255),
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabla: MOVIMIENTO_INVENTARIO
CREATE TABLE movimiento_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo_movimiento ENUM('ENTRADA', 'SALIDA', 'AJUSTE') NOT NULL,
    cantidad INT NOT NULL,
    observacion TEXT,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabla: CARRITO
CREATE TABLE carrito (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carrito_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabla: CARRITO_DETALLE
CREATE TABLE carrito_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_cdetalle_carrito FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito) ON DELETE CASCADE,
    CONSTRAINT fk_cdetalle_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabla: PEDIDO (Corregida con total, numero_pedido y datos de entrega)
CREATE TABLE pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NULL,
    id_usuario INT NOT NULL,
    id_direccion INT NULL,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    costo_envio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50) DEFAULT 'Contraentrega',
    estado_pago VARCHAR(20) DEFAULT 'Pendiente',
    estado_pedido ENUM('Pendiente', 'Procesando', 'Completado', 'Cancelado') DEFAULT 'Pendiente',
    direccion_entrega VARCHAR(255) NULL,
    ciudad_entrega VARCHAR(100) NULL,
    telefono_contacto VARCHAR(20) NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_pedido_direccion FOREIGN KEY (id_direccion) REFERENCES direccion_usuario(id_direccion) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabla: PEDIDO_DETALLE
CREATE TABLE pedido_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_pdetalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_pdetalle_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Tabla: PROMOCION
CREATE TABLE promocion (
    id_promocion INT AUTO_INCREMENT PRIMARY KEY,
    codigo_cupon VARCHAR(50) NOT NULL UNIQUE,
    descuento_porcentaje DECIMAL(5, 2) NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Tabla: FAQ
CREATE TABLE faq (
    id_faq INT AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    respuesta TEXT NOT NULL,
    categoria VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Tabla: MENSAJE_CONTACTO
CREATE TABLE mensaje_contacto (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    asunto VARCHAR(150),
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Tabla: CHAT_MENSAJE
CREATE TABLE chat_mensaje (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Tabla: PERMISO_ROL
CREATE TABLE permiso_rol (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre_permiso VARCHAR(100) NOT NULL,
    CONSTRAINT fk_permiso_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================================
-- SECCIÓN 2: DML (DATOS DE PRUEBA / SEED CORREGIDOS)
-- ========================================================

-- Contraseña por defecto para usuarios: '123456'

-- 1. ROLES
INSERT INTO rol (id_rol, nombre_rol, descripcion) VALUES 
(1, 'Administrador', 'Acceso total a la gestión del sistema, inventario y pedidos'),
(2, 'Cliente', 'Acceso al catálogo, compras, carrito y seguimiento de pedidos');

-- 2. USUARIOS
INSERT INTO usuario (id_usuario, id_rol, nombre, apellido, correo, telefono, contrasena, estado) VALUES
(1, 1, 'Luis Carlos', 'Carranza Morales', 'luis.carranza@gmail.com', '3001234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo'),
(2, 2, 'Carlos', 'Martínez', 'carlos.martinez@cafam.com', '3109876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo');

-- 3. DIRECCIONES
INSERT INTO direccion_usuario (id_direccion, id_usuario, nombre_ubicacion, direccion, ciudad, departamento, codigo_postal, es_principal) VALUES
(1, 1, 'Casa', 'Calle 26 # 68-90, Suba', 'Bogotá', 'Cundinamarca', '111111', 1),
(2, 2, 'Trabajo / Finca', 'Carrera 15 # 45-20', 'Bogotá', 'Cundinamarca', '110011', 1);

-- 4. CATEGORÍAS
INSERT INTO categoria (id_categoria, nombre_categoria, descripcion) VALUES
(1, 'Paquetes de Larvas', 'Larvas vivas de Tenebrio Molitor de alta calidad con garantía de llegada viva'),
(2, 'Abono Orgánico', 'Frass (excremento de tenebrio) ideal como bioestimulante agrícola'),
(3, 'Kits de Cría', 'Insumos y accesorios completos para la cría y reproducción doméstica');

-- 5. PRODUCTOS (Catálogo Tenebrios Gold)
INSERT INTO producto (id_producto, id_categoria, nombre_producto, descripcion, precio, stock, imagen, estado) VALUES
(1, 1, 'Paquete Inicial – 500 larvas', 'Ideal para probar o si tienes una mascota. Las que no uses las guardas en nevera, hibernan hasta 2 meses y medio.', 34900.00, 50, 'paquete_500.jpg', 'Activo'),
(2, 1, 'Paquete Básico – 1,000 larvas', 'Incluye guía en PDF con todo el proceso de cría. Ideal para iniciar un cultivo pequeño y tener siempre disponibles.', 59900.00, 50, 'paquete_1000.jpg', 'Activo'),
(3, 1, 'Paquete Recomendado – 2,000 larvas', 'Incluye ENVÍO GRATIS y curso completo en video paso a paso. Estableces un cultivo sólido y producción continua desde el inicio.', 122900.00, 40, 'paquete_2000.jpg', 'Activo'),
(4, 1, 'Paquete Profesional – 5,000 larvas', 'Incluye ENVÍO GRATIS, curso en video y guía para gran escala. Ideal si tu proyecto es grande o quieres emprender.', 289900.00, 25, 'paquete_5000.jpg', 'Activo'),
(5, 1, 'Paquete Industrial – 10,000 larvas', 'Incluye curso completo y asesoría personalizada (pago anticipado). Para producción comercial o suplementar grandes volúmenes.', 499900.00, 15, 'paquete_10000.jpg', 'Activo');

-- 6. MOVIMIENTOS DE INVENTARIO
INSERT INTO movimiento_inventario (id_producto, tipo_movimiento, cantidad, observacion) VALUES
(1, 'ENTRADA', 50, 'Inventario inicial de producción'),
(2, 'ENTRADA', 50, 'Inventario inicial de producción'),
(3, 'ENTRADA', 40, 'Inventario inicial de producción'),
(4, 'ENTRADA', 25, 'Inventario inicial de producción'),
(5, 'ENTRADA', 15, 'Inventario inicial de producción');

-- 7. CARRITO DE COMPRAS DE EJEMPLO
INSERT INTO carrito (id_carrito, id_usuario) VALUES (1, 2);
INSERT INTO carrito_detalle (id_carrito, id_producto, cantidad) VALUES (1, 1, 2);

-- 8. PEDIDO DE EJEMPLO
INSERT INTO pedido (id_pedido, numero_pedido, id_usuario, id_direccion, subtotal, costo_envio, total, metodo_pago, estado_pago, estado_pedido, direccion_entrega, ciudad_entrega, telefono_contacto) VALUES
(1, 'PED-1001', 1, 1, 122900.00, 0.00, 122900.00, 'Contraentrega', 'Aprobado', 'Completado', 'Calle 26 # 68-90, Suba', 'Bogotá', '3001234567');

INSERT INTO pedido_detalle (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 3, 1, 122900.00, 122900.00);

-- 9. PROMOCIONES
INSERT INTO promocion (codigo_cupon, descuento_porcentaje, fecha_inicio, fecha_fin, estado) VALUES
('TENEBRIO10', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'Activo');

-- 10. PREGUNTAS FRECUENTES (FAQ)
INSERT INTO faq (pregunta, respuesta, categoria) VALUES
('¿Tienen garantía de llegada viva?', 'Sí, todos nuestros paquetes se envían acondicionados térmicamente con garantía total de llegada viva a todo el país.', 'Envíos'),
('¿Cómo debo almacenar las larvas que no use de inmediato?', 'Puedes guardarlas en la bandeja inferior de la nevera (refrigeración suave), donde hibernarán sin comer ni metamorfosearse hasta por 2 meses y medio.', 'Cuidados'),
('¿Los envíos son gratuitos?', 'El envío es GRATIS a partir del Paquete Recomendado de 2,000 larvas en adelante.', 'Envíos');

-- 11. MENSAJE DE CONTACTO
INSERT INTO mensaje_contacto (nombre, correo, asunto, mensaje) VALUES
('Cliente Consulta', 'consulta@cliente.com', 'Duda sobre envío', '¿Hacen entregas los días sábados en Bogotá?');

-- 12. PERMISOS POR ROL
INSERT INTO permiso_rol (id_rol, nombre_permiso) VALUES
(1, 'GESTIONAR_PRODUCTOS'),
(1, 'GESTIONAR_USUARIOS'),
(1, 'VER_REPORTES'),
(2, 'COMPRAR_PRODUCTOS'),
(2, 'VER_HISTORIAL_PEDIDOS');

SET FOREIGN_KEY_CHECKS = 1;