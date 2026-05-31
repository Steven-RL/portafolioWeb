<?php
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PROCESADOR DEL FORMULARIO DE CONTACTO (process.php)
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Archivo encargado de:
// 1. Validar datos del formulario en el servidor (verificación adicional de seguridad)
// 2. Sanear los datos para evitar inyecciones
// 3. Guardar el mensaje en la base de datos
// 4. Redirigir al usuario con mensajes de éxito o error
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════

// Incluye el archivo de conexión a la base de datos
require_once 'db.php';
// Inicia la sesión para usar variables de sesión (flash messages)
session_start();

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Validar formato del nombre
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
function validar_nombre($nombre) {
    // No permite: números, caracteres especiales, símbolos
    return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre);
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// VALIDACIÓN 0: Verificar método HTTP
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Solo procesamos formularios enviados con POST
// Si se intenta acceder por GET u otro método, se redirige al formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PASO 1: OBTENCIÓN Y SANEAMIENTO DE DATOS
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Recupera los datos del POST, los limpia de espacios y elimina etiquetas HTML/PHP
// Utiliza el operador ?? para asignar cadena vacía si el valor no existe

$nombre  = trim(strip_tags($_POST['nombre']  ?? ''));
// ├─ trim(): elimina espacios al inicio y final
// ├─ strip_tags(): elimina cualquier etiqueta HTML/PHP
// └─ ?? '': usa cadena vacía si no existe el índice

$correo  = trim(strip_tags($_POST['correo']  ?? ''));
// Mismo tratamiento para el correo electrónico

$mensaje = trim(strip_tags($_POST['mensaje'] ?? ''));
// Mismo tratamiento para el mensaje

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PASO 2: VALIDACIÓN DE DATOS EN EL SERVIDOR
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Array para almacenar los mensajes de error encontrados
$errores = [];

// ─── VALIDACIÓN DEL NOMBRE ───
// Verifica que el nombre solo contenga letras, acentos y espacios
if (!validar_nombre($nombre)) {
    $errores[] = 'El nombre solo puede contener letras, espacios, guiones o apóstrofes.';
}
// Verifica que el nombre tenga longitud válida (2-100 caracteres)
elseif(strlen($nombre) < 2 || strlen($nombre) > 100) {
    $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
}

// ─── VALIDACIÓN DEL CORREO ───
// filter_var() con FILTER_VALIDATE_EMAIL valida el formato del email
// También verifica que no exceda 150 caracteres
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 150) {
    $errores[] = 'El correo electrónico no es válido.';
}

// ─── VALIDACIÓN DEL MENSAJE ───
// Verifica que el mensaje tenga longitud válida (10-1000 caracteres)
if (strlen($mensaje) < 10 || strlen($mensaje) > 1000) {
    $errores[] = 'El mensaje debe tener entre 10 y 1000 caracteres.';
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PASO 3: MANEJO DE ERRORES DE VALIDACIÓN
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Si se encontraron errores de validación:
if (!empty($errores)) {
    // Crea un mensaje flash con todos los errores concatenados
    $_SESSION['flash'] = [
        'tipo' => 'error',
        // implode() une los errores con ' | ' como separador
        'texto' => implode(' | ', $errores)
    ];
    
    // Guarda los datos del formulario en sesión para repoblar los campos
    // (permite que el usuario vea qué escribió y corrija errores)
    $_SESSION['form_data'] = [
        'nombre'  => $nombre,
        'correo'  => $correo,
        'mensaje' => $mensaje
    ];
    
    // Redirige al formulario de contacto
    header('Location: contact.php');
    exit;  // Detiene la ejecución del script
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PASO 4: INSERCIÓN EN LA BASE DE DATOS
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Utiliza un bloque try-catch para manejar excepciones de base de datos
try {
    // ─── PREPARAR CONSULTA SQL ───
    // Usa prepared statements (?) para evitar inyecciones SQL
    // Las ? son placeholders que se reemplazan con los valores reales
    $stmt = $conexion->prepare("INSERT INTO contactos (nombre, correo, mensaje) VALUES (?, ?, ?)");
    
    // ─── BINDEAR PARÁMETROS ───
    // bind_param() vincula variables a los placeholders
    // 'sss' significa: string, string, string (los 3 parámetros son texto)
    $stmt->bind_param('sss', $nombre, $correo, $mensaje);
    
    // ─── EJECUTAR CONSULTA ───
    // Realiza la inserción en la tabla 'contactos'
    $stmt->execute();
    
    // ─── LIBERAR RECURSOS ───
    // Cierra el statement para liberar memoria
    $stmt->close();

    // ─── MENSAJE DE ÉXITO ───
    // Crea un mensaje flash personalizado con el nombre del usuario
    $_SESSION['flash'] = [
        'tipo' => 'ok',
        'texto' => "¡Gracias, {$nombre}! Tu mensaje fue recibido correctamente."
    ];
    
    // Redirige al formulario con el mensaje de éxito
    header('Location: contact.php');
    exit;

// ─── CAPTURA DE EXCEPCIONES ───
} catch (Exception $e) {
    // Registra el error en el log del servidor (visible solo para admins)
    // Formato: [Contacto] Descripción del error
    error_log('[Contacto] ' . $e->getMessage());

    // Mensaje genérico para el usuario (sin detalles técnicos por seguridad)
    $_SESSION['flash'] = [
        'tipo' => 'error',
        'texto' => 'Ocurrió un problema al guardar tu mensaje. Intenta de nuevo más tarde.'
    ];
    
    // Redirige al formulario con el mensaje de error
    header('Location: contact.php');
    exit;
}

?>
