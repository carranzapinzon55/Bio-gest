<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Función fn_estado_stock: retorna 'AGOTADO', 'STOCK BAJO' o 'DISPONIBLE'
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_estado_stock;
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
            END;
        ");

        // 2. Trigger trg_auditoria_stock_producto: AFTER UPDATE ON producto
        // Inserta en movimiento_inventario (ENTRADA o SALIDA) cuando cambie el stock
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_auditoria_stock_producto;
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
            END;
        ");

        // 3. Procedimiento almacenado sp_resumen_ventas_fechas(IN p_inicio DATE, IN p_fin DATE)
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_resumen_ventas_fechas;
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
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_resumen_ventas_fechas;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_auditoria_stock_producto;");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_estado_stock;");
    }
};
