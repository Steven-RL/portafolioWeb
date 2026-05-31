<?php
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// PANEL DE ADMINISTRACIÓN - GESTOR DE MENSAJES (admin.php)
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Este archivo muestra un panel protegido con:
// - Autenticación requerida (solo admin)
// - Visualización de mensajes de contacto recibidos
// - Búsqueda y filtrado por fecha
// - Paginación de resultados
// - Eliminación de mensajes con protección CSRF
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════

// Incluye configuración (credenciales admin y parámetros de seguridad)
require_once 'config.php';
// Incluye conexión a base de datos
require_once 'db.php';
// Inicia sesión para verificar si el usuario está logueado
session_start();

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// VERIFICACIÓN 1: AUTENTICACIÓN REQUERIDA
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Si no está logueado (la variable de sesión 'admin_logged_in' no existe),
// redirige al formulario de login
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: admin_login.php');
  exit;
}

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// VERIFICACIÓN 2: EXPIRACIÓN DE SESIÓN
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Calcula el tiempo transcurrido desde el último login/actividad
// Si excede SESSION_LIFETIME (1800 segundos = 30 minutos), cierra la sesión
if ((time() - ($_SESSION['login_time'] ?? 0)) > SESSION_LIFETIME) {
  session_destroy();  // Elimina todos los datos de sesión
  header('Location: admin_login.php?expired=1');  // Redirige con parámetro de expiración
  exit;
}

// Actualiza el timestamp de la última actividad
$_SESSION['login_time'] = time();

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// VERIFICACIÓN 3: TOKEN CSRF (Cross-Site Request Forgery)
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Genera un token único por sesión para prevenir ataques CSRF
// El token se incluye en todos los formularios y se valida antes de procesar
if (empty($_SESSION['csrf_admin']))
  $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));  // Genera 64 caracteres aleatorios

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PROCESAMIENTO: ELIMINACIÓN DE MENSAJES
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Solo procesa si se envió un formulario POST con intención de eliminar
$accion_msg = '';  // Variable para almacenar el mensaje de éxito/error
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
  // Valida que el token CSRF enviado coincida con el almacenado en sesión
  // hash_equals() compara sin ser vulnerable a timing attacks
  if (!hash_equals($_SESSION['csrf_admin'], $_POST['csrf_admin'] ?? '')) {
    $accion_msg = 'error:Token de seguridad inválido.';
  } else {
    // Token válido, procede a eliminar el mensaje
    $id   = (int) $_POST['eliminar_id'];  // Convierte a entero para evitar inyección SQL
    $stmt = $conexion->prepare("DELETE FROM contactos WHERE id = ?");
    $stmt->bind_param('i', $id);  // 'i' = integer
    $stmt->execute();
    // affected_rows retorna cuántas filas se eliminaron (0 si no existía)
    $affected = $stmt->affected_rows;
    $stmt->close();
    $accion_msg = $affected > 0
      ? 'ok:Mensaje eliminado correctamente.'
      : 'error:No se encontró el mensaje.';
  }
}

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// CONFIGURACIÓN DE PAGINACIÓN
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Define cuántos mensajes mostrar por página
$por_pagina = 10;
// Obtiene el número de página del URL (?p=2), con validación (mínimo 1)
$pagina     = max(1, (int)($_GET['p'] ?? 1));
// Calcula el desplazamiento (OFFSET) para la consulta SQL
// Página 1: offset=0, Página 2: offset=10, Página 3: offset=20, etc.
$offset     = ($pagina - 1) * $por_pagina;

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// OBTENCIÓN DE PARÁMETROS DE BÚSQUEDA Y FILTRO
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Búsqueda por palabras clave en nombre, correo o mensaje
$busqueda      = trim($_GET['q'] ?? '');
$fecha_desde   = $_GET['fecha_desde'] ?? '';
$fecha_hasta   = $_GET['fecha_hasta'] ?? '';
// Prepara la búsqueda para usar en LIKE (% al inicio y final)
// LIKE '%palabra%' encuentra la palabra en cualquier posición
$busqueda_like = '%' . $busqueda . '%';

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// CONSTRUCCIÓN DINÁMICA DE LA CLÁUSULA WHERE
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// En lugar de escribir una consulta fija, se construye dinámicamente según los filtros
// Esto permite combinar búsqueda + filtro de fechas sin duplicar código
$where = [];     // Array con las condiciones WHERE
$params = [];    // Array con los valores a bindear
$types = "";     // String con los tipos de datos (s=string, i=int, etc.)

