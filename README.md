# Foro Hacking PHP – Despliegue seguro en Docker

Proyecto de foro sencillo para compartir mensajes, imágenes y vídeos entre compañeros, desplegado con Docker y MySQL.  
El objetivo principal es aprender a poner en producción una aplicación web propia aplicando buenas prácticas de **seguridad**, **aislamiento** y **versionado**.

---

## 🧱 Arquitectura del proyecto

- **Aplicación web**: PHP 8.2 con Apache, servida en el contenedor `web`. [web:289]
- **Base de datos**: MySQL 8.0 en el contenedor `db`. [web:289]
- **Orquestación**: `docker-compose.yml` para levantar todos los servicios. [web:289]
- **Front-end**: TailwindCSS vía CDN, HTML y JavaScript para refrescar mensajes por AJAX.
- **Persistencia**:
  - Volumen Docker `db_data` para los datos de MySQL.
  - Carpeta `uploads/` en el host para los archivos que suben los usuarios.
- **Código fuente y docs**: versionados en Git y publicados en GitHub.

Esta separación en servicios (web y BD) reduce la superficie de ataque y sigue el patrón típico de despliegue seguro con Docker. [web:289][web:293]

---

## 🧾 Cambios versión 0.1 (roles)

En esta versión se añadió autenticación con roles y capacidades de administración:

- Nueva columna `rol` en la tabla `usuarios` (`admin` / `user`).
- El proceso de login ahora guarda en la sesión:
  - `$_SESSION['usuario_id']`
  - `$_SESSION['nick']`
  - `$_SESSION['rol']`
- Interfaz diferenciada según el rol:
  - Los usuarios con rol **admin** ven:
    - Fondo distinto en la aplicación.
    - Título “Foro Hacking (Administrador)” y badge ADMIN junto a su nombre.
  - Los usuarios normales ven el foro con el estilo estándar.
- Autorización a nivel de interfaz:
  - Solo los usuarios con rol **admin** ven el botón **Borrar** en cada mensaje.
  - Al pulsar “Borrar” se elimina el mensaje de la tabla `mensajes`
    y también el archivo asociado en la carpeta `uploads/` si existe.
- Se mantiene el almacenamiento seguro de contraseñas:
  - PEPPER con `hash_hmac("sha256", $pass, $PEPPER)`.
  - Hash con `password_hash` y verificación con `password_verify`.

Esta actualización demuestra control de autenticación (identificar al usuario) y de autorización (diferenciar qué puede hacer un admin frente a un usuario normal), alineado con OWASP Top 10 – *Broken Access Control* y *Identification and Authentication Failures*. [web:265][web:295]

---

## 🧾 Cambios versión 0.2 (tokens por usuario)

En la versión 0.2 se añadieron **tokens de seguridad por usuario**:

- Nueva columna `api_token` en la tabla `usuarios`.
- En el registro de usuarios se genera un token aleatorio con `random_bytes` +
  `bin2hex` y se guarda en `usuarios.api_token`.
- Cada usuario tiene un identificador secreto único que podría usarse en el
  futuro para exponer una API segura o reforzar ciertas operaciones.
- El token se puede consultar desde la base de datos y, opcionalmente, mostrar
  en la interfaz solo al usuario autenticado.
- El flujo principal de autenticación sigue siendo usuario + contraseña
  (con PEPPER + `password_hash`), pero la aplicación está preparada para
  trabajar también con tokens por usuario.

Esto refuerza la protección frente a *Identification and Authentication Failures* y ayuda a evitar *Broken Access Control* si se usan los tokens para autorizar acciones sensibles. [web:265]

---

## 🧾 Cambios versión 1.0 (seguridad y pruebas)

La versión **1.0** consolida el proyecto como versión estable con todas las
medidas de seguridad activas y un plan de pruebas documentado:

- **Pruebas unitarias**:
  - Script `pruebas_unitarias.php` ejecutado en el contenedor `web`:
    ```bash
    docker-compose exec web php /var/www/html/pruebas_unitarias.php
    ```
  - Casos que validan:
    - Hash de contraseñas con PEPPER + `hash_hmac` + `password_hash` +
      `password_verify`.
    - Generación de tokens aleatorios de longitud correcta y no repetidos.
    - Estructura de datos que se envía a la base de datos al crear mensajes.
- **Pruebas de integración (Postman)**:
  - Colección de peticiones para probar:
    - Registro y login de usuarios.
    - Publicación de mensajes y subida de archivos.
    - Acciones restringidas de administrador (borrado de mensajes).
    - Accesos no autorizados y comportamiento ante entradas con HTML/JS.

Con estas pruebas se cubre el requisito de disponer de pruebas **unitarias** y
**de integración**, reduciendo el riesgo de *Security Misconfiguration* y
errores introducidos por cambios de código (OWASP Top 10 – *Security Misconfiguration*). [web:265][web:281]

---

## 🛡️ Medidas de seguridad y relación con OWASP Top 10

La aplicación incorpora varias medidas concretas relacionadas con OWASP Top 10. [web:265][web:295]

- **Gestión de autenticación y contraseñas**
  - Uso de PEPPER + `hash_hmac` + `password_hash` + `password_verify` para
    almacenar contraseñas de forma robusta.
  - Protección frente a *Identification and Authentication Failures*
    (OWASP Top 10). [web:265]

- **Control de acceso por roles**
  - Rol `admin` con permisos adicionales (borrado de mensajes) frente a rol `user`.
  - Comprobaciones de rol antes de permitir acciones sensibles.
  - Reducción del riesgo de *Broken Access Control* al separar claramente
    capacidades de usuario y administrador. [web:265]

- **Protección frente a XSS**
  - Salida escapada con funciones tipo `htmlspecialchars` + `nl2br` en los
    mensajes mostrados.
  - Evita la ejecución de HTML/JavaScript insertado por usuarios y mitiga
    *Cross-Site Scripting (XSS)*. [web:265]

- **Protección CSRF**
  - Uso de token CSRF por sesión, incluido en formularios sensibles y
    validado en cada petición POST.
  - Reduce el riesgo de ataques de falsificación de petición en sitios
    cruzados, relacionados con *Broken Access Control* e *Identification and
    Authentication Failures*. [web:265]

- **Validación de subida de archivos**
  - Lista blanca de extensiones permitidas (imágenes / vídeos).
  - Comprobación de tipo MIME real y límite de tamaño.
  - Nombres de archivo aleatorios y almacenados fuera del código fuente.
  - Mitiga riesgos de *Security Misconfiguration* y de exposición de datos
    a través de archivos maliciosos. [web:260][web:265]

- **Aislamiento con Docker**
  - Separación en servicios `web` y `db`.
  - Persistencia de datos controlada mediante volúmenes.
  - Facilita aplicar principios de mínimo privilegio y mitigar impacto de
    vulnerabilidades, alineado con *Security Misconfiguration* y *Vulnerable
    and Outdated Components* (al poder actualizar imágenes de forma controlada). [web:289][web:295]

---

## 🚀 Puesta en producción con Docker

### Requisitos previos

- Docker y Docker Compose instalados. [web:289]
- Clonar el repositorio:

```bash
git clone https://github.com/TU_USUARIO/foro-hacking-php.git
cd foro-hacking-php

