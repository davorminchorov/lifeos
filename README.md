# Davor Minchorov's LifeOS

LifeOS is a personal management system built with Laravel to track and manage various aspects of daily life.

## Features

- **Monthly Subscription Tracking**: Manage and monitor recurring subscription services, payment dates, and costs
- **Recurring Utility Bills Tracking**: Track utility bills, due dates, and payment history with automated reminders
- **Investments Tracking**: Monitor investment portfolio across multiple categories (Life insurance, Investment funds, etc.), track performance metrics, and transaction history
- **Job Application Tracking**: Keep track of job applications, interview status, and follow-ups
- **Expenses Tracking**: Log and categorize daily expenses with budget setting and analysis features

## Technology Stack

- **Backend**: Laravel 10+
- **Frontend**: 
  - Livewire for interactive dashboard components
  - Filament for admin panel interface
  - Tailwind CSS for styling
- **Database**: MySQL/PostgreSQL
- **Authentication**: Single-user application with sign-in only (no registration)
- **Mobile Support**: Responsive design for mobile devices

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Node.js and NPM

### Option 1: Using Laravel Herd (macOS)

1. Install [Laravel Herd](https://herd.laravel.com/)
2. Clone the repository:
   ```
   git clone https://github.com/davorminchorov/lifeos.git
   cd lifeos
   ```
3. Install dependencies:
   ```
   composer install
   npm install
   ```
4. Copy `.env.example` to `.env` and update the database configuration:
   ```
   cp .env.example .env
   ```
5. Generate application key:
   ```
   php artisan key:generate
   ```
6. Run migrations:
   ```
   php artisan migrate
   ```
7. Start the development server with Vite:
   ```
   npm run dev
   ```
8. Access the application at `http://lifeos.test` (Configure this site in Herd)

### Option 2: Using Laravel Sail (Docker)

1. Clone the repository:
   ```
   git clone https://github.com/davorminchorov/lifeos.git
   cd lifeos
   ```
2. Copy `.env.example` to `.env`:
   ```
   cp .env.example .env
   ```
3. Install Composer dependencies:
   ```
   docker run --rm \
       -v "$(pwd)":/opt \
       -w /opt \
       laravelsail/php81-composer:latest \
       composer install --ignore-platform-reqs
   ```
4. Start Laravel Sail:
   ```
   ./vendor/bin/sail up -d
   ```
5. Generate application key:
   ```
   ./vendor/bin/sail artisan key:generate
   ```
6. Run migrations:
   ```
   ./vendor/bin/sail artisan migrate
   ```
7. Install and compile frontend assets:
   ```
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```
8. Access the application at `http://localhost`

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).