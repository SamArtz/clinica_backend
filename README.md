# Clínica Digital Backend

Proyecto del workshop **Sistema de Gestión de Citas Médicas** con **Laravel 11 + Filament 3 + Sanctum + Spatie Permission**.

## Esta versión queda preparada para PostgreSQL

- `.env.example` ya viene configurado para **PostgreSQL**.
- Panel administrativo con Filament.
- API REST para login y creación de citas.
- Validación de horario por médico.
- Bloqueo de citas duplicadas para el mismo médico, fecha y hora.
- Roles base: **admin**, **doctor**, **assistant**.
- Seeders y factories con datos de prueba.

## Requisitos

- PHP 8.2+
- Composer
- Node.js + npm
- PostgreSQL

## Instalación rápida con PostgreSQL

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

## Base de datos PostgreSQL

Crear una base llamada `clinica_backend` y luego dejar estos datos en `.env` si usas el usuario por defecto:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clinica_backend
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Si tu contraseña de PostgreSQL es otra, solo cambia `DB_PASSWORD`.

## Migrar y poblar

```bash
php artisan migrate:fresh --seed
npm run dev
php -S 127.0.0.1:8088 -t public
```

## Accesos de prueba

- **Admin**: `admin@clinica.com` / `password`
- **Doctor**: `house@clinica.com` / `password`
- **Doctor**: `wilson@clinica.com` / `password`
- **Asistente**: `assistant@clinica.com` / `password`

## URLs importantes

- **Inicio**: `http://127.0.0.1:8088/`
- **Panel Filament**: `http://127.0.0.1:8088/admin`
- **API login**: `POST http://127.0.0.1:8088/api/login`
- **API citas**: `POST http://127.0.0.1:8088/api/appointments`

## Postman

### Login

```json
{
  "email": "house@clinica.com",
  "password": "password"
}
```

### Crear cita

Usa el token del login como Bearer Token y envía:

```json
{
  "patient_id": 1,
  "doctor_id": 3,
  "appointment_date": "2026-03-30",
  "appointment_time": "09:00",
  "reason": "Consulta general",
  "status": "confirmed"
}
```

## Nota

El proyecto **ya es Laravel**. No necesitas crear otro proyecto Laravel aparte. Lo que sí necesitas es tener un entorno para correr Laravel: PHP, Composer, Node.js y PostgreSQL.
