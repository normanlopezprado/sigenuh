<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>
<p align="center">

</p>

> [!info] 
> La información que esta contenida dentro de `comillas`, se inserta individualmente, linea por linea.

## ⚙️ Dependencias

composer
node
npm install

npm install --global yarn

yarn install

## 🗄️ DataBase

php artisan migrate
php artisan db:seed

php artisan migrate:fresh
php artisan db:seed

## 💾 Rutas - Guardar imagenes/icon en la DB

php artisan storage:link


## 🪄 Run - En consolas diferentes

`
npm run dev
`

`
php artisan serve
`

🎉🪄🔑⚙️🔎📦🧰💾🗃️🧑🏻‍💻🗄️


## actualizar permisos 🔑
routes/web.php
RolePermissionSeeder.php


php artisan db:seed --class=RolePermissionSeeder



# 🚢 Docker - Guia

# Guía de despliegue local

## Índice
1. [Requisitos previos](#1-requisitos-previos)
2. [Clonar el repositorio](#2-clonar-el-repositorio)
3. [Configurar variables de entorno](#3-configurar-variables-de-entorno)
4. [Construir e iniciar con Docker](#4-construir-e-iniciar-con-docker)
5. [Instalar dependencias y preparar la aplicación](#5-instalar-dependencias-y-preparar-la-aplicación)
6. [Migrar y seedear la base de datos](#6-migrar-y-seedear-la-base-de-datos)
7. [Enlazar almacenamiento y cargar permisos](#7-enlazar-almacenamiento-y-cargar-permisos)
8. [Iniciar el frontend con Vite](#8-iniciar-el-frontend-con-vite)
9. [Acceder a la aplicación](#9-acceder-a-la-aplicación)
10. [Detener los servicios](#10-detener-los-servicios)

---

## 1. Requisitos previos
- Docker 20.10 o superior
- Docker Compose v2
- Git instalado en tu máquina

## 2. Clonar el repositorio
```bash
git clone https://github.com/normanlopezprado/sigenuh.git
cd <tu-repositorio>
```

## 3. Configurar variables de entorno
1. Copia el archivo de ejemplo:
   ```bash
   cp .env.example .env
   ```
2. Ajusta las variables de base de datos y cualquier otra configuración necesaria dentro de `.env`.

## 4. Construir e iniciar con Docker
Levanta los servicios en segundo plano y construye las imágenes la primera vez:
```bash
docker compose up -d --build
```

## 5. Instalar dependencias y preparar la aplicación
Ejecuta estos comandos dentro del contenedor `app` para instalar dependencias y generar la clave de la aplicación:
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

## 6. Migrar y seedear la base de datos
1. Ejecuta las migraciones base:
   ```bash
   docker compose exec app php artisan migrate
   ```
2. Si necesitas poblar datos iniciales, ejecuta los seeders:
   ```bash
   docker compose exec app php artisan db:seed
   ```
3. Para reconstruir completamente la base de datos en entornos de desarrollo:
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```

## 7. Enlazar almacenamiento y cargar permisos
1. Publica el enlace simbólico del almacenamiento para servir archivos:
   ```bash
   docker compose exec app php artisan storage:link
   ```
2. Ejecuta el seeder de roles y permisos cuando necesites actualizar los accesos definidos en `routes/web.php` y `database/seeders/RolePermissionSeeder.php`:
   ```bash
   docker compose exec app php artisan db:seed --class=RolePermissionSeeder
   ```

## 8. Iniciar el frontend con Vite
Para servir los assets en caliente desde Vite (accesible desde tu host):
```bash
docker compose exec app npm install
docker compose exec app npm run dev -- --host
```

## 9. Acceder a la aplicación
- Aplicación Laravel: `http://localhost:8080`
- Servidor de Vite (opcional): `http://localhost:5173`

La landing page configurada en `routes/web.php` responde en la raíz (`/`). Puedes acceder al flujo de autenticación en `/login` y, tras iniciar sesión, al dashboard protegido.

## 10. Detener los servicios
Cuando termines de trabajar, puedes detener y eliminar los contenedores con:
```bash
docker compose down
```