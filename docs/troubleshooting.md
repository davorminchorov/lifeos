# LifeOS Troubleshooting Guide

This guide covers common issues you might encounter when setting up, developing, or using the LifeOS application.

## Development Environment Issues

### Docker Container Issues

**Problem**: Docker containers fail to start or have connection issues.
**Solution**: 
1. Make sure Docker is running on your system
2. Try stopping and removing the containers, then rebuilding:
   ```
   docker-compose down
   docker-compose up -d --build
   ```
3. Check for port conflicts with other running services

**Problem**: Database connection errors in Docker environment.
**Solution**:
1. Verify database container is running: `docker-compose ps`
2. Check the database credentials in your `.env` file
3. Ensure the database hostname matches the service name in docker-compose.yml
4. Wait a moment for the database to fully initialize before connecting

### Composer Dependencies Issues

**Problem**: Composer install fails with PHP version requirements.
**Solution**:
1. Ensure you're using PHP 8.2 or higher: `php -v`
2. If using Docker, validate the PHP version in your container
3. Try clearing Composer cache: `composer clear-cache`
4. Update Composer: `composer self-update`

### NPM Dependencies Issues

**Problem**: NPM/Vite build errors.
**Solution**:
1. Clear npm cache: `npm cache clean --force`
2. Delete node_modules and reinstall: `rm -rf node_modules && npm install`
3. Check Node.js version (18+ required): `node -v`
4. Update npm: `npm install -g npm@latest`

## Application Setup Issues

### Migration Errors

**Problem**: Database migrations fail.
**Solution**:
1. Ensure database connection is properly configured in `.env`
2. Try refreshing the database: `php artisan migrate:fresh`
3. For specific migration errors, check the error message for clues about table or column issues
4. If a migration is stuck, try: `php artisan migrate:refresh`

### Laravel Key Generation

**Problem**: "No application encryption key has been specified" error.
**Solution**:
1. Generate a new application key: `php artisan key:generate`
2. Verify the key is set in your `.env` file
3. Restart the web server after generating the key

### Storage Permissions

**Problem**: File upload or storage issues.
**Solution**:
1. Ensure storage directory is writable: `chmod -R 775 storage`
2. Create symbolic link: `php artisan storage:link`
3. In Docker, check container permissions and volumes

## Runtime Issues

### WebSocket Connection Problems

**Problem**: WebSocket connections fail or do not receive real-time updates.
**Solution**:
1. Verify Laravel Reverb is running: `php artisan reverb:start`
2. Check WebSocket configuration in `.env`
3. Inspect browser console for connection errors
4. Ensure the frontend is properly configured with Laravel Echo

### Event Sourcing Issues

**Problem**: Projections not updating or events not processing.
**Solution**:
1. Check if queue worker is running: `php artisan queue:work`
2. Verify event handlers are registered in service providers
3. Rebuild projections: `php artisan event-sourcing:rebuild {projector}`
4. Check logs for event processing errors

### Performance Issues

**Problem**: Slow application response times.
**Solution**:
1. Enable Laravel query logging to identify slow queries
2. Check if projections need optimization
3. Verify frontend caching and data fetching strategies
4. Consider running `php artisan optimize` in production

## Authentication Issues

**Problem**: Authentication fails or sessions expire quickly.
**Solution**:
1. Check Sanctum configuration in `config/sanctum.php`
2. Verify session configuration in `config/session.php`
3. Clear browser cookies and cache
4. Check for CORS issues if using a separate frontend domain

## TypeScript and Frontend Issues

**Problem**: TypeScript type errors.
**Solution**:
1. Run the type checking script: `npm run typecheck`
2. Update TypeScript definitions if needed
3. Check import paths and module resolution
4. Verify compatibility between React and TypeScript versions

**Problem**: React components not rendering or updating properly.
**Solution**:
1. Check state management with React Query and XState
2. Verify component props and key prop usage
3. Use React Developer Tools to inspect component hierarchies
4. Look for console errors in the browser developer tools

## Deployment Issues

**Problem**: Application fails after deployment.
**Solution**:
1. Verify environment variables on the production server
2. Check file permissions on the server
3. Run `php artisan optimize` after deployment
4. Ensure all required PHP extensions are installed
5. Verify server requirements match Laravel 12 needs

## Common Error Codes

### HTTP 500 Errors
- Check Laravel logs: `storage/logs/laravel.log`
- Verify PHP error logs
- Ensure all dependencies are installed
- Check file permissions

### HTTP 419 Errors
- CSRF token mismatch
- Regenerate token: `php artisan session:clear`
- Verify forms include CSRF token field
- Check session configuration

### HTTP 403 Errors
- Permission issues
- Check authorization policies
- Verify user authentication status
- Ensure proper CORS headers if using API

## Getting Help

If you've tried the solutions above and still have issues:

1. Check the existing [documentation](../docs)
2. Search for similar issues in the project repository
3. Open a detailed issue including:
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Log outputs
   - Environment details (PHP version, Laravel version, etc.) 
