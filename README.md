# Matriz de Riesgo LA/FT

Aplicación web académica para registrar, evaluar y priorizar eventos de riesgo de **lavado de activos y financiamiento del terrorismo (LA/FT)**. Permite configurar versiones de parámetros, calcular riesgo inherente y residual, registrar decisiones de seguimiento y consultar una bitácora con verificación de integridad.

Desarrollada para el curso de Auditoría y Seguridad de Sistemas, 2026. El sistema es un prototipo académico: los pesos, umbrales y criterios deben ser definidos y revisados por los responsables del proyecto.

## Funcionalidades

- **Tablero:** consulta y priorización de eventos, resultados y pendientes.
- **Clientes:** registro y actualización de información utilizada en las evaluaciones.
- **Coincidencias en listas:** consulta y revisión de coincidencias registradas.
- **Catálogos:** administración de factores, agravantes y controles.
- **Versiones de parámetros:** configuración de pesos, escalas, bandas, topes y reglas; propuesta y aprobación con separación de funciones.
- **Evaluaciones y eventos:** registro de eventos, calificación de factores y cálculo de resultados mediante procedimientos de MySQL.
- **Resultados y seguimiento:** consulta del riesgo inherente y residual, clasificación y decisiones de seguimiento.
- **Sensibilidad:** simulación de cambios en las ponderaciones.
- **Bitácora:** registro de operaciones con encadenamiento de hashes y consulta de integridad.
- **Usuarios:** administración de cuentas, roles y estados de acceso.

## Tecnologías

| Componente | Tecnología |
| --- | --- |
| Aplicación | Laravel 12 y PHP 8.3 |
| Interfaz | Blade, CSS/JavaScript, Tailwind CSS 3 y Alpine.js |
| Compilación | Vite 7 y Node.js |
| Base de datos | MySQL 8.4 LTS |
| Autenticación | Sesiones de Laravel y Sanctum 4 |
| Pruebas | PHPUnit 11 |
| Despliegue | Docker con Apache/PHP en Railway |

El proyecto original utilizaba Laravel 10. La copia actual se actualizó a Laravel 12, junto con las dependencias compatibles, durante la preparación del despliegue. Los archivos `composer.lock` y `package-lock.json` fijan las versiones instaladas.

## Roles y acceso

| Rol | Operaciones principales |
| --- | --- |
| Administrador | Gestionar usuarios y catálogos; crear, editar y proponer versiones de parámetros. |
| Analista | Registrar y calificar operaciones; consultar resultados. |
| Supervisor | Realizar operaciones, decidir versiones y registrar seguimiento. |
| Auditor | Consultar información, verificar bitácora y ejecutar simulaciones de sensibilidad. |

Los permisos se comprueban en el servidor. Los triggers de MySQL impiden que quien propone una versión sea también quien la aprueba. Las cuentas bloqueadas no pueden iniciar sesión ni continuar usando una sesión web existente. El registro público está deshabilitado.

## Instalación local

### Requisitos

- PHP 8.3 con las extensiones necesarias para Laravel, incluyendo PDO MySQL, Mbstring, OpenSSL, Fileinfo, DOM/XML y ZIP.
- Composer 2.
- Node.js 22.12 o superior compatible con Vite 7; también puede utilizar Node.js 24.
- MySQL 8.4 LTS y una base vacía dedicada al proyecto.
- Git.

La base utiliza funciones, procedimientos almacenados, triggers, vistas, JSON, restricciones CHECK y columnas generadas. Las migraciones no están diseñadas para SQLite ni validadas con MariaDB.

### 1. Obtener el código

```powershell
git clone https://github.com/1815ChristianSierra/Auditoria-Matriz-LA-FT.git
cd Auditoria-Matriz-LA-FT
composer install
npm ci
Copy-Item .env.example .env
```

El repositorio es privado: la cuenta de GitHub utilizada debe tener acceso. Si ya existe un archivo `.env`, conserve su configuración en lugar de reemplazarlo.

### 2. Configurar el entorno

Cree una base de datos MySQL vacía y ajuste `.env` con su conexión local:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auditoria_matriz
DB_USERNAME=USUARIO_LOCAL
DB_PASSWORD=CLAVE_LOCAL

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

Reemplace los valores de conexión por los de su equipo. `APP_DEBUG=true` y las cookies sin HTTPS se muestran únicamente para desarrollo local. No suba el archivo `.env` al repositorio.

### 3. Inicializar la aplicación

```powershell
php artisan key:generate
php artisan migrate
php artisan app:create-admin
npm run build
php artisan serve
```

El comando `app:create-admin` solicita nombre, usuario, correo y una clave de al menos 12 caracteres, con confirmación oculta. También inicializa los roles necesarios sin crear cuentas de demostración ni reemplazar usuarios existentes.

Abra **http://127.0.0.1:8000** e ingrese con el correo y la clave del administrador creado.

Para trabajar con recarga de assets durante el desarrollo, ejecute `npm run dev` en otra terminal. Para el despliegue se utiliza `npm run build`.