// Si hay búsqueda, agrega condiciones LIKE para nombre, correo y mensaje
if ($busqueda !== '') {
    $where[] = "(nombre LIKE ? OR correo LIKE ? OR mensaje LIKE ?)";
    $params[] = $busqueda_like;
    $params[] = $busqueda_like;
    $params[] = $busqueda_like;
    $types .= "sss";
}

// Si hay fecha desde, filtra mensajes posteriores a esa fecha
if ($fecha_desde !== '') {
    $where[] = "DATE(fecha_registro) >= ?";
    $params[] = $fecha_desde;
    $types .= "s";
}

// Si hay fecha hasta, filtra mensajes anteriores a esa fecha
if ($fecha_hasta !== '') {
    $where[] = "DATE(fecha_registro) <= ?";
    $params[] = $fecha_hasta;
    $types .= "s";
}

// Construye la cláusula WHERE completa
// Si no hay filtros: $where_sql = ""
// Si hay filtros: $where_sql = "WHERE condicion1 AND condicion2 AND ..."
$where_sql = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 1: OBTENER TOTAL DE REGISTROS FILTRADOS
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Se ejecuta una consulta COUNT para saber cuántos registros hay en total
// (necesario para calcular el número de páginas)
$sql_count = "SELECT COUNT(*) FROM contactos $where_sql";
$stmtCount = $conexion->prepare($sql_count);
// Si hay parámetros de filtro, los bindea
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);  // ... desempaqueta el array
}
$stmtCount->execute();
// fetch_row() retorna un array con el resultado: [42] (por ejemplo)
$total = $stmtCount->get_result()->fetch_row()[0];  // [0] obtiene el primer (único) elemento
$stmtCount->close();

// Calcula el número total de páginas
// ceil() redondea hacia arriba: 42 mensajes / 10 por página = 4.2 → 5 páginas
$total_paginas = max(1, (int)ceil($total / $por_pagina));

// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// PASO 2: OBTENER MENSAJES CON PAGINACIÓN Y FILTROS
// ────────────────────────────────────────────────────────────────────────────────────────────────────────────────
// Consulta que obtiene los mensajes para la página actual, con búsqueda/filtros aplicados
$sql = "SELECT id, nombre, correo, mensaje, fecha_registro
        FROM contactos
        $where_sql
        ORDER BY fecha_registro DESC
        LIMIT ? OFFSET ?";  // LIMIT 10 OFFSET 0 para página 1, OFFSET 10 para página 2, etc.

$stmt = $conexion->prepare($sql);

// Combina los parámetros de filtro (búsqueda + fechas) con los de paginación (LIMIT + OFFSET)
$params_paginacion = $params;  // Copia los parámetros de filtro
$params_paginacion[] = $por_pagina;  // Agrega LIMIT
$params_paginacion[] = $offset;      // Agrega OFFSET
$types_paginacion = $types . "ii"; // Agrega tipos de datos para LIMIT y OFFSET (ambos integers)

// Bindea todos los parámetros en el orden correcto
$stmt->bind_param($types_paginacion, ...$params_paginacion);
$stmt->execute();
// fetch_all() retorna todos los resultados como array de arrays asociativos
// MYSQLI_ASSOC = cada fila es un array asociativo (clave => valor)
$mensajes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Procesa el mensaje de acción (éxito/error) en dos partes
// explode() divide el string 'ok:Mensaje aquí' → ['ok', 'Mensaje aquí']
[$tipo_msg, $texto_msg] = $accion_msg ? explode(':', $accion_msg, 2) : ['', ''];
?>

<!DOCTYPE html>
<html lang="es">

<!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     SECCIÓN: ENCABEZADO (HEAD)
     Meta tags, Tailwind CSS, Google Fonts y configuración de estilos
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
<head>
  <!-- Meta tags para codificación y responsive design -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin | Mensajes</title>

  <!-- Framework Tailwind CSS para estilos utility-first -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts: Playfair Display y Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Configuración personalizada de Tailwind CSS -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          // Fuentes personalizadas
          fontFamily: {
            display: ['"DM Serif Display"', 'serif'],
            body: ['"DM Sans"', 'sans-serif']
          },
          // Paleta de colores personalizada
          colors: {
            base: '#EEF2F0',      // Fondo principal
            surface: '#FCFCFA',   // Fondo de secciones
            raised: '#F4F6F3',    // Fondo elevado
            fg: '#0F1C16',        // Texto principal
            accent: '#0E7A5A',    // Color primario (verde)
            accent2: '#E8622A',   // Color secundario (naranja)
            muted: '#6B7C74',     // Texto atenuado
          },
        }
      },
    }
  </script>
  <!-- Estilos personalizados adicionales -->
  <link rel="stylesheet" href="css/styles.css" />
