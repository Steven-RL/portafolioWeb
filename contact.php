<!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
     PORTAFOLIO PERSONAL - PÁGINA DE CONTACTO
     Formulario de contacto con validación cliente-servidor y gestor de mensajes
     ══════════════════════════════════════════════════════════════════════════════════════════════════ -->

<?php
// ══════════════════════════════════════════════════════════════════════════════════════════════════
// SECCIÓN: INICIALIZACIÓN DE SESIÓN Y RECUPERACIÓN DE DATOS
// ══════════════════════════════════════════════════════════════════════════════════════════════════
// Inicia la sesión para acceder a variables de sesión
session_start(); 

// Recupera el mensaje flash (notificación temporal) y datos del formulario de la sesión anterior
// Esto sirve para mostrar confirmaciones después de procesar el formulario
$flash      = $_SESSION['flash'] ?? null;        // Mensaje de éxito o error
$form_data  = $_SESSION['form_data'] ?? [];      // Datos del formulario para repoblar en caso de error

// Elimina los datos de sesión después de usarlos para que no reaparezcan al recargar la página
unset($_SESSION['flash']);
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="es">

<!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
     SECCIÓN: ENCABEZADO (HEAD)
     Meta tags, configuración de estilos y fuentes del proyecto
     ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
<head>
  <!-- Meta tags para codificación de caracteres y responsive design -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Portafolio | Contacto</title>

  <!-- Framework Tailwind CSS para estilos utility-first -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts: Playfair Display y Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Configuración personalizada de Tailwind CSS: fuentes y colores personalizados -->
  <script>
    // ══════════════════════════════════════════════════════════════════════════════════════════════════
    // CONFIGURACIÓN DE TAILWIND CSS
    // Extensión del tema por defecto con fuentes y paleta de colores personalizadas
    tailwind.config = {
      theme: {
        extend: {
          // Fuentes personalizadas para títulos (display) y cuerpo (body)
          fontFamily: {
            display: ['"DM Serif Display"', 'serif'],
            body:    ['"DM Sans"', 'sans-serif'],
          },
          // Paleta de colores: tonos neutros base, de superficie, y colores de énfasis
          colors: {
            base:    '#EEF2F0',    // Fondo principal
            surface: '#FCFCFA',    // Fondo de secciones
            raised:  '#F4F6F3',    // Fondo elevado
            fg:      '#0F1C16',    // Texto principal
            accent:  '#0E7A5A',    // Color de énfasis primario (verde)
            accent2: '#E8622A',    // Color de énfasis secundario (naranja)
            muted:   '#6B7C74',    // Texto atenuado
          },
        },
      },
    }
  </script>

  <!-- Estilos personalizados del proyecto -->
  <link rel="stylesheet" href="css/styles.css" />
</head>

<!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
     SECCIÓN: CUERPO DE LA PÁGINA
     Estructura principal con navegación, contenido y pie de página
     ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
