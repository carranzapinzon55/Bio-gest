/* ==========================================================
   BIO-GEST · Panel de Administrador · Tenebrios Gold
   SPA Vanilla JS con datos precargados e interactividad real
   ========================================================== */
(() => {
    'use strict';

    // -------------------- Utilidades --------------------
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    const fmtCOP = (n) => '$ ' + Number(n || 0).toLocaleString('es-CO');
    const fmtNum = (n) => Number(n || 0).toLocaleString('es-CO');
    const initials = (name) => String(name || 'U').trim().split(/\s+/).slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('');

    const imagePath = window.BIOGEST_ADMIN_ASSET_PATH || '../assets/img/';
    const IMG = {
        tenebrios: `${imagePath}Tenebrios_img.png`,
        zophobas: `${imagePath}Zophobas_img.png`,
        dubias: `${imagePath}Dubias_img.png`,
        combos: `${imagePath}Combos_img.png`,
        pro: `${imagePath}pro.jfif`,
        oferta1: `${imagePath}oferta_1.jfif`,
        oferta2: `${imagePath}oferta_2.jfif`,
        oferta3: `${imagePath}oferta_3.jfif`,
    };

    const AVATAR_COLORS = [
        ['#E8EFE9', '#3F5543'],
        ['#E0ECFF', '#1D4ED8'],
        ['#FEF3C7', '#B45309'],
        ['#EFE7FF', '#6D28D9'],
        ['#FFEDD5', '#C2410C'],
    ];

    // -------------------- Estado Global --------------------
    const state = {
        view: 'inicio',
        globalQuery: '',
        selectedDate: '2025-08-25',
        liveDataLoaded: false,
        summary: {
            totalUsers: 18,
            totalOrders: 48,
            pendingOrders: 14,
            confirmedOrders: 22,
            deliveredOrders: 12,
            monthlyOrders: 18,
            monthlyRevenue: 120000000,
        },
        homeUserPage: 1,
        selectedRoleId: 'ROL-001',
        selectedProductId: null, // null = modo Agregar producto
        selectedPromoId: 'PROM-001',
        selectedOrderId: 'PED-0015',
        selectedChatId: 'CHT-001',
        productDraftImage: '',
        filters: {
            users: { q: '', role: 'Todos', status: 'Todos' },
            roles: { q: '' },
            products: { q: '', category: 'Todas', status: 'Todos' },
            promos: { q: '', status: 'Todos', type: 'Todos' },
            orders: { q: '', status: 'Todos', date: 'Todos' },
            reports: { start: '2025-08-01', end: '2025-08-31', status: 'Todos' },
        },

        notifications: [
            { id: 1, icon: 'fa-triangle-exclamation', text: 'Stock bajo en Gryllus assimilis (Dubia): 6.320 unidades', time: 'Hace 10 min', view: 'inventario', unread: true },
            { id: 2, icon: 'fa-bag-shopping', text: 'Nuevo pedido de Andrés Gómez por $ 54.600', time: 'Hace 32 min', view: 'pedidos', unread: true },
            { id: 3, icon: 'fa-comment-dots', text: 'Carlos Ramírez envió un mensaje en Chats', time: 'Hace 1 h', view: 'chats', unread: true },
            { id: 4, icon: 'fa-tag', text: 'La promoción "Black Friday" está programada para 01/11/2025', time: 'Hace 3 h', view: 'promociones', unread: false },
        ],

        users: [
            { id: 'USR-001', name: 'María López', email: 'maria@bio-gest.com', role: 'Administrador', status: 'Activo', lastAccess: '25/08/2025 10:24 a. m.' },
            { id: 'USR-002', name: 'Carlos Ramírez', email: 'carlos@bio-gest.com', role: 'Vendedor', status: 'Activo', lastAccess: '25/08/2025 09:15 a. m.' },
            { id: 'USR-003', name: 'Laura Torres', email: 'laura@bio-gest.com', role: 'Cliente', status: 'Activo', lastAccess: '24/08/2025 06:42 p. m.' },
            { id: 'USR-004', name: 'Andrés Gómez', email: 'andres@bio-gest.com', role: 'Inventario', status: 'Activo', lastAccess: '24/08/2025 04:37 p. m.' },
            { id: 'USR-005', name: 'Sofía Rojas', email: 'sofia@bio-gest.com', role: 'Cliente', status: 'Inactivo', lastAccess: '20/08/2025 11:12 a. m.' },
            { id: 'USR-006', name: 'Daniel Peña', email: 'daniel@bio-gest.com', role: 'Administrador', status: 'Activo', lastAccess: '18/08/2025 03:28 p. m.' },
            { id: 'USR-007', name: 'Valentina Cruz', email: 'valentina@bio-gest.com', role: 'Vendedor', status: 'Activo', lastAccess: '17/08/2025 01:05 p. m.' },
            { id: 'USR-008', name: 'Jorge Castillo', email: 'jorge@bio-gest.com', role: 'Cliente', status: 'Activo', lastAccess: '15/08/2025 09:20 a. m.' },
            { id: 'USR-009', name: 'Camila Méndez', email: 'camila@bio-gest.com', role: 'Cliente', status: 'Activo', lastAccess: '14/08/2025 05:10 p. m.' },
            { id: 'USR-010', name: 'Mateo Vargas', email: 'mateo@bio-gest.com', role: 'Inventario', status: 'Activo', lastAccess: '12/08/2025 08:45 a. m.' },
        ],

        roles: [
            { id: 'ROL-001', name: 'Administrador', desc: 'Acceso total al sistema y gestión de todos los módulos.', usersCount: 1, status: 'Activo' },
            { id: 'ROL-002', name: 'Vendedor', desc: 'Gestiona pedidos, clientes y promociones.', usersCount: 3, status: 'Activo' },
            { id: 'ROL-003', name: 'Cliente', desc: 'Realiza compras y consulta su historial.', usersCount: 2, status: 'Activo' },
            { id: 'ROL-004', name: 'Inventario', desc: 'Controla stock y gestiona productos.', usersCount: 2, status: 'Activo' },
        ],

        permissionModules: [
            { key: 'usuarios', label: 'Usuarios', icon: 'fa-users' },
            { key: 'roles', label: 'Roles y permisos', icon: 'fa-shield-halved' },
            { key: 'productos', label: 'Productos', icon: 'fa-boxes-stacked' },
            { key: 'promociones', label: 'Promociones', icon: 'fa-bullhorn' },
            { key: 'pedidos', label: 'Pedidos', icon: 'fa-receipt' },
            { key: 'carrito', label: 'Carrito de compras', icon: 'fa-cart-shopping' },
            { key: 'inventario', label: 'Inventario', icon: 'fa-clipboard-list' },
            { key: 'reportes', label: 'Reportes', icon: 'fa-chart-column' },
            { key: 'config', label: 'Configuración', icon: 'fa-gear' },
        ],

        rolePermissions: {
            'ROL-001': {
                usuarios: ['ver', 'crear', 'editar', 'eliminar'],
                roles: ['ver', 'crear', 'editar', 'eliminar'],
                productos: ['ver', 'crear', 'editar', 'eliminar'],
                promociones: ['ver', 'crear', 'editar', 'eliminar'],
                pedidos: ['ver', 'crear', 'editar', 'eliminar'],
                carrito: ['ver', 'crear', 'editar', 'eliminar'],
                inventario: ['ver', 'crear', 'editar', 'eliminar'],
                reportes: ['ver', 'crear', 'editar', 'eliminar'],
                config: ['ver', 'crear', 'editar', 'eliminar'],
            },
            'ROL-002': {
                usuarios: ['ver'],
                roles: [],
                productos: ['ver'],
                promociones: ['ver', 'crear', 'editar'],
                pedidos: ['ver', 'crear', 'editar'],
                carrito: ['ver'],
                inventario: ['ver'],
                reportes: ['ver'],
                config: [],
            },
            'ROL-003': {
                usuarios: [],
                roles: [],
                productos: ['ver'],
                promociones: ['ver'],
                pedidos: ['ver', 'crear'],
                carrito: ['ver', 'crear', 'editar', 'eliminar'],
                inventario: [],
                reportes: [],
                config: [],
            },
            'ROL-004': {
                usuarios: [],
                roles: [],
                productos: ['ver', 'crear', 'editar'],
                promociones: ['ver'],
                pedidos: ['ver'],
                carrito: [],
                inventario: ['ver', 'crear', 'editar', 'eliminar'],
                reportes: ['ver'],
                config: [],
            },
        },

        categories: [
            { id: 'CAT-1', name: 'Tenebrios', color: '#2E7D32' },
            { id: 'CAT-2', name: 'Zophobas', color: '#0284C7' },
            { id: 'CAT-3', name: 'Grillos', color: '#D97706' },
            { id: 'CAT-4', name: 'Cucarachas Dubia', color: '#6B7280' },
        ],

        products: [
            { id: 'PROD-001', name: 'Tenebrios 100 unidades', category: 'Tenebrios', price: 8000, stock: 120, status: 'Activo', img: IMG.tenebrios, desc: 'Larvas vivas de Tenebrio molitor frescas y nutritivas.' },
            { id: 'PROD-002', name: 'Tenebrios 250 unidades', category: 'Tenebrios', price: 16000, stock: 85, status: 'Activo', img: IMG.tenebrios, desc: 'Presentación mediana de Tenebrio molitor para alimentación frecuente.' },
            { id: 'PROD-003', name: 'Zophobas 100 unidades', category: 'Zophobas', price: 10000, stock: 60, status: 'Activo', img: IMG.zophobas, desc: 'Larvas gigantes Zophobas morio ricas en proteína.' },
            { id: 'PROD-004', name: 'Zophobas 250 unidades', category: 'Zophobas', price: 18000, stock: 45, status: 'Activo', img: IMG.zophobas, desc: 'Pack de 250 larvas Zophobas morio de alto valor nutricional.' },
            { id: 'PROD-005', name: 'Grillos 100 unidades', category: 'Grillos', price: 9000, stock: 70, status: 'Activo', img: IMG.combos, desc: 'Grillos vivos seleccionados para reptiles y aves insectívoras.' },
            { id: 'PROD-006', name: 'Grillos 250 unidades', category: 'Grillos', price: 18000, stock: 55, status: 'Activo', img: IMG.combos, desc: 'Presentación de 250 grillos activos y bien hidratados.' },
            { id: 'PROD-007', name: 'Cucarachas Dubia 100 un.', category: 'Cucarachas Dubia', price: 12000, stock: 40, status: 'Activo', img: IMG.dubias, desc: 'Blaptica dubia de fácil digestión y bajo olor.' },
            { id: 'PROD-008', name: 'Tenebrios 1000 unidades', category: 'Tenebrios', price: 48000, stock: 25, status: 'Activo', img: IMG.tenebrios, desc: 'Pack inicial ideal para criaderos y granjas.' },
            { id: 'PROD-009', name: 'Combo Pie de Cría Gold', category: 'Tenebrios', price: 65000, stock: 0, status: 'Inactivo', img: IMG.pro, desc: 'Kit completo de reproducción con sustrato incluido.' },
            { id: 'PROD-010', name: 'Cucarachas Dubia 250 un.', category: 'Cucarachas Dubia', price: 28000, stock: 0, status: 'Inactivo', img: IMG.dubias, desc: 'Presentación familiar de cucarachas Dubia.' },
            { id: 'PROD-011', name: 'Grillos 500 unidades', category: 'Grillos', price: 32000, stock: 1000, status: 'Activo', img: IMG.combos, desc: 'Presentación de 500 grillos vivos para criadores.' },
            { id: 'PROD-012', name: 'Cucarachas Dubia 500 un.', category: 'Cucarachas Dubia', price: 52000, stock: 950, status: 'Activo', img: IMG.dubias, desc: 'Presentación de 500 cucarachas Dubia para alimentación frecuente.' },
        ],

        promotions: [
            { id: 'PROM-001', name: 'Pack inicial Tenebrios', subtitle: '1000 unidades', product: 'Tenebrios 1000 unidades', type: 'Por producto', discount: '10%', start: '2025-08-25', end: '2025-08-31', status: 'Activa', img: IMG.tenebrios, desc: 'Llévate 1000 tenebrios con un 10% de descuento. Ideal para iniciar tu granja.' },
            { id: 'PROM-002', name: 'Envío gratis en compras +$100.000', subtitle: 'Envíos a todo el país', product: 'Todos los productos', type: 'Por envío', discount: 'Envío gratis', start: '2025-08-20', end: '2025-08-31', status: 'Activa', img: IMG.oferta1, desc: 'Aplica automáticamente en pedidos superiores a $100.000 COP.' },
            { id: 'PROM-003', name: 'Zophobas 250 un. - 15%', subtitle: 'Fuente de proteína', product: 'Zophobas 250 unidades', type: 'Por producto', discount: '15%', start: '2025-08-18', end: '2025-08-25', status: 'Activa', img: IMG.zophobas, desc: 'Descuento especial en Zophobas morio de 250 unidades.' },
            { id: 'PROM-004', name: 'Grillos 100 un. - 2x1', subtitle: 'Para aves y reptiles', product: 'Grillos 100 unidades', type: 'Por cantidad', discount: '2x1', start: '2025-08-15', end: '2025-08-30', status: 'Activa', img: IMG.combos, desc: 'Lleva el doble de grillos pagando una sola caja.' },
            { id: 'PROM-005', name: 'Cucarachas Dubia - 20%', subtitle: 'Alimento vivo de alta proteína', product: 'Cucarachas Dubia 100 un.', type: 'Por producto', discount: '20%', start: '2025-08-10', end: '2025-08-25', status: 'Activa', img: IMG.dubias, desc: '20% de descuento en cucarachas Dubia seleccionadas.' },
            { id: 'PROM-006', name: 'Black Friday', subtitle: 'Todos los productos', product: 'Todos los productos', type: 'General', discount: '15%', start: '2025-11-01', end: '2025-11-10', status: 'Programada', img: IMG.oferta2, desc: 'Campaña promocional de noviembre en toda la tienda.' },
            { id: 'PROM-007', name: 'Semana del Criador', subtitle: 'Combos y pie de cría', product: 'Combo Pie de Cría Gold', type: 'General', discount: '12%', start: '2025-09-10', end: '2025-09-20', status: 'Programada', img: IMG.oferta3, desc: 'Descuento especial para clientes mayoristas y criadores.' },
        ],

        orders: [
            {
                id: 'PED-0018', customer: 'Andrés Gómez', email: 'andres@bio-gest.com', phone: '+57 312 456 7890',
                date: '25/08/2025', time: '10:24 a. m.', status: 'Entregado', payment: 'Transferencia bancaria', shipping: 0,
                items: [
                    { name: 'Tenebrios 250 unidades', category: 'Tenebrios', qty: 2, price: 16000, img: IMG.tenebrios },
                    { name: 'Cucarachas Dubia 100 un.', category: 'Cucarachas Dubia', qty: 1, price: 12000, img: IMG.dubias },
                    { name: 'Zophobas 100 unidades', category: 'Zophobas', qty: 1, price: 10600, img: IMG.zophobas },
                ],
                extraCount: 2,
            },
            {
                id: 'PED-0017', customer: 'Laura Torres', email: 'laura@bio-gest.com', phone: '+57 301 987 6543',
                date: '24/08/2025', time: '06:42 p. m.', status: 'Confirmado', payment: 'Nequi', shipping: 0,
                items: [
                    { name: 'Tenebrios 100 unidades', category: 'Tenebrios', qty: 2, price: 8000, img: IMG.tenebrios },
                    { name: 'Tenebrios 250 unidades', category: 'Tenebrios', qty: 1, price: 16000, img: IMG.tenebrios },
                ],
                extraCount: 1,
            },
            {
                id: 'PED-0016', customer: 'Carlos Ramírez', email: 'carlos@bio-gest.com', phone: '+57 300 123 4567',
                date: '24/08/2025', time: '04:37 p. m.', status: 'Pendiente', payment: 'Contraentrega', shipping: 0,
                items: [
                    { name: 'Zophobas 250 unidades', category: 'Zophobas', qty: 1, price: 18000, img: IMG.zophobas },
                ],
                extraCount: 1,
            },
            {
                id: 'PED-0015', customer: 'Carlos Ramírez', email: 'carlos@bio-gest.com', phone: '+57 300 123 4567',
                date: '25/08/2025', time: '10:24 a. m.', status: 'Pendiente', payment: 'Transferencia bancaria', shipping: 0,
                items: [
                    { name: 'Tenebrios 100 unidades', category: 'Tenebrios', qty: 2, price: 8000, img: IMG.tenebrios },
                    { name: 'Grillos 100 unidades', category: 'Grillos', qty: 2, price: 9000, img: IMG.combos },
                ],
                extraCount: 3,
            },
            {
                id: 'PED-0014', customer: 'Daniel Peña', email: 'daniel@bio-gest.com', phone: '+57 315 222 3344',
                date: '22/08/2025', time: '11:03 a. m.', status: 'Confirmado', payment: 'Tarjeta de crédito', shipping: 0,
                items: [
                    { name: 'Tenebrios 100 unidades', category: 'Tenebrios', qty: 1, price: 8000, img: IMG.tenebrios },
                    { name: 'Zophobas 250 unidades', category: 'Zophobas', qty: 1, price: 18000, img: IMG.zophobas },
                ],
                extraCount: 1,
            },
            {
                id: 'PED-0013', customer: 'Valentina Cruz', email: 'valentina@bio-gest.com', phone: '+57 318 777 8899',
                date: '21/08/2025', time: '05:20 p. m.', status: 'Pendiente', payment: 'PSE', shipping: 0,
                items: [
                    { name: 'Tenebrios 1000 unidades', category: 'Tenebrios', qty: 1, price: 48000, img: IMG.tenebrios },
                ],
                extraCount: 2,
            },
            {
                id: 'PED-0012', customer: 'Jorge Castillo', email: 'jorge@bio-gest.com', phone: '+57 311 555 6677',
                date: '20/08/2025', time: '09:15 a. m.', status: 'Entregado', payment: 'Efectivo', shipping: 0,
                items: [
                    { name: 'Grillos 100 unidades', category: 'Grillos', qty: 1, price: 9000, img: IMG.combos },
                    { name: 'Tenebrios 100 unidades', category: 'Tenebrios', qty: 1, price: 5000, img: IMG.tenebrios },
                ],
                extraCount: 1,
            },
        ],

        inventory: [
            { id: 'INV-001', species: 'Tenebrio molitor', scientific: 'Tenebrio molitor', stock: 48500, minStock: 10000, maxCapacity: 59000, change: 12, updated: '25/08/2025 10:24 a. m.', img: IMG.tenebrios },
            { id: 'INV-002', species: 'Zophobas morio', scientific: 'Zophobas morio', stock: 8750, minStock: 3000, maxCapacity: 18600, change: 8, updated: '25/08/2025 09:18 a. m.', img: IMG.zophobas },
            { id: 'INV-003', species: 'Gryllus assimilis (Dubia)', scientific: 'Gryllus assimilis (Dubia)', stock: 6320, minStock: 2000, maxCapacity: 19750, change: -5, updated: '24/08/2025 04:32 p. m.', img: IMG.dubias },
        ],

        inventoryMovements: [
            { date: '25/08/2025 10:24 a. m.', species: 'Tenebrio molitor', type: 'Entrada', qty: '+2.500', reason: 'Cosecha bandeja #14' },
            { date: '25/08/2025 09:18 a. m.', species: 'Zophobas morio', type: 'Entrada', qty: '+650', reason: 'Reproducción semanal' },
            { date: '24/08/2025 04:32 p. m.', species: 'Gryllus assimilis (Dubia)', type: 'Salida', qty: '-320', reason: 'Despacho de pedido' },
        ],

        chats: [
            {
                id: 'CHT-001', user: 'Carlos Ramírez', role: 'Vendedor', unread: 2,
                messages: [
                    { me: false, text: 'Hola admin, el cliente pregunta por el envío.', time: '10:15 a. m.' },
                    { me: true, text: 'Hola Carlos, ya lo estamos preparando para despacho hoy.', time: '10:18 a. m.' },
                    { me: false, text: 'Perfecto, ¿le confirmo el número de guía por la tarde?', time: '10:20 a. m.' },
                ],
            },
            {
                id: 'CHT-002', user: 'Laura Torres', role: 'Cliente', unread: 1,
                messages: [
                    { me: false, text: 'Buenos días, ¿tienen disponibilidad de Tenebrios 1000 unidades?', time: '09:40 a. m.' },
                ],
            },
            {
                id: 'CHT-003', user: 'Andrés Gómez', role: 'Inventario', unread: 0,
                messages: [
                    { me: false, text: 'Actualicé el conteo de las bandejas de Zophobas.', time: 'Ayer' },
                    { me: true, text: 'Excelente Andrés, gracias por el reporte.', time: 'Ayer' },
                ],
            },
        ],

        settings: {
            company: 'Tenebrios Gold',
            platform: 'BIO-GEST',
            email: 'admin@tenebriosgold.com',
            phone: '+57 300 123 4567',
            currency: 'COP ($)',
            lowStockAlerts: true,
            orderEmailAlerts: true,
            autoConfirmPaid: false,
            maintenanceMode: false,
        },
    };

    const NAV_ITEMS = [
        { id: 'inicio', label: 'Inicio', icon: 'fa-house' },
        { id: 'usuarios', label: 'Usuarios', icon: 'fa-user-group' },
        { id: 'roles', label: 'Roles y permisos', icon: 'fa-shield-halved' },
        { id: 'productos', label: 'Productos', icon: 'fa-cubes' },
        { id: 'promociones', label: 'Promociones', icon: 'fa-bullhorn' },
        { id: 'pedidos', label: 'Pedidos', icon: 'fa-receipt' },
        { id: 'inventario', label: 'Inventario', icon: 'fa-clipboard-list' },
        { id: 'chats', label: 'Chats', icon: 'fa-comments' },
        { id: 'estadisticas', label: 'Estadísticas', icon: 'fa-chart-line' },
        { id: 'reportes', label: 'Reportes', icon: 'fa-file-lines' },
        { id: 'ajustes', label: 'Ajustes', icon: 'fa-gear' },
    ];

    // -------------------- Helpers de Dominio --------------------
    const orderSubtotal = (o) => o.subtotal != null
        ? Number(o.subtotal)
        : o.items.reduce((acc, it) => acc + it.qty * it.price, 0);
    const orderTotal = (o) => o.total != null
        ? Number(o.total)
        : orderSubtotal(o) + (o.shipping || 0);
    const reportOrderDate = (date) => {
        const [day, month, year] = date.split('/');
        return `${year}-${month}-${day}`;
    };

    function getReportData(filters) {
        const orders = state.orders.filter(order => {
            const date = reportOrderDate(order.date);
            return order.status !== 'Cancelado'
                && (!filters.start || date >= filters.start)
                && (!filters.end || date <= filters.end)
                && (filters.status === 'Todos' || order.status === filters.status);
        });
        const productsByName = new Map();

        orders.forEach(order => {
            order.items.forEach(item => {
                const product = productsByName.get(item.name) || {
                    name: item.name,
                    units: 0,
                    revenue: 0,
                };
                product.units += item.qty;
                product.revenue += item.qty * item.price;
                productsByName.set(item.name, product);
            });
        });

        const products = [...productsByName.values()].sort((a, b) => b.revenue - a.revenue);
        const revenue = orders.reduce((total, order) => total + orderTotal(order), 0);
        const units = products.reduce((total, product) => total + product.units, 0);

        return {
            orders,
            products,
            revenue,
            units,
            topProduct: products[0]?.name || '—',
        };
    }

    function reportCsvCell(value) {
        const text = String(value ?? '');
        const safeText = /^[\s]*[=+\-@]/.test(text) ? `'${text}` : text;
        return `"${safeText.replace(/"/g, '""')}"`;
    }

    function buildReportCsv(filters) {
        const report = getReportData(filters);
        const rows = [
            ['Reporte de ventas', 'Tenebrios Gold'],
            ['Fecha inicial', filters.start || 'Sin límite'],
            ['Fecha final', filters.end || 'Sin límite'],
            ['Estado', filters.status],
            [],
            ['Ventas registradas'],
            ['N.° Pedido', 'Fecha', 'Cliente', 'Productos y cantidades', 'Estado', 'Total ($ COP)'],
            ...report.orders.map(order => [
                order.number || order.id,
                order.date,
                order.customer,
                order.items.map(item => `${item.name} x ${item.qty}`).join(', '),
                order.status,
                fmtCOP(orderTotal(order)),
            ]),
            [],
            ['Totales por producto'],
            ['Producto', 'Unidades vendidas', 'Participación (%)', 'Ingresos ($ COP)'],
            ...report.products.map(product => [
                product.name,
                product.units,
                report.revenue ? (product.revenue / report.revenue * 100).toFixed(1) : '0.0',
                fmtCOP(product.revenue),
            ]),
        ];

        return rows.map(row => row.map(reportCsvCell).join(';')).join('\r\n');
    }

    const statusBadge = (status) => {
        const map = {
            'Activo': 'green',
            'Activa': 'green',
            'Inactivo': 'gray',
            'Programada': 'yellow',
            'Entregado': 'green',
            'Confirmado': 'blue',
            'En proceso': 'blue',
            'Pendiente': 'yellow',
            'Cancelado': 'red',
            'En stock': 'green',
            'Alto': 'green',
            'Medio': 'yellow',
            'Bajo': 'red',
            'Stock bajo': 'red',
        };
        return `<span class="badge ${map[status] || 'gray'}">${esc(status)}</span>`;
    };

    const promoTypeBadge = (type) => {
        const map = {
            'Por producto': 'blue',
            'Por envío': 'purple',
            'Por cantidad': 'orange',
            'General': 'red',
        };
        return `<span class="badge ${map[type] || 'gray'}">${esc(type)}</span>`;
    };

    const formatDisplayDate = (iso) => {
        if (!iso || !iso.includes('-')) return iso || '';
        const [y, m, d] = iso.split('-');
        return `${d}/${m}/${y}`;
    };

    const formatLongDate = (iso) => {
        const dt = new Date((iso || '2025-08-25') + 'T12:00:00');
        const txt = dt.toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        return txt.charAt(0).toUpperCase() + txt.slice(1);
    };

    const inventoryPercent = (item) => Math.min(100, Math.max(5, Math.round((item.stock / item.maxCapacity) * 100)));
    const inventoryBarColor = (pct) => (pct >= 65 ? 'green' : pct >= 40 ? 'yellow' : 'red');
    const inventoryStatus = (item) => (item.stock <= item.minStock ? 'Stock bajo' : 'En stock');
    const inventoryChange = (item) => item.change == null
        ? ''
        : `<span class="trend ${item.change >= 0 ? 'up' : 'down'}"><i class="fa-solid fa-arrow-${item.change >= 0 ? 'up' : 'down'}"></i> ${item.change >= 0 ? '+' : ''}${item.change}%</span> vs. mes anterior`;
    const displayOrderStatus = (status) => ({
        Procesando: 'Confirmado',
        Despachado: 'En proceso',
        'Listo para retiro': 'En proceso',
        Completado: 'Entregado',
    }[status] || status);

    // -------------------- UI Feedback (Toasts, Modals, Menús) --------------------
    function toast(msg, type = 'success') {
        const icon = type === 'error' ? 'fa-circle-xmark' : type === 'info' ? 'fa-circle-info' : 'fa-circle-check';
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.innerHTML = `<i class="fa-solid ${icon}"></i><span>${esc(msg)}</span>`;
        $('#toasts').appendChild(el);
        setTimeout(() => {
            el.classList.add('hide');
            setTimeout(() => el.remove(), 300);
        }, 3000);
    }

    function closeMenus() {
        $$('.float-menu').forEach(m => m.remove());
    }

    function openModal({ title, body, foot = '', wide = false, onMount }) {
        closeMenus();
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal ${wide ? 'wide' : ''}" role="dialog" aria-modal="true">
                <div class="modal-head">
                    <h3>${esc(title)}</h3>
                    <button class="icon-btn" data-close-modal aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">${body}</div>
                ${foot ? `<div class="modal-foot">${foot}</div>` : ''}
            </div>
        `;
        document.body.appendChild(backdrop);
        requestAnimationFrame(() => backdrop.classList.add('show'));

        const close = () => {
            backdrop.classList.remove('show');
            setTimeout(() => backdrop.remove(), 180);
        };
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop || e.target.closest('[data-close-modal]')) close();
        });
        if (onMount) onMount(backdrop, close);
        return { backdrop, close };
    }

    function confirmModal(title, message, onConfirm, danger = true) {
        openModal({
            title,
            body: `<p class="confirm-text">${esc(message)}</p>`,
            foot: `
                <button class="btn" data-close-modal>Cancelar</button>
                <button class="btn ${danger ? 'btn-danger' : 'btn-primary'}" id="confirmActionBtn">Confirmar</button>
            `,
            onMount: (m, close) => {
                $('#confirmActionBtn', m).addEventListener('click', () => {
                    onConfirm();
                    close();
                });
            },
        });
    }

    // -------------------- Navegación y Sidebar --------------------
    function renderNav() {
        const unreadChats = state.chats.reduce((a, c) => a + c.unread, 0);
        const pendingOrders = state.orders.filter(o => o.status === 'Pendiente').length;
        $('#sideNav').innerHTML = NAV_ITEMS.map(item => {
            let badge = '';
            if (item.id === 'pedidos' && pendingOrders) badge = `<span class="nav-badge">${pendingOrders}</span>`;
            if (item.id === 'chats' && unreadChats) badge = `<span class="nav-badge">${unreadChats}</span>`;
            return `
                <button class="nav-item ${state.view === item.id ? 'active' : ''}" data-nav="${item.id}">
                    <i class="fa-solid ${item.icon}"></i>
                    <span>${item.label}</span>
                    ${badge}
                </button>
            `;
        }).join('');

        const unreadNotifs = state.notifications.filter(n => n.unread).length;
        const nEl = $('#notifCount');
        nEl.textContent = unreadNotifs;
        nEl.hidden = unreadNotifs === 0;
    }

    function navigate(viewId) {
        if (!NAV_ITEMS.some(n => n.id === viewId)) return;
        state.view = viewId;
        document.body.classList.remove('sidebar-open');
        closeMenus();
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // -------------------- Vistas --------------------

    // A. INICIO
    function renderInicio() {
        const pageSize = 5;
        const totalPages = Math.max(1, Math.ceil(state.users.length / pageSize));
        state.homeUserPage = Math.min(state.homeUserPage, totalPages);
        const userSlice = state.users.slice((state.homeUserPage - 1) * pageSize, state.homeUserPage * pageSize);

        const invSummary = state.inventory.slice(0, 4);
        const pendingOrders = state.liveDataLoaded
            ? state.summary.pendingOrders
            : state.orders.filter(order => order.status === 'Pendiente').length;

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Bienvenido, administrador</h2>
                    <p>Gestiona todo el sistema de TENEBRIOS GOLD desde aquí</p>
                </div>
            </div>

            <section class="kpi-grid">
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-regular fa-user"></i></div>
                    <div>
                        <p class="kpi-label">Total de usuarios</p>
                        <p class="kpi-value">${fmtNum(state.liveDataLoaded ? state.summary.totalUsers : 18)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 12%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div>
                        <p class="kpi-label">Pedidos pendientes</p>
                        <p class="kpi-value">${fmtNum(pendingOrders)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 5%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-box-open"></i></div>
                    <div>
                        <p class="kpi-label">Pedidos realizados</p>
                        <p class="kpi-value">${fmtNum(state.liveDataLoaded ? state.summary.monthlyOrders : 18)}</p>
                        <p class="kpi-foot">este mes</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div>
                        <p class="kpi-label">Ingresos del mes</p>
                        <p class="kpi-value">${fmtCOP(state.liveDataLoaded ? state.summary.monthlyRevenue : 120000000)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 12%</span> vs. mes anterior</p>
                    </div>
                </div>
            </section>

            <section class="grid-3 home">
                <!-- 1. Gestión de usuarios -->
                <div class="card">
                    <div class="card-header">
                        <h3>Gestión de usuarios</h3>
                        <button class="link" data-nav="usuarios">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                ${userSlice.map((u, idx) => {
                                    const [bg, fg] = AVATAR_COLORS[idx % AVATAR_COLORS.length];
                                    return `<tr>
                                        <td><div class="cell-user"><span class="avatar" style="background:${bg};color:${fg}">${initials(u.name)}</span><span>${esc(u.name)}</span></div></td>
                                        <td class="muted">${esc(u.email)}</td>
                                        <td>${esc(u.role)}</td>
                                        <td>${statusBadge(u.status)}</td>
                                        <td><div class="actions">
                                            <button class="icon-action" title="Editar" data-action="editUser" data-id="${u.id}"><i class="fa-solid fa-pen"></i></button>
                                            <button class="icon-action danger" title="Eliminar" data-action="deleteUser" data-id="${u.id}"><i class="fa-solid fa-trash"></i></button>
                                        </div></td>
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <button data-action="homeUserPage" data-page="${state.homeUserPage - 1}" ${state.homeUserPage <= 1 ? 'disabled' : ''}>&lt;</button>
                        ${Array.from({ length: totalPages }, (_, i) => i + 1).map(p => `<button class="${p === state.homeUserPage ? 'active' : ''}" data-action="homeUserPage" data-page="${p}">${p}</button>`).join('')}
                        <button data-action="homeUserPage" data-page="${state.homeUserPage + 1}" ${state.homeUserPage >= totalPages ? 'disabled' : ''}>&gt;</button>
                    </div>
                </div>

                <!-- 2. Roles y permisos -->
                <div class="card">
                    <div class="card-header">
                        <h3>Roles y permisos</h3>
                        <button class="link" data-nav="roles">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Rol</th><th>Usuarios</th><th>Acciones</th></tr></thead>
                            <tbody>
                                ${state.roles.map(r => `<tr>
                                    <td class="strong">${esc(r.name)}</td>
                                    <td>${r.usersCount}</td>
                                    <td><div class="actions">
                                        <button class="icon-action" title="Editar" data-action="editRole" data-id="${r.id}"><i class="fa-solid fa-pen"></i></button>
                                        <button class="icon-action danger" title="Eliminar" data-action="deleteRole" data-id="${r.id}"><i class="fa-solid fa-trash"></i></button>
                                    </div></td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm" data-action="newRole"><i class="fa-solid fa-plus"></i> Crear rol</button>
                    </div>
                </div>

                <!-- 3. Productos -->
                <div class="card">
                    <div class="card-header">
                        <h3>Productos</h3>
                        <button class="link" data-nav="productos">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Producto</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                ${state.products.slice(0, 4).map(p => `<tr>
                                    <td>${esc(p.name)}</td>
                                    <td class="mono">${fmtCOP(p.price)}</td>
                                    <td>${statusBadge(p.status)}</td>
                                    <td><div class="actions">
                                        <button class="icon-action" title="Editar" data-action="goProductEdit" data-id="${p.id}"><i class="fa-solid fa-pen"></i></button>
                                        <button class="icon-action danger" title="Eliminar" data-action="deleteProduct" data-id="${p.id}"><i class="fa-solid fa-trash"></i></button>
                                    </div></td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm" data-nav="productos"><i class="fa-solid fa-plus"></i> Agregar producto</button>
                    </div>
                </div>

                <!-- 4. Promociones activas -->
                <div class="card">
                    <div class="card-header">
                        <h3>Promociones activas</h3>
                        <button class="link" data-nav="promociones">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Producto</th><th>Descuento</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                ${state.promotions.slice(0, 3).map(pr => `<tr>
                                    <td>${esc(pr.product)}</td>
                                    <td class="strong">${esc(pr.discount)}</td>
                                    <td class="nowrap">${formatDisplayDate(pr.start)}</td>
                                    <td class="nowrap">${formatDisplayDate(pr.end)}</td>
                                    <td>${statusBadge(pr.status)}</td>
                                    <td><div class="actions">
                                        <button class="icon-action" title="Editar" data-action="goPromoEdit" data-id="${pr.id}"><i class="fa-solid fa-pen"></i></button>
                                        <button class="icon-action danger" title="Eliminar" data-action="deletePromo" data-id="${pr.id}"><i class="fa-solid fa-trash"></i></button>
                                    </div></td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm" data-action="newPromoModal"><i class="fa-solid fa-plus"></i> Crear promoción</button>
                    </div>
                </div>

                <!-- 5. Pedidos recientes -->
                <div class="card">
                    <div class="card-header">
                        <h3>Pedidos recientes</h3>
                        <button class="link" data-nav="pedidos">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Cliente</th><th>Fecha</th><th>Estado</th><th>Total</th><th>Acción</th></tr></thead>
                            <tbody>
                                ${state.orders.slice(0, 4).map(o => `<tr>
                                    <td>${esc(o.customer)}</td>
                                    <td class="nowrap">${esc(o.date)}</td>
                                    <td>${statusBadge(o.status)}</td>
                                    <td class="mono">${fmtCOP(orderTotal(o))}</td>
                                    <td><button class="icon-action" title="Ver pedido" data-action="goOrderDetail" data-id="${o.id}"><i class="fa-regular fa-eye"></i></button></td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. Inventario -->
                <div class="card">
                    <div class="card-header">
                        <h3>Inventario</h3>
                        <button class="link" data-nav="inventario">Ver todos</button>
                    </div>
                    <div class="table-wrap">
                        <table class="table compact">
                            <thead><tr><th>Producto</th><th>Stock actual</th><th>Stock mínimo</th><th>Estado</th></tr></thead>
                            <tbody>
                                ${invSummary.map(inv => `<tr>
                                    <td>${esc(inv.species)}</td>
                                    <td class="mono">${inv.stock}</td>
                                    <td class="mono">${inv.minStock}</td>
                                    <td>${statusBadge(inventoryStatus(inv))}</td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>`;
    }

    // B. USUARIOS
    function renderUsuarios() {
        const f = state.filters.users;
        const q = (state.globalQuery || f.q).toLowerCase().trim();
        const rows = state.users.filter(u => {
            const matchQ = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.id.toLowerCase().includes(q);
            const matchR = f.role === 'Todos' || u.role === f.role;
            const matchS = f.status === 'Todos' || u.status === f.status;
            return matchQ && matchR && matchS;
        });

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Usuarios</h2>
                    <p>Gestionar los usuarios del sistema</p>
                </div>
                <div class="head-actions">
                    <button class="btn btn-primary" data-action="newUser"><i class="fa-solid fa-plus"></i> Crear usuario</button>
                </div>
            </div>

            <div class="card filters">
                <div class="search-input">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" class="input" data-filter="users.q" value="${esc(f.q)}" placeholder="Buscar por nombre o correo...">
                </div>
                <div class="field">
                    <label>Rol</label>
                    <select class="input" data-filter="users.role">
                        ${['Todos', 'Administrador', 'Vendedor', 'Cliente', 'Inventario'].map(r => `<option ${f.role === r ? 'selected' : ''}>${r}</option>`).join('')}
                    </select>
                </div>
                <div class="field">
                    <label>Estado</label>
                    <select class="input" data-filter="users.status">
                        ${['Todos', 'Activo', 'Inactivo'].map(s => `<option ${f.status === s ? 'selected' : ''}>${s}</option>`).join('')}
                    </select>
                </div>
                <button class="btn" data-action="resetFilters" data-scope="users"><i class="fa-solid fa-filter"></i> Filtros</button>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-check"><input type="checkbox" data-check-all></th>
                                <th>Nombre completo</th>
                                <th>Correo electrónico</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th class="t-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.length ? rows.map((u, idx) => {
                                const [bg, fg] = AVATAR_COLORS[idx % AVATAR_COLORS.length];
                                return `<tr>
                                    <td><input type="checkbox"></td>
                                    <td>
                                        <div class="cell-user">
                                            <span class="avatar" style="background:${bg};color:${fg}">${initials(u.name)}</span>
                                            <span class="strong">${esc(u.name)}</span>
                                        </div>
                                    </td>
                                    <td class="muted">${esc(u.email)}</td>
                                    <td><span class="badge gray">${esc(u.role)}</span></td>
                                    <td>${statusBadge(u.status)}</td>
                                    <td class="muted nowrap">${esc(u.lastAccess)}</td>
                                    <td>
                                        <div class="actions end">
                                            <button class="icon-action" title="Editar" data-action="editUser" data-id="${u.id}"><i class="fa-solid fa-pen"></i></button>
                                            <button class="icon-action" title="Ver detalle" data-action="viewUser" data-id="${u.id}"><i class="fa-regular fa-eye"></i></button>
                                            <button class="icon-action" title="Más opciones" data-action="userMenu" data-id="${u.id}"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>`;
                            }).join('') : `<tr class="empty-row"><td colspan="7">No se encontraron usuarios con los filtros aplicados.</td></tr>`}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <span>Mostrando ${rows.length} de ${state.users.length} usuarios</span>
                </div>
            </div>
        </div>`;
    }

    // C. ROLES Y PERMISOS
    function renderRoles() {
        const q = (state.globalQuery || state.filters.roles.q).toLowerCase().trim();
        const filteredRoles = state.roles.filter(r => !q || r.name.toLowerCase().includes(q) || r.id.toLowerCase().includes(q) || r.desc.toLowerCase().includes(q));
        const perms = state.rolePermissions[state.selectedRoleId] || {};
        const totalPerms = 18;
        const assignedUsers = state.roles.reduce((total, role) => total + role.usersCount, 0);

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Roles y permisos</h2>
                    <p>Administrar los roles de los usuarios y asigna los permisos correspondientes de cada uno</p>
                </div>
            </div>

            <section class="grid-3 mb">
                <div class="card stat-card">
                    <div class="kpi-icon"><i class="fa-regular fa-user"></i></div>
                    <div class="grow">
                        <p class="kpi-label">Total de roles</p>
                        <p class="kpi-value">${state.roles.length}</p>
                        <p class="small muted">Roles configurados en el sistema</p>
                    </div>
                    <button class="btn btn-sm" data-action="newRole"><i class="fa-solid fa-plus"></i> Nuevo rol</button>
                </div>
                <div class="card stat-card">
                    <div class="kpi-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="grow">
                        <p class="kpi-label">Permisos totales</p>
                        <p class="kpi-value">${totalPerms}</p>
                        <p class="small muted">Permisos disponibles</p>
                    </div>
                    <button class="btn btn-sm" data-action="focusPermissions"><i class="fa-solid fa-gear"></i> Gestionar permisos</button>
                </div>
                <div class="card stat-card">
                    <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="grow">
                        <p class="kpi-label">Usuarios asignados</p>
                        <p class="kpi-value">${assignedUsers}</p>
                        <p class="small muted">Usuarios con roles definidos</p>
                    </div>
                    <button class="btn btn-sm" data-nav="usuarios"><i class="fa-regular fa-eye"></i> Ver usuarios</button>
                </div>
            </section>

            <section class="split split-roles">
                <div class="card">
                    <div class="card-header">
                        <h3>Roles del sistema</h3>
                        <div class="header-tools">
                            <div class="search-input sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" class="input" data-filter="roles.q" value="${esc(state.filters.roles.q)}" placeholder="Buscar rol...">
                            </div>
                            <button class="btn btn-sm" data-action="newRole"><i class="fa-solid fa-plus"></i> Crear rol</button>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Nombre del rol</th><th>Descripción</th><th>Usuarios</th><th>Estado</th><th class="t-right">Acciones</th></tr>
                            </thead>
                            <tbody>
                                ${filteredRoles.length ? filteredRoles.map(r => `<tr class="clickable ${r.id === state.selectedRoleId ? 'selected' : ''}" data-action="selectRole" data-id="${r.id}">
                                    <td class="strong">${esc(r.name)}</td>
                                    <td class="muted small">${esc(r.desc)}</td>
                                    <td class="mono">${r.usersCount}</td>
                                    <td>${statusBadge(r.status)}</td>
                                    <td>
                                        <div class="actions end">
                                            <button class="icon-action" title="Editar" data-action="editRole" data-id="${r.id}"><i class="fa-solid fa-pen"></i></button>
                                            <button class="icon-action danger" title="Eliminar" data-action="deleteRole" data-id="${r.id}"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>`).join('') : `<tr class="empty-row"><td colspan="5">Sin resultados</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <span>Mostrando 1 - ${filteredRoles.length} de ${state.roles.length} roles</span>
                        <div class="pagination"><button class="active">1</button></div>
                    </div>
                </div>

                <div class="card" id="permissionsCard">
                    <div class="card-header">
                        <h3>Permisos por rol</h3>
                        <div class="field inline-field">
                            <label>Selecciona un rol</label>
                            <select class="input" id="rolePermSelect">
                                ${state.roles.map(r => `<option value="${r.id}" ${r.id === state.selectedRoleId ? 'selected' : ''}>${esc(r.name)}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="table perm-table">
                            <thead><tr><th>Módulo</th><th colspan="4">Permisos asignados</th></tr></thead>
                            <tbody>
                                ${state.permissionModules.map(mod => {
                                    const list = perms[mod.key] || [];
                                    return `<tr>
                                        <td>
                                            <div class="module-cell">
                                                <span class="module-icon"><i class="fa-solid ${mod.icon}"></i></span>
                                                <span>${esc(mod.label)}</span>
                                            </div>
                                        </td>
                                        ${['ver', 'crear', 'editar', 'eliminar'].map(act => `
                                            <td>
                                                <label class="check">
                                                    <input type="checkbox" data-perm-mod="${mod.key}" data-perm-act="${act}" ${list.includes(act) ? 'checked' : ''}>
                                                    <span>${act.charAt(0).toUpperCase() + act.slice(1)}</span>
                                                </label>
                                            </td>
                                        `).join('')}
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>`;
    }

    // D. PRODUCTOS
    function renderProductos() {
        const f = state.filters.products;
        const q = (state.globalQuery || f.q).toLowerCase().trim();
        const rows = state.products.filter(p => {
            const matchQ = !q || p.name.toLowerCase().includes(q) || p.id.toLowerCase().includes(q) || p.category.toLowerCase().includes(q);
            const matchC = f.category === 'Todas' || p.category === f.category;
            const matchS = f.status === 'Todos' || p.status === f.status;
            return matchQ && matchC && matchS;
        });

        const totalProd = state.products.length;
        const activeProd = state.products.filter(p => p.status === 'Activo').length;
        const inactiveProd = totalProd - activeProd;
        const totalStock = state.products.reduce((a, p) => a + Number(p.stock || 0), 0);

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Productos</h2>
                    <p>Gestiona el catálogo de productos, precios, stock y disponibilidad</p>
                </div>
                <div class="head-actions">
                    <label class="date-pill">
                        <i class="fa-regular fa-calendar"></i>
                        <span>${formatLongDate(state.selectedDate)}</span>
                        <input type="date" id="datePickerInput" value="${state.selectedDate}">
                    </label>
                    <button class="btn btn-primary" data-action="resetProductForm"><i class="fa-solid fa-plus"></i> Agregar producto</button>
                </div>
            </div>

            <section class="kpi-grid">
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-cubes"></i></div>
                    <div>
                        <p class="kpi-label">Total de productos</p>
                        <p class="kpi-value">${totalProd}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 20%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-regular fa-circle-check"></i></div>
                    <div>
                        <p class="kpi-label">Productos activos</p>
                        <p class="kpi-value">${activeProd}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 11%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-ban"></i></div>
                    <div>
                        <p class="kpi-label">Productos inactivos</p>
                        <p class="kpi-value">${inactiveProd}</p>
                        <p class="kpi-foot"><span class="trend down"><i class="fa-solid fa-arrow-down"></i> 33%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div>
                        <p class="kpi-label">Stock total en inventario</p>
                        <p class="kpi-value">${fmtNum(totalStock)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 18%</span> vs. mes anterior</p>
                    </div>
                </div>
            </section>

            <section class="stack">
                <div class="card filters" style="margin-bottom:0">
                    <div class="search-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" class="input" data-filter="products.q" value="${esc(f.q)}" placeholder="Buscar producto por nombre, categoría o código...">
                    </div>
                    <div class="field">
                        <label>Categoría</label>
                        <select class="input" data-filter="products.category">
                            <option>Todas</option>
                            ${state.categories.map(c => `<option ${f.category === c.name ? 'selected' : ''}>${esc(c.name)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="field">
                        <label>Estado</label>
                        <select class="input" data-filter="products.status">
                            ${['Todos', 'Activo', 'Inactivo'].map(s => `<option ${f.status === s ? 'selected' : ''}>${s}</option>`).join('')}
                        </select>
                    </div>
                    <button class="btn" data-action="resetFilters" data-scope="products"><i class="fa-solid fa-filter"></i> Filtros</button>
                </div>

                <div class="card">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-check"><input type="checkbox" data-check-all></th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th class="t-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length ? rows.map(p => `<tr class="clickable" data-action="viewProduct" data-id="${p.id}">
                                    <td><input type="checkbox"></td>
                                    <td>
                                        <div class="cell-product">
                                            <img class="thumb" src="${esc(p.img)}" alt="${esc(p.name)}">
                                            <div><span class="strong">${esc(p.name)}</span></div>
                                        </div>
                                    </td>
                                    <td><span class="badge gray">${esc(p.category)}</span></td>
                                    <td class="mono">${fmtCOP(p.price)}</td>
                                    <td class="mono">${p.stock}</td>
                                    <td>${statusBadge(p.status)}</td>
                                    <td>
                                        <div class="actions end">
                                            <button class="icon-action" title="Editar" data-action="editProductModal" data-id="${p.id}"><i class="fa-solid fa-pen"></i></button>
                                            <button class="icon-action" title="Ver" data-action="viewProduct" data-id="${p.id}"><i class="fa-regular fa-eye"></i></button>
                                            <button class="icon-action danger" title="Eliminar" data-action="deleteProduct" data-id="${p.id}"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>`).join('') : `<tr class="empty-row"><td colspan="7">No se encontraron productos.</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Categorías</h3>
                        <button class="btn btn-sm" data-action="newCategory"><i class="fa-solid fa-plus"></i> Nueva categoría</button>
                    </div>
                    <ul class="cat-list">
                        ${state.categories.map(c => {
                            const count = state.products.filter(p => p.category === c.name).length;
                            return `<li>
                                <span class="dot" style="background:${c.color}"></span>
                                <span class="grow strong">${esc(c.name)}</span>
                                <span class="small muted">${count} productos</span>
                                <button class="icon-action" title="Filtrar por ${esc(c.name)}" data-action="filterByCat" data-cat="${esc(c.name)}"><i class="fa-solid fa-filter"></i></button>
                            </li>`;
                        }).join('')}
                    </ul>
                </div>
            </section>
        </div>`;
    }

    // E. PROMOCIONES
    function renderPromociones() {
        const f = state.filters.promos;
        const q = (state.globalQuery || f.q).toLowerCase().trim();
        const rows = state.promotions.filter(pr => {
            const matchQ = !q || pr.name.toLowerCase().includes(q) || pr.product.toLowerCase().includes(q) || pr.type.toLowerCase().includes(q);
            const matchS = f.status === 'Todos' || pr.status === f.status;
            const matchT = f.type === 'Todos' || pr.type === f.type;
            return matchQ && matchS && matchT;
        });

        const total = state.promotions.length;
        const active = state.promotions.filter(p => p.status === 'Activa').length;
        const scheduled = state.promotions.filter(p => p.status === 'Programada').length;
        const selected = state.promotions.find(p => p.id === state.selectedPromoId) || state.promotions[0];

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Promociones</h2>
                    <p>Gestiona las ofertas y promociones de los productos</p>
                </div>
                <div class="head-actions">
                    <label class="date-pill">
                        <i class="fa-regular fa-calendar"></i>
                        <span>${formatLongDate(state.selectedDate)}</span>
                        <input type="date" id="datePickerInput" value="${state.selectedDate}">
                    </label>
                    <button class="btn btn-primary" data-action="newPromoModal"><i class="fa-solid fa-plus"></i> Crear promoción</button>
                </div>
            </div>

            <section class="kpi-grid">
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-tag"></i></div>
                    <div>
                        <p class="kpi-label">Total de promociones</p>
                        <p class="kpi-value">${total}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 17%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-percent"></i></div>
                    <div>
                        <p class="kpi-label">Promociones activas</p>
                        <p class="kpi-value">${active}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 25%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <p class="kpi-label">Promociones programadas</p>
                        <p class="kpi-value">${scheduled}</p>
                        <p class="kpi-foot"><span class="trend flat"><i class="fa-solid fa-arrow-right"></i> 0%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-box"></i></div>
                    <div>
                        <p class="kpi-label">Descuentos aplicados (mes)</p>
                        <p class="kpi-value">$ 1.248.000</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 32%</span> vs. mes anterior</p>
                    </div>
                </div>
            </section>

            <section class="split">
                <div class="stack">
                    <div class="card filters" style="margin-bottom:0">
                        <div class="search-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="search" class="input" data-filter="promos.q" value="${esc(f.q)}" placeholder="Buscar promoción por nombre, producto o tipo...">
                        </div>
                        <div class="field">
                            <label>Estado</label>
                            <select class="input" data-filter="promos.status">
                                ${['Todos', 'Activa', 'Programada'].map(s => `<option ${f.status === s ? 'selected' : ''}>${s}</option>`).join('')}
                            </select>
                        </div>
                        <div class="field">
                            <label>Tipo</label>
                            <select class="input" data-filter="promos.type">
                                ${['Todos', 'Por producto', 'Por envío', 'Por cantidad', 'General'].map(t => `<option ${f.type === t ? 'selected' : ''}>${t}</option>`).join('')}
                            </select>
                        </div>
                        <button class="btn" data-action="resetFilters" data-scope="promos"><i class="fa-solid fa-filter"></i> Filtros</button>
                    </div>

                    <div class="card">
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="w-check"><input type="checkbox" data-check-all></th>
                                        <th>Imagen</th>
                                        <th>Nombre de la promoción</th>
                                        <th>Tipo</th>
                                        <th>Descuento</th>
                                        <th>Fecha inicio</th>
                                        <th>Fecha fin</th>
                                        <th>Estado</th>
                                        <th class="t-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows.length ? rows.map(pr => `<tr class="clickable ${selected && pr.id === selected.id ? 'selected' : ''}" data-action="selectPromo" data-id="${pr.id}">
                                        <td><input type="checkbox"></td>
                                        <td><img class="thumb" src="${esc(pr.img)}" alt="${esc(pr.name)}"></td>
                                        <td>
                                            <div class="two-line">
                                                <span class="strong">${esc(pr.name)}</span>
                                                <small class="muted">${esc(pr.subtitle)}</small>
                                            </div>
                                        </td>
                                        <td>${promoTypeBadge(pr.type)}</td>
                                        <td class="strong">${esc(pr.discount)}</td>
                                        <td class="nowrap">${formatDisplayDate(pr.start)}</td>
                                        <td class="nowrap">${formatDisplayDate(pr.end)}</td>
                                        <td>${statusBadge(pr.status)}</td>
                                        <td>
                                            <div class="actions end">
                                                <button class="icon-action" title="Editar" data-action="selectPromo" data-id="${pr.id}"><i class="fa-solid fa-pen"></i></button>
                                                <button class="icon-action" title="Ver" data-action="selectPromo" data-id="${pr.id}"><i class="fa-regular fa-eye"></i></button>
                                                <button class="icon-action danger" title="Eliminar" data-action="deletePromo" data-id="${pr.id}"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>`).join('') : `<tr class="empty-row"><td colspan="9">No hay promociones que coincidan.</td></tr>`}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Detalles de la promoción</h3></div>
                    ${selected ? `
                    <form class="panel-form" id="promoDetailForm">
                        <div class="panel-preview">
                            <img class="thumb lg" src="${esc(selected.img)}" alt="${esc(selected.name)}">
                            <div>
                                <span class="strong">${esc(selected.name)}</span>
                                <small class="muted">${esc(selected.subtitle)}</small>
                                ${statusBadge(selected.status)}
                            </div>
                        </div>
                        <div class="field">
                            <label>Tipo de promoción</label>
                            <select class="input" name="type">
                                ${['Por producto', 'Por envío', 'Por cantidad', 'General'].map(t => `<option ${selected.type === t ? 'selected' : ''}>${t}</option>`).join('')}
                            </select>
                        </div>
                        <div class="field">
                            <label>Producto</label>
                            <select class="input" name="product">
                                <option ${selected.product === 'Todos los productos' ? 'selected' : ''}>Todos los productos</option>
                                ${state.products.map(p => `<option ${selected.product === p.name ? 'selected' : ''}>${esc(p.name)}</option>`).join('')}
                            </select>
                        </div>
                        <div class="field">
                            <label>Descuento</label>
                            <input type="text" class="input" name="discount" value="${esc(selected.discount)}">
                        </div>
                        <div class="row-2">
                            <div class="field">
                                <label>Fecha de inicio</label>
                                <input type="date" class="input" name="start" value="${esc(selected.start)}">
                            </div>
                            <div class="field">
                                <label>Fecha de fin</label>
                                <input type="date" class="input" name="end" value="${esc(selected.end)}">
                            </div>
                        </div>
                        <div class="field">
                            <label>Descripción</label>
                            <textarea class="input" name="desc" maxlength="500">${esc(selected.desc)}</textarea>
                            <span class="counter" id="promoDescCount">${selected.desc.length}/500</span>
                        </div>
                        <div class="btn-row">
                            <button type="button" class="btn" data-action="resetPromoPanel">Cancelar</button>
                            <button type="submit" class="btn btn-dark">Guardar cambios</button>
                        </div>
                    </form>` : `<div class="empty-panel"><i class="fa-solid fa-tag"></i>Selecciona una promoción de la tabla.</div>`}
                </div>
            </section>
        </div>`;
    }

    // F. PEDIDOS
    function renderPedidos() {
        const f = state.filters.orders;
        const q = (state.globalQuery || f.q).toLowerCase().trim();
        const rows = state.orders.filter(o => {
            const matchQ = !q || `${o.number || ''} ${o.id}`.toLowerCase().includes(q) || o.customer.toLowerCase().includes(q) || o.email.toLowerCase().includes(q) || o.items.some(i => i.name.toLowerCase().includes(q));
            const matchS = f.status === 'Todos' || o.status === f.status;
            const matchD = f.date === 'Todos' || o.date === f.date;
            return matchQ && matchS && matchD;
        });

        const selected = state.orders.find(o => o.id === state.selectedOrderId) || state.orders[0];
        const steps = ['Pendiente', 'Confirmado', 'En proceso', 'Entregado'];
        const stepIdx = selected ? steps.indexOf(selected.status) : -1;
        const orderStats = state.liveDataLoaded
            ? state.summary
            : {
                totalOrders: state.orders.length,
                pendingOrders: state.orders.filter(order => order.status === 'Pendiente').length,
                confirmedOrders: state.orders.filter(order => order.status === 'Confirmado').length,
                deliveredOrders: state.orders.filter(order => order.status === 'Entregado').length,
            };

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Pedidos</h2>
                    <p>Gestiona y da seguimiento a los pedidos realizados por los clientes</p>
                </div>
                <div class="head-actions">
                    <label class="date-pill">
                        <i class="fa-regular fa-calendar"></i>
                        <span>${formatLongDate(state.selectedDate)}</span>
                        <input type="date" id="datePickerInput" value="${state.selectedDate}">
                    </label>
                    <button class="btn btn-primary" data-action="newOrderModal"><i class="fa-solid fa-plus"></i> Nuevo pedido</button>
                </div>
            </div>

            <section class="kpi-grid">
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div>
                        <p class="kpi-label">Total de pedidos</p>
                        <p class="kpi-value">${fmtNum(orderStats.totalOrders)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 12%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <p class="kpi-label">Pendientes</p>
                        <p class="kpi-value">${fmtNum(orderStats.pendingOrders)}</p>
                        <p class="kpi-foot"><span class="trend down"><i class="fa-solid fa-arrow-down"></i> 8%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <p class="kpi-label">Confirmados</p>
                        <p class="kpi-value">${fmtNum(orderStats.confirmedOrders)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 15%</span> vs. mes anterior</p>
                    </div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-truck"></i></div>
                    <div>
                        <p class="kpi-label">Entregados</p>
                        <p class="kpi-value">${fmtNum(orderStats.deliveredOrders)}</p>
                        <p class="kpi-foot"><span class="trend up"><i class="fa-solid fa-arrow-up"></i> 33%</span> vs. mes anterior</p>
                    </div>
                </div>
            </section>

            <section class="split">
                <div class="stack">
                    <div class="card filters" style="margin-bottom:0">
                        <div class="search-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="search" class="input" data-filter="orders.q" value="${esc(f.q)}" placeholder="Buscar por número de pedido, cliente o producto...">
                        </div>
                        <div class="field">
                            <label>Estado</label>
                            <select class="input" data-filter="orders.status">
                                ${['Todos', 'Pendiente', 'Confirmado', 'En proceso', 'Entregado', 'Cancelado'].map(s => `<option ${f.status === s ? 'selected' : ''}>${s}</option>`).join('')}
                            </select>
                        </div>
                        <div class="field">
                            <label>Fecha</label>
                            <select class="input" data-filter="orders.date">
                                <option>Todos</option>
                                ${[...new Set(state.orders.map(o => o.date))].map(d => `<option ${f.date === d ? 'selected' : ''}>${esc(d)}</option>`).join('')}
                            </select>
                        </div>
                        <button class="btn" data-action="resetFilters" data-scope="orders"><i class="fa-solid fa-filter"></i> Filtros</button>
                    </div>

                    <div class="card">
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Productos</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th class="t-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows.length ? rows.map(o => `<tr class="clickable ${selected && o.id === selected.id ? 'selected' : ''}" data-action="selectOrder" data-id="${o.id}">
                                        <td>
                                            <div class="two-line">
                                                <span class="strong">${esc(o.customer)}</span>
                                                <small class="muted">${esc(o.email)}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="thumbs">
                                                ${o.items.slice(0, 2).map(it => `<img class="thumb sm" src="${esc(it.img)}" alt="${esc(it.name)}">`).join('')}
                                                <span class="more">+${o.extraCount || o.items.length}</span>
                                            </div>
                                        </td>
                                        <td class="mono">${fmtCOP(orderTotal(o))}</td>
                                        <td>${statusBadge(o.status)}</td>
                                        <td>
                                            <div class="two-line">
                                                <span>${esc(o.date)}</span>
                                                <small class="muted">${esc(o.time)}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="actions end">
                                                <button class="icon-action" title="Ver" data-action="selectOrder" data-id="${o.id}"><i class="fa-regular fa-eye"></i></button>
                                                <button class="icon-action" title="Cambiar estado" data-action="cycleOrderStatus" data-id="${o.id}"><i class="fa-solid fa-pen"></i></button>
                                                <button class="icon-action" title="Opciones" data-action="orderMenu" data-id="${o.id}"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                            </div>
                                        </td>
                                    </tr>`).join('') : `<tr class="empty-row"><td colspan="7">No se encontraron pedidos.</td></tr>`}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Detalle del pedido</h3>
                        ${selected ? `<button class="icon-btn" title="Ocultar" data-action="clearOrderDetail"><i class="fa-solid fa-xmark"></i></button>` : ''}
                    </div>
                    ${selected ? `
                    <div class="panel-body">
                        <div class="order-head">
                            <h4>Pedido #${esc(selected.number || selected.id)}</h4>
                            ${statusBadge(selected.status)}
                        </div>
                        <ul class="meta-list">
                            <li><i class="fa-regular fa-calendar"></i> <span>${esc(selected.date)} ${esc(selected.time)}</span></li>
                            <li><i class="fa-regular fa-user"></i> <div><span class="strong">${esc(selected.customer)}</span><small class="muted">${esc(selected.email)}</small></div></li>
                            <li><i class="fa-solid fa-phone"></i> <span>${esc(selected.phone)}</span></li>
                        </ul>

                        <div class="section-title">
                            <span>Productos</span>
                            <button class="link" data-nav="productos">Ver más</button>
                        </div>
                        <ul class="order-items">
                            ${selected.items.map(it => `<li>
                                <img class="thumb" src="${esc(it.img)}" alt="${esc(it.name)}">
                                <div class="grow">
                                    <span class="strong">${esc(it.name)}</span>
                                    <small class="muted">${esc(it.category)}</small>
                                    <small class="muted">${fmtCOP(it.price)} &times; ${it.qty}</small>
                                </div>
                                <span class="mono strong">${fmtCOP(it.price * it.qty)}</span>
                            </li>`).join('')}
                        </ul>

                        <div class="totals">
                            <div><span>Subtotal</span><span class="mono">${fmtCOP(orderSubtotal(selected))}</span></div>
                            <div><span>Envío</span><span class="mono">${fmtCOP(selected.shipping)}</span></div>
                            <div class="total"><span>Total</span><span class="mono">${fmtCOP(orderTotal(selected))}</span></div>
                        </div>

                        <div class="pay-box">
                            <i class="fa-solid fa-building-columns"></i>
                            <div class="grow">
                                <small class="muted">Método de pago</small>
                                <span class="strong">${esc(selected.payment)}</span>
                            </div>
                        </div>

                        <div class="section-title"><span>Estado del pedido</span></div>
                        ${selected.status === 'Cancelado' ? `<div class="cancel-note"><i class="fa-solid fa-ban"></i> Pedido cancelado</div>` : `
                        <div class="stepper">
                            ${steps.map((st, idx) => {
                                const cls = idx < stepIdx ? 'done' : idx === stepIdx ? 'current' : '';
                                return `<div class="step ${cls}">
                                    <span class="step-dot">${idx < stepIdx ? '<i class="fa-solid fa-check"></i>' : ''}</span>
                                    <span class="step-label">${st}</span>
                                </div>`;
                            }).join('')}
                        </div>`}

                        <div class="section-title"><span>Acciones</span></div>
                        <div class="btn-grid">
                            <button class="btn btn-dark" data-action="cycleOrderStatus" data-id="${selected.id}"><i class="fa-solid fa-rotate"></i> Cambiar estado</button>
                            <button class="btn" data-action="editOrderModal" data-id="${selected.id}"><i class="fa-solid fa-pen"></i> Editar pedido</button>
                            <button class="btn btn-outline-danger" data-action="cancelOrder" data-id="${selected.id}"><i class="fa-solid fa-ban"></i> Cancelar pedido</button>
                        </div>
                    </div>` : `<div class="empty-panel"><i class="fa-solid fa-receipt"></i>Selecciona un pedido para ver su detalle.</div>`}
                </div>
            </section>
        </div>`;
    }

    // G. INVENTARIO
    function renderInventario() {
        const lowStockItems = state.inventory.filter(item => item.stock <= item.minStock);
        const healthyStockItems = state.inventory.filter(item => item.stock > item.minStock).slice(0, 2);
        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Inventario</h2>
                    <p>Control de existencias, entradas, salidas y ajustes de productos</p>
                </div>
                <div class="head-actions">
                    <label class="date-pill">
                        <i class="fa-regular fa-calendar"></i>
                        <span>${formatLongDate(state.selectedDate)}</span>
                        <input type="date" id="datePickerInput" value="${state.selectedDate}">
                    </label>
                    <button class="btn btn-primary" data-action="newMovementModal"><i class="fa-solid fa-plus"></i> Registrar movimiento</button>
                    <button class="btn" data-action="movementHistoryModal"><i class="fa-solid fa-clock-rotate-left"></i> Historial de movimientos</button>
                </div>
            </div>

            <section class="species-grid mb">
                ${state.inventory.map(item => `
                <div class="card species-card">
                    <img src="${esc(item.img)}" alt="${esc(item.species)}">
                    <div>
                        <p class="kpi-label">${esc(item.species)}</p>
                        <p class="kpi-value">${fmtNum(item.stock)}</p>
                        <p class="small muted">en stock</p>
                        <p class="kpi-foot">${inventoryChange(item)}</p>
                    </div>
                </div>`).join('')}
            </section>

            <section class="split">
                <div class="card">
                    <div class="card-header"><h3>Stock por animal</h3></div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Especie</th>
                                    <th>Imagen</th>
                                    <th>Stock actual</th>
                                    <th>Stock mínimo</th>
                                    <th>Estado</th>
                                    <th>Última actualización</th>
                                    <th class="t-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${state.inventory.map(item => `<tr>
                                    <td>
                                        <div class="two-line">
                                            <span class="strong">${esc(item.species)}</span>
                                            <small class="muted italic">${esc(item.scientific)}</small>
                                        </div>
                                    </td>
                                    <td><img class="thumb md" src="${esc(item.img)}" alt="${esc(item.species)}"></td>
                                    <td>
                                        <div class="stock-cell">
                                            <div class="stock-now">
                                                <span class="strong mono">${fmtNum(item.stock)}</span>
                                                ${inventoryChange(item)}
                                            </div>
                                            <div class="qty-row">
                                                <div class="qty">
                                                    <button type="button" data-action="stepStock" data-id="${item.id}" data-delta="-100">−</button>
                                                    <input type="number" min="0" class="qty-input" id="qty-${item.id}" value="${item.stock}">
                                                    <button type="button" data-action="stepStock" data-id="${item.id}" data-delta="100">+</button>
                                                </div>
                                                <button class="btn btn-sm" data-action="applyStock" data-id="${item.id}">Actualizar</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="mono">${fmtNum(item.minStock)}</td>
                                    <td>${statusBadge(inventoryStatus(item))}</td>
                                    <td class="muted small nowrap">${esc(item.updated)}</td>
                                    <td>
                                        <div class="actions end">
                                            <button class="icon-action" title="Editar mínimo" data-action="editMinStock" data-id="${item.id}"><i class="fa-solid fa-pen"></i></button>
                                            <button class="icon-action" title="Historial" data-action="movementHistoryModal"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        </div>
                                    </td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <span>Mostrando 1 - ${state.inventory.length} de ${state.inventory.length} animales</span>
                        <div class="pagination"><button class="active">1</button></div>
                    </div>
                </div>

                <div class="stack">
                    <div class="card">
                        <div class="card-header"><h3>Resumen del inventario</h3></div>
                        <ul class="summary-list">
                            ${state.inventory.map(item => `<li>
                                <span class="dot" style="background:var(--primary)"></span>
                                <span class="grow">${esc(item.species)}</span>
                                <span class="mono strong">${fmtNum(item.stock)}</span>
                                ${inventoryChange(item)}
                            </li>`).join('')}
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-header"><h3>Nivel de stock</h3></div>
                        <div class="level">
                            ${state.inventory.map(item => {
                                const pct = inventoryPercent(item);
                                return `<div class="level-item">
                                    <div class="level-top">
                                        <span>${esc(item.species)}</span>
                                        <span class="mono strong">${pct}%</span>
                                    </div>
                                    <div class="bar"><span class="${inventoryBarColor(pct)}" style="width:${pct}%"></span></div>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Alertas</h3>
                            <button class="link" data-action="movementHistoryModal">Ver todos</button>
                        </div>
                        <div class="alert-list">
                            ${lowStockItems.length ? lowStockItems.map(item => `<div class="alert-item danger">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <div>
                                    <span class="strong">${esc(item.species)}</span>
                                    <span class="muted">Stock bajo (${fmtNum(item.stock)} / mín. ${fmtNum(item.minStock)}).</span>
                                    <small class="muted">${esc(item.updated)}</small>
                                </div>
                            </div>`).join('') : `<div class="alert-item info">
                                <i class="fa-solid fa-circle-check"></i>
                                <div><span class="strong">Sin alertas de stock</span><span class="muted">Todos los productos están sobre su mínimo.</span></div>
                            </div>`}
                            ${healthyStockItems.map(item => `<div class="alert-item info">
                                <i class="fa-solid fa-circle-info"></i>
                                <div>
                                    <span class="strong">${esc(item.species)}</span>
                                    <span class="muted">Stock estable (${fmtNum(item.stock)} / mín. ${fmtNum(item.minStock)}).</span>
                                    <small class="muted">${esc(item.updated)}</small>
                                </div>
                            </div>`).join('')}
                        </div>
                    </div>
                </div>
            </section>
        </div>`;
    }

    // H. CHATS
    function renderChats() {
        const activeChat = state.chats.find(c => c.id === state.selectedChatId) || state.chats[0];
        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Chats</h2>
                    <p>Mensajería interna y atención rápida con clientes y equipo</p>
                </div>
            </div>
            <div class="card chat-layout">
                <div class="chat-list">
                    ${state.chats.map((c, idx) => {
                        const last = c.messages[c.messages.length - 1];
                        const [bg, fg] = AVATAR_COLORS[idx % AVATAR_COLORS.length];
                        return `<button class="chat-item ${c.id === activeChat.id ? 'active' : ''}" data-action="selectChat" data-id="${c.id}">
                            <span class="avatar" style="background:${bg};color:${fg}">${initials(c.user)}</span>
                            <div class="grow">
                                <span class="strong">${esc(c.user)}</span>
                                <span class="preview">${esc(last?.text || '')}</span>
                            </div>
                            ${c.unread ? `<span class="chat-unread">${c.unread}</span>` : ''}
                        </button>`;
                    }).join('')}
                </div>
                <div class="chat-main">
                    <div class="chat-head">
                        <span class="avatar" style="background:var(--primary-soft);color:var(--primary)">${initials(activeChat.user)}</span>
                        <div>
                            <div class="strong">${esc(activeChat.user)}</div>
                            <div class="small muted">${esc(activeChat.role)} · En línea</div>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        ${activeChat.messages.map(m => `<div class="msg ${m.me ? 'me' : ''}">
                            <span>${esc(m.text)}</span>
                            <small>${esc(m.time)}</small>
                        </div>`).join('')}
                    </div>
                    <form class="chat-form" id="chatForm">
                        <input type="text" class="input" name="msg" placeholder="Escribe un mensaje..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
                    </form>
                </div>
            </div>
        </div>`;
    }

    // I. ESTADÍSTICAS
    function renderEstadisticas() {
        const months = [
            { label: 'Mar', val: 68, text: '$68M' },
            { label: 'Abr', val: 74, text: '$74M' },
            { label: 'May', val: 89, text: '$89M' },
            { label: 'Jun', val: 95, text: '$95M' },
            { label: 'Jul', val: 107, text: '$107M' },
            { label: 'Ago', val: 120, text: '$120M', current: true },
        ];
        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Estadísticas</h2>
                    <p>Rendimiento comercial, ventas mensuales y demanda por especie</p>
                </div>
                <button class="btn" data-action="exportStats"><i class="fa-solid fa-download"></i> Exportar reporte</button>
            </div>

            <section class="kpi-grid">
                <div class="card kpi"><div class="kpi-icon"><i class="fa-solid fa-chart-line"></i></div><div><p class="kpi-label">Ticket promedio</p><p class="kpi-value">$ 42.500</p><p class="kpi-foot"><span class="trend up">↑ 9%</span> este mes</p></div></div>
                <div class="card kpi"><div class="kpi-icon"><i class="fa-solid fa-repeat"></i></div><div><p class="kpi-label">Tasa de recompra</p><p class="kpi-value">64%</p><p class="kpi-foot"><span class="trend up">↑ 4%</span> clientes frecuentes</p></div></div>
                <div class="card kpi"><div class="kpi-icon"><i class="fa-solid fa-trophy"></i></div><div><p class="kpi-label">Producto líder</p><p class="kpi-value" style="font-size:18px">Tenebrios 250 un.</p><p class="kpi-foot">42% de las ventas</p></div></div>
                <div class="card kpi"><div class="kpi-icon"><i class="fa-solid fa-truck-fast"></i></div><div><p class="kpi-label">Entregas a tiempo</p><p class="kpi-value">96,8%</p><p class="kpi-foot">Últimos 30 días</p></div></div>
            </section>

            <section class="split split-even">
                <div class="card">
                    <div class="card-header"><h3>Ingresos últimos 6 meses</h3><span class="badge green">2025</span></div>
                    <div class="chart">
                        ${months.map(m => `<div class="chart-col">
                            <div class="chart-bar ${m.current ? 'current' : ''}" style="height:${Math.round((m.val / 130) * 100)}%"><span>${m.text}</span></div>
                            <small>${m.label}</small>
                        </div>`).join('')}
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h3>Distribución de ventas por categoría</h3></div>
                    <div class="level" style="padding-top:14px">
                        <div class="level-item"><div class="level-top"><span>Tenebrios</span><span class="mono strong">52%</span></div><div class="bar"><span class="green" style="width:52%"></span></div></div>
                        <div class="level-item"><div class="level-top"><span>Zophobas</span><span class="mono strong">24%</span></div><div class="bar"><span class="green" style="width:24%"></span></div></div>
                        <div class="level-item"><div class="level-top"><span>Grillos</span><span class="mono strong">15%</span></div><div class="bar"><span class="yellow" style="width:15%"></span></div></div>
                        <div class="level-item"><div class="level-top"><span>Cucarachas Dubia</span><span class="mono strong">9%</span></div><div class="bar"><span class="yellow" style="width:9%"></span></div></div>
                    </div>
                </div>
            </section>
        </div>`;
    }

    // J. REPORTES
    function renderReportes() {
        const filters = state.filters.reports;
        const report = getReportData(filters);
        const totalRevenue = report.revenue || 1;
        const reportStatusBadge = (status) => {
            const colors = {
                'Entregado': 'green',
                'Confirmado': 'blue',
                'En proceso': 'purple',
                'Pendiente': 'yellow',
            };
            return `<span class="badge ${colors[status] || 'gray'}">${esc(status)}</span>`;
        };

        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Reportes de Ventas</h2>
                    <p>Consulta, analiza y exporta los reportes financieros y de productos por período</p>
                </div>
                <button class="btn btn-primary" data-action="exportReportCsv">
                    <i class="fa-solid fa-download"></i> Exportar CSV
                </button>
            </div>

            <form class="card filters report-filters" id="reportFiltersForm">
                <div class="field">
                    <label for="reportStart">Fecha inicial</label>
                    <input class="input" type="date" id="reportStart" name="start" value="${esc(filters.start)}">
                </div>
                <div class="field">
                    <label for="reportEnd">Fecha final</label>
                    <input class="input" type="date" id="reportEnd" name="end" value="${esc(filters.end)}">
                </div>
                <div class="field">
                    <label for="reportStatus">Estado del pedido</label>
                    <select class="input" id="reportStatus" name="status">
                        ${['Todos', 'Entregado', 'Confirmado', 'En proceso', 'Pendiente'].map(status => `<option ${filters.status === status ? 'selected' : ''}>${status}</option>`).join('')}
                    </select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Generar reporte</button>
                <button class="btn" type="button" data-action="clearReportFilters">Limpiar</button>
            </form>

            <section class="kpi-grid">
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div><p class="kpi-label">Ingresos del período</p><p class="kpi-value">${fmtCOP(report.revenue)}</p><p class="kpi-foot">COP</p></div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-receipt"></i></div>
                    <div><p class="kpi-label">Total de ventas / pedidos</p><p class="kpi-value">${report.orders.length}</p><p class="kpi-foot">En el período seleccionado</p></div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div><p class="kpi-label">Unidades vendidas</p><p class="kpi-value">${fmtNum(report.units)}</p><p class="kpi-foot">Unidades despachadas</p></div>
                </div>
                <div class="card kpi">
                    <div class="kpi-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div><p class="kpi-label">Producto estrella</p><p class="kpi-value report-star">${esc(report.topProduct)}</p><p class="kpi-foot">Mayor ingreso generado</p></div>
                </div>
            </section>

            <section class="reports-layout">
                <div class="card">
                    <div class="card-header"><h3>Ventas registradas</h3></div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>N.° Pedido</th><th>Fecha</th><th>Cliente</th><th>Productos y cantidades</th><th>Estado</th><th>Total ($)</th></tr>
                            </thead>
                            <tbody>
                                ${report.orders.length ? report.orders.map(order => `<tr>
                                    <td class="mono strong">${esc(order.number || order.id)}</td>
                                    <td class="nowrap">${esc(order.date)}</td>
                                    <td>${esc(order.customer)}</td>
                                    <td>${order.items.map(item => `${esc(item.name)} × ${item.qty}`).join('<br>')}</td>
                                    <td>${reportStatusBadge(order.status)}</td>
                                    <td class="mono">${fmtCOP(orderTotal(order))}</td>
                                </tr>`).join('') : `<tr class="empty-row"><td colspan="6"><div class="report-empty"><i class="fa-regular fa-folder-open"></i><strong>No existen ventas registradas en el período seleccionado</strong><span>Prueba con otro rango de fechas o estado del pedido.</span></div></td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>Totales por producto</h3></div>
                    ${report.products.length ? `<div class="report-products">
                        ${report.products.map(product => {
                            const share = Math.round(product.revenue / totalRevenue * 100);
                            return `<div class="report-product">
                                <div class="report-product-heading">
                                    <span class="strong">${esc(product.name)}</span>
                                    <span class="mono">${share}%</span>
                                </div>
                                <div class="bar"><span class="green" style="width:${share}%"></span></div>
                                <div class="report-product-foot">
                                    <span>${fmtNum(product.units)} ${product.units === 1 ? 'unidad' : 'unidades'}</span>
                                    <span class="mono strong">${fmtCOP(product.revenue)}</span>
                                </div>
                            </div>`;
                        }).join('')}
                    </div>` : `<div class="empty-panel"><i class="fa-solid fa-box-open"></i>Sin productos vendidos en este período.</div>`}
                </div>
            </section>
        </div>`;
    }

    // K. AJUSTES
    function renderAjustes() {
        const s = state.settings;
        return `
        <div class="fade-in">
            <div class="page-head">
                <div>
                    <h2>Ajustes</h2>
                    <p>Configuración general de la plataforma BIO-GEST y Tenebrios Gold</p>
                </div>
            </div>
            <div class="settings-grid">
                <form class="card" id="settingsForm">
                    <div class="card-header"><h3>Información de la empresa</h3></div>
                    <div class="panel-form">
                        <div class="field"><label>Nombre comercial</label><input class="input" name="company" value="${esc(s.company)}"></div>
                        <div class="field"><label>Plataforma</label><input class="input" name="platform" value="${esc(s.platform)}"></div>
                        <div class="row-2">
                            <div class="field"><label>Correo de contacto</label><input class="input" name="email" value="${esc(s.email)}"></div>
                            <div class="field"><label>Teléfono</label><input class="input" name="phone" value="${esc(s.phone)}"></div>
                        </div>
                        <div class="field"><label>Moneda</label><input class="input" name="currency" value="${esc(s.currency)}"></div>
                        <button type="submit" class="btn btn-primary">Guardar configuración</button>
                    </div>
                </form>

                <div class="card">
                    <div class="card-header"><h3>Preferencias del sistema</h3></div>
                    <div class="panel-form">
                        ${[
                            ['lowStockAlerts', 'Alertas de stock bajo', 'Notificar cuando una especie baje del mínimo'],
                            ['orderEmailAlerts', 'Notificaciones de pedidos', 'Enviar aviso al recibir un nuevo pedido'],
                            ['autoConfirmPaid', 'Confirmación automática', 'Confirmar pedidos pagados por PSE o tarjeta'],
                            ['maintenanceMode', 'Modo mantenimiento', 'Pausar temporalmente la tienda pública'],
                        ].map(([key, title, desc]) => `
                        <div class="toggle-row">
                            <div><span class="strong">${title}</span><small class="muted">${desc}</small></div>
                            <label class="switch">
                                <input type="checkbox" data-setting="${key}" ${s[key] ? 'checked' : ''}>
                                <span></span>
                            </label>
                        </div>`).join('')}
                    </div>
                </div>
            </div>
        </div>`;
    }

    // -------------------- Render Principal & Bindings --------------------
    function render() {
        renderNav();
        const views = {
            inicio: renderInicio,
            usuarios: renderUsuarios,
            roles: renderRoles,
            productos: renderProductos,
            promociones: renderPromociones,
            pedidos: renderPedidos,
            inventario: renderInventario,
            chats: renderChats,
            estadisticas: renderEstadisticas,
            reportes: renderReportes,
            ajustes: renderAjustes,
        };
        $('#app').innerHTML = (views[state.view] || renderInicio)();
        bindViewSpecificEvents();
    }

    function bindViewSpecificEvents() {
        const reportForm = $('#reportFiltersForm');
        if (reportForm) {
            reportForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(reportForm);
                const start = formData.get('start');
                const end = formData.get('end');
                if (start && end && start > end) {
                    toast('La fecha inicial no puede ser posterior a la fecha final', 'error');
                    return;
                }
                state.filters.reports = {
                    start,
                    end,
                    status: formData.get('status'),
                };
                render();
            });
        }

        // Selectores de filtro en vivo
        $$('[data-filter]').forEach(el => {
            const evt = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evt, () => {
                const [scope, key] = el.dataset.filter.split('.');
                state.filters[scope][key] = el.value;
                state.globalQuery = '';
                $('#globalSearch').value = '';
                const pos = el.selectionStart;
                render();
                const restored = $(`[data-filter="${el.dataset.filter}"]`);
                if (restored && restored.tagName === 'INPUT') {
                    restored.focus();
                    if (typeof pos === 'number') restored.setSelectionRange(pos, pos);
                }
            });
        });

        // Selector de fecha
        const dp = $('#datePickerInput');
        if (dp) {
            dp.addEventListener('change', () => {
                state.selectedDate = dp.value;
                render();
            });
        }

        // Check-all en tablas
        $$('[data-check-all]').forEach(master => {
            master.addEventListener('change', () => {
                const table = master.closest('table');
                $$('tbody input[type="checkbox"]', table).forEach(cb => { cb.checked = master.checked; });
            });
        });

        // Roles: cambio de rol y checkboxes de permisos
        const roleSelect = $('#rolePermSelect');
        if (roleSelect) {
            roleSelect.addEventListener('change', () => {
                state.selectedRoleId = roleSelect.value;
                render();
            });
        }
        $$('[data-perm-mod]').forEach(cb => {
            cb.addEventListener('change', () => {
                const mod = cb.dataset.permMod;
                const act = cb.dataset.permAct;
                const map = state.rolePermissions[state.selectedRoleId] || (state.rolePermissions[state.selectedRoleId] = {});
                const list = map[mod] || (map[mod] = []);
                if (cb.checked && !list.includes(act)) list.push(act);
                if (!cb.checked) map[mod] = list.filter(x => x !== act);
                toast(`Permiso "${act}" actualizado`);
            });
        });

        // Promociones: formulario derecho
        const promoForm = $('#promoDetailForm');
        if (promoForm) {
            const ta = $('textarea[name="desc"]', promoForm);
            const cnt = $('#promoDescCount');
            ta?.addEventListener('input', () => { cnt.textContent = `${ta.value.length}/500`; });
            promoForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const pr = state.promotions.find(x => x.id === state.selectedPromoId);
                if (!pr) return;
                const fd = new FormData(promoForm);
                pr.type = fd.get('type');
                pr.product = fd.get('product');
                pr.discount = fd.get('discount');
                pr.start = fd.get('start');
                pr.end = fd.get('end');
                pr.desc = fd.get('desc');
                toast(`Promoción "${pr.name}" guardada`);
                render();
            });
        }

        // Chats
        const chatForm = $('#chatForm');
        if (chatForm) {
            const box = $('#chatMessages');
            if (box) box.scrollTop = box.scrollHeight;
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const text = chatForm.msg.value.trim();
                if (!text) return;
                const chat = state.chats.find(c => c.id === state.selectedChatId);
                if (!chat) return;
                const now = new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
                chat.messages.push({ me: true, text, time: now });
                render();
            });
        }

        // Ajustes
        const settingsForm = $('#settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const fd = new FormData(settingsForm);
                state.settings.company = fd.get('company');
                state.settings.platform = fd.get('platform');
                state.settings.email = fd.get('email');
                state.settings.phone = fd.get('phone');
                state.settings.currency = fd.get('currency');
                toast('Configuración guardada correctamente');
            });
        }
        $$('[data-setting]').forEach(sw => {
            sw.addEventListener('change', () => {
                state.settings[sw.dataset.setting] = sw.checked;
                toast('Preferencia actualizada');
            });
        });
    }

    let liveDataSignature = '';
    let liveDataRequestPending = false;
    let liveSyncErrorShown = false;
    let liveRefreshQueued = false;

    async function refreshLiveData(notify = false) {
        if (!window.BIOGEST_ADMIN_DATA_URL) return;
        if (liveDataRequestPending) {
            liveRefreshQueued = liveRefreshQueued || notify;
            return;
        }
        liveDataRequestPending = true;

        try {
            const response = await fetch(window.BIOGEST_ADMIN_DATA_URL, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!response.ok) throw new Error(`Error HTTP ${response.status}`);

            const data = await response.json();
            const signature = JSON.stringify(data);
            if (signature === liveDataSignature) {
                liveSyncErrorShown = false;
                return;
            }

            const firstLoad = !state.liveDataLoaded;
            liveDataSignature = signature;
            state.products = data.products || [];
            state.categories = Array.isArray(data.categories) && data.categories.length
                ? data.categories
                : [...new Set(state.products.map(product => product.category))].map((name, index) => {
                    const existing = state.categories.find(category => category.name === name);
                    return existing || { id: `LIVE-CAT-${index}`, name, color: '#536E58' };
                });
            state.orders = (data.orders || []).map(order => ({
                ...order,
                status: displayOrderStatus(order.status),
                items: order.items || [],
            }));
            state.inventory = data.inventory || [];
            state.inventoryMovements = data.inventoryMovements || [];
            state.summary = { ...state.summary, ...(data.summary || {}) };
            state.liveDataLoaded = true;

            if (firstLoad) state.selectedProductId = null;
            if (firstLoad || !state.orders.some(order => order.id === state.selectedOrderId)) {
                state.selectedOrderId = state.orders[0]?.id || null;
            }

            liveSyncErrorShown = false;
            render();
            if (notify) toast('Pedidos e inventario sincronizados con la compra.', 'info');
        } catch (error) {
            if (!liveSyncErrorShown) {
                toast('No fue posible sincronizar pedidos e inventario con el servidor.', 'error');
                console.error('Error sincronizando el panel administrativo:', error);
                liveSyncErrorShown = true;
            }
        } finally {
            liveDataRequestPending = false;
            if (liveRefreshQueued) {
                liveRefreshQueued = false;
                refreshLiveData(true);
            }
        }
    }

    function startLiveDataSync() {
        if (!window.BIOGEST_ADMIN_DATA_URL) return;
        refreshLiveData();
        window.setInterval(() => refreshLiveData(), 10000);
        window.addEventListener('focus', () => refreshLiveData());
        window.addEventListener('storage', event => {
            if (event.key === 'biogest_admin_sync') refreshLiveData(true);
        });
    }

    async function persistOrderStatus(order, status) {
        if (!window.BIOGEST_ADMIN_ORDER_STATUS_URL) {
            order.status = status;
            render();
            return;
        }

        const dbStatuses = {
            Pendiente: 'Pendiente',
            Confirmado: 'Procesando',
            'En proceso': 'Despachado',
            Entregado: 'Entregado',
            Cancelado: 'Cancelado',
        };

        try {
            const url = window.BIOGEST_ADMIN_ORDER_STATUS_URL
                .replace('__ORDER_ID__', encodeURIComponent(order.id));
            const csrfToken = $('meta[name="csrf-token"]')?.content;
            const response = await fetch(url, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({ estado_pedido: dbStatuses[status] || status }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'No se pudo actualizar el pedido.');

            await refreshLiveData();
            toast(`Pedido #${order.number} actualizado a "${status}".`);
        } catch (error) {
            toast(error.message || 'No se pudo actualizar el pedido.', 'error');
            console.error('Error actualizando el estado del pedido:', error);
        }
    }

    function openProductModal(product = null) {
        const selectedCategoryId = product?.categoryId
            || state.categories.find(category => category.name === product?.category)?.id
            || '';
        let selectedImageFile = null;
        let imagePreview = product?.img || '';

        openModal({
            title: product ? `Editar ${product.name}` : 'Agregar producto',
            wide: true,
            body: `<form id="productModalForm" class="form-grid">
                <div class="field full">
                    <label>Nombre del producto *</label>
                    <input class="input" name="nombre_producto" required maxlength="150" value="${esc(product?.name || '')}" placeholder="Ej: Tenebrios 100 unidades">
                </div>
                <div class="field full">
                    <label>Categoría *</label>
                    <select class="input" name="id_categoria" required>
                        <option value="">Selecciona una categoría</option>
                        ${state.categories.map(category => `<option value="${esc(category.id)}" ${String(selectedCategoryId) === String(category.id) ? 'selected' : ''}>${esc(category.name)}</option>`).join('')}
                    </select>
                </div>
                <div class="field">
                    <label>Precio (COP) *</label>
                    <input class="input" type="number" name="precio" min="0.01" step="0.01" required value="${product ? product.price : ''}" placeholder="8000">
                </div>
                <div class="field">
                    <label>Stock disponible *</label>
                    <input class="input" type="number" name="stock" min="0" step="1" required value="${product ? product.stock : ''}" placeholder="100">
                </div>
                <div class="field full">
                    <label>Descripción</label>
                    <textarea class="input" name="descripcion" maxlength="500" placeholder="Describe el producto...">${esc(product?.desc || '')}</textarea>
                    <span class="counter" id="prodDescCount">${(product?.desc || '').length}/500</span>
                </div>
                <div class="field full">
                    <label>Imagen del producto</label>
                    <label class="dropzone" id="productModalDropzone">
                        <input type="file" accept="image/png,image/jpeg" hidden id="productModalFileInput">
                        <span id="productModalImagePreview">${imagePreview ? `<img class="drop-preview" src="${esc(imagePreview)}" alt="Vista previa del producto">` : '<i class="fa-solid fa-cloud-arrow-up"></i>'}</span>
                        <span>Arrastra una imagen o haz clic para seleccionarla</span>
                        <small>JPG o PNG, máximo 5 MB.</small>
                    </label>
                </div>
                <input type="hidden" name="imagen" value="${esc(product?.imagePath || '')}">
                <input type="hidden" name="stock_minimo" value="${product?.minStock ?? 10}">
                <input type="hidden" name="estado" value="${esc(product?.status || 'Activo')}">
            </form>`,
            foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-dark" type="submit" form="productModalForm">${product ? 'Guardar cambios' : 'Guardar producto'}</button>`,
            onMount: (modal, close) => {
                const form = $('#productModalForm', modal);
                const description = $('textarea[name="descripcion"]', form);
                const counter = $('#prodDescCount', modal);
                const fileInput = $('#productModalFileInput', modal);
                const preview = $('#productModalImagePreview', modal);
                const dropzone = $('#productModalDropzone', modal);

                description.addEventListener('input', () => {
                    counter.textContent = `${description.value.length}/500`;
                });

                const selectImage = file => {
                    if (!file) return;
                    if (!['image/jpeg', 'image/png'].includes(file.type)) {
                        toast('Selecciona una imagen JPG o PNG.', 'error');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        toast('La imagen no puede superar los 5 MB.', 'error');
                        return;
                    }
                    selectedImageFile = file;
                    const reader = new FileReader();
                    reader.onload = () => {
                        imagePreview = String(reader.result || '');
                        preview.innerHTML = `<img class="drop-preview" src="${esc(imagePreview)}" alt="Vista previa del producto">`;
                    };
                    reader.readAsDataURL(file);
                };

                fileInput.addEventListener('change', () => selectImage(fileInput.files[0]));
                dropzone.addEventListener('dragover', event => {
                    event.preventDefault();
                    dropzone.classList.add('drag');
                });
                dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag'));
                dropzone.addEventListener('drop', event => {
                    event.preventDefault();
                    dropzone.classList.remove('drag');
                    selectImage(event.dataTransfer.files[0]);
                });

                form.addEventListener('submit', async event => {
                    event.preventDefault();
                    const formData = new FormData(form);
                    if (selectedImageFile) formData.set('imagen_archivo', selectedImageFile);

                    if (window.BIOGEST_ADMIN_PRODUCT_STORE_URL) {
                        const url = product
                            ? window.BIOGEST_ADMIN_PRODUCT_UPDATE_URL.replace(
                                '__PRODUCT_ID__',
                                encodeURIComponent(product.backendId || product.id.replace('PROD-', ''))
                            )
                            : window.BIOGEST_ADMIN_PRODUCT_STORE_URL;
                        if (product) formData.set('_method', 'PUT');

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    Accept: 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || '',
                                },
                                body: formData,
                            });
                            const result = await response.json();
                            if (!response.ok) {
                                const validationMessage = result.errors
                                    ? Object.values(result.errors).flat().join(' ')
                                    : result.message;
                                throw new Error(validationMessage || 'No se pudo guardar el producto.');
                            }

                            let syncWarning = '';
                            try {
                                const syncMarker = JSON.stringify({ type: 'productos', updatedAt: Date.now() });
                                localStorage.setItem('biogest_admin_sync', syncMarker);
                                localStorage.setItem('biogest_product_catalog_sync', syncMarker);
                            } catch (syncError) {
                                syncWarning = 'Producto guardado, pero otras pestañas no pudieron sincronizarse.';
                                console.error('No se pudo notificar el cambio de catálogo a otras pestañas:', syncError);
                            }
                            close();
                            state.productDraftImage = '';
                            await refreshLiveData();
                            toast(syncWarning || result.message || 'Producto guardado correctamente.');
                        } catch (error) {
                            toast(error.message || 'No se pudo guardar el producto.', 'error');
                            console.error('Error guardando producto:', error);
                        }
                        return;
                    }

                    const category = state.categories.find(item => String(item.id) === String(formData.get('id_categoria')));
                    const payload = {
                        name: formData.get('nombre_producto').trim(),
                        category: category?.name || '',
                        categoryId: String(formData.get('id_categoria')),
                        price: Number(formData.get('precio')),
                        stock: Number(formData.get('stock')),
                        minStock: Number(formData.get('stock_minimo')),
                        desc: formData.get('descripcion').trim(),
                        status: product?.status || 'Activo',
                    };
                    if (product) {
                        Object.assign(product, payload);
                        if (selectedImageFile) product.img = imagePreview;
                        toast(`Producto ${product.name} actualizado`);
                    } else {
                        const id = `PROD-${String(state.products.length + 1).padStart(3, '0')}`;
                        state.products.unshift({
                            id,
                            ...payload,
                            img: imagePreview || IMG.tenebrios,
                        });
                        toast('Producto agregado');
                    }
                    close();
                    render();
                });
            },
        });
    }

    // -------------------- Acciones Globales (Delegación) --------------------
    document.addEventListener('click', (e) => {
        const navBtn = e.target.closest('[data-nav]');
        if (navBtn) {
            e.preventDefault();
            navigate(navBtn.dataset.nav);
            return;
        }

        const actEl = e.target.closest('[data-action]');
        if (!actEl) {
            if (!e.target.closest('.float-menu')) closeMenus();
            return;
        }

        const action = actEl.dataset.action;
        const id = actEl.dataset.id;

        // Evitar que botones dentro de filas clickable disparen doble acción
        if (actEl.tagName === 'BUTTON' && actEl.closest('tr.clickable')) e.stopPropagation();

        switch (action) {
            case 'toggleSidebar':
                document.body.classList.toggle('sidebar-open');
                break;

            case 'homeUserPage': {
                const p = Number(actEl.dataset.page);
                if (p >= 1) { state.homeUserPage = p; render(); }
                break;
            }

            case 'resetFilters':
                Object.keys(state.filters[actEl.dataset.scope]).forEach(k => {
                    state.filters[actEl.dataset.scope][k] = (k === 'q') ? '' : (k === 'category' ? 'Todas' : 'Todos');
                });
                state.globalQuery = '';
                $('#globalSearch').value = '';
                render();
                break;
            case 'clearReportFilters':
                state.filters.reports = { start: '', end: '', status: 'Todos' };
                render();
                break;

            // --- Usuarios ---
            case 'newUser':
            case 'editUser': {
                const user = action === 'editUser' ? state.users.find(u => u.id === id) : null;
                openModal({
                    title: user ? `Editar usuario ${user.name}` : 'Crear nuevo usuario',
                    body: `
                    <form id="userModalForm" class="form-grid">
                        <div class="field full"><label>Nombre completo *</label><input class="input" name="name" required value="${esc(user?.name || '')}"></div>
                        <div class="field full"><label>Correo electrónico *</label><input type="email" class="input" name="email" required value="${esc(user?.email || '')}" placeholder="nombre@bio-gest.com"></div>
                        <div class="field"><label>Rol</label><select class="input" name="role">${(user ? ['Administrador', 'Vendedor', 'Cliente', 'Inventario'] : ['Administrador']).map(r => `<option ${user?.role === r || !user ? 'selected' : ''}>${r}</option>`).join('')}</select></div>
                        ${user ? `<div class="field"><label>Estado</label><select class="input" name="status">${['Activo', 'Inactivo'].map(s => `<option ${user.status === s ? 'selected' : ''}>${s}</option>`).join('')}</select></div>` : '<input type="hidden" name="status" value="Activo">'}
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="userModalForm">Guardar</button>`,
                    onMount: (m, close) => {
                        $('#userModalForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            if (user) {
                                user.name = fd.get('name').trim();
                                user.email = fd.get('email').trim();
                                user.role = fd.get('role');
                                user.status = fd.get('status');
                                toast(`Usuario ${user.name} actualizado`);
                            } else {
                                const nextId = `USR-${String(state.users.length + 1).padStart(3, '0')}`;
                                state.users.unshift({
                                    id: nextId,
                                    name: fd.get('name').trim(),
                                    email: fd.get('email').trim(),
                                    role: 'Administrador',
                                    status: 'Activo',
                                    lastAccess: 'Ahora',
                                });
                                toast(`Usuario ${fd.get('name').trim()} creado`);
                            }
                            close();
                            render();
                        });
                    },
                });
                break;
            }
            case 'viewUser': {
                const u = state.users.find(x => x.id === id);
                if (!u) break;
                openModal({
                    title: `Detalle de ${u.name}`,
                    body: `<ul class="detail-list">
                        <li><span>Correo</span><strong>${esc(u.email)}</strong></li>
                        <li><span>Rol</span><strong>${esc(u.role)}</strong></li>
                        <li><span>Estado</span>${statusBadge(u.status)}</li>
                        <li><span>Último acceso</span><span>${esc(u.lastAccess)}</span></li>
                    </ul>`,
                    foot: `<button class="btn" data-close-modal>Cerrar</button>`,
                });
                break;
            }
            case 'deleteUser': {
                const user = state.users.find(u => u.id === id);
                if (!user) break;
                confirmModal('Eliminar usuario', `¿Deseas eliminar a ${user.name}?`, () => {
                    state.users = state.users.filter(u => u.id !== id);
                    toast(`Usuario ${user.name} eliminado`, 'info');
                    render();
                });
                break;
            }
            case 'userMenu': {
                const u = state.users.find(x => x.id === id);
                if (!u) break;
                showFloatMenu(actEl, [
                    { icon: 'fa-pen', label: 'Editar usuario', onClick: () => triggerAction('editUser', id) },
                    { icon: 'fa-power-off', label: u.status === 'Activo' ? 'Desactivar' : 'Activar', onClick: () => { u.status = u.status === 'Activo' ? 'Inactivo' : 'Activo'; toast('Estado actualizado'); render(); } },
                    { icon: 'fa-trash', label: 'Eliminar', danger: true, onClick: () => triggerAction('deleteUser', id) },
                ]);
                break;
            }

            // --- Roles ---
            case 'selectRole':
                state.selectedRoleId = id;
                render();
                break;
            case 'focusPermissions':
                $('#permissionsCard')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                break;
            case 'newRole':
            case 'editRole': {
                const role = action === 'editRole' ? state.roles.find(r => r.id === id) : null;
                openModal({
                    title: role ? `Editar rol ${role.name}` : 'Crear nuevo rol',
                    body: `<form id="roleModalForm" class="form-grid">
                        <div class="field full"><label>Nombre del rol *</label><input class="input" name="name" required value="${esc(role?.name || '')}"></div>
                        <div class="field full"><label>Descripción *</label><textarea class="input" name="desc" required>${esc(role?.desc || '')}</textarea></div>
                        <div class="field full"><label>Estado</label><select class="input" name="status">${['Activo', 'Inactivo'].map(s => `<option ${role?.status === s ? 'selected' : ''}>${s}</option>`).join('')}</select></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="roleModalForm">Guardar</button>`,
                    onMount: (m, close) => {
                        $('#roleModalForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            if (role) {
                                role.name = fd.get('name').trim();
                                role.desc = fd.get('desc').trim();
                                role.status = fd.get('status');
                                toast(`Rol ${role.name} actualizado`);
                            } else {
                                const newId = `ROL-${String(state.roles.length + 1).padStart(3, '0')}`;
                                state.roles.push({ id: newId, name: fd.get('name').trim(), desc: fd.get('desc').trim(), usersCount: 0, status: fd.get('status') });
                                state.rolePermissions[newId] = { productos: ['ver'] };
                                state.selectedRoleId = newId;
                                toast(`Rol "${fd.get('name').trim()}" creado`);
                            }
                            close();
                            render();
                        });
                    },
                });
                break;
            }
            case 'deleteRole': {
                if (state.roles.length <= 1) return toast('Debe existir al menos un rol', 'error');
                const role = state.roles.find(r => r.id === id);
                if (!role) break;
                confirmModal('Eliminar rol', `¿Seguro que deseas eliminar el rol ${role.name}?`, () => {
                    state.roles = state.roles.filter(r => r.id !== id);
                    if (state.selectedRoleId === id) state.selectedRoleId = state.roles[0].id;
                    toast(`Rol ${role.name} eliminado`, 'info');
                    render();
                });
                break;
            }

            // --- Productos ---
            case 'selectProduct':
                state.selectedProductId = id;
                state.productDraftImage = '';
                render();
                break;
            case 'goProductEdit':
                openProductModal(state.products.find(product => product.id === id));
                break;
            case 'resetProductForm':
                openProductModal();
                break;
            case 'editProductModal': {
                const product = state.products.find(item => item.id === id);
                if (product) openProductModal(product);
                break;
            }
            case 'viewProduct': {
                const p = state.products.find(x => x.id === id);
                if (!p) break;
                openModal({
                    title: p.name,
                    body: `<div style="display:flex;gap:14px;align-items:center;margin-bottom:14px">
                        <img class="thumb lg" src="${esc(p.img)}" alt="${esc(p.name)}">
                        <div><div class="strong" style="font-size:16px">${fmtCOP(p.price)}</div>${statusBadge(p.status)}</div>
                    </div>
                    <p class="muted">${esc(p.desc || 'Sin descripción')}</p>`,
                    foot: `<button class="btn" data-close-modal>Cerrar</button>`,
                });
                break;
            }
            case 'deleteProduct': {
                const product = state.products.find(p => p.id === id);
                if (!product) break;
                confirmModal('Eliminar producto', `¿Deseas eliminar ${product.name}?`, () => {
                    state.products = state.products.filter(p => p.id !== id);
                    if (state.selectedProductId === id) state.selectedProductId = null;
                    toast(`Producto ${product.name} eliminado`, 'info');
                    render();
                });
                break;
            }
            case 'newCategory':
                openModal({
                    title: 'Nueva categoría',
                    body: `<form id="catForm" class="form-grid">
                        <div class="field full"><label>Nombre *</label><input class="input" name="name" required placeholder="Ej: Suplementos"></div>
                        <div class="field full"><label>Color indicador</label><input type="color" class="input" name="color" value="#536E58" style="padding:4px"></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="catForm">Crear</button>`,
                    onMount: (m, close) => {
                        $('#catForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            state.categories.push({ id: `CAT-${Date.now()}`, name: fd.get('name').trim(), color: fd.get('color') });
                            toast('Categoría agregada');
                            close();
                            render();
                        });
                    },
                });
                break;
            case 'filterByCat':
                state.filters.products.category = actEl.dataset.cat;
                render();
                break;

            // --- Promociones ---
            case 'selectPromo':
                state.selectedPromoId = id;
                render();
                break;
            case 'goPromoEdit':
                state.selectedPromoId = id;
                navigate('promociones');
                break;
            case 'resetPromoPanel':
                render();
                toast('Cambios descartados', 'info');
                break;
            case 'deletePromo': {
                const promo = state.promotions.find(p => p.id === id);
                if (!promo) break;
                confirmModal('Eliminar promoción', `¿Eliminar "${promo.name}"?`, () => {
                    state.promotions = state.promotions.filter(p => p.id !== id);
                    if (state.selectedPromoId === id) state.selectedPromoId = state.promotions[0]?.id || null;
                    toast(`Promoción "${promo.name}" eliminada`, 'info');
                    render();
                });
                break;
            }
            case 'newPromoModal':
                openModal({
                    title: 'Crear nueva promoción',
                    body: `<form id="newPromoForm" class="form-grid">
                        <div class="field full"><label>Nombre de la promoción *</label><input class="input" name="name" required placeholder="Ej: Combo Tenebrios 15%"></div>
                        <div class="field"><label>Tipo</label><select class="input" name="type"><option>Por producto</option><option>Por envío</option><option>Por cantidad</option><option>General</option></select></div>
                        <div class="field"><label>Descuento *</label><input class="input" name="discount" required placeholder="Ej: 15% o 2x1"></div>
                        <div class="field full"><label>Producto</label><select class="input" name="product"><option>Todos los productos</option>${state.products.map(p => `<option>${esc(p.name)}</option>`).join('')}</select></div>
                        <div class="field"><label>Fecha inicio</label><input type="date" class="input" name="start" value="2025-08-25"></div>
                        <div class="field"><label>Fecha fin</label><input type="date" class="input" name="end" value="2025-09-15"></div>
                        <div class="field full"><label>Descripción</label><textarea class="input" name="desc"></textarea></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="newPromoForm">Crear promoción</button>`,
                    onMount: (m, close) => {
                        $('#newPromoForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            const newId = `PROM-${String(state.promotions.length + 1).padStart(3, '0')}`;
                            state.promotions.unshift({
                                id: newId,
                                name: fd.get('name').trim(),
                                subtitle: fd.get('product'),
                                product: fd.get('product'),
                                type: fd.get('type'),
                                discount: fd.get('discount').trim(),
                                start: fd.get('start'),
                                end: fd.get('end'),
                                status: 'Activa',
                                img: IMG.oferta1,
                                desc: fd.get('desc').trim() || 'Promoción vigente.',
                            });
                            state.selectedPromoId = newId;
                            toast(`Promoción "${fd.get('name').trim()}" creada`);
                            close();
                            if (state.view !== 'promociones') navigate('promociones');
                            else render();
                        });
                    },
                });
                break;

            // --- Pedidos ---
            case 'selectOrder':
                state.selectedOrderId = id;
                render();
                break;
            case 'goOrderDetail':
                state.selectedOrderId = id;
                navigate('pedidos');
                break;
            case 'clearOrderDetail':
                state.selectedOrderId = null;
                render();
                break;
            case 'cycleOrderStatus': {
                const o = state.orders.find(x => x.id === id);
                if (!o) break;
                const flow = ['Pendiente', 'Confirmado', 'En proceso', 'Entregado'];
                const next = flow[(flow.indexOf(o.status) + 1) % flow.length];
                persistOrderStatus(o, next);
                break;
            }
            case 'cancelOrder': {
                const o = state.orders.find(x => x.id === id);
                if (!o) break;
                confirmModal('Cancelar pedido', `¿Confirmas cancelar el pedido de ${o.customer}?`, () => {
                    persistOrderStatus(o, 'Cancelado');
                });
                break;
            }
            case 'editOrderModal': {
                const o = state.orders.find(x => x.id === id);
                if (!o) break;
                openModal({
                    title: `Editar pedido de ${o.customer}`,
                    body: `<form id="editOrderForm" class="form-grid">
                        <div class="field full"><label>Cliente</label><input class="input" name="customer" value="${esc(o.customer)}"></div>
                        <div class="field"><label>Teléfono</label><input class="input" name="phone" value="${esc(o.phone)}"></div>
                        <div class="field"><label>Método de pago</label><input class="input" name="payment" value="${esc(o.payment)}"></div>
                        <div class="field full"><label>Estado</label><select class="input" name="status">${['Pendiente', 'Confirmado', 'En proceso', 'Entregado', 'Cancelado'].map(s => `<option ${o.status === s ? 'selected' : ''}>${s}</option>`).join('')}</select></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="editOrderForm">Guardar</button>`,
                    onMount: (m, close) => {
                        $('#editOrderForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            o.customer = fd.get('customer').trim();
                            o.phone = fd.get('phone').trim();
                            o.payment = fd.get('payment').trim();
                            o.status = fd.get('status');
                            toast(`Pedido de ${o.customer} actualizado`);
                            close();
                            render();
                        });
                    },
                });
                break;
            }
            case 'newOrderModal':
                openModal({
                    title: 'Nuevo pedido manual',
                    body: `<form id="newOrderForm" class="form-grid">
                        <div class="field full"><label>Cliente *</label><input class="input" name="customer" required placeholder="Nombre del cliente"></div>
                        <div class="field"><label>Correo</label><input type="email" class="input" name="email" value="cliente@bio-gest.com"></div>
                        <div class="field"><label>Teléfono</label><input class="input" name="phone" value="+57 300 000 0000"></div>
                        <div class="field"><label>Producto</label><select class="input" name="productIdx">${state.products.map((p, i) => `<option value="${i}">${esc(p.name)} (${fmtCOP(p.price)})</option>`).join('')}</select></div>
                        <div class="field"><label>Cantidad</label><input type="number" min="1" class="input" name="qty" value="1"></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="newOrderForm">Crear pedido</button>`,
                    onMount: (m, close) => {
                        $('#newOrderForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            const prod = state.products[Number(fd.get('productIdx'))];
                            const qty = Math.max(1, Number(fd.get('qty')));
                            const newId = `PED-${String(19 + state.orders.length).padStart(4, '0')}`;
                            state.orders.unshift({
                                id: newId,
                                customer: fd.get('customer').trim(),
                                email: fd.get('email').trim(),
                                phone: fd.get('phone').trim(),
                                date: '25/08/2025',
                                time: 'Ahora',
                                status: 'Pendiente',
                                payment: 'Transferencia bancaria',
                                shipping: 0,
                                items: [{ name: prod.name, category: prod.category, qty, price: prod.price, img: prod.img }],
                                extraCount: 1,
                            });
                            state.selectedOrderId = newId;
                            toast('Pedido registrado correctamente');
                            close();
                            render();
                        });
                    },
                });
                break;
            case 'orderMenu':
                showFloatMenu(actEl, [
                    { icon: 'fa-rotate', label: 'Avanzar estado', onClick: () => triggerAction('cycleOrderStatus', id) },
                    { icon: 'fa-pen', label: 'Editar pedido', onClick: () => triggerAction('editOrderModal', id) },
                    { icon: 'fa-ban', label: 'Cancelar pedido', danger: true, onClick: () => triggerAction('cancelOrder', id) },
                ]);
                break;

            // --- Inventario ---
            case 'stepStock': {
                const input = $(`#qty-${id}`);
                if (!input) break;
                const delta = Number(actEl.dataset.delta);
                input.value = Math.max(0, Number(input.value || 0) + delta);
                break;
            }
            case 'applyStock': {
                const item = state.inventory.find(i => i.id === id);
                const input = $(`#qty-${id}`);
                if (!item || !input) break;
                const newVal = Math.max(0, Number(input.value || 0));
                const diff = newVal - item.stock;
                item.stock = newVal;
                item.updated = 'Actualizado ahora';
                if (diff !== 0) {
                    state.inventoryMovements.unshift({
                        date: 'Ahora',
                        species: item.species,
                        type: diff > 0 ? 'Entrada' : 'Salida',
                        qty: `${diff > 0 ? '+' : ''}${fmtNum(diff)}`,
                        reason: 'Ajuste manual en tabla',
                    });
                }
                toast(`Stock de ${item.species} actualizado a ${fmtNum(newVal)}`);
                render();
                break;
            }
            case 'editMinStock': {
                const item = state.inventory.find(i => i.id === id);
                if (!item) break;
                openModal({
                    title: `Stock mínimo · ${item.species}`,
                    body: `<form id="minStockForm" class="form-grid">
                        <div class="field full"><label>Stock mínimo permitido</label><input type="number" min="0" class="input" name="min" value="${item.minStock}" required></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="minStockForm">Guardar</button>`,
                    onMount: (m, close) => {
                        $('#minStockForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            item.minStock = Number(new FormData(ev.target).get('min'));
                            toast('Stock mínimo actualizado');
                            close();
                            render();
                        });
                    },
                });
                break;
            }
            case 'newMovementModal':
                openModal({
                    title: 'Registrar movimiento de inventario',
                    body: `<form id="movForm" class="form-grid">
                        <div class="field full"><label>Especie</label><select class="input" name="id">${state.inventory.map(i => `<option value="${i.id}">${esc(i.species)}</option>`).join('')}</select></div>
                        <div class="field"><label>Tipo</label><select class="input" name="type"><option value="Entrada">Entrada (+)</option><option value="Salida">Salida (-)</option></select></div>
                        <div class="field"><label>Cantidad *</label><input type="number" min="1" class="input" name="qty" required placeholder="Ej: 500"></div>
                        <div class="field full"><label>Motivo</label><input class="input" name="reason" placeholder="Ej: Cosecha, venta, merma..." required></div>
                    </form>`,
                    foot: `<button class="btn" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit" form="movForm">Registrar</button>`,
                    onMount: (m, close) => {
                        $('#movForm', m).addEventListener('submit', (ev) => {
                            ev.preventDefault();
                            const fd = new FormData(ev.target);
                            const item = state.inventory.find(i => i.id === fd.get('id'));
                            const qty = Number(fd.get('qty'));
                            const isIn = fd.get('type') === 'Entrada';
                            item.stock = Math.max(0, item.stock + (isIn ? qty : -qty));
                            item.updated = 'Actualizado ahora';
                            state.inventoryMovements.unshift({
                                date: 'Ahora',
                                species: item.species,
                                type: fd.get('type'),
                                qty: `${isIn ? '+' : '-'}${fmtNum(qty)}`,
                                reason: fd.get('reason').trim(),
                            });
                            toast('Movimiento registrado');
                            close();
                            render();
                        });
                    },
                });
                break;
            case 'movementHistoryModal':
                openModal({
                    title: 'Historial de movimientos',
                    wide: true,
                    body: `<div class="table-wrap"><table class="table compact">
                        <thead><tr><th>Fecha</th><th>Especie</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th></tr></thead>
                        <tbody>${state.inventoryMovements.map(m => `<tr>
                            <td class="nowrap muted">${esc(m.date)}</td>
                            <td class="strong">${esc(m.species)}</td>
                            <td>${statusBadge(m.type === 'Entrada' ? 'Activo' : 'Bajo')}</td>
                            <td class="mono strong">${esc(m.qty)}</td>
                            <td>${esc(m.reason)}</td>
                        </tr>`).join('')}</tbody>
                    </table></div>`,
                    foot: `<button class="btn" data-close-modal>Cerrar</button>`,
                });
                break;

            // --- Chats / Stats / Topbar ---
            case 'selectChat': {
                state.selectedChatId = id;
                const ch = state.chats.find(c => c.id === id);
                if (ch) ch.unread = 0;
                render();
                break;
            }
            case 'exportStats':
                toast('Reporte de estadísticas exportado (CSV)');
                break;
            case 'exportReportCsv': {
                const BOM = '\uFEFF';
                const csv = buildReportCsv(state.filters.reports);
                const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `reporte_ventas_tenebrios_gold_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(() => URL.revokeObjectURL(url), 0);
                toast('Reporte CSV descargado correctamente');
                break;
            }
            case 'notifications': {
                closeMenus();
                const rect = actEl.getBoundingClientRect();
                const pop = document.createElement('div');
                pop.className = 'float-menu notif-pop';
                pop.style.top = `${rect.bottom + 8}px`;
                pop.style.right = '24px';
                pop.innerHTML = `
                    <h4>Notificaciones</h4>
                    <ul>
                        ${state.notifications.map(n => `<li data-notif-id="${n.id}">
                            <i class="fa-solid ${n.icon}"></i>
                            <div class="grow">
                                <span class="${n.unread ? 'strong' : ''}">${esc(n.text)}</span>
                                <small class="muted">${esc(n.time)}</small>
                            </div>
                        </li>`).join('')}
                    </ul>
                `;
                document.body.appendChild(pop);
                pop.addEventListener('click', (ev) => {
                    const li = ev.target.closest('[data-notif-id]');
                    if (!li) return;
                    const notif = state.notifications.find(x => x.id === Number(li.dataset.notifId));
                    if (notif) { notif.unread = false; navigate(notif.view); }
                });
                break;
            }
            case 'profileMenu':
                showFloatMenu(actEl, [
                    { icon: 'fa-gear', label: 'Ajustes del sistema', onClick: () => navigate('ajustes') },
                    { icon: 'fa-store', label: 'Ir a la tienda pública', onClick: () => { window.location.href = '../'; } },
                    { icon: 'fa-right-from-bracket', label: 'Cerrar sesión', danger: true, onClick: logoutAdmin },
                ]);
                break;
        }
    });

    function triggerAction(action, id) {
        const fakeBtn = document.createElement('button');
        fakeBtn.dataset.action = action;
        if (id) fakeBtn.dataset.id = id;
        fakeBtn.hidden = true;
        document.body.appendChild(fakeBtn);
        fakeBtn.click();
        fakeBtn.remove();
    }

    function logoutAdmin() {
        const authKeys = [
            'biogest_usuario_sesion',
            'biogest_usuario_actual',
            'biogest_rol_sesion',
            'biogest_token',
            'biogest_auth_token',
            'auth_token',
            'access_token',
        ];
        [window.localStorage, window.sessionStorage].forEach(storage => {
            authKeys.forEach(key => storage.removeItem(key));
        });

        if (!window.BIOGEST_ADMIN_LOGOUT_URL) {
            window.location.href = window.BIOGEST_HOME_URL || '../';
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.BIOGEST_ADMIN_LOGOUT_URL;
        const csrfToken = $('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = csrfToken;
            form.appendChild(token);
        }
        document.body.appendChild(form);
        form.submit();
    }

    function showFloatMenu(anchor, items) {
        closeMenus();
        const rect = anchor.getBoundingClientRect();
        const menu = document.createElement('div');
        menu.className = 'float-menu';
        menu.style.top = `${rect.bottom + 6 + window.scrollY}px`;
        menu.style.left = `${Math.max(12, rect.right - 190)}px`;
        items.forEach((it, idx) => {
            const b = document.createElement('button');
            if (it.danger) b.className = 'danger';
            b.innerHTML = `<i class="fa-solid ${it.icon}"></i><span>${esc(it.label)}</span>`;
            b.addEventListener('click', () => { closeMenus(); it.onClick(idx); });
            menu.appendChild(b);
        });
        document.body.appendChild(menu);
    }

    // Buscador global en topbar
    $('#globalSearch').addEventListener('input', (e) => {
        state.globalQuery = e.target.value;
        const query = state.globalQuery.toLowerCase().trim();
        if (query.length > 1) {
            const hasProduct = state.products.some(p => `${p.id} ${p.name} ${p.category}`.toLowerCase().includes(query));
            const hasOrder = state.orders.some(o => `${o.number || ''} ${o.id} ${o.customer} ${o.email} ${o.items.map(item => item.name).join(' ')}`.toLowerCase().includes(query));
            state.view = hasProduct ? 'productos' : hasOrder ? 'pedidos' : 'usuarios';
        }
        render();
        $('#globalSearch').focus();
    });

    // Inicializar
    render();
    startLiveDataSync();
})();
