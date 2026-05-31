<?php
/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   ARCHIVO: admin_login.php
   PROPÓSITO: Página de autenticación para acceso al panel administrativo
   
   FUNCIONALIDADES:
   1. Verificar si el usuario ya está autenticado (redirigir a admin.php)
   2. Protección contra ataques CSRF con tokens generados por sesión
   3. Limitación de intentos fallidos (5 intentos máximo antes de bloquear)
   4. Validación de credenciales contra hash bcrypt almacenado
   5. Regeneración de ID de sesión tras login exitoso
   6. Interfaz responsiva con Tailwind CSS
   
   SEGURIDAD:
   - Tokens CSRF únicos por sesión
   - Password hashing con bcrypt
   - Rate limiting (protección fuerza bruta)
   - Session regeneration (previene session fixation)
   - Sanitización con htmlspecialchars()
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 1: CARGAR CONFIGURACIÓN Y INICIALIZAR SESIÓN
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
require_once 'config.php';  // Incluye credenciales admin, configuración de sesión, constantes de seguridad
session_start();            // Inicia sesión para acceder a variables almacenadas en $_SESSION

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 2: VERIFICAR SI YA ESTÁ AUTENTICADO
   Si el usuario ya tiene sesión activa, redirigir inmediatamente al panel administrativo
   
   LÓGICA:
   - $_SESSION['admin_logged_in'] se establece en el login exitoso
   - Si existe y no está vacía, el usuario ya está logeado
   - Evita que usuarios autenticados vean la página de login nuevamente
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
if (!empty($_SESSION['admin_logged_in'])) {
  header('Location: admin.php');  // Redirigir al panel administrativo
  exit;                            // Detener ejecución
}

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 3: INICIALIZAR VARIABLES DE CONTROL
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
$error     = '';                              // Variable para almacenar mensajes de error
$intentos  = $_SESSION['login_intentos'] ?? 0; // Contador de intentos fallidos (0 si no existe)
$bloqueado = $intentos >= 5;                  // Bloquear acceso si hay 5 o más intentos fallidos

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 4: GENERAR TOKEN CSRF (CROSS-SITE REQUEST FORGERY)
   
   ¿QUÉ ES UN ATAQUE CSRF?
   Un atacante crea un sitio malicioso con un formulario que intenta cambiar tu contraseña admin:
   <form action="https://miportafolio.com/admin_login.php" method="POST">
     <input name="usuario" value="admin" />
     <input name="clave" value="nuevacontraseña" />
     <input type="submit" />
   </form>
   
   Si visitas el sitio malicioso mientras tienes sesión activa, el navegador envía automáticamente
   cookies de sesión y el formulario se envía sin tu consentimiento.
   
   SOLUCIÓN: TOKEN CSRF
   - Generar un token aleatorio único POR SESIÓN
   - Incluir en formulario como campo oculto
   - Validar en server que el token del POST coincida con el almacenado en $_SESSION
   - El sitio malicioso no puede acceder a tokens de otra sesión
   
   DETALLES:
   - bin2hex(random_bytes(32)): Genera 64 caracteres hexadecimales (256 bits)
   - Se almacena en $_SESSION['csrf_token'] (lado servidor)
   - Se envía en formulario HTML como campo oculto
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Generar token de 64 caracteres
}

/* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
   PASO 5: PROCESAR FORMULARIO DE LOGIN
   Se ejecuta solo cuando el usuario envía el formulario (método POST)
   ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     VALIDACIÓN 1: VERIFICAR CSRF TOKEN
     Confirmar que la solicitud POST proviene del mismo sitio (prevenir CSRF)
     
     hash_equals($str1, $str2):
     - Comparación "timing-safe" (previene timing attacks)
     - Tarda el mismo tiempo independientemente de dónde falle la coincidencia
     - Retorna true solo si ambas cadenas son idénticas
     
     $_POST['csrf_token'] ?? '' :
     - Obtener el token del formulario o cadena vacía si no existe
     - El ?? (null coalescing) retorna el primer valor no nulo/no definido
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
  if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $error = 'Token de seguridad inválido. Recarga la página.';
  }
  /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     VALIDACIÓN 2: VERIFICAR INTENTOS FALLIDOS
     Si hay 5 o más intentos fallidos, bloquear la solicitud
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
  elseif ($bloqueado) {
    $error = 'Demasiados intentos fallidos. Reinicia la sesión.';
  }
  /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     VALIDACIONES PASARON: Proceder con autenticación
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
  else {
    /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       OBTENER CREDENCIALES DEL FORMULARIO
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
    $usuario = trim($_POST['usuario'] ?? '');  // Obtener usuario y eliminar espacios en blanco
    $clave   = $_POST['clave'] ?? '';          // Obtener contraseña (cadena vacía si no existe)

    /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       VALIDACIÓN 3: VERIFICAR CREDENCIALES
       Comparar contra las constantes definidas en config.php
       
       CONSTANTES EN config.php:
       - ADMIN_USER: 'admin'
       - ADMIN_PASS_HASH: hash bcrypt de la contraseña
       
       password_verify($plaintext, $hash):
       - Función bcrypt de PHP para verificar contraseñas
       - Compara la contraseña en texto plano contra el hash almacenado
       - Retorna true solo si coinciden
       - Es resistente a ataques rainbow table y timing attacks
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
    if ($usuario === ADMIN_USER && password_verify($clave, ADMIN_PASS_HASH)) {
      /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         ✅ CREDENCIALES CORRECTAS - CREAR SESIÓN AUTENTICADA
         ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
      
      /* session_regenerate_id(true):
         PREVENIR SESSION FIXATION ATTACK
         
         ¿QUÉ ES?
         Un atacante intenta forzar a la víctima a usar un ID de sesión conocido:
         1. Atacante visita login y obtiene PHPSESSID=abc123 en la URL
         2. Atacante envía enlace: miportafolio.com/admin_login.php?PHPSESSID=abc123
         3. Víctima hace click, PHP acepta el ID
         4. Víctima ingresa credenciales, sesión se autentica
         5. Atacante ahora puede usar PHPSESSID=abc123 para acceder como admin
         
         SOLUCIÓN:
         session_regenerate_id(true) genera un nuevo ID de sesión tras el login:
         - Invalida el ID anterior (el parámetro true lo borra)
         - Crea un nuevo ID único que el atacante no conoce
         - Los datos de sesión se transfieren al nuevo ID
      */
      session_regenerate_id(true);
      
      $_SESSION['admin_logged_in'] = true;  // Marcar como autenticado
      $_SESSION['admin_user']      = $usuario;  // Almacenar nombre de usuario
      $_SESSION['login_time']      = time();    // Registrar hora de login (para expiración)
      unset($_SESSION['login_intentos']);       // Limpiar contador de intentos
      
      header('Location: admin.php');  // Redirigir al panel
      exit;
    }
    /* ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       ❌ CREDENCIALES INCORRECTAS - INCREMENTAR INTENTO FALLIDO
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ */
    else {
      $_SESSION['login_intentos'] = $intentos + 1;  // Incrementar contador
      $error = 'Usuario o contraseña incorrectos.';
      
      /* PROTECCIÓN CONTRA FUERZA BRUTA:
         sleep(1) añade un retraso de 1 segundo por cada intento fallido
         - Ralentiza intentos automatizados de adivinanza de contraseña
         - Un atacante necesitaría ~50 segundos para 50 intentos
         - Los usuarios legítimos no notan este retraso
      */
      sleep(1);
    }
  }
}
?>

<!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     SECCIÓN: HTML - ESTRUCTURA Y ESTILOS
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->

<!DOCTYPE html>
<html lang="es">

<!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     HEAD: METADATOS Y RECURSOS
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
<head>
  <!-- Codificación de caracteres UTF-8 (soporta acentos y símbolos especiales) -->
  <meta charset="UTF-8" />
  
  <!-- Viewport para responsive design (mobile-friendly) -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Título de la página (aparece en pestaña del navegador) -->
  <title>Iniciar Sesión</title>

  <!-- Framework CSS: Tailwind (utilidades de diseño responsivo) -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Preconectar a Google Fonts para mejorar velocidad de carga -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  
  <!-- Fuentes tipográficas:
       - Playfair Display: Display serif para títulos
       - Plus Jakarta Sans: Fuente sans-serif para body/contenido
  -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Configuración personalizada de Tailwind CSS -->
  <script>
    /* TEMA PERSONALIZADO:
       Define colores, fuentes y otros ajustes específicos del diseño del portfolio
    */
    tailwind.config = {
      theme: {
        extend: {
          /* Familias de fuentes personalizadas */
          fontFamily: {
            display: ['"DM Serif Display"', 'serif'],      // Títulos elegantes
            body: ['"DM Sans"', 'sans-serif']              // Texto del cuerpo
          },
          /* Paleta de colores personalizada */
          colors: {
            base: '#E3E8E5',      // Color de fondo base
            surface: '#FCFCFA',   // Color de superficies (cards, contenedores)
            raised: '#F4F6F3',    // Color ligeramente elevado
            fg: '#0F1C16',        // Color de texto principal (foreground)
            accent: '#0E7A5A',    // Color de énfasis principal (verde)
            accent2: '#E8622A',   // Color de énfasis secundario (naranja)
            muted: '#6B7C74',     // Color de texto atenuado (secundario)
          },
        }
      },
    }
  </script>
  
  <!-- Estilos CSS personalizados adicionales -->
  <link rel="stylesheet" href="css/styles.css" />
</head>

