-- ========================================================
-- PROYECTO: BIO-GEST (TENEBRIOS GOLD)
-- BASE DE DATOS: bio_gest
-- CONSOLIDADO: ESTRUCTURA (DDL), RESTRICCIONES CHECK,
--              RUTINAS (FUNCIÓN, TRIGGER, PROCEDIMIENTO)
--              Y DATOS INICIALES (DML)
-- Compatibilidad: MySQL 8.0+ / MariaDB 10.4+ (XAMPP)
-- ========================================================

DROP DATABASE IF EXISTS bio_gest;
CREATE DATABASE bio_gest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bio_gest;

SET FOREIGN_KEY_CHECKS = 0;

-- ========================================================
-- SECCIÓN 1: DDL - TABLAS DEL SISTEMA
-- ========================================================

-- 1. Tabla: rol
DROP TABLE IF EXISTS rol;
CREATE TABLE rol (
    id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla: usuario
DROP TABLE IF EXISTS usuario;
CREATE TABLE usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol INT UNSIGNED NOT NULL DEFAULT 2,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NULL,
    contrasena VARCHAR(255) NOT NULL,
    perfil_uso ENUM(
        'Inicio de criadero',
        'Alimentación continua',
        'Proyecto comercial / Gran escala',
        'Mascota única'
    ) NOT NULL DEFAULT 'Alimentación continua',
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remember_token VARCHAR(100) NULL,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla: direccion_usuario
DROP TABLE IF EXISTS direccion_usuario;
CREATE TABLE direccion_usuario (
    id_direccion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    nombre_ubicacion VARCHAR(100) NOT NULL DEFAULT 'Casa',
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NULL,
    es_principal TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_direccion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla: categoria
DROP TABLE IF EXISTS categoria;
CREATE TABLE categoria (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla: producto (Con restricciones CHECK: precio > 0 y stock >= 0)
DROP TABLE IF EXISTS producto;
CREATE TABLE producto (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT UNSIGNED NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    beneficios TEXT NULL,
    instrucciones_conservacion TEXT NULL,
    especies_recomendadas VARCHAR(255) NULL,
    politica_reembolso TEXT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 10,
    imagen VARCHAR(255) NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    CONSTRAINT chk_producto_precio CHECK (precio > 0),
    CONSTRAINT chk_producto_stock CHECK (stock >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla: movimiento_inventario
DROP TABLE IF EXISTS movimiento_inventario;
CREATE TABLE movimiento_inventario (
    id_movimiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL,
    tipo_movimiento ENUM('ENTRADA', 'SALIDA', 'AJUSTE') NOT NULL,
    cantidad INT NOT NULL,
    observacion TEXT NULL,
    fecha_movimiento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla: carrito
DROP TABLE IF EXISTS carrito;
CREATE TABLE carrito (
    id_carrito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carrito_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla: carrito_detalle
DROP TABLE IF EXISTS carrito_detalle;
CREATE TABLE carrito_detalle (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_cdetalle_carrito FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito) ON DELETE CASCADE,
    CONSTRAINT fk_cdetalle_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabla: pedido
DROP TABLE IF EXISTS pedido;
CREATE TABLE pedido (
    id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NULL,
    id_usuario INT UNSIGNED NOT NULL,
    id_direccion INT UNSIGNED NULL,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    costo_envio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL DEFAULT 'Contraentrega',
    estado_pago VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
    estado_pedido ENUM(
        'Pendiente',
        'Procesando',
        'Despachado',
        'Listo para retiro',
        'Entregado',
        'Completado',
        'Cancelado'
    ) NOT NULL DEFAULT 'Pendiente',
    direccion_entrega VARCHAR(255) NULL,
    ciudad_entrega VARCHAR(100) NULL,
    telefono_contacto VARCHAR(20) NULL,
    fecha_pedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_pedido_direccion FOREIGN KEY (id_direccion) REFERENCES direccion_usuario(id_direccion) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabla: pedido_detalle (Con restricción CHECK: cantidad > 0)
DROP TABLE IF EXISTS pedido_detalle;
CREATE TABLE pedido_detalle (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_pdetalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_pdetalle_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    CONSTRAINT chk_pedido_detalle_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabla: promocion
DROP TABLE IF EXISTS promocion;
CREATE TABLE promocion (
    id_promocion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_cupon VARCHAR(50) NOT NULL UNIQUE,
    descuento_porcentaje DECIMAL(5, 2) NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabla: faq
DROP TABLE IF EXISTS faq;
CREATE TABLE faq (
    id_faq INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pregunta TEXT NOT NULL,
    respuesta TEXT NOT NULL,
    categoria VARCHAR(50) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabla: mensaje_contacto
DROP TABLE IF EXISTS mensaje_contacto;
CREATE TABLE mensaje_contacto (
    id_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    asunto VARCHAR(150) NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabla: chat_mensaje
DROP TABLE IF EXISTS chat_mensaje;
CREATE TABLE chat_mensaje (
    id_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabla: permiso_rol
DROP TABLE IF EXISTS permiso_rol;
CREATE TABLE permiso_rol (
    id_permiso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol INT UNSIGNED NOT NULL,
    nombre_permiso VARCHAR(100) NOT NULL,
    CONSTRAINT fk_permiso_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabla: personal_access_tokens (Laravel Sanctum)
DROP TABLE IF EXISTS personal_access_tokens;
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- SECCIÓN 2: RUTINAS ALMACENADAS (FUNCIÓN, TRIGGER, SP)
-- ========================================================

DELIMITER $$

-- 1. Función fn_estado_stock(p_id_producto INT)
DROP FUNCTION IF EXISTS fn_estado_stock$$
CREATE FUNCTION fn_estado_stock(p_id_producto INT) 
RETURNS VARCHAR(20)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_stock INT DEFAULT 0;
    DECLARE v_stock_minimo INT DEFAULT 0;
    
    SELECT stock, stock_minimo INTO v_stock, v_stock_minimo
    FROM producto 
    WHERE id_producto = p_id_producto
    LIMIT 1;
    
    IF v_stock IS NULL OR v_stock <= 0 THEN
        RETURN 'AGOTADO';
    ELSEIF v_stock <= v_stock_minimo THEN
        RETURN 'STOCK BAJO';
    ELSE
        RETURN 'DISPONIBLE';
    END IF;
END$$

-- 2. Trigger trg_auditoria_stock_producto (AFTER UPDATE ON producto)
DROP TRIGGER IF EXISTS trg_auditoria_stock_producto$$
CREATE TRIGGER trg_auditoria_stock_producto
AFTER UPDATE ON producto
FOR EACH ROW
BEGIN
    IF NEW.stock <> OLD.stock THEN
        IF NEW.stock > OLD.stock THEN
            INSERT INTO movimiento_inventario (
                id_producto, 
                tipo_movimiento, 
                cantidad, 
                observacion, 
                fecha_movimiento
            ) VALUES (
                NEW.id_producto, 
                'ENTRADA', 
                NEW.stock - OLD.stock, 
                CONCAT('Auditoría automática: Entrada de stock. Stock anterior: ', OLD.stock, ', nuevo stock: ', NEW.stock), 
                NOW()
            );
        ELSE
            INSERT INTO movimiento_inventario (
                id_producto, 
                tipo_movimiento, 
                cantidad, 
                observacion, 
                fecha_movimiento
            ) VALUES (
                NEW.id_producto, 
                'SALIDA', 
                OLD.stock - NEW.stock, 
                CONCAT('Auditoría automática: Salida de stock. Stock anterior: ', OLD.stock, ', nuevo stock: ', NEW.stock), 
                NOW()
            );
        END IF;
    END IF;
END$$

-- 3. Procedimiento almacenado sp_resumen_ventas_fechas
DROP PROCEDURE IF EXISTS sp_resumen_ventas_fechas$$
CREATE PROCEDURE sp_resumen_ventas_fechas(IN p_inicio DATE, IN p_fin DATE)
BEGIN
    SELECT 
        COUNT(DISTINCT p.id_pedido) AS total_pedidos,
        COALESCE(SUM(p.total), 0) AS total_ventas,
        COALESCE(AVG(p.total), 0) AS ticket_promedio,
        COUNT(DISTINCT CASE WHEN p.estado_pedido IN ('Entregado', 'Completado') THEN p.id_pedido END) AS pedidos_completados,
        COUNT(DISTINCT CASE WHEN p.estado_pedido = 'Cancelado' THEN p.id_pedido END) AS pedidos_cancelados,
        COALESCE(SUM(pd.cantidad), 0) AS total_unidades_vendidas
    FROM pedido p
    LEFT JOIN pedido_detalle pd ON p.id_pedido = pd.id_pedido
    WHERE DATE(p.fecha_pedido) BETWEEN p_inicio AND p_fin;
END$$

DELIMITER ;

-- ========================================================
-- SECCIÓN 3: DML - DATOS INICIALES DEL SISTEMA
-- ========================================================

-- 1. Roles
INSERT INTO rol (id_rol, nombre_rol, descripcion) VALUES
(1, 'Administrador', 'Acceso total a la administración y gestión del sistema'),
(2, 'Cliente', 'Acceso al catálogo, compras, carrito y seguimiento de pedidos');

-- 2. Usuarios (Hash::make bcrypt de Admin123* y Cliente123*)
INSERT INTO usuario (id_usuario, id_rol, nombre, apellido, correo, telefono, contrasena, perfil_uso, estado, fecha_registro) VALUES
(1, 1, 'Administrador', 'BIO-GEST', 'admin@tenebriosgold.com', '3001234567', '$2y$10$Vt1Lu/FeDa8fcRkRn1FQ0eexymeU/BAbkKX.AJ80v4B8nr./6jsNy', 'Proyecto comercial / Gran escala', 'Activo', NOW()),
(2, 2, 'Cliente', 'Tenebrios Gold', 'cliente@tenebriosgold.com', '3109876543', '$2y$10$zjNfjDMO7btGWUU2MgH0reNguAzi0Ah0rRFol.q0yGNepC1DR3rKG', 'Alimentación continua', 'Activo', NOW());

-- 3. Direcciones
INSERT INTO direccion_usuario (id_direccion, id_usuario, nombre_ubicacion, direccion, ciudad, departamento, codigo_postal, es_principal) VALUES
(1, 1, 'Sede Principal Bio-Gest', 'Calle 26 # 68-90, Suba', 'Bogotá', 'Cundinamarca', '111111', 1),
(2, 2, 'Casa', 'Carrera 15 # 45-20, Chapinero', 'Bogotá', 'Cundinamarca', '110011', 1);

-- 4. Categorías
INSERT INTO categoria (id_categoria, nombre_categoria, descripcion) VALUES
(1, 'Paquetes de Larvas', 'Larvas vivas de Tenebrio Molitor de alta calidad con garantía de llegada viva'),
(2, 'Abono Orgánico', 'Frass (excremento de tenebrio) ideal como bioestimulante agrícola y enmienda orgánica'),
(3, 'Kits de Cría', 'Insumos, contenedores y accesorios completos para la cría y reproducción doméstica');

-- 5. Productos (5 Paquetes de Tenebrios Gold)
INSERT INTO producto (id_producto, id_categoria, nombre_producto, descripcion, beneficios, instrucciones_conservacion, especies_recomendadas, politica_reembolso, precio, stock, stock_minimo, imagen, estado, fecha_creacion) VALUES
(1, 1, 'Paquete Inicial – 500 larvas', 'Ideal para probar o si tienes una mascota. Las que no uses las guardas en nevera, hibernan hasta 2 meses y medio.', 'Alta proteína digestiva, ideal para reptiles y aves pequeñas.', 'Refrigerar entre 8°C y 12°C en sustrato de salvado.', 'Aves pequeñas, reptiles, erizos, peces de acuario.', 'Garantía 100% de llegada viva documentando desempaque.', 34900.00, 50, 10, 'paquete_500.jpg', 'Activo', NOW()),
(2, 1, 'Paquete Básico – 1.000 larvas', 'Incluye guía en PDF con todo el proceso de cría. Ideal para iniciar un cultivo pequeño y tener siempre disponibles.', 'Permite rotación alimentaria y primeras pruebas de reproducción.', 'Mantener a temperatura ambiente (22°C-26°C) o refrigerar.', 'Erizos, petauros, aves ornamentales, anfibios.', 'Garantía de reposición o reembolso dentro de las 24 horas.', 59900.00, 45, 10, 'paquete_1000.jpg', 'Activo', NOW()),
(3, 1, 'Paquete Recomendado – 2.000 larvas', 'Incluye ENVÍO GRATIS y curso completo en video paso a paso. Estableces un cultivo sólido y producción continua desde el inicio.', 'Excelente balance costo-beneficio, pie de cría autosostenible.', 'Separar en bandejas plásticas ventiladas con salvado de trigo.', 'Criaderos de aves, pogonas, geckos leopardo, granjas avícolas.', 'Reposición garantizada con registro fotográfico.', 122900.00, 40, 10, 'paquete_2000.jpg', 'Activo', NOW()),
(4, 1, 'Paquete Profesional – 5.000 larvas', 'Incluye ENVÍO GRATIS, curso en video y guía para gran escala. Ideal si tu proyecto es grande o quieres emprender.', 'Volumen ideal para proyectos comerciales y bioconversión de residuos.', 'Sistema vertical de bandejas con control de humedad.', 'Criaderos intensivos, fincas avícolas, zoológicos y centros de rescate.', 'Garantía integral de llegada viva y soporte técnico.', 289900.00, 25, 5, 'paquete_5000.jpg', 'Activo', NOW()),
(5, 1, 'Paquete Industrial – 10.000 larvas', 'Incluye curso completo y asesoría personalizada (pago anticipado). Para producción comercial o suplementar grandes volúmenes.', 'Máximo rendimiento económico por unidad, soporte zootécnico directo.', 'Granja comercial o sala de cría climatizada.', 'Industria acuícola, avicultura comercial, plantas de procesamiento.', 'Acompañamiento zootécnico y garantía de despacho.', 499900.00, 15, 5, 'paquete_10000.jpg', 'Activo', NOW());

-- 6. Movimientos de Inventario Iniciales
INSERT INTO movimiento_inventario (id_producto, tipo_movimiento, cantidad, observacion, fecha_movimiento) VALUES
(1, 'ENTRADA', 50, 'Inventario inicial de producción BIO-GEST', NOW()),
(2, 'ENTRADA', 45, 'Inventario inicial de producción BIO-GEST', NOW()),
(3, 'ENTRADA', 40, 'Inventario inicial de producción BIO-GEST', NOW()),
(4, 'ENTRADA', 25, 'Inventario inicial de producción BIO-GEST', NOW()),
(5, 'ENTRADA', 15, 'Inventario inicial de producción BIO-GEST', NOW());

-- 7. Pedidos de Prueba
-- Pedido 1: Pedido Completado
INSERT INTO pedido (id_pedido, numero_pedido, id_usuario, id_direccion, subtotal, costo_envio, total, metodo_pago, estado_pago, estado_pedido, direccion_entrega, ciudad_entrega, telefono_contacto, fecha_pedido) VALUES
(1, 'PED-1001', 2, 2, 122900.00, 0.00, 122900.00, 'Contraentrega', 'Aprobado', 'Completado', 'Carrera 15 # 45-20, Chapinero', 'Bogotá', '3109876543', DATE_SUB(NOW(), INTERVAL 3 DAY));

INSERT INTO pedido_detalle (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 3, 1, 122900.00, 122900.00);

-- Pedido 2: Pedido Pendiente con múltiples detalles
INSERT INTO pedido (id_pedido, numero_pedido, id_usuario, id_direccion, subtotal, costo_envio, total, metodo_pago, estado_pago, estado_pedido, direccion_entrega, ciudad_entrega, telefono_contacto, fecha_pedido) VALUES
(2, 'PED-1002', 2, 2, 94800.00, 10000.00, 104800.00, 'Transferencia Bancaria', 'Pendiente', 'Pendiente', 'Carrera 15 # 45-20, Chapinero', 'Bogotá', '3109876543', NOW());

INSERT INTO pedido_detalle (id_detalle, id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES
(2, 2, 1, 1, 34900.00, 34900.00),
(3, 2, 2, 1, 59900.00, 59900.00);

-- 8. Permisos de Rol
INSERT INTO permiso_rol (id_rol, nombre_permiso) VALUES
(1, 'GESTIONAR_PRODUCTOS'),
(1, 'GESTIONAR_USUARIOS'),
(1, 'VER_REPORTES'),
(2, 'COMPRAR_PRODUCTOS'),
(2, 'VER_HISTORIAL_PEDIDOS');

-- 9. Promociones
INSERT INTO promocion (codigo_cupon, descuento_porcentaje, fecha_inicio, fecha_fin, estado) VALUES
('TENEBRIO10', 10.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'Activo');

-- 10. Preguntas Frecuentes
INSERT INTO faq (pregunta, respuesta, categoria) VALUES
('¿Tienen garantía de llegada viva?', 'Sí, todos nuestros paquetes se envían acondicionados térmicamente con garantía total de llegada viva a todo el país.', 'Envíos'),
('¿Cómo debo almacenar las larvas que no use de inmediato?', 'Puedes guardarlas en la bandeja inferior de la nevera (refrigeración suave a 8-12°C), donde hibernarán sin metamorfosearse hasta por 2 meses y medio.', 'Cuidados'),
('¿Los envíos son gratuitos?', 'El envío es GRATIS a partir del Paquete Recomendado de 2,000 larvas en adelante.', 'Envíos');
