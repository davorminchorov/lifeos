# LifeOS Project Context

## Project Overview
LifeOS is a personal management system built with Laravel to track and manage various aspects of daily life including subscriptions, utility bills, investments, job applications, and expenses.

## Technology Stack
- Backend: 
  - Laravel 12+
  - Event Sourcing architecture
  - Vertical Slices Architecture with bounded contexts
  - Laravel Reverb for WebSockets
  - Laravel Filament for admin interfaces
  - PHP 8.2+

- Frontend: 
  - React 19+ with TypeScript
  - TanStack React Query for data fetching
  - XState for state machines
  - Tailwind CSS 4+ for styling
  - Radix UI for accessible components
  - Vite for frontend build tooling
  - Chart.js for data visualization

- Database: 
  - MySQL/PostgreSQL for relational data
  - Event Store for domain events

- Authentication: Single-user application with Laravel Sanctum
- Environment: Docker-based development setup with options for Laravel Herd on macOS

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

docs/                         # Comprehensive documentation
├── frontend-architecture.md  # Frontend architecture documentation
├── backend-architecture.md   # Backend architecture documentation
└── ...                       # Various other documentation files
```

## Architecture Principles
- Vertical slices (bounded contexts) for feature organization
- Event Sourcing for complete history and audit trails
- CQRS pattern separating commands and queries
- Responsive design for mobile support
- Real-time updates via WebSockets (Laravel Reverb)
- Domain-Driven Design tactical patterns for domain modeling

## Core Features
1. **Subscription Management**
   - Commands: AddSubscription, UpdateSubscription, CancelSubscription, RecordPayment
   - Events: SubscriptionAdded, SubscriptionUpdated, SubscriptionCancelled, PaymentRecorded
   - Projections: ActiveSubscriptions, UpcomingPayments, MonthlyExpenditure

2. **Utility Bills Management**
   - Commands: AddBill, UpdateBill, PayBill, ScheduleReminder
   - Events: BillAdded, BillUpdated, BillPaid, ReminderScheduled, ReminderSent
   - Projections: PendingBills, PaymentHistory, UpcomingReminders

3. **Investment Portfolio**
   - Commands: CreateInvestment, RecordTransaction, UpdateValuation
   - Events: InvestmentCreated, TransactionRecorded, ValuationUpdated
   - Projections: PortfolioSummary, InvestmentPerformance (basic ROI)

4. **Job Application Pipeline**
   - Commands: SubmitApplication, ScheduleInterview, RecordOutcome
   - Events: ApplicationSubmitted, InterviewScheduled, OutcomeRecorded
   - Projections: ActiveApplications, InterviewSchedule, ApplicationHistory

5. **Expense Tracking**
   - Commands: RecordExpense, CategorizeExpense, SetBudget
   - Events: ExpenseRecorded, ExpenseCategorized, BudgetSet, BudgetExceeded
   - Projections: MonthlyExpenses, CategorySpending, BudgetPerformance

6. **Dashboard & Analytics**
   - Cross-domain reporting
   - Data visualization with Chart.js
   - Budget performance analysis

## Frontend Implementation
- Vertical slice architecture organizing code by business capability
- Feature-specific stores using XState for state machines
- React Query for API communication with caching
- React Hook Form for form handling
- Tailwind CSS 4 for styling with custom design system
- Radix UI for accessible component primitives
- Real-time updates via Laravel Echo and Pusher

## Event Sourcing Implementation
- Events as the primary source of truth
- Command handlers validating and processing commands
- Aggregates applying and emitting domain events
- Projectors building read models from event streams
- Real-time updates via WebSockets when projections change

## Development Workflow
- Local development using composer dev command (starts server, queue worker, logs, and vite)
- Docker-based environment for consistent development experience
- TypeScript type checking with custom check-ts.cjs script
- Laravel Pail for improved log visualization

## Documentation Structure
The project includes comprehensive documentation in the docs/ directory covering:
- Frontend and backend architecture
- Event sourcing implementation details
- API design guidelines
- Testing strategy
- Deployment procedures
- Performance optimization
- Design system specifications
- Development guidelines and coding standards

## Design System
- Defined in docs/design-system.md with comprehensive component library
- Tailwind CSS 4 for utility-based styling
- Accessible components built on Radix UI primitives
- Consistent typography, spacing, and color systems
- Mobile-first responsive design approach

## Key Development Guidelines
- Follow vertical slice architecture for new features
- Implement event sourcing patterns consistently
- Write comprehensive tests for all components
- Adhere to the design system for UI consistency
- Optimize for both desktop and mobile experiences
- Ensure accessibility compliance in all UI components
- Document new patterns and components
- Use TypeScript for type safety across the frontend

## Key Documentation Resources
- [Backend Architecture](docs/backend-architecture.md)
- [Event Sourcing Guide](docs/event-sourcing.md)
- [Testing Strategy](docs/testing-strategy.md)
- [Domain-Driven Design](docs/domain-driven-design.md)
- [Projection Rebuilding](docs/projection-rebuilding.md)
- [Reverb Integration](docs/reverb-integration.md)
- [Design System](docs/design-system.md)
- [Deployment Guide](docs/deployment-guide.md)
- [Performance Optimization](docs/performance-optimization.md)
- [Architecture Diagrams](docs/architecture-diagrams.md)

## Development Guidelines
- Follow established architecture patterns
- Implement DDD tactical patterns consistently across domains
- Use immutable value objects for domain concepts with validation
- Define clear aggregate boundaries to protect business invariants
- Version domain events explicitly for schema evolution
- Adhere to the design system for visual consistency
- Write comprehensive tests for all new features
- Document new components and patterns
- Optimize for both desktop and mobile experiences
- Ensure all features are accessible
- Consider real-time aspects for all state changes
- Keep aggregates small and focused on core business rules
- Implement proper command validation before processing
- Design projections for specific query needs 
