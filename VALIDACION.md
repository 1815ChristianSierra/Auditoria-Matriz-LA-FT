# Validación de la preparación

Fecha: 24 de octubre de 2026. Se trabajó en una copia extraída del ZIP, sin modificar las referencias sincronizadas ni el proyecto original del Escritorio.

## Resultado

| Comprobación | Resultado |
| --- | --- |
| Composer validate | Correcto |
| Instalación de dependencias PHP con lock | Correcta |
| Actualización de dependencias | Laravel 12.70.1, Sanctum 4.3.3, PHP 8.3.36 utilizado en QA |
| Auditoría Composer posterior | Sin alertas conocidas reportadas |
| Auditoría npm posterior | 0 vulnerabilidades reportadas |
| Vite 7.3.7, npm run build | Correcto, manifiesto y CSS/JS producidos |
| Sintaxis de app, config, routes y database | 130 archivos PHP comprobados antes de la actualización; las rutas y pruebas posteriores cargaron el código actualizado |
| Migraciones completas | 35 ejecutadas correctamente en MySQL 8.4.13 local; la suite posterior volvió a crear el esquema con Laravel 12 |
| Funciones, procedimientos, triggers y vistas | Se crearon sin omitir migraciones |
| Suite completa final | 27 pruebas, 94 comprobaciones, ninguna falla |
| Cachés de rutas, Blade y configuración | Generación correcta con Laravel 12; la caché temporal de configuración se limpió |
| Referencias estáticas de assets en Blade | Se detectó y corrigió la referencia al logo ausente; verificación final en el empaquetado |
| Construcción de imagen Docker | Pendiente: motor Docker no disponible en este equipo |
| Publicación y comprobación remota Railway | Pendiente: la sesión de Railway guardada caducó |

Las auditorías de paquetes representan el estado consultado en la fecha de revisión; no garantizan ausencia de vulnerabilidades futuras.

## Cobertura de las pruebas

La suite comprueba autenticación, cierre de sesión, verificación de correo, cambio de clave, perfil, registro público cerrado, recuperación por correo cerrada, rechazo de cuentas bloqueadas, permisos del auditor, roles repetibles, bloqueo del seeder demo en producción, healthcheck y creación auditable del primer borrador con umbrales definidos por el usuario. Se verifica que la vista de integridad de bitácora no reporte alteración tras esa creación.

También se comprueban nueve pantallas principales con base vacía y la redirección prevista de crear evento cuando no existe una evaluación seleccionada. Los umbrales usados en pruebas son sintéticos y las transacciones se revierten; no se cargaron parámetros de riesgo operativos.

No se verificó de extremo a extremo todo el ciclo de cálculo de riesgo, decisiones y concurrencia de la bitácora en Railway. La guía pide una evaluación sintética después de desplegar y antes de usar datos reales. No se cargaron seeders de cuentas en producción ni se imprimieron claves, tokens o contraseñas de servicios.

## Problemas del ZIP resueltos

- Faltaba una configuración reproducible para compilar Vite y servir Laravel completo.
- Laravel 10 presentaba alertas de seguridad y estaba fuera de soporte; se actualizó la copia a Laravel 12.
- Vite y el analizador CSS tenían alertas; se actualizaron con lock y compilación posterior.
- Los seeders originales incluían cuentas con claves de demostración y dependían de IDs de roles fijos. Ahora no se ejecutan en producción; el administrador obtiene su rol por nombre.
- Existían rutas públicas de registro incompatibles con los campos obligatorios agregados a usuarios.
- Las rutas del negocio no comprobaban roles y el login aceptaba cuentas bloqueadas.
- Las sesiones locales se perderían en un reemplazo del contenedor; se agrega almacenamiento en MySQL.
- La base vacía no podía crear su primera versión porque exigía clonar otra.
- La pantalla de login publicaba accesos de ejemplo y un correo prellenado.
- La referencia a `public/images/logo.png` apuntaba a un archivo ausente. Se añadió un monograma vectorial sencillo y se actualizó esa referencia, sin asumir un logotipo institucional.

## Entrega

El ZIP preparado incluye la aplicación completa, configuración Docker/Railway, ejemplos de variables sin secretos, guía, pruebas y assets compilados. Excluye vendor, node_modules, sesiones, logs, cachés de ejecución y archivos .env reales. Incluye un listado SHA-256 de los archivos entregados.
