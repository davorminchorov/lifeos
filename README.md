# Davor Minchorov's LifeOS

LifeOS is a personal management system built with Laravel to track and manage various aspects of daily life.

## Features

- **Monthly Subscription Tracking**: Manage and monitor recurring subscription services, payment dates, and costs
- **Recurring Utility Bills Tracking**: Track utility bills, due dates, and payment history with automated reminders
- **Investments Tracking**: Monitor investment portfolio across multiple categories (Life insurance, Investment funds, etc.), track performance metrics, and transaction history
- **Job Application Tracking**: Keep track of job applications, interview status, and follow-ups
- **Expenses Tracking**: Log and categorize daily expenses with budget setting and analysis features

## Technology Stack

- **Backend**: 
  - Laravel 12+
  - Event Sourcing for complete history and audit trails
  - Vertical Slices Architecture with bounded contexts
  - Laravel Reverb for WebSockets
  - Laravel Filament for admin interfaces
  - File storage for attachments

- **Frontend**: 
  - React 19+ with TypeScript
  - TanStack React Query for data fetching
  - XState for state machines
  - Tailwind CSS 4+ for styling
  - Radix UI for accessible components
  - Vite for frontend build tooling

- **Database**: 
  - MySQL/PostgreSQL for relational data
  - Event Store for domain events

- **Authentication**: Single-user application with Laravel Sanctum
- **Mobile Support**: Responsive design for mobile devices
- **Data Management**: Import/Export capabilities for backup and migration

## Architecture Overview

### Vertical Slices with Event Sourcing

The application is structured in vertical slices (bounded contexts) where each feature is implemented as a complete stack from UI to data layer:

1. **Authentication**
   - Single-user authentication
   - Profile management

2. **Subscription Management**
   - Commands: AddSubscription, UpdateSubscription, CancelSubscription, RecordPayment
   - Events: SubscriptionAdded, SubscriptionUpdated, SubscriptionCancelled, PaymentRecorded
   - Projections: ActiveSubscriptions, UpcomingPayments, MonthlyExpenditure

3. **Utility Bills Management**
   - Commands: AddBill, UpdateBill, PayBill, ScheduleReminder
   - Events: BillAdded, BillUpdated, BillPaid, ReminderScheduled, ReminderSent
   - Projections: PendingBills, PaymentHistory, UpcomingReminders

4. **Investment Portfolio**
   - Commands: CreateInvestment, RecordTransaction, UpdateValuation
   - Events: InvestmentCreated, TransactionRecorded, ValuationUpdated
   - Projections: PortfolioSummary, InvestmentPerformance (basic ROI)

5. **Job Application Pipeline**
   - Commands: SubmitApplication, ScheduleInterview, RecordOutcome
   - Events: ApplicationSubmitted, InterviewScheduled, OutcomeRecorded
   - Projections: ActiveApplications, InterviewSchedule, ApplicationHistory

6. **Expense Tracking**
   - Commands: RecordExpense, CategorizeExpense, SetBudget
   - Events: ExpenseRecorded, ExpenseCategorized, BudgetSet, BudgetExceeded
   - Projections: MonthlyExpenses, CategorySpending, BudgetPerformance

7. **Dashboard & Analytics**
   - Cross-domain reporting
   - Data visualization with Chart.js
   - Budget performance analysis

### Event Sourcing Implementation

- **Event Store**: Records all domain events as immutable facts
- **Aggregates**: Domain models that emit and apply events
- **Projections**: Read models built from event streams
- **Command Handlers**: Process commands and coordinate domain logic
- **Real-time Updates**: WebSockets broadcast projection updates to UI using Laravel Reverb

## Project Structure

```
app/
├── Core/                     # Core application components
├── Authentication/           # Authentication bounded context
├── Dashboard/                # Dashboard and analytics
├── Expenses/                 # Expense tracking bounded context
├── Http/                     # HTTP layer components
├── Investments/              # Investment tracking bounded context
├── JobApplications/          # Job application bounded context
├── Models/                   # Base model definitions
├── Providers/                # Service providers
├── Subscriptions/            # Subscription management bounded context
└── UtilityBills/             # Utility bills bounded context

resources/
├── js/                       # Frontend JavaScript/TypeScript
├── css/                      # CSS and Tailwind styles
└── views/                    # Blade templates for SSR
```

## Documentation

Comprehensive documentation is available in the `docs/` directory:

### Frontend
- [Frontend Architecture](docs/frontend-architecture.md)
- [Frontend Development Guidelines](docs/frontend-development.md)
- [UI Component Development](docs/frontend-components.md)
- [Frontend Implementation Guide](docs/frontend-implementation.md)

### Backend
- [Backend Architecture](docs/backend-architecture.md)
- [Backend Implementation Guide](docs/backend-implementation.md)
- [API Design Guidelines](docs/api-design.md)
- [Event Sourcing Guide](docs/event-sourcing.md)
- [Testing Strategy](docs/testing-strategy.md)
- [Projection Rebuilding](docs/projection-rebuilding.md)

### DevOps and Performance
- [Deployment Guide](docs/deployment-guide.md)
- [Performance Optimization](docs/performance-optimization.md)
- [Architecture Diagrams](docs/architecture-diagrams.md)

### Real-time Features
- [Reverb Integration](docs/reverb-integration.md)

### Design and Frontend
- [Design System](docs/design-system.md)

### System Documentation
- [Development Guidelines](docs/development-guidelines.md)
- [Coding Standards](docs/coding-standards.md)
- [Domain-Driven Design](docs/domain-driven-design.md)
- [Event Storming Plan](docs/event-storming-plan.md)

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL or PostgreSQL
- Node.js 18+ with npm/pnpm/yarn

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

### Option 2: Using Docker Compose

1. Clone the repository:
   ```
   git clone https://github.com/davorminchorov/lifeos.git
   cd lifeos
   ```
2. Copy `.env.example` to `.env`:
   ```
   cp .env.example .env
   ```
3. Start the Docker containers:
   ```
   docker-compose up -d
   ```
4. Install dependencies:
   ```
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```
5. Generate application key:
   ```
   docker-compose exec app php artisan key:generate
   ```
6. Run migrations:
   ```
   docker-compose exec app php artisan migrate
   ```
7. Build frontend assets:
   ```
   docker-compose exec app npm run dev
   ```
8. Access the application at `http://localhost`

## Development Workflow

For local development, you can use the combined dev command:

```
composer dev
```

This will start:
- Laravel development server
- Queue worker
- Laravel Pail for logs
- Vite for frontend assets

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
