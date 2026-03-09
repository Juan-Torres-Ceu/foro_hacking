# Foro Hacking PHP – Despliegue seguro en Docker

Proyecto de foro sencillo para compartir mensajes, imágenes y vídeos entre compañeros, desplegado con Docker y MySQL.  
El objetivo principal es aprender a poner en producción una aplicación web propia aplicando buenas prácticas de **seguridad**, **aislamiento** y **versionado**.

---

## 🧱 Arquitectura del proyecto

- **Aplicación web**: PHP 8.2 con Apache, servida en el contenedor `web`.
- **Base de datos**: MySQL 8.0 en el contenedor `db`.
- **Orquestación**: `docker-compose.yml` para levantar todos los servicios.
- **Front-end**: TailwindCSS vía CDN, HTML y JavaScript para refrescar mensajes por AJAX.
- **Persistencia**:
  - Volumen Docker `db_data` para los datos de MySQL.
  - Carpeta `uploads/` en el host para los archivos que suben los usuarios.
- **Código fuente y docs**: versionados en Git y publicados en GitHub.

Esta separación en servicios (web y BD) reduce la superficie de ataque y sigue el patrón típico de despliegue seguro con Docker. [web:111][web:116]

---

## 🧾 Cambios versión 0.1 (roles)

En esta versión he añadido autenticación con roles y capacidades de administración:

- Nueva columna `rol` en la tabla `usuarios` (`admin` / `user`).
- El proceso de login ahora guarda en la sesión:
  - `$_SESSION['usuario_id']`
  - `$_SESSION['nick']`
  - `$_SESSION['rol']`
- Interfaz diferenciada según el rol:
  - Los usuarios con rol **admin** ven:
    - Fondo distinto en la aplicación.
    - Título “Foro Hacking (Administrador)” y badge **ADMIN** junto a su nombre.
  - Los usuarios normales ven el foro con el estilo estándar.
- Autorización a nivel de interfaz:
  - Solo los usuarios con rol **admin** ven el botón **Borrar** en cada mensaje.
  - Al pulsar “Borrar” se elimina el mensaje de la tabla `mensajes`
    y también el archivo asociado en la carpeta `uploads/` si existe.
- Se mantiene el almacenamiento seguro de contraseñas:
  - PEPPER con `hash_hmac("sha256", $pass, $PEPPER)`.
  - Hash con `password_hash` y verificación con `password_verify`.

Esta actualización demuestra control de autenticación (identificar al usuario) y de autorización (diferenciar qué puede hacer un admin frente a un usuario normal).

---

## 🧾 Cambios versión 0.2 (tokens por usuario)

En la versión 0.2 he añadido **tokens de seguridad por usuario**:

- Nueva columna `api_token` en la tabla `usuarios`.
- En el registro de usuarios se genera un token aleatorio con `random_bytes` +
  `bin2hex` y se guarda en `usuarios.api_token`.
- Cada usuario tiene un identificador secreto único que podría usarse en el
  futuro para exponer una API segura o reforzar ciertas operaciones.
- El token se puede consultar desde la base de datos y, opcionalmente, mostrar
  en la interfaz solo al usuario autenticado.
- El flujo principal de autenticación sigue siendo usuario + contraseña
  (con PEPPER + `password_hash`), pero ahora la aplicación está preparada
  para trabajar también con tokens por usuario.

---

## 🚀 Puesta en producción con Docker

### Requisitos previos

- Docker y Docker Compose instalados.
- Clonar el repositorio:

```bash
git clone https://github.com/TU_USUARIO/foro-hacking-php.git
cd foro-hacking-php

