# Deployment (VPS)

## DocumentRoot
Set the web server DocumentRoot to `public/`.

## Install dependencies
```bash
composer install --no-dev --optimize-autoloader
```

## Permissions
Ensure the following directories are writable by the web server user:
- `storage/`
- `bootstrap/cache/`

## Nginx example
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/app/public;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
}
```

## Environment
- `APP_ENV=prod`
- `APP_DEBUG=0`

## Restart services
Restart PHP-FPM/Nginx after deploy if needed.
