# LifeOS Deployment Guide

This document outlines the process for deploying the LifeOS application to various environments, including staging and production.

## Deployment Architecture

LifeOS is designed with a modern deployment architecture:

1. **Web Server**: Nginx for serving static assets and routing requests
2. **Application Server**: PHP-FPM for handling application code
3. **Database**: MySQL/PostgreSQL for relational data and event store
4. **WebSocket Server**: Laravel Reverb for real-time updates
5. **Cache**: Redis for application caching and session storage
6. **Queue Workers**: For handling background processing

## Prerequisites

Before deploying, ensure you have:

- A server with at least 2GB RAM (4GB recommended)
- PHP 8.1+ installed with required extensions
- Composer installed globally
- MySQL 8.0+ or PostgreSQL 14+
- Nginx web server
- Redis server
- Git access to the repository
- SSL certificate for secure HTTPS connections

## Environment Setup

### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server git curl zip unzip

# Install PHP and extensions
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-pgsql \
  php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath \
  php8.1-intl php8.1-gd php8.1-redis
```

### 2. Database Setup

```bash
# For MySQL
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE lifeos;
CREATE USER 'lifeos'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON lifeos.* TO 'lifeos'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure Nginx

Create a new Nginx server block configuration:

```
# /etc/nginx/sites-available/lifeos
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/lifeos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/lifeos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. SSL Configuration

Use Certbot to obtain and configure SSL:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Application Deployment

### 1. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/davorminchorov/lifeos.git
cd lifeos
sudo chown -R www-data:www-data .
```

### 2. Install Dependencies

```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build
```

### 3. Configure Environment

```bash
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

Edit the `.env` file with your production settings:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lifeos
DB_USERNAME=lifeos
DB_PASSWORD=your-secure-password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

REVERB_APP_ID=lifeos
REVERB_APP_KEY=your-reverb-app-key
REVERB_APP_SECRET=your-reverb-app-secret
REVERB_HOST=reverb.your-domain.com
```

### 4. Database Migration

```bash
sudo -u www-data php artisan migrate --force
```

### 5. Optimize Application

```bash
sudo -u www-data php artisan optimize
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
```

### 6. Set Up Laravel Reverb

Create a Supervisor configuration for Laravel Reverb:

```
# /etc/supervisor/conf.d/reverb.conf
[program:reverb]
process_name=%(program_name)s
command=php /var/www/lifeos/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/lifeos/storage/logs/reverb.log
stopwaitsecs=3600
```

### 7. Set Up Queue Workers

Create a Supervisor configuration for queue workers:

```
# /etc/supervisor/conf.d/lifeos-worker.conf
[program:lifeos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lifeos/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lifeos/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor processes:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
sudo supervisorctl start lifeos-worker:*
```

### 8. Set Up Scheduler

Add Laravel scheduler to crontab:

```bash
sudo -u www-data crontab -e
```

Add the following line:

```
* * * * * cd /var/www/lifeos && php artisan schedule:run >> /dev/null 2>&1
```

## Deployment Automation

### Using GitHub Actions

Create a `.github/workflows/deploy.yml` file:

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Set up SSH
        uses: webfactory/ssh-agent@v0.7.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
      
      - name: Deploy to Production
        run: |
          ssh -o StrictHostKeyChecking=no user@your-domain.com "cd /var/www/lifeos && \
          git pull origin main && \
          composer install --no-dev --optimize-autoloader && \
          npm install && \
          npm run build && \
          php artisan migrate --force && \
          php artisan optimize && \
          php artisan route:cache && \
          php artisan view:cache && \
          php artisan event:cache && \
          sudo supervisorctl restart reverb:* && \
          sudo supervisorctl restart lifeos-worker:*"
```

### Using Laravel Forge

If using Laravel Forge for deployment:

1. Create a new site in Forge
2. Connect your GitHub repository
3. Configure deployment script:

```bash
cd $FORGE_SITE
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
```

4. Set up SSL certificate
5. Configure environment variables
6. Configure Daemon for Laravel Reverb

## Zero-Downtime Deployment

For zero-downtime deployment, consider using a blue-green deployment strategy:

1. Set up two identical production environments (blue and green)
2. Deploy to the inactive environment
3. Run tests to ensure everything works
4. Switch traffic from active to inactive environment
5. The previously active environment becomes inactive

### Using Laravel Envoyer

Laravel Envoyer automates zero-downtime deployments:

1. Create a project in Envoyer
2. Configure your repository and server
3. Set up your deployment hooks:

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install and compile npm assets
npm ci
npm run build

# Laravel commands
php artisan migrate --force
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

4. Configure activation hook:

```bash
php artisan queue:restart
```

## Rollback Procedures

If deployment fails, follow these rollback procedures:

### Manual Rollback

```bash
cd /var/www/lifeos
git reset --hard HEAD~1
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
```

### Using Envoyer

Simply activate a previous deployment in the Envoyer dashboard.

## Monitoring and Maintenance

### 1. Log Monitoring

Set up log monitoring using Papertrail or ELK stack:

```bash
# Install Filebeat (for ELK)
curl -L -O https://artifacts.elastic.co/downloads/beats/filebeat/filebeat-8.6.0-amd64.deb
sudo dpkg -i filebeat-8.6.0-amd64.deb