<!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     BODY: CONTENIDO VISIBLE
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
<body class="bg-body text-fg min-h-screen flex items-center justify-center px-4 ">

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       FONDO DECORATIVO: Patrón de puntos
       Capa de fondo fija que cubre toda la pantalla
  ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <div class="fixed inset-0 bg-base"
    style="background-image: radial-gradient(rgba(14,122,90,.07) 1px, transparent 1px);
              background-size: 32px 32px;"></div>

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       CONTENEDOR PRINCIPAL: Card de login
       z-10: Posicionado encima del fondo decorativo
       max-w-sm: Ancho máximo de 384px (pequeño, para formulario)
       fade-up: Animación personalizada (definida en styles.css)
  ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <div class="relative z-10 w-full max-w-sm fade-up">

    <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         SECCIÓN: HEADER - LOGO Y TÍTULO
         ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <div class="text-center mb-8">
      <!-- Icono visual de candado (representando seguridad) -->
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-4
                  bg-accent/15 border border-accent/25">
        <!-- SVG: Icono de candado -->
        <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      
      <!-- Título principal -->
      <h1 class="font-display text-3xl text-fg">Panel de Seguridad</h1>
      
      <!-- Subtítulo -->
      <p class="text-muted text-sm mt-1 font-body">Acceso restringido</p>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         SECCIÓN: TARJETA DEL FORMULARIO
         Contenedor principal para el formulario de login con borde y relleno
    ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <div class="bg-surface border border-green-900/10 rounded-3xl p-8">

      <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
           MOSTRAR ERRORES: Si hay mensaje de error, mostrarlo en alerta roja
      ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
      <?php if ($error): ?>
        <div class="alert alert-err mb-5 text-sm">
          <span>⚠️</span>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
           CONDICIONAL: Mostrar estado bloqueado O mostrar formulario
      ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
      
      <?php if ($bloqueado): ?>
        <!-- ESTADO: Acceso bloqueado por intentos fallidos -->
        <div class="text-center text-muted text-sm font-body">
          <p class="mb-4">Demasiados intentos fallidos.</p>
          <a href="admin_login.php?reset=1" class="text-accent underline">Reiniciar intentos</a>
        </div>
      <?php else: ?>
        <!-- ESTADO: Formulario disponible -->
        <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
             FORMULARIO DE LOGIN
             Método POST para enviar credenciales de forma segura
        ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <form method="POST" action="admin_login.php">
          <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
               CAMPO OCULTO: Token CSRF
               Prevenir ataques Cross-Site Request Forgery
               htmlspecialchars() convierte caracteres especiales a entidades HTML para seguridad
          ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />

          <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
               CAMPO 1: USUARIO
               Usuario administrativo a ingresar
          ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
          <div class="mb-5">
            <label for="usuario" class="block text-sm font-medium text-fg mb-2 font-body">Usuario</label>
            <input type="text" id="usuario" name="usuario"
              required autocomplete="username" required: campo obligatorio, autocomplete: completar automático con sugerencias del navegador
              class="form-input"
              placeholder="admin" />
          </div>

          <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
               CAMPO 2: CONTRASEÑA
               Contraseña de acceso administrativo
          ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
          <div class="mb-7">
            <label for="clave" class="block text-sm font-medium text-fg mb-2 font-body">Contraseña</label>
            <input type="password" id="clave" name="clave"
              required autocomplete="current-password" type="password" oculta los caracteres escritos para mayor seguridad, autocomplete: completar automático con sugerencias del navegador
              class="form-input"
              placeholder="••••••••" />
          </div>

          <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
               BOTÓN SUBMIT: Ingresar
               Envía el formulario con credenciales al servidor para validación
          ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
          <button type="submit" class="btn-primary w-full">
            Ingresar
            <!-- Icono de flecha (indicando dirección) -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </button>
        </form>

        <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
             INDICADOR: Contador de intentos fallidos
             Muestra al usuario cuántos intentos le quedan antes de ser bloqueado
             Solo se muestra si hay al menos 1 intento fallido previo
        ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <?php if ($intentos > 0): ?>
          <p class="text-center text-xs text-muted mt-4 font-body">
            Intentos fallidos: <?= $intentos ?>/5
          </p>
        <?php endif; ?>

      <?php endif; ?>

    </div>

    <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         ENLACE: Volver al sitio principal
         Link para usuarios que llegaron por accidente o quieren volver al portfolio
    ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <p class="text-center text-btn-primary text-muted mt-6 font-body">
      <a href="index.php" class="hover:text-accent transition-colors">← Volver al sitio</a>
    </p>
  </div>

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       UTILIDAD: Endpoint para resetear intentos fallidos
       
       SOLO PARA DESARROLLO:
       En producción, este endpoint debería requerir verificación adicional
       (email de confirmación, preguntas de seguridad, etc.)
       
       CÓMO FUNCIONA:
       - Si el usuario visita ?reset=1, se limpia el contador de intentos
       - Permite intentar login nuevamente sin esperar
       - Útil para testing y para usuarios bloqueados
  ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <?php
  if (isset($_GET['reset'])) {
    unset($_SESSION['login_intentos']);  // Eliminar contador de intentos
    header('Location: admin_login.php'); // Redirigir para limpiar URL
    exit;
  }
  ?>
</body>

</html>