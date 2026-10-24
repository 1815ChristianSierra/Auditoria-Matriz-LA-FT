# Despliegue completo en Railway

El paquete contiene Laravel con Blade, los assets públicos originales y Vite. Railway ejecuta la aplicación completa y un servicio MySQL separado en el mismo proyecto. No necesita Vercel. El ZIP original no se modificó.

## Cambios realizados

- Laravel 10.50.3 se actualizó a Laravel 12.70.1, PHP mínimo 8.3 y Sanctum 4, con sus dependencias de desarrollo compatibles. Se conserva la estructura original de Kernel y proveedores. Laravel 10 dejó de recibir correcciones de seguridad el 4 de febrero de 2025. Las alertas detectadas en ese framework motivaron la actualización.
- Vite 5 pasó a Vite 7 y su integración Laravel a la versión 2. Se fija un analizador CSS corregido mediante `overrides`, manteniendo Tailwind 3. Los archivos lock se incluyen para reproducir la instalación.
- Docker compila Vite y sirve `public/` con Apache/PHP. No usa el servidor de desarrollo de PHP ni Vite en producción. El puerto se toma de Railway.
- `railway.json` configura Docker, migraciones previas al despliegue, comprobación `/health` y reinicio por fallos. Las cachés de configuración, rutas y Blade se generan al arrancar, en el contenedor que sirve la aplicación.
- Se agrega la tabla de sesiones y su almacenamiento en MySQL. Los logs se envían a Railway. Se configura HTTPS mediante el proxy y cookies seguras.
- Se deshabilita el registro público. Las cuentas bloqueadas no pueden entrar ni conservar una sesión web. Los seeders de usuarios demo se rechazan en producción, incluso con `--force`. El seeder general solo inicializa roles en producción y los roles se crean sin duplicarlos.
- `app:create-admin` crea el administrador con clave oculta introducida interactivamente. No usa contraseñas predeterminadas ni sobrescribe cuentas.
- Se aplican permisos en el servidor: administrador gestiona usuarios/catálogos y propone versiones; supervisor decide versiones y seguimiento; analista/supervisor realizan operaciones; auditor consulta y simula. Cada usuario conserva acceso a su perfil. Los triggers siguen imponiendo la separación entre proponente y aprobador. La navegación original puede mostrar acciones que el servidor responderá con 403 según el rol.
- La primera versión ya no exige clonar una existente. El administrador introduce tres umbrales ascendentes con dos decimales; Bajo comienza en 0, Crítico termina en 100 y los intervalos son contiguos. Se guarda un borrador auditable, sin aprobarlo automáticamente ni inventar pesos. Se deben crear factores en Catálogos y completar pesos/escalas antes de proponer.
- Se eliminan accesos rápidos y correos de demostración del login. La recuperación de contraseña por correo queda cerrada hasta configurar un transporte real; el administrador puede restablecer las claves desde Usuarios. La actualización de la clave propia sigue disponible.

## 1. Preparar los servicios

1. Extraiga el ZIP completo y use la carpeta que contiene `artisan`, `Dockerfile` y `railway.json` como raíz del servicio. No use `public/` como raíz del repositorio.
2. En Railway cree un proyecto y agregue un servicio MySQL llamado exactamente `MySQL`. Utilice MySQL 8.4 LTS con volumen persistente; esta aplicación depende de JSON, CHECK, columnas generadas, funciones, procedimientos, triggers y vistas. No sustituya por SQLite ni MariaDB.
3. Agregue un servicio vacío llamado `App`, o conecte un repositorio privado que contenga esta carpeta completa. MySQL debe quedar dentro de la red privada; solo App necesita dominio público.
4. Antes de modificar una base existente, haga una copia de seguridad y compruebe su restauración. Este paquete está probado con una base vacía; no incluye los datos de su instalación local.

## 2. Configurar las variables de App

Use `.env.railway.example` como plantilla en el editor de variables de Railway. No suba un `.env` al repositorio ni pegue valores secretos en el chat.

Las expresiones `${{MySQL.MYSQLHOST}}`, `${{MySQL.MYSQLPORT}}`, `${{MySQL.MYSQLDATABASE}}`, `${{MySQL.MYSQLUSER}}` y `${{MySQL.MYSQLPASSWORD}}` son referencias al servicio MySQL, no valores para reemplazar manualmente. Si el servicio tiene otro nombre, ajuste ese prefijo. No use la dirección pública de MySQL ni `localhost`.

Genere una sola vez una clave con `php artisan key:generate --show` en un equipo de confianza y guárdela como `APP_KEY` en Railway. Mantenga ese valor entre despliegues. También puede generar los 32 bytes aleatorios con PHP sin instalar dependencias:

