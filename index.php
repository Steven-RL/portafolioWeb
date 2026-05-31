<!-- ════════════════════════════════════════════════════════════════════
     PORTAFOLIO PERSONAL - PÁGINA DE INICIO
     Página principal que muestra la presentación, biografía e intereses
     ════════════════════════════════════════════════════════════════════ -->
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Meta tags para caracteres y responsive design -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Portafolio | Inicio</title>

  <!-- Framework Tailwind CSS para estilos utility-first -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts: Playfair Display y Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Configuración personalizada de Tailwind CSS: fuentes y colores personalizados -->
  <script>
    // ══════════════ CONFIGURACIÓN DE TAILWIND CSS ══════════════
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

<!-- ══════════════ CUERPO DE LA PÁGINA ══════════════ -->
<body class="bg-base text-fg min-h-screen">

  <!-- ══════════════ NAVEGACIÓN ══════════════ -->
  <!-- Barra de navegación fija en la parte superior con enlaces principales -->
  <nav class="sticky top-0 z-50 bg-base/80 backdrop-blur border-b border-accent/10">
    <!-- Contenedor central con logo/nombre y menú de navegación -->
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
      <!-- Logo/Nombre del portafolio -->
      <span class="font-display text-xl italic text-accent">Mi Portafolio</span>
      
      <!-- Enlaces de navegación principal -->
      <ul class="flex gap-8 font-body text-sm font-medium text-fg">
        <!-- Enlace a Inicio -->
        <li>
          <a href="index.php" class="group flex flex-col items-start">
            <span>Inicio</span>
            <span class="line-draw w-full"></span>
          </a>
        </li>
        <!-- Enlace a Contacto -->
        <li>
          <a href="contact.php" class="group flex flex-col items-start">
            <span>Contacto</span>
            <span class="line-draw"></span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- ══════════════ SECCIÓN HERO (PRESENTACIÓN PRINCIPAL) ══════════════ -->
  <!-- Sección destacada con presentación personal e imagen de perfil -->
  <header class="max-w-5xl mx-auto px-6 pt-20 pb-16 grid md:grid-cols-2 gap-12 items-center relative">

    <!-- Sección de Texto: Subtítulo, Nombre y Descripción -->
    <div>
      <!-- Subtítulo de bienvenida animado -->
      <p class="fade-up text-accent font-body font-medium tracking-widest uppercase mb-4">
        ¡Yōkoso Mugiwaras, Watashi wa
      </p>
      
      <!-- Nombre principal con énfasis en el primer nombre -->
      <h1 class="fade-up-delay-1 font-display text-5xl md:text-6xl leading-tight mb-6 text-fg">
        Rivadeneira D.<br /><em class="text-accent2">Steven</em>
      </h1>
      
      <!-- Descripción personal breve -->
      <p class="fade-up-delay-2 font-body text-muted text-lg leading-relaxed mb-8">
        Estudiante de Tecnologias de la Informacion apasionado por el desarrollo web,
        la tecnología y crear soluciones que impacten positivamente a las personas.
      </p>
      
      <!-- Botones de acción: Contacto y Saber más -->
      <div class="fade-up-delay-3 flex gap-4 flex-wrap">
        <a href="contact.php" class="btn-primary">
          Escríbeme
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
        <a href="#sobre-mi" class="btn-outline">Saber más</a>
      </div>
    </div>

    <!-- Sección de Avatar: Imagen de perfil con ícono de respaldo -->
    <div class="flex justify-center fade-up-delay-2">
      <!-- Contenedor del avatar con animación de anillo -->
      <div class="avatar-ring w-72 h-72 md:w-80 md:h-80">
        <div class="avatar-ring-inner">
          <!-- Imagen de perfil del usuario (si existe) -->
          <img src="img/avatar.png"
               alt="Foto de perfil"
               class="w-full h-full object-cover rounded-full"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
          
          <!-- Ícono de respaldo en caso de que no cargue la imagen -->
          <div class="hidden w-full h-full items-center justify-center rounded-full">
            <svg class="w-24 h-24 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

  </header>

  <!-- ══════════════ SECCIÓN SOBRE MÍ ══════════════ -->
  <!-- Información biográfica personal y datos de contacto -->
  <section id="sobre-mi" class="bg-surface py-20 border-y border-white/8">
    <div class="max-w-5xl mx-auto px-6">
      <!-- Etiqueta de sección -->
      <p class="text-accent font-body font-medium tracking-widest uppercase mb-3">Sobre mí</p>
      
      <!-- Título de la sección -->
      <h2 class="font-display text-4xl mb-8 text-fg">Biografía</h2>
      
      <!-- Contenedor con biografía (izquierda) y datos personales (derecha) -->
      <div class="grid md:grid-cols-3 gap-8 font-body text-fg/70 leading-relaxed">
        <!-- Párrafo biográfico principal -->
        <p class="md:col-span-2">
          Soy estudiante de <strong class="text-fg">Tecnologias de la Informacion</strong> en la UTPL.
          Mi pasión por la tecnología empezó recientemente y desde entonces no he parado de aventurarme explorando este nuevo mundo y cada dia aprender muchas cosas mas de gran ayuda. Me parece fascinante el <strong class="text-fg">desarrollo web</strong> y disfruto
          transformar ideas en productos digitales reales.
        </p>
        
        <!-- Información de contacto y ubicación -->
        <div class="space-y-3 text-sm">
          <div class="flex gap-3 items-center"><span class="text-accent">📍</span> Morona, Ecuador</div>
          <div class="flex gap-3 items-center"><span class="text-accent">🎓</span> Tecnologias de la Informacion</div>
          <div class="flex gap-3 items-center"><span class="text-accent">📱</span> 0987654321</div>
          <div class="flex gap-3 items-center"><span class="text-accent">✉️</span> osrivadeneira@utpl.edu.ec</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════ SECCIÓN DE HOBBIES ══════════════ -->
  <!-- Muestra los intereses y hobbies personales en tarjetas -->
  <section class="max-w-5xl mx-auto px-6 py-20 bg-img-overlay">
    <!-- Etiqueta de sección -->
    <p class="text-accent font-body font-medium tracking-widest uppercase mb-3">Intereses</p>
    
    <!-- Título de la sección -->
    <h2 class="font-display text-4xl mb-12 text-fg">Mis Hobbies</h2>
    
    <!-- Contenedor de tarjetas de hobbies en grilla responsiva -->
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">

      <?php
      // ══════════════ ARRAY DE HOBBIES ══════════════
      // Lista de hobbies con emoji, título y descripción
      $hobbies = [
        ['emoji' => '💻 👩🏻‍💻', 'titulo' => 'Programar',  'desc' => 'Crear y aprender nuevos proyectos web y aprender nuevas tecnologías.'],
        ['emoji' => '🎮 🧙🏻‍♂️', 'titulo' => 'Gaming',      'desc' => 'Videojuegos de estrategia: juego mucho "League of Legends" los fines de semana con mi hermano menor y tambien me gusta lo que es el Call of Duty Warzone.'],
        ['emoji' => '🥋 🤼', 'titulo' => 'Judo',  'desc' => 'Me encanta tambien el deporte del "Judo" recientemente he vuelto a entrenar pues deje de practicarlo por un par de años.'],
        ['emoji' => '👒 ⚔ 🏴‍☠️ 🌊', 'titulo' => 'Los animes',  'desc' => 'Veo animes y leo mangas para desestresarme y hago espoilers a mis amigos.'],
        ['emoji' => '🌄 🏞️', 'titulo' => 'Caminar por la selva',  'desc' => 'Explorar la naturaleza y desconectar al aire libre conociendo y explorando nuevos lugares de mi selvita.'],
        ['emoji' => '🦮 🐾', 'titulo' => 'Actividad perruna',  'desc' => 'Me encanta salir a correr con mis perros y realizar actividades que a ellos les gusta mucho como es ir a los rios a nadar.'],        
      ]; 
      
      // ══════════════ ITERACIÓN Y RENDERIZADO DE TARJETAS ══════════════
      // Recorre el array y muestra cada hobby en una tarjeta
      foreach ($hobbies as $hobby): ?>
        <div class="hobby-card">
          <div class="text-4xl mb-4"><?= $hobby['emoji'] ?></div>
          <h3 class="font-display text-xl mb-2 text-fg"><?= htmlspecialchars($hobby['titulo']) ?></h3>
          <p class="text-muted text-sm leading-relaxed"><?= htmlspecialchars($hobby['desc']) ?></p>
          <div class="line-draw mt-4"></div>
        </div>
      <?php endforeach; ?>

    </div>
  </section>

  <!-- ══════════════ PIE DE PÁGINA (FOOTER) ══════════════ -->
  <!-- Información de copyright y tecnologías utilizadas -->
  <footer class="border-t border-green-900/10 py-8 text-center text-muted font-body text-sm">
    <!-- Texto de copyright y créditos -->
    <p>© <?= date('Y') ?> Mi Portafolio · Hecho con PHP &amp; Tailwind CSS</p>
  </footer>

</body>
</html>
