# Mi Portafolio

Portafolio personal desarrollado con PHP, MySQL y Tailwind CSS. El sitio presenta informacion personal, biografia, hobbies y una pagina de contacto donde los visitantes pueden enviar mensajes.

El proyecto tambien incluye un panel de administracion protegido para revisar, buscar, filtrar y eliminar los mensajes recibidos desde el formulario de contacto.


## Enlace del Hosting gratuito
   https://practicaswebrl.site.je/

## Credenciales de acceso al Panel de Seguridad

- Usuario: `admin`
- Contraseña: `a1d2m3i@n#`

## Caracteristicas

- Pagina principal con presentacion personal, biografia e intereses.
- Formulario de contacto con validacion en cliente y servidor.
- Almacenamiento de mensajes en base de datos MySQL.
- Mensajes flash para confirmar envio o mostrar errores.
- Panel administrativo con inicio de sesion.
- Busqueda, filtros por fecha, paginacion y eliminacion de mensajes.
- Diseno responsive usando Tailwind CSS y estilos personalizados.

## Tecnologias usadas

- PHP
- MySQL / MariaDB
- MySQLi
- HTML5
- Tailwind CSS CDN
- CSS personalizado
- XAMPP para entorno local

## Estructura principal

```text
portafolio/
|-- admin.php          # Panel para administrar mensajes
|-- admin_login.php    # Login del administrador
|-- admin_logout.php   # Cierre de sesion
|-- config.php         # Configuracion del panel admin
|-- contact.php        # Formulario de contacto
|-- db.php             # Conexion a la base de datos
|-- index.php          # Pagina principal del portafolio
|-- process.php        # Procesa y guarda mensajes
|-- portafolio.sql     # Script para crear la base de datos
|-- css/               # Estilos personalizados
`-- img/               # Imagenes del sitio
```

## Requisitos

- XAMPP, WAMP, Laragon o un servidor compatible con PHP y MySQL.
- PHP 7.4 o superior recomendado.
- MySQL o MariaDB.
- Navegador web.

## Instalacion y uso local

1. Clona o copia el proyecto dentro de la carpeta del servidor local:

```text
E:\xampp\htdocs\proyectos\portafolio
```

2. Inicia Apache y MySQL desde XAMPP.

3. Crea la base de datos importando el archivo `portafolio.sql` desde phpMyAdmin o ejecutandolo en MySQL.

4. Revisa la configuracion de conexion en `db.php`:

```php
$host = "localhost";
$usuario = "portafolio_user";
$password = "a1d2m3i@n#";
$base_datos = "portafolio";
```

5. Abre el proyecto en el navegador:

```text
http://localhost/proyectos/portafolio/
```

6. Para probar el formulario, entra a:

```text
http://localhost/proyectos/portafolio/contact.php
```

7. Para revisar los mensajes recibidos, entra al panel:

```text
http://localhost/proyectos/portafolio/admin_login.php
```

Las credenciales del administrador se configuran en `config.php`.

## Base de datos

El archivo `portafolio.sql` crea la base de datos `portafolio` y la tabla `contactos`, donde se guardan los mensajes enviados desde el formulario.

Campos principales:

- `id`
- `nombre`
- `correo`
- `mensaje`
- `fecha_registro`

-- Base de datos: portafolio

CREATE DATABASE IF NOT EXISTS portafolio;

USE portafolio;

CREATE TABLE IF NOT EXISTS contactos (
    id             INT AUTO_INCREMENT,
    nombre         VARCHAR(100) NOT NULL,
    correo         VARCHAR(100) NOT NULL,
    mensaje        TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

## Uso en hosting

1. Sube todos los archivos del proyecto al hosting.
2. Crea una base de datos MySQL desde el panel del hosting.
3. Importa el archivo `portafolio.sql`.
4. Actualiza `db.php` con los datos reales del hosting:

```php
    $host = "sql302.infinityfree.com";
    $usuario = "if0_42065043";
    $password = "SIwmfFjiBAHGv";
    $base_datos = "if0_42065043_web_portafolio";
```