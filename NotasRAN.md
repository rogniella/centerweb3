# CenterWeb Versiones  

- Vs 3.00 5/2026 Actualizacion Laravel 13.9.0
   - Se quito: laravelcollective/htm
   - Se cambio: afipsdk  por adrianbarabino/afip-php-libre  (Para manejo    facturación Afip)
   - Incorporo proceso y visualizacion movientos de Tarjetas
- Vs 2.02 5/2023 Mejoras en las consultas de Clientes,Ot y Ventas
- Vs 2.01 5/2023 Agrego posibilidad de Generar Notas de Créditos

# GitHub:     
   github.com  rogniella   r_niella@hotmail.com
   En Rama Main en Repositorio centerweb3 

# Hosting:  lucushost  https://www.lucushost.com/     
   Es Español  vs gratis pero solo 15 dias  HostingWeb   36 U$s año 20gb  Se paga desde 30/5/2020

   https://panel.lucushost.com/clientarea.php
   r_niella@hotmail.com
   BelenRoge6

   Panel de Control:
   https://hl109.lucushost.org:2083/logout/?locale=es
   Usuario:  gtgjotcc
   Clave:    IFD2020practica

   Base de datos: gtgjotcc_centerweb
   Usuario1: gtgjotcc_admin
   =Jeq*9;yr]mz

   Ftp:
   Servidor FTP:  ftp.centerfotooptica.com.ar
   Usuario de FTP: center@centerfotooptica.com.ar
   Puerto FTPS explícito de FTP &:  21
   BelenRoge6

# Instalaciones:
 ##  Instalación Monte Caseros:  1/2024
   https://visual.centerfotooptica.com.ar/
   - Crear Bd limpia  (Con iniComercioOptica_web.txt)
      Base de datos: gtgjotcc_visual
      Usuario1: gtgjotcc_admin
   - Corregir .env

 ## Instalación Facturador Marcos: 
   https://demo.centerfotooptica.com.ar/

 ## En Nic se registro http://www.centerfotooptica.com.ar/
   Se asocio:   Entrar por la AFIP y delegar (cambiar los DNS)
   ns1.lucushost.com   ns2.lucushost.com   ns3.lucushost.com

# Tienda:  (WordPress 6.2)
   Admin URL : https://tienda.centerfotooptica.com.ar/wp-admin/


Pasos para instalar en el Servidor: (Admin3) 5/2026
-----------------------------------
- Copiar en zip todas las carpetas  
- Descompactar  en public_html Ej /admin3
   (Se puede desde el Adm de Archivos del hosting Extraer)
- Actualizar el .env
- Actualizar los key y cert  (del control afip al nuevo: adrianbarabino)
- Crear el SubDominio o cambiar existente en (Panel Contro- Dominios)
    admin3.centerfotooptica.com.ar
    public_html/admin3/public
- Cambiar vs de PHP (en el panel control del hosting) , usar 8.4
- Redireccionar  Trabajos de cron  (El envio de mail automatico)
- Desde la terminal:    cd public_html/admin3  php artisan key:generate



- En el Hosting Habilitar el Acceso remoto a la bases 

   https://www.cual-es-mi-ip.net/   ( Para ver Ip en maquina local )
   181.81.93.10     libres desde 3/23
   45.175.150.244   mercedes actual


Para Importar una copia de la bd desde la Web:
------------------------------------
1)- Desde el panel de Control del Hosting  Archivos / Copias de Seguridad   Bajar el archivo

2)- Desde linea Comando en la carpeta c:/xampp/mysql/bin

   mysql -u usuario -p nombre_basededatos < data.sql
   Ej:
    mysql -u root -p centerweb < data.sql
    mysql -u root -p tienda_center < tienda_center.sql


Instalaciones NoteBook HP: (Para este proyecto)
=========================

- Xampp 8.2.0 / PHP 8.2.0
      Incluye: Apache 2.4.54, MariaDB 10.4.27, PHP 8.2.0, phpMyAdmin 5.2.0, OpenSSL 1.1.1, XAMPP Control Panel 3.2.4

- Composer 2.5.5

- Node y npm:
      Versión actual: 18.15.0 (includes npm 9.5.0)


Proyecto centerweb2 usa:  (Con Laravel Framework 10.5.1)  4/2023
=================
   - Vite		    4.1.1  	 Ya viene instalado
   - Laravel/ui       4.2.1    https://github.com/laravel/ui 
   - Bootstrap        5.3 
   - laravel/lang     3.1.1	 laravel-lang.com/   
   - laravelcollective/html    6.4.0
   - laracasts/flash  3.2.2 
   - barryvdh/laravel-debugbar	3.8.1
   - spipu/html2pdf	5.2.7
   - intervention/image	2.7.2
   - afipsdk/afip.php	0.7.6	
   - phpoffice/phpspreadsheet  1.28
   - composer require codexshaper/laravel-woocommerce

Pasos:
-----
composer create-project laravel/laravel centerweb2       (crea proyecto aprox 49 Mb)
composer require twbs/bootstrap:5.3.0-alpha1
composer require laravel/ui:* 

php artisan ui bootstrap --auth
 (antes editar los archivos para que importe bootstrap, ver tutorial  vite.config.js y resources/js/app.js o en resources/js/bootstrap.js)  

npm install      (el proyecto para a 101 Mb) crea node_modules
npm run build    ( con Vite compila js y css)

composer require barryvdh/laravel-debugbar --dev  
composer require spipu/html2pdf   ***ver dio error  **
composer require intervention/image
composer require afipsdk/afip.php    *** Hay que hacer todos los casos para generar key y los cert ***
composer require phpoffice/phpspreadsheet
composer require codexshaper/laravel-woocommerce

Cambios en php.ini
------------------
- Error en complemento afip
   extension=soap

- Para permitir seleccionar mas de 20 archivos
  max_file_uploads = 200
  Y en el hosting: desde el adm de archivo en el archivo   .htaccess    agregar la liena  php_value max_file_uploads 100

- Si da Error al crear proyectos, en Php.ini:
Por ejemplo, C:\xampp\phpbusque la línea de extensión zip y descoméntelo (elimine el punto y coma) detrás de él. parece extension=zip_ Eso es todo

- Error al instalar  spipu/html2pdf
Cannot use spipu/html2pdf's latest version v5.2.7 as it requires ext-gd * which is missing from your platform.
Para php-8 , solo ubique su php.iniarchivo y elimine el comentario de la línea extension=gd



php artisan config:cache
php artisan cache:clear
php artisan optimize
php artisan clear-compiled

php artisan config:cache

*** si da error raro con las rutas ***
php artisan route:clear

cd public_html/admin3
php artisan optimize
php artisan config:clear

PENDIENTES:
----------
- Home2  - menu con Boostrap 5  (cambio de plantilla)
- Ver porque no toma formato $users->render()

??
- En la migrasion de Facturacion hay casos del PAMI que no pasa a la Web  ej 3/2023 y 5/2022

SELECT  MCaj_Codigo,  MCaj_Monto  FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo where Mcaj_fecMov >= '2023-4-1 00:00:00' and Mcaj_fecMov <= '2023-2-31 23:59:59'  and MCaj_Codigo<>'0900' and Mcod_HyD = 'H'

SELECT  MCaj_Codigo, sum( MCaj_Monto ) , count( MCaj_Monto ) FROM mcaja join mcodigo on  MCaj_Codigo = MCod_Codigo where Mcaj_fecMov >= '2023-2-1 00:00:00' and Mcaj_fecMov <= '2023-2-31 23:59:59'  and MCaj_Codigo<>'0900' and Mcod_HyD = 'H' group by MCaj_Codigo