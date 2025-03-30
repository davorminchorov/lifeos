# LifeOS Project Context

## Project Overview
LifeOS is a personal management system built with Laravel to track and manage various aspects of daily life including subscriptions, utility bills, investments, job applications, and expenses.

## Technology Stack
- Backend: Laravel 12+, Event Sourcing, Vertical Slices Architecture, Laravel Reverb
- Frontend: React 19, Tailwind CSS 4, TypeScript, Vite
- Database: MySQL/PostgreSQL + Event Store
- Authentication: Single-user application with sign-in only

## Architecture Principles
- Vertical Slices with bounded contexts for feature organization
- Event Sourcing for complete history and audit trails
- CQRS pattern separating commands and queries
- Responsive design for mobile support
- Real-time updates via WebSockets (Laravel Reverb)
- Domain-Driven Design tactical patterns for domain modeling

## Core Features
1. Monthly Subscription Tracking
2. Recurring Utility Bills Tracking
3. Investments Tracking
4. Job Application Tracking
5. Expenses Tracking
6. Dashboard & Analytics

## Design System
- Primary Colors: Deep Teal (#0F766E), Slate Blue (#1E293B), Warm White (#F8FAFC)
- Accent Colors: Sunrise Orange (#F97316), Mint Green (#10B981), Ocean Blue (#3B82F6)
- Typography: Inter font family with comprehensive type scale
- Components: Standardized buttons, forms, cards, navigation, and data visualization
- Patterns: Consistent empty states, notifications, loading states, and responsive approaches
- Accessibility: WCAG 2.1 AA compliance throughout the application

## Implementation Approach
- Event Sourcing with projections for read models
- Command pattern for state changes
- Zero-downtime projection rebuilding with blue/green deployment
- Optimistic UI updates via WebSockets integration
- Comprehensive testing strategy for event-sourced systems

## Documentation Structure
- Frontend Development (architecture, guidelines, components)
- Backend Development (architecture, APIs, event sourcing)
- DevOps and Performance (deployment, optimization)
- Real-time Features (WebSocket integration)
- Design System (colors, typography, components)
- System Documentation (guidelines, troubleshooting)

## Event Sourcing Implementation
- Using Spatie's Laravel Event Sourcing package
- Events stored in a dedicated event store
- Aggregates encapsulate business rules
- Projectors rebuild read models from events
- Command handlers validate and process commands
- Blue/green rebuilding strategy for zero-downtime projection updates
- Event versioning with upcasters for schema evolution

## Real-time Updates
- Laravel Reverb for WebSocket communication
- Domain events translated to broadcast events
- Optimistic UI updates with command-event correlation
- Secure channel authorization
- Client reconnection handling with missed event recovery

## Domain-Driven Design Tactical Patterns
- **Core Domain Patterns**:
  - Aggregates: SubscriptionAggregate, BillAggregate, InvestmentAggregate, JobApplicationAggregate, ExpenseAggregate
  - Entities: Core domain objects with identity (Subscription, Bill, Investment, JobApplication, Expense)
  - Value Objects: Immutable objects representing domain concepts (Money, BillingCycle, DateRange, Category, PaymentStatus)
- **Behavioral Patterns**:
  - Domain Events: Immutable past-tense named events (SubscriptionCreated, BillPaid, InvestmentValuationUpdated)
  - Commands: Imperative verbs representing user intentions (CreateSubscription, PayBill, UpdateInvestmentValuation)
  - Repositories: Interfaces for aggregate persistence (SubscriptionRepository, BillRepository, InvestmentRepository)
- **Structural Patterns**:
  - Domain Services: Cross-aggregate business logic (BudgetAnalysisService, ReminderService, PortfolioAnalysisService)
  - Application Services: Orchestration layer connecting UI with domain (CommandBus, QueryBus, EventBus)
  - Factories: Creation logic for complex domain objects (MoneyFactory, AggregateFactory, EventFactory)
- **Data Access Patterns**:
  - Projections: Optimized read models for specific query needs (ActiveSubscriptionsProjection, MonthlyExpenseSummaryProjection)
  - Event Store: System of record for all domain events with versioning and snapshot support

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
