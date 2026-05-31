<?php
/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   ARCHIVO: admin_logout.php
   PROPÓSITO: Cerrar la sesión del administrador de forma segura
   
   Este archivo maneja el cierre de sesión del panel administrativo. Destruye toda la información de sesión,
   invalida la cookie de sesión y redirige al usuario a la página de login.
   
   FLUJO:
   1. Inicia la sesión para acceder a las variables almacenadas
   2. Limpia todas las variables de sesión ($_SESSION)
   3. Destruye completamente la sesión en el servidor
   4. Invalida la cookie de sesión en el navegador del cliente
   5. Redirige a la página de login con un parámetro de confirmación
   
   SEGURIDAD:
   - session_unset() elimina todas las variables de sesión
   - session_destroy() destruye la sesión en el servidor
   - Invalidar la cookie previene reutilización del mismo ID de sesión
   - exit detiene la ejecución para asegurar que se ejecute la redirección
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 1: INICIAR SESIÓN
   Accede a la sesión actual del usuario logeado
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
session_start();

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 2: LIMPIAR VARIABLES DE SESIÓN
   Elimina todas las variables almacenadas en $_SESSION (incluye admin_logged_in, login_time, csrf_admin, etc.)
   
   DIFERENCIA IMPORTANTE:
   - session_unset(): Vacía todas las variables de $_SESSION pero NO destruye la sesión en el servidor
   - session_destroy(): Destruye la sesión completamente en el servidor
   
   Combinamos ambas para asegurar limpieza completa
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
session_unset();

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 3: DESTRUIR LA SESIÓN EN EL SERVIDOR
   Elimina completamente el archivo/registro de sesión del servidor
   
   UBICACIÓN DEL ALMACENAMIENTO:
   Por defecto, las sesiones de PHP se almacenan en /tmp (Linux) o directorio temporal del sistema
   Ejemplo de nombre de sesión: sess_a1b2c3d4e5f6g7h8i9j0
   
   Al destruir la sesión, se elimina el archivo servidor, haciendo imposible restaurarla
   incluso si el cliente intenta reutilizar el ID de sesión
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
session_destroy();

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 4: INVALIDAR LA COOKIE DE SESIÓN EN EL CLIENTE
   
   ¿POR QUÉ ESTO ES IMPORTANTE?
   Aunque hemos destruido la sesión en el servidor, el navegador del cliente aún tiene la cookie con el ID de sesión.
   
   ATAQUE POTENCIAL:
   1. Usuario está logeado (session_id = "abc123xyz")
   2. Se ejecuta logout pero se olvida limpiar la cookie del cliente
   3. Si el servidor no valida correctamente, el cliente podría reutilizar "abc123xyz" en futuras solicitudes
   
   SOLUCIÓN:
   Invalidar la cookie enviando una con fecha de expiración pasada (time() - 42000)
   Esto obliga al navegador a eliminarla
   
   DETALLES TÉCNICOS:
   - session_get_cookie_params(): Obtiene configuración de la cookie actual (domain, path, secure, httponly)
   - setcookie(): Sobrescribe la cookie con fecha expirada
   - Incluir los mismos parámetros de path, domain, secure y httponly asegura que se sobrescriba la cookie correcta
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */

// Validar que las cookies estén habilitadas en la configuración
if (ini_get("session.use_cookies")) {
    // Obtener los parámetros originales de la cookie de sesión
    $params = session_get_cookie_params();
    
    // Sobrescribir la cookie con una fecha expirada para que el navegador la elimine
    setcookie(
        session_name(),           // Nombre de la cookie (por defecto: PHPSESSID)
        '',                       // Valor vacío
        time() - 42000,           // Expiración: 42000 segundos en el pasado (~11.6 horas)
        $params["path"],          // Ruta: mismo valor que la cookie original
        $params["domain"],        // Dominio: mismo valor que la cookie original
        $params["secure"],        // HTTPS only: mismo valor que la cookie original
        $params["httponly"]       // HTTP only: mismo valor que la cookie original (previene acceso por JavaScript)
    );
}

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 5: REDIRIGIR AL LOGIN
   
   Después de limpiar la sesión, enviamos al usuario de vuelta a la página de login
   
   PARÁMETRO ?logout=1
   - Permite a admin_login.php mostrar un mensaje de "Sesión cerrada correctamente"
   - El usuario verá confirmación visual de que el logout fue exitoso
   - Ayuda a evitar confusiones sobre si la acción se completó
   
   HEADER LOCATION
   - Realiza redirección HTTP 302 (redirección temporal)
   - El navegador automáticamente sigue a la nueva URL
   
   EXIT
   - Detiene la ejecución del script inmediatamente
   - Asegura que no se envíe código HTML o PHP adicional al navegador
   - Previene conflictos con output antes de la redirección
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
header('Location: admin_login.php?logout=1');

// Detener la ejecución para asegurar que la redirección se procese correctamente
exit;
