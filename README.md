# LifeOS

![CodeRabbit Pull Request Reviews](https://img.shields.io/coderabbit/prs/github/davorminchorov/lifeos?utm_source=oss&utm_medium=github&utm_campaign=davorminchorov%2Flifeos&labelColor=171717&color=FF570A&link=https%3A%2F%2Fcoderabbit.ai&label=CodeRabbit+Reviews)

LifeOS is a Laravel-based application designed to help you organize and manage your life effectively.

## Features

LifeOS provides a comprehensive suite of tools to manage various aspects of your personal life and finances:

### 🔄 Payment Subscriptions Tracking
- Track all recurring payments (Netflix, Spotify, gym memberships, etc.)
- Cost analysis with monthly/yearly spending breakdowns
- Renewal alerts and notifications
- Cancellation tracking and savings calculation
- Category organization and price change history
- Multi-currency support for international subscriptions
- Advanced analytics including spending trends and category breakdowns

### 📄 Contracts Management  
- Centralized contract repository with document storage
- Expiration management and renewal tracking
- Key terms extraction and performance tracking
- Reminder system for important contract actions
- Contract amendments and termination workflows
- Financial impact tracking with payment schedules

### 🛡️ Warranties Tracking
- Product registration and warranty coverage tracking
- Visual warranty calendar with expiration timeline
- Warranty claim management and status tracking
- Digital receipt and proof of purchase storage
- Transfer tracking for resold items
- Maintenance reminders to preserve warranty coverage

### 📈 Investments Portfolio
- Real-time portfolio overview and performance analytics
- Multi-asset tracking (stocks, bonds, crypto, real estate)
- Investment goal setting and progress tracking
- Tax reporting with capital gains/losses calculations
- Portfolio rebalancing alerts and recommendations
- Transaction history and dividend tracking
- Risk assessment and market data integration

### 💰 Expenses Management
- Comprehensive expense tracking with customizable categories
- Budget management and spending analytics
- Receipt scanning and automatic expense entry
- Recurring expense tracking
- Multi-currency support and tag system
- Reimbursement tracking and bulk operations
- Export capabilities for accounting software integration

### 🏠 Utility Bills Tracking
- Bill calendar with payment due dates
- Usage monitoring and consumption patterns
- Historical cost analysis and trend tracking
- Payment status management
- Provider comparison and rate tracking
- Budget alerts for unusual usage spikes

### 💳 IOU / Debt Tracking
- Track money owed to and from others
- Record payment history and partial payments
- Multiple currency support with automatic conversion
- Status management (pending, paid, cancelled)
- Due date tracking and reminders
- Detailed transaction history
- Mark IOUs as paid or cancel them

### 📊 Budget Management
- Create and manage budgets with customizable periods
- Category-based budget allocation
- Real-time budget tracking and spending analysis
- Budget vs. actual expense comparison
- Period selection (monthly, quarterly, yearly, custom)
- Multi-currency support
- Budget analytics and insights
- Automatic rollover tracking

### 📊 Cross-Module Features
- **Unified Dashboard**: Comprehensive overview of all financial commitments
- **Advanced Analytics**: Spending insights and trend analysis across all modules
- **Global Search**: Search functionality across all data types
- **Custom Reporting**: Generate reports combining data from multiple modules
- **Notification System**: 
  - Centralized notification center with read/unread management
  - Customizable notification preferences (email, database, push)
  - Mark notifications as read or delete them
  - Notification statistics and filtering
  - Configurable reminder timings
- **Mobile Responsive Design**: 
  - Fully optimized mobile experience across all 50+ templates
  - Touch-optimized interfaces with larger touch targets (44px minimum)
  - Responsive table solutions with card-based mobile layouts
  - Collapsible mobile navigation with hamburger menu
  - Mobile-first form optimization
  - Progressive enhancement approach
- **Currency Management**: 
  - Multi-currency support across all financial modules
  - Automatic currency conversion with live exchange rates
  - Real-time currency refresh capabilities
  - Support for 150+ currencies
  - Fallback mechanisms for offline functionality
- **Dark Mode Support**: Complete theme customization
- **Data Export**: Backup and export capabilities

### 🔐 Security & Privacy
- Secure user authentication and authorization
- Encrypted sensitive data storage
- Secure file uploads with validation
- Activity logging for audit trails
- Regular automated backups

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

