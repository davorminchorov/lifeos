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
  - File storage for attachments

- **Frontend**: 
  - React 19 (Single Page Application)
  - Tailwind CSS 4 for styling
  - Vite for frontend build tooling
  - Modern TypeScript with module system

- **Database**: 
  - MySQL/PostgreSQL for relational data
  - Event Store for domain events

- **Authentication**: Single-user application with sign-in only (no registration)
- **Mobile Support**: Responsive design for mobile devices
- **Data Management**: Import/Export capabilities for backup and migration

## Architecture Overview

### Vertical Slices with Event Sourcing

The application is structured in vertical slices (bounded contexts) where each feature is implemented as a complete stack from UI to data layer:

1. **Identity & Authentication**
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
   - Data visualization
   - Budget performance analysis

### Event Sourcing Implementation

- **Event Store**: Records all domain events as immutable facts
- **Aggregates**: Domain models that emit and apply events
- **Projections**: Read models built from event streams
- **Command Handlers**: Process commands and coordinate domain logic
- **Real-time Updates**: WebSockets broadcast projection updates to UI

## Implementation Plan

### Phase 1: Foundation
- Project setup with Laravel, React 19, and Tailwind CSS 4
- Event sourcing infrastructure
- Authentication system
- Base UI components and layouts

### Phase 2: Core Features
- Subscription management implementation
- Utility bills system with reminders
- Basic expense tracking
- Dashboard foundation

### Phase 3: Advanced Features
- Investment portfolio tracking with basic ROI calculations
- Job application pipeline
- Budget analysis tools
- File attachment system
- WebSocket real-time updates with Laravel Reverb

### Phase 4: Refinement
- Import/Export functionality
- UI/UX improvements
- Performance optimization
- Reporting enhancements
- Mobile responsiveness

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Node.js 18+ (Node.js 23 recommended)
- NPM

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