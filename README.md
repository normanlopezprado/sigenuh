# SIGENUH 
Sistema de Gestión Nutricional Hospitalaria

---


Esta aplicación fue desarrollado como parte del proyecto de graduación para el Hospital Regional de Occidente
en la ciudad de Quetzaltenango, Guatemala; Por el alumno 6356, de la Universidad Mariano Gálvez de Guatemala, con
sede en la ciudad de Quetzaltenango.

Repositorio: https://github.com/normanlopezprado/sigenuh

```bash
git clone https://github.com/normanlopezprado/sigenuh.git
```

```bash
cd sigenuh
```

---

## Ejecución Docker 

Entorno Docker listo para desarrollo/prod local que empaqueta **Laravel** (Nginx + PHP-FPM) y **MariaDB**.  
El contenedor `app` incluye **PHP**, **Composer**, **Node.js**, **Yarn** y sirve la app con **Nginx**.


Requisitos
- Docker 20.10+  
- Docker Compose v2 
- Archivo `.env.docker` (proporcionado por el desarrollador)


### Puesta en marcha
Construye e inicia todo en segundo plano:

```bash
docker compose --env-file .env.docker build --no-cache app
```

```bash
docker compose --env-file .env.docker up -d
```

---

## Ejecución local con XAMPP 

### 1) Descargas recomendadas

- XAMPP (Apache, MariaDB y PHP): https://www.apachefriends.org/download.html
- Node.js LTS + npm: https://nodejs.org/en/download
- Composer 2.x: https://getcomposer.org/download/
- Git: https://git-scm.com/download/
- Yarn: 
    ```bash
    npm i -g yarn
    ```

### 2) Clonar el proyecto

```bash
git clone https://github.com/normanlopezprado/sigenuh.git
```

```bash
cd sigenuh
```

### 3) Colocar el .env (proporcionado por el desarrollador)

Copia el archivo .env que te entrega el desarrollador en la raíz del proyecto.

Verifica que APP_KEY esté definido. Si no aparece, genera uno:

```bash
php artisan key:generate
```

### 4) Instalar dependencias

PHP (Composer)

```bash
composer install
```

Frontend (Node/Vite)

```bash
npm install
```

### 5) Migraciones, seeders y enlace de storage

```bash
php artisan migrate --seed
```

```bash
php artisan storage:link
```

### 6) Levantar entorno de desarrollo

Abre dos terminales en la carpeta del proyecto:

Terminal A — Backend (PHP):

```bash
php artisan serve
```
Servirá normalmente en http://127.0.0.1:8000.

Terminal B — Frontend (Vite):

```bash
npm run dev
```
---
