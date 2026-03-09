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

## 🚀 Puesta en producción con Docker

### Requisitos previos

- Docker y Docker Compose instalados.
- Clonar el repositorio:

```bash
git clone https://github.com/TU_USUARIO/foro-hacking-php.git
cd foro-hacking-php