```powershell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

La clave se muestra en su terminal local: no la comparta ni la guarde en este documento. Establezca `APP_URL` con el dominio HTTPS generado para App. Mantenga `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=database` y `SESSION_SECURE_COOKIE=true`.

`TRUSTED_PROXIES=*` está pensado para el servicio situado detrás del proxy de Railway. No exponga el contenedor por una ruta que permita evitar ese proxy. Use una réplica de App inicialmente; si necesita varias, cambie el caché/limitador de intentos a un almacén compartido como Redis. No se ha encontrado una necesidad actual de workers ni cron: no hay trabajos programados activos y la cola está configurada como síncrona.

## 3. Subir la carpeta entera

Con un repositorio conectado, publique el contenido de esta carpeta en la rama que usa Railway. Alternativamente, abra una terminal dentro de la carpeta extraída y ejecute:

```powershell
railway login
railway link
railway up --service App
```

En `railway link`, seleccione el proyecto y entorno correctos. La sesión encontrada en este equipo estaba caducada: se necesita iniciar sesión de nuevo. No se creó ningún servicio remoto ni se ejecutaron migraciones en producción durante la preparación.

Railway detectará el Dockerfile. No configure otro comando de build ni `php artisan serve`. Mantenga el comando previo definido en `railway.json`: `php artisan migrate --force --no-interaction`. No añada `--seed`. El fallo de una migración impide continuar con el nuevo despliegue. Las operaciones DDL de MySQL no son completamente transaccionales: si una migración falla a mitad, revise el estado antes de reintentar.

Si falla con permisos de funciones o triggers, compruebe privilegios CREATE ROUTINE, ALTER ROUTINE, EXECUTE, TRIGGER y CREATE VIEW en esa base. Si aparece el error 1419 con binlog, un administrador de MySQL debe revisar `log_bin_trust_function_creators`; no desactive restricciones a ciegas ni modifique el código para omitir la bitácora. La función original declara DETERMINISTIC NO SQL. No se cambia ninguna variable global automáticamente.

## 4. Primer acceso

Después del despliegue exitoso, use una terminal del contenedor App en Railway (o `railway ssh` seleccionando App) y ejecute:

```sh
php artisan app:create-admin
```

Introduzca nombre, usuario, correo y una clave única de al menos 12 caracteres. La clave y su confirmación se ocultan. No use `railway run` para inicializar la base desde su equipo: las referencias privadas de MySQL están previstas para ejecutarse dentro de Railway. No ejecute UserSeeder, migrate:fresh, db:wipe ni migrate:refresh en producción.

Entre por el correo del administrador creado. Cree las cuentas de analista, supervisor y auditor desde Usuarios; sus IDs de rol se obtienen de la base. Cree el catálogo de factores, la primera versión y los parámetros; propóngala y haga que un supervisor distinto la apruebe antes de registrar evaluaciones. El sistema queda vacío a propósito: no se cargaron clientes ni parámetros de demostración.

## 5. Verificación en Railway

- Genere el dominio de App en Settings / Networking y actualice APP_URL.
- Compruebe `/health`: debe devolver HTTP 200 y `{"status":"ok"}`. El endpoint verifica la conexión a MySQL y oculta los detalles de errores; no sustituye una prueba de negocio.
- Compruebe login, CSS/JS, persistencia de sesión después de reiniciar App y las restricciones de cada rol. `/register` y `/forgot-password` deben responder 404.
- Complete una evaluación sintética y compruebe resultado, seguimiento y verificación de bitácora antes de cargar datos reales.
- Active copias de seguridad del volumen/base y pruebe restauración. Para una actualización, respalde la base y despliegue el paquete completo; no regenere APP_KEY. Para volver a una versión anterior del código, confirme primero compatibilidad con el esquema: no ejecute rollback de base sin revisar sus efectos.

No hay cargas de archivos implementadas que requieran un volumen adicional de App en el código revisado. Si incorpora adjuntos, use almacenamiento persistente u objetos; el disco del contenedor no conserva esos archivos entre despliegues.

## Pruebas locales y límites

Consulte `VALIDACION.md`. La imagen Docker no se pudo construir aquí porque el motor Docker no estaba disponible. El despliegue remoto y la comprobación por dominio siguen pendientes del inicio de sesión de Railway y de elegir el proyecto. Las pruebas locales no equivalen a una auditoría integral de seguridad o a certificar el modelo de riesgo académico.

Las pruebas de PHP requieren una base MySQL desechable cuyo nombre termine en `_qa`, `_test` o `testing`, con `APP_ENV=testing`, clave de pruebas y SESSION_DRIVER=array. La suite puede recrear tablas: jamás use la base de Railway de producción. Se añadió una comprobación para rechazar nombres distintos antes de iniciar las pruebas.

## Documentación consultada

- Laravel y su base en Railway: https://docs.railway.com/guides/laravel
- Comandos previos: https://docs.railway.com/deployments/pre-deploy-command
- MySQL: https://docs.railway.com/databases/mysql
- Soporte de Laravel 10: https://laravel.com/framework/docs/10.x/releases
- Actualizaciones conservando la estructura: https://laravel.com/framework/docs/11.x/upgrade
- Actualización a Laravel 12: https://laravel.com/framework/docs/12.x/upgrade
