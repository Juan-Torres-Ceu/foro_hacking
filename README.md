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

## 🛡️ Relación con OWASP Top 10

Este proyecto aplica controles relacionados con varias categorías del
OWASP Top 10:2021. Referencia oficial: OWASP Top 10:2021. [web:302][web:313]

### A01:2021 – Broken Access Control

- Uso de roles `admin` / `user` almacenados en la tabla `usuarios`.
- Comprobación del rol antes de permitir acciones sensibles (por ejemplo,
  borrar mensajes).
- Botones y funcionalidades de administración visibles solo para usuarios
  con rol adecuado.

### A02:2021 – Cryptographic Failures

- Contraseñas protegidas con PEPPER + `hash_hmac("sha256", password, PEPPER)`
  + `password_hash(...)`.
- Verificación de contraseñas con `password_verify`, evitando almacenamiento
  en texto claro.
- Generación de tokens aleatorios por usuario con `random_bytes` + `bin2hex`.

### A03:2021 – Injection

- Uso de consultas preparadas (`prepare` + `bind_param`) en las operaciones
  de inserción de mensajes y gestión de usuarios en la versión segura del
  código.

### A05:2021 – Security Misconfiguration

- Despliegue en Docker con separación de servicios (`web` y `db`).
- Configuración explícita de puertos y volúmenes en `docker-compose.yml`.
- Restricción de tipos de archivo y tamaños máximos en subidas.

### A07:2021 – Identification and Authentication Failures

- Sistema de login con usuario + contraseña y sesión PHP.
- Gestión de sesión mediante `$_SESSION['usuario_id']`, `$_SESSION['nick']`
  y `$_SESSION['rol']`.
- Tokens de API por usuario preparados para reforzar futuras operaciones.

---

## 🚀 Puesta en producción con Docker

### Requisitos previos

- Docker y Docker Compose instalados. [web:289]
- Clonar el repositorio:

```bash
git clone https://github.com/TU_USUARIO/foro-hacking-php.git
cd foro-hacking-php