</head>

<!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
     SECCIÓN: CUERPO DE LA PÁGINA
     Estructura con navegación, contenido principal y footer
     ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
<body class="bg-base text-fg min-h-screen flex flex-col">

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: NAVEGACIÓN
       Barra superior con logo, título, menú y opciones de usuario
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <nav class="sticky top-0 z-50 bg-base/90 backdrop-blur border-b border-green-900/10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <!-- Sección izquierda: Logo y título del panel -->
      <div class="flex items-center gap-3">
        <!-- Icono del panel -->
        <div class="w-8 h-8 rounded-lg bg-accent/15 border border-accent/25 flex items-center justify-center">
          <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <!-- Título del panel -->
        <span class="font-display italic text-lg text-fg">Panel de Seguridad</span>
        <!-- Badge indicador -->
        <span class="badge">Mensajes</span>
      </div>

      <!-- Sección derecha: Menú y opciones de usuario -->
      <div class="flex items-center gap-6 text-sm font-body">
        <!-- Usuario logueado (solo visible en pantallas medianas) -->
        <span class="text-black hidden sm:block">
          👤 <?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?>
        </span>

        <!-- Enlace a página de inicio -->
        <a href="index.php" class="group flex flex-col items-start">
            <span>Ver sitio</span>
            <span class="line-draw"></span></a>

        <!-- Enlace a formulario de contacto -->
        <a href="contact.php" class="group flex flex-col items-start">
            <span>Contacto</span>
            <span class="line-draw"></span>
        </a>

        <!-- Botón de cerrar sesión -->
        <a href="admin_logout.php"
          class="flex items-center gap-2 text-muted hover:text-red-400 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Cerrar sesión
        </a>

      </div>
    </div>
  </nav>

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: CONTENIDO PRINCIPAL
       Encabezado, alertas, formulario de búsqueda y tabla de mensajes
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <main class="flex-1 max-w-6xl mx-auto w-full px-6 py-10">

    <!-- Encabezado con título y contador de mensajes -->
    <div class="mb-8 fade-up">
      <h1 class="font-display text-4xl text-fg mb-1">Mensajes recibidos</h1>
      <p class="text-muted font-body text-sm">
        <?= $total ?> mensaje<?= $total !== 1 ? 's' : '' ?> en total
      </p>
    </div>

    <!-- Alerta de acción (éxito o error) -->
    <!-- Se muestra solo si hubo una acción previa (eliminar, etc.) -->
    <?php if ($texto_msg): ?>
      <div class="alert <?= $tipo_msg === 'ok' ? 'alert-ok' : 'alert-err' ?> mb-6 text-sm">
        <span><?= $tipo_msg === 'ok' ? '✅' : '⚠️' ?></span>
        <span><?= htmlspecialchars($texto_msg) ?></span>
      </div>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         FORMULARIO DE BÚSQUEDA Y FILTRADO
         ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <form method="GET" action="admin.php" class="mb-6 fade-up-delay-1">
      <!-- Campo de búsqueda por palabras clave -->
      <div class="flex gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
          placeholder="Buscar por nombre, correo o mensaje..."
          class="form-input flex-1" />
        <!-- Botón para ejecutar búsqueda -->
        <button type="submit" class="btn-primary px-5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Buscar
        </button>
        <!-- Botón para limpiar búsqueda (solo si hay búsqueda activa) -->
        <?php if ($busqueda): ?>
          <a href="admin.php" class="btn-outline px-4">✕ Limpiar</a>
        <?php endif; ?>
      </div>

      <!-- Filtros de fecha (rango) -->
      <div class="flex flex-wrap gap-3 mt-3 items-end justify-center">
        <!-- Campo fecha desde -->
        <div>
          <label class="block text-xs font-medium text-muted mb-1">Desde</label>
          <input type="date" name="fecha_desde" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>" class="form-input text-sm py-2">
        </div>
        <!-- Campo fecha hasta -->
        <div>
          <label class="block text-xs font-medium text-muted mb-1">Hasta</label>
          <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>" class="form-input text-sm py-2">
        </div>
        <!-- Botones de filtrado -->
        <div>
          <button type="submit" class="btn-primary text-sm px-4 py-2">Filtrar</button>
          <!-- Botón limpiar fechas (solo si hay filtro de fecha) -->
          <?php if (isset($_GET['fecha_desde']) || isset($_GET['fecha_hasta'])): ?>
            <a href="admin.php" class="btn-outline text-sm px-4 py-2 ml-2">Limpiar fechas</a>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
         TABLA DE MENSAJES
         ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <div class="bg-surface border border-green-900/10 rounded-2xl overflow-hidden fade-up-delay-1">

      <!-- Mensaje cuando no hay mensajes -->
      <?php if (empty($mensajes)): ?>
        <div class="py-20 text-center text-muted font-body">
          <div class="text-5xl mb-4">📭</div>
          <p class="font-medium text-fg">No hay mensajes<?= $busqueda ? ' que coincidan con la búsqueda' : ' aún' ?></p>
          <p class="text-sm mt-1">Los mensajes del formulario de contacto aparecerán aquí</p>
        </div>

      <!-- Tabla con los mensajes -->
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="admin-table">
            <!-- Encabezado de la tabla -->
            <thead>
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Mensaje</th>
                <th>Fecha</th>
                <th>Acción</th>
              </tr>
            </thead>
            <!-- Cuerpo de la tabla: Filas con datos de mensajes -->
            <tbody>
              <?php foreach ($mensajes as $msg): ?>
                <tr>
                  <!-- ID del mensaje -->
                  <td class="text-muted text-xs"><?= (int)$msg['id'] ?></td>
                  <!-- Nombre del remitente -->
                  <td class="font-medium text-fg"><?= htmlspecialchars($msg['nombre']) ?></td>
                  <!-- Email (con enlace mailto) -->
                  <td>
                    <a href="mailto:<?= htmlspecialchars($msg['correo']) ?>"
                      class="text-accent hover:text-accent2 transition-colors text-sm">
                      <?= htmlspecialchars($msg['correo']) ?>
                    </a>
                  </td>
                  <!-- Contenido del mensaje (con saltos de línea preservados) -->
                  <td class="msg-cell text-fg/80 text-sm">
                    <?= nl2br(htmlspecialchars($msg['mensaje'])) ?>
                  </td>
                  <!-- Fecha y hora de recepción -->
                  <td class="text-muted text-xs whitespace-nowrap">
                    <?= date('d/m/Y', strtotime($msg['fecha_registro'])) ?><br />
                    <span class="text-muted/60"><?= date('H:i', strtotime($msg['fecha_registro'])) ?></span>
                  </td>
                  <!-- Botón para eliminar mensaje -->
                  <td>
                    <form method="POST" action="admin.php"
                      onsubmit="return confirm('¿Eliminar este mensaje? Esta acción no se puede deshacer.')">
                      <!-- Token CSRF para prevenir ataques -->
                      <input type="hidden" name="csrf_admin" value="<?= htmlspecialchars($_SESSION['csrf_admin']) ?>" />
                      <!-- ID del mensaje a eliminar -->
                      <input type="hidden" name="eliminar_id" value="<?= (int)$msg['id'] ?>" />
                      <!-- Botón para eliminar -->
                      <button type="submit"
                        class="text-xs text-red-400 hover:text-red-300 font-body font-medium
                                     transition-colors border border-red-400/20 hover:border-red-300/40
                                     px-3 py-1.5 rounded-lg">
                        Eliminar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
             PAGINACIÓN
             Controles para navegar entre páginas
             ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <?php if ($total_paginas > 1): ?>
          <div class="flex items-center justify-between px-6 py-4 border-t border-accent/10">
            <!-- Indicador de página actual -->
            <p class="text-muted text-sm font-body">
              Página <?= $pagina ?> de <?= $total_paginas ?>
            </p>
            <!-- Botones de navegación (anterior/siguiente) -->
            <div class="flex gap-2">
              <!-- Botón anterior (solo si no estamos en la primera página) -->
              <?php if ($pagina > 1): ?>
                <a href="admin.php?p=<?= $pagina - 1 ?><?= $busqueda ? '&q=' . urlencode($busqueda) : '' ?>"
                  class="btn-outline text-sm px-4 py-2">← Anterior</a>
              <?php endif; ?>
              <!-- Botón siguiente (solo si no estamos en la última página) -->
              <?php if ($pagina < $total_paginas): ?>
                <a href="admin.php?p=<?= $pagina + 1 ?><?= $busqueda ? '&q=' . urlencode($busqueda) : '' ?>"
                  class="btn-primary text-sm px-4 py-2">Siguiente →</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

      <?php endif; ?>
    </div>

  </main>

  <!-- ════════════════════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: PIE DE PÁGINA (FOOTER)
       Información del usuario logeado y créditos del panel
       ════════════════════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <footer class="border-t border-green-900/10 py-6 text-center text-muted font-body text-sm">
    <p>Panel de Seguridad · Sesión activa como <strong class="text-fg"><?= htmlspecialchars($_SESSION['admin_user'] ?? '') ?></strong></p>
  </footer>

</body>

</html>