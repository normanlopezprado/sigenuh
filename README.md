# SIGENUH 
Sistema de Gestión Nutricional Hospitalaria

Esta aplicación fue desarrollado como parte del proyecto de graduación para el Hospital Regional de Occidente
en la ciudad de Quetzaltenango, Guatemala; Por el alumno 6356, de la Universidad Mariano Gálvez de Guatemala, con
sede en la ciudad de Quetzaltenango.

Entorno Docker listo para desarrollo/prod local que empaqueta **Laravel** (Nginx + PHP-FPM) y **MariaDB**.  
El contenedor `app` incluye **PHP**, **Composer**, **Node.js**, **Yarn** y sirve la app con **Nginx**.

---

## Requisitos
- Docker 20.10+  
- Docker Compose v2 

- Archivo `.env` en la raíz del proyecto (proporcionado por el desarrollador)


---

## Puesta en marcha (desatendida)
Construye e inicia todo en segundo plano:
```bash
docker compose build --progress=plain
```
```bash
docker compose up -d
```