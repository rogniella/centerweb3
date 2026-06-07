# Deploy con Git + cPanel (LucusHost)

## Estructura de carpetas en el hosting

Laravel no se deploya completo dentro de `public_html`. Se separa la app del directorio web:

```
/home/usuario/
├── laravel-app/        ← app completa (fuera del public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── .cpanel.yml
└── public_html/        ← symlink → laravel-app/public
```

## Crear `.cpanel.yml` en la raíz del proyecto

Archivo de configuración que cPanel usa al deployar:

```yaml
---
deployment:
  tasks:
    - export DEPLOYPATH=/home/tu-usuario/laravel-app
    - /bin/cp -R app $DEPLOYPATH
    - /bin/cp -R bootstrap $DEPLOYPATH
    - /bin/cp -R config $DEPLOYPATH
    - /bin/cp -R database $DEPLOYPATH
    - /bin/cp -R public $DEPLOYPATH
    - /bin/cp -R resources $DEPLOYPATH
    - /bin/cp -R routes $DEPLOYPATH
    - /bin/cp -R storage $DEPLOYPATH
    - /bin/cp composer.json $DEPLOYPATH
    - /bin/cp composer.lock $DEPLOYPATH
    - /bin/cp artisan $DEPLOYPATH
    - cd $DEPLOYPATH
    - /usr/local/bin/composer install --no-dev --optimize-autoloader
    - /bin/cp .env.production $DEPLOYPATH/.env
    - /usr/local/bin/php artisan optimize:clear
    - /usr/local/bin/php artisan config:cache
    - /usr/local/bin/php artisan route:cache
    - /usr/local/bin/php artisan view:cache
    - /usr/local/bin/php artisan migrate --force
    - chmod -R 775 storage bootstrap/cache
```

> Reemplazar `tu-usuario` por el usuario real de cPanel.
> El archivo `.env.production` debe existir en el repo con las credenciales de producción.

## Configurar symlink de `public/`

Desde SSH de cPanel (o Terminal en File Manager):

```bash
cd ~
mv public_html public_html_backup  # si ya existe contenido
ln -s ~/laravel-app/public public_html
```

## Configurar Git Version Control en cPanel

1. Entrar a cPanel → **Files** → **Git Version Control**
2. Hacer clic en **Create** → **Clone a Repository**
   - **Clone URL**: `https://github.com/tu-usuario/tu-repo.git` (o SSH para repos privados)
   - **Repository Path**: `/home/tu-usuario/repositories/centerweb3`
   - **Repository Name**: `centerweb3`
3. Si el repo es privado: cPanel → **SSH Access** → generar key → agregar la key pública a GitHub/GitLab

## Flujo de actualización

### Opción A — Manual desde cPanel

1. Ir a **Git Version Control** → **Manage**
2. **Update from Remote** — trae los últimos cambios del repo
3. **Deploy HEAD Commit** — ejecuta `.cpanel.yml` y actualiza la app

### Opción B — Automático (Webhook)

1. En cPanel → Manage repo → habilitar **Automatic Deployment**
2. Copiar la URL del **Deployment Hook**
3. En GitHub → repo → **Settings** → **Webhooks** → **Add webhook**
4. Pegar la URL del hook
5. Cada `git push` dispara el deploy automáticamente

## Consideraciones

- **Permisos**: `storage/` y `bootstrap/cache/` necesitan permisos 775 para escritura del servidor web
- **Archivos generados**: `/public/facturas`, `/public/remitos`, `/public/salidas`, `/public/presupuestos` están en `.gitignore` y no se sobreescriben en el deploy
- **Base de datos**: las migraciones corren automáticamente con `migrate --force` al deployar
- **Rollback**: desde cPanel se puede redeployar un commit anterior si algo falla
