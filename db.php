<?php
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ARCHIVO DE CONEXIÓN A BASE DE DATOS (db.php)
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Este archivo centraliza la configuración y conexión a la base de datos MySQL
// Se incluye en otros archivos (require_once 'db.php') para reutilizar la conexión
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 1: DEFINIR CREDENCIALES DE CONEXIÓN
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────

// Dirección del servidor MySQL (localhost = máquina local)
// Si la BD estuviera en otro servidor, se pondría su IP o dominio (ej: "192.168.1.100", "db.example.com")

// Detectar el entono de desarrollo o producción para usar credenciales adecuadas
$es_local = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    if($es_local) {
        // Entorno de desarrollo (XAMPP local)
    $host = "localhost";

    // Usuario de MySQL que tiene permisos en la base de datos 'portafolio'
    // Este usuario debe estar creado en MySQL con sus permisos correspondientes
    $usuario = "portafolio_user";

    // Contraseña del usuario de MySQL
    // IMPORTANTE: En producción, guardar esto en un archivo .env o variable de entorno
    $password = "a1d2m3i@n#";

    // Nombre de la base de datos a la que conectarse
    // Debe existir previamente en el servidor MySQL
    $base_datos = "portafolio";
} else {
    // Entorno de producción (hosting gratuito o servidor externo)
    $host = "sql302.infinityfree.com";
    $usuario = "if0_42065043";
    $password = "SIwmfFjiBAHGv";
    $base_datos = "if0_42065043_web_portafolio";
}
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 2: CREAR CONEXIÓN A LA BASE DE DATOS
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// MySQLi (MySQL Improved) es una extensión mejorada de PHP para trabajar con MySQL
// Parámetros: (host, usuario, contraseña, nombre_base_datos)
if (!isset($conexion)) {
    $conexion = new mysqli($host, $usuario, $password, $base_datos);

    // ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    // PASO 3: VERIFICAR ERRORES DE CONEXIÓN
    // ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    // Si la conexión falla, $conexion->connect_error contendrá el mensaje de error
    if ($conexion->connect_error) {
        // die() detiene la ejecución del script y muestra el mensaje de error
        // Mensajes posibles:
        // - "Access denied for user 'portafolio_user'@'localhost'": contraseña incorrecta
        // - "Unknown database 'portafolio'": la BD no existe
        // - "Can't connect to MySQL server": servidor MySQL no está corriendo
        die("❌ Error de conexión: " . $conexion->connect_error);
    }

    // ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    // PASO 4: CONFIGURAR CONJUNTO DE CARACTERES UTF-8
    // ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
    // set_charset("utf8mb4") configura:
    // - utf8mb4: soporta caracteres especiales (emojis, acentos, ñ, etc.)
    // - Evita problemas de codificación al almacenar/recuperar datos
    // Ejemplo: Sin esto, "María" podría mostrarse como "Mar??a"
    $conexion->set_charset("utf8mb4");
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// NOTAS IMPORTANTES DE SEGURIDAD
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// 1. CREDENCIALES: No guardar credenciales en el código fuente
//    → Usar archivos .env o variables de entorno en producción
// 2. PREPARED STATEMENTS: Siempre usar prepared statements para queries
//    → Previene inyecciones SQL
// 3. PERMISOS: El usuario 'portafolio_user' debe tener solo los permisos necesarios
//    → No usar usuario 'root' para aplicaciones web
// 4. ERRORES: En producción, no mostrar detalles técnicos de errores
//    → Mostrar mensaje genérico al usuario, guardar errores en logs
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════

?>