## Primer uso con una base vacía

1. Ingrese como administrador y cree las cuentas necesarias desde **Usuarios**, incluyendo un supervisor distinto.
2. Registre los factores y los demás catálogos que utilizará el proyecto.
3. Cree la primera versión desde **Versiones**. Introduzca los umbrales de las bandas Medio, Alto y Crítico; la banda Bajo comienza en 0 y el puntaje máximo es 100.
4. Complete pesos, escalas, topes y reglas en el borrador. Los pesos deben sumar 100 para proponer la versión.
5. Proponga la versión y haga que otro usuario con rol supervisor la revise y apruebe.
6. Registre clientes y una evaluación con la versión aprobada; agregue eventos, califique factores y consulte los resultados.
7. Registre el seguimiento y compruebe la bitácora.

No se cargan automáticamente clientes, cuentas de ejemplo ni parámetros de riesgo operativos. La configuración de la primera versión requiere decisiones del equipo responsable.

## Despliegue en Railway

Se despliega la aplicación completa en un servicio y MySQL en otro servicio del mismo proyecto. La interfaz Blade y los assets se sirven desde Laravel; no se requiere Vercel para esta configuración.

1. Agregue un servicio **MySQL** con volumen persistente.
2. Conecte este repositorio a un servicio de aplicación y use como raíz la carpeta que contiene `Dockerfile`, `artisan` y `railway.json`.
3. Configure las variables tomando como referencia [`.env.railway.example`](.env.railway.example), incluyendo una `APP_KEY` propia y las referencias privadas al servicio MySQL.
4. Railway construirá los assets y la imagen mediante Docker. `railway.json` ejecuta las migraciones antes del despliegue, **sin seeders**.
5. Genere un dominio HTTPS, configure `APP_URL` y compruebe `/health`.
6. En una terminal del contenedor de la aplicación, ejecute `php artisan app:create-admin` para crear el primer acceso.

La guía detallada incluye variables, conexión privada, permisos de MySQL, primer acceso y resolución de fallos: **[RAILWAY.md](RAILWAY.md)**.

### Configuración de producción

- Mantenga `APP_ENV=production`, `APP_DEBUG=false` y `SESSION_SECURE_COOKIE=true`.
- Conserve la misma `APP_KEY` entre despliegues y guarde los secretos únicamente en las variables del servicio.
- Use `SESSION_DRIVER=database` para conservar las sesiones al reemplazar el contenedor y `LOG_CHANNEL=stderr` para consultar los logs en Railway.
- No ejecute `migrate:fresh`, `migrate:refresh` ni `db:wipe` contra una base con datos que deba conservar.
- No ejecute el seeder de usuarios de demostración en producción: está bloqueado explícitamente. El seeder general solo crea roles en ese entorno.
- La recuperación de contraseña por correo está deshabilitada hasta configurar un transporte real y habilitar las rutas correspondientes. El administrador puede restablecer claves desde Usuarios.
- Haga copias de seguridad de MySQL y compruebe su restauración antes de actualizar una instalación con datos.

## Pruebas

Utilice exclusivamente una base MySQL desechable. Algunas pruebas recrean las tablas: **no use la base de producción**.

La aplicación de pruebas exige `APP_ENV=testing` y un nombre de base que termine en `_qa`, `_test` o `testing`. Configure un archivo `.env.testing` separado con su conexión, una clave de pruebas y `SESSION_DRIVER=array`. Puede partir de `.env.example`, ajustando todos esos valores antes de ejecutar la suite.

```powershell
php artisan config:clear
php artisan key:generate --env=testing
php artisan test
```

Para comprobar la compilación:

```powershell
npm run build
```

La validación registrada durante la preparación obtuvo **27 pruebas y 94 comprobaciones sin fallos**, además de migraciones completas en MySQL 8.4 y compilación de assets. Consulte [VALIDACION.md](VALIDACION.md) para conocer el alcance, las versiones verificadas y las comprobaciones pendientes; estos resultados locales no confirman por sí solos un despliegue remoto.

## Estructura del proyecto

```text
app/                  Controladores, modelos, middleware y comandos
config/               Configuración de la aplicación
database/             Migraciones, factories y seeders
public/               Entrada web y assets públicos
resources/            Vistas Blade y fuentes de Vite
routes/               Rutas web, API y consola
railway/              Configuración de Apache y arranque del contenedor
tests/                Pruebas automatizadas
Dockerfile            Construcción de assets y aplicación
railway.json          Configuración del despliegue
RAILWAY.md            Guía de publicación y operación inicial
VALIDACION.md         Resultados y límites de la verificación
```

## Contribuciones

Trabaje con una rama para cada cambio y describa el problema, la solución y las comprobaciones realizadas al abrir una solicitud de cambios. Conserve los archivos lock y acompañe los cambios del esquema con migraciones revisadas. Nunca incluya claves, tokens, archivos `.env` reales ni datos personales en commits o capturas.
