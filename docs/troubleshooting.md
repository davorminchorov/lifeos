# LifeOS Troubleshooting Guide

This document provides solutions for common issues encountered during development of the LifeOS project.

## Vite & Asset Loading Issues

### White Screen / Assets Not Loading

**Symptoms:**
- Blank/white screen when loading the application
- Browser console shows 404 errors for CSS/JS assets
- Page structure visible but unstyled

**Solutions:**

1. **Check Vite Dev Server:**
   ```bash
   # Start the Vite dev server
   ./vendor/bin/sail npm run dev
   ```

2. **Verify .env Configuration:**
   ```
   # For development with Vite dev server
   VITE_MANIFEST_POLL=false
   VITE_DEV_SERVER_KEY=
   VITE_DEV_SERVER_URL=http://localhost:5173
   
   # For production/built assets
   VITE_MANIFEST_POLL=true
   VITE_DEV_SERVER_KEY=false
   VITE_DEV_SERVER_URL=
   ```

3. **Check app.blade.php Configuration:**
   - Ensure Vite directive is correctly set up
   ```php
   @vite(['resources/css/app.css', 'resources/js/app.tsx'])
   ```

4. **Clear Laravel Caches:**
   ```bash
   ./vendor/bin/sail artisan optimize:clear
   ```

5. **Rebuild Assets:**
   ```bash
   ./vendor/bin/sail npm run build
   ```

6. **Check for Symlink Issues:**
   - Ensure the manifest.json is correctly linked:
   ```bash
   # Check if manifest exists
   ls -la public/build/.vite/manifest.json
   
   # Create symlink if needed
   ln -sf public/build/.vite/manifest.json public/build/manifest.json
   ```

### Tailwind CSS Issues

**Symptoms:**
- CSS classes not applying
- Build errors about unknown utility classes
- Components appear unstyled

**Solutions:**

1. **Verify Tailwind Configuration:**
   - Check tailwind.config.js content paths
   - Ensure proper theme extension

2. **Use Direct CSS Instead of Tailwind Directives:**
   - If having issues with @apply directives, use standard CSS

3. **Check for CSS Syntax Errors:**
   - Look for missing semicolons or brackets
   - Validate nested CSS rules

4. **Update Tailwind Content Configuration:**
   ```js
   // tailwind.config.js
   content: [
     './resources/**/*.blade.php',
     './resources/**/*.js',
     './resources/**/*.jsx',
     './resources/**/*.ts',
     './resources/**/*.tsx',
   ],
   ```

## React/Inertia Issues

### Component Rendering Problems

**Symptoms:**
- Components render incorrectly
- Missing UI elements
- Console errors about undefined props

**Solutions:**

1. **Check Browser Console for Errors:**
   - Look for JavaScript errors
   - Verify props are being passed correctly

2. **Verify Inertia Setup:**
   - Check HandleInertiaRequests middleware
   - Ensure @inertia directive is in app.blade.php

3. **Debug Props:**
   - Add console.log statements to check data
   - Use React DevTools to inspect component state

4. **Clear Browser Cache:**
   - Use hard refresh (Ctrl+F5 or Cmd+Shift+R)
   - Try incognito/private browsing mode

### TypeScript Errors

**Symptoms:**
- Build fails with type errors
- IDE shows type mismatch warnings
- Runtime errors about undefined properties

**Solutions:**

1. **Add Proper Type Definitions:**
   - Define interfaces for all props
   - Use proper type annotations

2. **Update tsconfig.json:**
   - Check compilation options
   - Ensure paths are correctly configured

## Docker/Sail Issues

### Container Problems

**Symptoms:**
- Services unavailable
- Connection refused errors
- Database connection issues

**Solutions:**

1. **Restart Containers:**
   ```bash
   ./vendor/bin/sail down
   ./vendor/bin/sail up -d
   ```

2. **Check Container Status:**
   ```bash
   docker ps | grep lifeos
   ```

3. **View Container Logs:**
   ```bash
   ./vendor/bin/sail logs
   ```

4. **Rebuild Containers:**
   ```bash
   ./vendor/bin/sail build --no-cache
   ```

### Database Connection Issues

**Symptoms:**
- PDOException errors
- Database connection failures
- Missing tables/data

**Solutions:**

1. **Check Database Configuration:**
   - Verify .env database settings
   - Ensure MySQL container is running

2. **Run Migrations:**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

3. **Refresh Database:**
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

## Production Deployment Issues

### Environment Configuration

**Symptoms:**
- Different behavior in production vs development
- Missing environment variables
- Features working locally but not in production

**Solutions:**

1. **Set Production Environment Variables:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Build Production Assets:**
   ```bash
   ./vendor/bin/sail npm run build
   ```

3. **Optimize Laravel:**
   ```bash
   ./vendor/bin/sail artisan optimize
   ```

## Performance Optimization

### Slow Page Loading

**Symptoms:**
- Pages take a long time to load
- Assets download slowly
- UI feels sluggish

**Solutions:**

1. **Optimize Asset Size:**
   - Use code splitting
   - Compress images
   - Minimize CSS/JS

2. **Enable Caching:**
   ```bash
   ./vendor/bin/sail artisan route:cache
   ./vendor/bin/sail artisan config:cache
   ```

3. **Use Production Mode:**
   - Set APP_ENV=production in .env
   - Disable debug mode

## Common Error Messages and Solutions

### "Unknown utility class" in Tailwind

**Error:**
```
error: resources/css/app.css:X:Y: Unknown utility class
```

**Solution:**
- Check Tailwind configuration
- Use standard CSS instead of problematic utility
- Verify class is properly defined in theme extension

### "Cannot find module" in JavaScript/TypeScript

**Error:**
```
Error: Cannot find module './components/Example'
```

**Solution:**
- Check file paths and imports
- Verify file extensions (.js, .tsx, etc.)
- Rebuild node_modules if necessary

### "PDOException: Connection refused"

**Error:**
```
PDOException: SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
- Restart MySQL container
- Check database credentials in .env
- Verify MySQL service is running 