# Configure Filebeat
sudo nano /etc/filebeat/filebeat.yml
```

### 2. Performance Monitoring

Set up Laravel Telescope in a secure production environment:

```bash
composer require laravel/telescope
php artisan telescope:install
```

Configure `.env` for Telescope:

```
TELESCOPE_ENABLED=true
TELESCOPE_PATH=telescope-dashboard
```

Secure Telescope with authentication middleware.

### 3. Backup Strategy

Set up automated backups:

```bash
# Install Laravel Backup Package
composer require spatie/laravel-backup

# Configure backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Configure `.env` for backups:

```
BACKUP_ARCHIVE_PASSWORD=your-secure-password
BACKUP_DESTINATION=s3
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket
```

Schedule backups:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:clean')->daily()->at('01:00');
    $schedule->command('backup:run')->daily()->at('02:00');
}
```

## Specialized Event Sourcing Deployment Considerations

### 1. Event Store Migration

When deploying schema changes to the event store:

```bash
# Create migration for stored_events table
php artisan make:migration add_new_field_to_stored_events_table

# Apply migration with caution
php artisan migrate --path=database/migrations/xxxx_xx_xx_add_new_field_to_stored_events_table.php
```

### 2. Projection Rebuilding

After deployment, you may need to rebuild projections:

```bash
# Rebuild all projections
php artisan event-sourcing:replay

# Rebuild specific projector
php artisan event-sourcing:replay "App\\Domain\\Subscriptions\\Projections\\SubscriptionProjector"
```

To minimize downtime during rebuilds:

1. Create temporary projections tables
2. Rebuild projections to temporary tables
3. Swap tables when complete

### 3. Event Versioning Deployment

When deploying changes to event schemas:

1. First deploy event upcasters
2. Then deploy code that produces new event versions

## Security Hardening

After deployment, perform these security hardening steps:

1. **File Permissions**:
```bash
sudo find /var/www/lifeos -type f -exec chmod 644 {} \;
sudo find /var/www/lifeos -type d -exec chmod 755 {} \;
sudo chmod -R ug+rwx /var/www/lifeos/storage /var/www/lifeos/bootstrap/cache
```

2. **Firewall Configuration**:
```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow ssh
sudo ufw enable
```

3. **Fail2Ban Setup**:
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## Troubleshooting

### Common Issues and Solutions

1. **500 Server Error**:
   - Check storage directory permissions
   - Review Laravel logs at `/var/www/lifeos/storage/logs/laravel.log`

2. **WebSocket Connection Issues**:
   - Verify Reverb daemon is running: `sudo supervisorctl status reverb`
   - Check Reverb logs: `/var/www/lifeos/storage/logs/reverb.log`

3. **Database Connection Issues**:
   - Confirm database credentials in `.env`
   - Check MySQL/PostgreSQL service: `sudo systemctl status mysql`

4. **Cache Issues**:
   - Clear application cache: `php artisan cache:clear`
   - Rebuild optimized classes: `php artisan optimize:clear && php artisan optimize`

5. **Queue Not Processing**:
   - Check queue worker status: `sudo supervisorctl status lifeos-worker`
   - Restart queue workers: `sudo supervisorctl restart lifeos-worker:*`

## Deployment Checklist

Before deploying to production, complete this checklist:

- [ ] Review all code changes and merge to main branch
- [ ] Run all tests and ensure they pass
- [ ] Update `.env` with production settings
- [ ] Ensure database backups are in place
- [ ] Verify supervisor configurations
- [ ] Configure proper logging
- [ ] Set up monitoring
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set up firewall rules
- [ ] Update documentation if needed
- [ ] Perform a test deployment to staging environment
- [ ] Check the deployed application for any issues
- [ ] Execute deployment to production
- [ ] Verify critical functionality after deployment 