<body class="bg-base text-fg min-h-screen flex flex-col">

  <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: NAVEGACIÓN
       Barra de navegación fija con enlaces a secciones principales
       ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <nav class="sticky top-0 z-50 bg-base/80 backdrop-blur border-b border-green-900/10">
    <!-- Contenedor central con logo/nombre y menú de navegación -->
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
      <!-- Logo/Nombre del portafolio (enlace a inicio) -->
      <a href="index.php" class="font-display text-xl italic text-accent">Mi Portafolio</a>
      
      <!-- Enlaces de navegación principal -->
      <ul class="flex gap-8 font-body text-sm font-medium text-fg">
        <!-- Enlace a Inicio -->
        <li><a href="index.php" class="group flex flex-col items-start"><span>Inicio</span><span class="line-draw"></span></a></li>
        <!-- Enlace a Contacto (página actual) -->
        <li><a href="contact.php" class="group flex flex-col items-start"><span>Contacto</span><span class="line-draw w-full"></span></a></li>
        <!-- Enlace a Panel de Mensajes (solo admin) -->
        <li><a href="admin_login.php" class="group flex flex-col items-start"><span>Mensajes</span><span class="line-draw"></span></a></li>
      </ul>
    </div>
  </nav>

  <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: CONTENIDO PRINCIPAL
       Diseño de dos columnas: información de contacto (izq) y formulario (der)
       ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <main class="flex-1 max-w-5xl mx-auto w-full px-6 py-20 grid md:grid-cols-2 gap-16 items-start">

    <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
         COLUMNA IZQUIERDA: Información de contacto
         Título, descripción, datos de contacto e imagen animada
         ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <div>
      <!-- Etiqueta de sección -->
      <p class="fade-up text-accent font-body text-xs font-medium tracking-widest uppercase mb-3">Hablemos</p>
      
      <!-- Título principal -->
      <h1 class="fade-up-delay-1 font-display text-5xl md:text-6xl leading-tight mb-6 text-fg">Contáctame</h1>
      
      <!-- Descripción breve del formulario -->
      <p class="fade-up-delay-2 text-muted font-body leading-relaxed mb-10">¿Tienes un proyecto, pregunta o simplemente quieres saludar? Llena el formulario y te respondo a la brevedad.</p>

      <!-- Tarjetas con información de contacto -->
      <div class="fade-up-delay-2 space-y-4 font-body text-sm">
        <!-- Tarjeta de Email -->
        <div class="flex items-center gap-4">
          <span class="bg-accent/15 text-accent2 rounded-full w-10 h-10 flex items-center justify-center text-lg">✉️</span>
          <div><p class="font-semibold text-fg">Email</p><p class="text-muted">osrivadeneira@utpl.edu.ec</p></div>
        </div>
        <!-- Tarjeta de Ubicación -->
        <div class="flex items-center gap-4">
          <span class="bg-accent/15 text-accent2 rounded-full w-10 h-10 flex items-center justify-center text-lg">📍</span>
          <div><p class="font-semibold text-fg">Ubicación</p><p class="text-muted">Morona, Ecuador</p></div>
        </div>
      </div>

      <!-- Imagen animada de contacto -->
      <div class="fade-up-delay-2 mt-8 flex justify-center">
        <img src="img/giphy.gif"
             alt="Animación de contacto"
             class="w-56 h-auto rounded-2xl shadow-md hover:scale-105 transition-transform duration-300"
             loading="lazy">
      </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
         COLUMNA DERECHA: Formulario de contacto
         Formulario con validación y alertas de éxito/error
         ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
    <div class="fade-up-delay-1 bg-surface rounded-3xl p-8 border border-green-900/10">

      <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
           Alerta de retroalimentación (éxito o error)
           Muestra el resultado después de procesar el formulario
           ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
      <?php if ($flash): ?>
        <div class="alert <?= $flash['tipo'] === 'ok' ? 'alert-ok' : 'alert-err' ?> mb-6">
          <span><?= $flash['tipo'] === 'ok' ? '✅' : '❌' ?></span>
          <div>
            <p class="font-semibold"><?= $flash['tipo'] === 'ok' ? '¡Mensaje enviado!' : 'Error al enviar' ?></p>
            <p class="text-sm mt-0.5"><?= htmlspecialchars($flash['texto']) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
           Formulario de contacto
           Envía datos a process.php para procesar y guardar el mensaje
           ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
      <form action="process.php" method="POST" novalidate id="contactForm">

        <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
             Campo: Nombre
             Validación: Solo letras, espacios, acentos; mínimo 2 caracteres
             ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <div class="mb-5">
          <label for="nombre" class="block font-body font-medium text-sm mb-2 text-fg">Nombre <span class="text-accent">*</span></label>
          <input type="text" id="nombre" name="nombre" required minlength="2" maxlength="100"
                 pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                 title="Solo letras, espacios y acentos (no números ni caracteres especiales)"
                 placeholder="Tu nombre completo"
                 value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>"
                 class="form-input" />
          <p class="error-msg text-red-400 text-xs mt-1 hidden">Solo letras y espacios (mín. 2 caracteres).</p>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
             Campo: Correo Electrónico
             Validación: Formato de email válido
             ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <div class="mb-5">
          <label for="correo" class="block font-body font-medium text-sm mb-2 text-fg">Correo electrónico <span class="text-accent">*</span></label>
          <input type="email" id="correo" name="correo" required maxlength="150"
                 placeholder="tucorreo@ejemplo.com"
                 value="<?= htmlspecialchars($form_data['correo'] ?? '') ?>"
                 class="form-input" />
          <p class="error-msg text-red-400 text-xs mt-1 hidden">Ingresa un correo válido.</p>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
             Campo: Mensaje
             Validación: Mínimo 10 caracteres, máximo 1000
             ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
        <div class="mb-7">
          <label for="mensaje" class="block font-body font-medium text-sm mb-2 text-fg">Mensaje <span class="text-accent">*</span></label>
          <textarea id="mensaje" name="mensaje" required minlength="10" maxlength="1000"
                    rows="5" placeholder="¿En qué te puedo ayudar?"
                    class="form-input resize-none"><?= htmlspecialchars($form_data['mensaje'] ?? '') ?></textarea>
          <p class="error-msg text-red-400 text-xs mt-1 hidden">El mensaje debe tener al menos 10 caracteres.</p>
        </div>

        <!-- Botón de envío -->
        <button type="submit" class="btn-primary w-full">
          <span>Enviar mensaje</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </button>

      </form>
    </div>
  </main>

  <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: PIE DE PÁGINA (FOOTER)
       Información de copyright y créditos de tecnologías utilizadas
       ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <footer class="border-t border-green-900/10 py-8 text-center text-muted font-body text-sm">
    <!-- Texto de copyright e información de créditos -->
    <p>© <?= date('Y') ?> Mi Portafolio · Hecho con PHP &amp; Tailwind CSS</p>
  </footer>

  <!-- ══════════════════════════════════════════════════════════════════════════════════════════════════
       SECCIÓN: SCRIPT DE VALIDACIÓN DEL FORMULARIO
       Validación cliente-side antes de enviar el formulario al servidor
       ══════════════════════════════════════════════════════════════════════════════════════════════════ -->
  <script>
    // ══════════════════════════════════════════════════════════════════════════════════════════════════
    // Obtiene el formulario y añade un listener para validar antes de enviar
    const form = document.getElementById('contactForm');
    form.addEventListener('submit', function (e) {
      let valid = true;
      
      // ══════════════════════════════════════════════════════════════════════════════════════════════════
      // Valida cada campo requerido del formulario
      form.querySelectorAll('[required]').forEach(field => {
        const errorEl = field.nextElementSibling;  // Elemento de error asociado
        const val = field.value.trim();             // Valor del campo sin espacios
        
        // Limpia estados anteriores de error
        field.classList.remove('border-red-400');
        errorEl?.classList.add('hidden');

        // Validación 1: Campo no vacío
        if (!val) { markInvalid(field, errorEl); valid = false; return; }

        // Validación 2: Formato de email válido
        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
          markInvalid(field, errorEl); valid = false; return;
        }

        // Validación 3: Nombre solo contiene letras y espacios
        if (field.id === 'nombre') {
          const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
          if (!nombreRegex.test(val)) {
            markInvalid(field, errorEl); valid = false; return;
          }
        }

        // Validación 4: Longitud mínima del campo
        if (field.minLength && val.length < field.minLength) {
          markInvalid(field, errorEl); valid = false; return;
        }
      });
      
      // Si hay errores, previene el envío del formulario
      if (!valid) e.preventDefault();
    });
    
    // ══════════════════════════════════════════════════════════════════════════════════════════════════
    // Función auxiliar para marcar un campo como inválido
    // Añade borde rojo y muestra mensaje de error
    function markInvalid(field, errorEl) {
      field.classList.add('border-red-400');
      errorEl?.classList.remove('hidden');
    }
  </script>

</body>
</html>