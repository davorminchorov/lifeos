# LifeOS

LifeOS is a Laravel-based application designed to help you organize and manage your life effectively.

## Requirements

- [ServerSideUp Spin](https://serversideup.net/open-source/spin/) - Docker development environment
- Docker and Docker Compose
- Node.js (for frontend assets)

## Docker Setup with ServerSideUp Spin

This project uses ServerSideUp Spin for local development, which provides a streamlined Docker-based development environment with automatic SSL certificates and easy service management.

### Installation

1. **Install ServerSideUp Spin**

   Install Spin globally using npm:
   ```bash
   npm install -g @serversideup/spin
   ```

   Or using your preferred package manager:
   ```bash
   yarn global add @serversideup/spin
   # or
   pnpm add -g @serversideup/spin
   ```

2. **Clone and Setup the Project**

   ```bash
   git clone <your-repository-url>
   cd lifeos
   cp .env.example .env  # If .env doesn't exist
   ```

3. **Initialize Spin**

   Initialize Spin in the project directory:
   ```bash
   spin init
   ```

4. **Start the Development Environment**

   Start all services with Spin:
   ```bash
   spin up
   ```

   This will start:
   - **PHP/Laravel** - Main application server
   - **Traefik** - Reverse proxy with automatic SSL
   - **Node.js** - For frontend asset compilation
   - **Mailpit** - Email testing interface (accessible at http://localhost:8025)

### Available Services

- **Application**: http://localhost (with automatic SSL via Traefik)
- **Mailpit**: http://localhost:8025 (Email testing interface)

### Common Development Commands

```bash
# Start the development environment
spin up

# Stop all services
spin down

# View running services
spin ps

# Execute commands in the PHP container
spin exec php php artisan migrate
spin exec php php artisan key:generate

# Install PHP dependencies
spin exec php composer install

# Install and compile frontend assets
spin exec node npm install
spin exec node npm run dev
```

### Project Structure

The project includes the following Docker configuration files:
- `docker-compose.yml` - Base service definitions
- `docker-compose.dev.yml` - Development-specific overrides
- `docker-compose.prod.yml` - Production-specific overrides

### Configuration

The development environment is pre-configured with:
- SQLite database (located at `.infrastructure/volume_data/sqlite/database.sqlite`)
- Automatic SSL certificates via Traefik
- Hot reload for frontend assets
- Email testing with Mailpit

