# Event Storming Plan for LifeOS

This document outlines the complete event storming approach for modeling the LifeOS application domains. Event storming is a collaborative discovery process that helps identify domain events, commands, actors, policies, and read models through a series of structured workshops.

## Table of Contents

- [Setup and Preparation](#setup-and-preparation)
- [Session Schedule](#session-schedule)
- [Session 1: Chaotic Exploration](#session-1-chaotic-exploration)
- [Session 2: Domain Exploration](#session-2-domain-exploration)
  - [Subscription Domain](#subscription-domain)
  - [Bills Domain](#bills-domain)
  - [Expenses Domain](#expenses-domain)
  - [Investments Domain](#investments-domain)
  - [Job Applications Domain](#job-applications-domain)
- [Session 3: Cross-Domain Integration](#session-3-cross-domain-integration)
- [Session 4: Technical Modeling](#session-4-technical-modeling)
- [Session 5: Hotspot Resolution](#session-5-hotspot-resolution)
- [Post-Event Storming Activities](#post-event-storming-activities)
- [Implementation Roadmap](#implementation-roadmap)

## Setup and Preparation

### Materials and Environment

- **Digital Board Setup**: Miro/Mural with color-coded sticky notes:
  - 🟠 **Orange**: Domain Events (past tense verbs, "what happened")
  - 🔵 **Blue**: Commands (imperative verbs, "do something")
  - 🟡 **Yellow**: Actors/Users (who issues commands)
  - 🟣 **Purple**: Aggregates (consistency boundaries)
  - 🟢 **Green**: Policies/Reactions (when X happens, do Y)
  - 🔴 **Red**: Hotspots/Questions (issues, unknowns)
  - 🟤 **Brown**: External Systems
  - 💗 **Pink**: Read Models (views/projections)

- **Board Layout**:
  - Timeline layout with swim lanes for each bounded context
  - Space for glossary and key decisions
  - Parking lot for future considerations

### Participants

- **Core Developer(s)**: Technical implementation perspective
- **Domain Expert(s)**: Personal finance knowledge
- **Facilitator**: Guide discussion and keep focus
- **Observer/Note-taker**: Document key decisions and questions

### Pre-Workshop Activities

1. **Domain Research**:
   - Research existing personal finance tools
   - Collect sample subscription and bill records
   - Understand investment tracking requirements
   - Define scope boundaries

2. **Participant Preparation**:
   - Distribute brief introduction to event storming
   - Share DDD terminology reference guide
   - Provide overview of event sourcing principles

## Session Schedule

| Session | Duration | Focus |
|---------|----------|-------|
| Session 1 | 3 hours | Chaotic Exploration - Big Picture |
| Session 2A | 3 hours | Subscription & Bills Domains |
| Session 2B | 3 hours | Expenses Domain |
| Session 2C | 3 hours | Investments Domain |
| Session 2D | 3 hours | Job Applications Domain |
| Session 3 | 4 hours | Cross-Domain Integration |
| Session 4 | 4 hours | Technical Modeling |
| Session 5 | 3 hours | Hotspot Resolution & Refinement |

## Session 1: Chaotic Exploration

### Objectives

- Identify major business events across all domains
- Create rough timeline of system behaviors
- Identify key bounded contexts

### Activities

1. **Brain Dump Domain Events** (60 minutes)
   - All participants place orange stickies for any events they can think of
   - Focus on business-relevant occurrences
   - Use past tense verb phrases
   - Example events:
     - SubscriptionCreated
     - BillPaid
     - ExpenseRecorded
     - InvestmentPurchased
     - JobApplicationSubmitted

2. **Timeline Organization** (45 minutes)
   - Arrange events chronologically
   - Group related events together
   - Identify event clusters

3. **Initial Context Mapping** (45 minutes)
   - Draw boundaries around event clusters
   - Label potential bounded contexts
   - Identify relationships between contexts

4. **Hotspot Identification** (30 minutes)
   - Mark unclear areas with red stickies
   - Note areas requiring more discussion
   - Document questions for domain experts

## Session 2: Domain Exploration

### Subscription Domain

#### Domain Events (Orange)

- **SubscriptionCreated**
  - Data: SubscriptionId, UserId, Name, Amount, BillingCycle, StartDate, NextBillingDate
  - Significance: New recurring payment obligation established

- **SubscriptionRenewed**
  - Data: SubscriptionId, RenewalDate, NextBillingDate, Amount
  - Significance: Subscription period extended, payment expected

- **SubscriptionCancelled**
  - Data: SubscriptionId, CancellationDate, Reason
  - Significance: Recurring obligation terminated

- **SubscriptionPaymentRecorded**
  - Data: SubscriptionId, PaymentId, Amount, PaymentDate, Method
  - Significance: Payment for subscription period received

- **SubscriptionAmountUpdated**
  - Data: SubscriptionId, OldAmount, NewAmount, EffectiveDate
  - Significance: Cost of subscription changed

- **SubscriptionBillingCycleChanged**
  - Data: SubscriptionId, OldCycle, NewCycle, EffectiveDate
  - Significance: Frequency of billing changed

- **RenewalReminderSent**
  - Data: SubscriptionId, ReminderDate, RenewalDueDate
  - Significance: User notified of upcoming renewal

#### Commands (Blue)

- **CreateSubscription**
  - Data: Name, Amount, Currency, BillingCycle, StartDate
  - Validation: Amount > 0, Valid BillingCycle, StartDate not in past

- **CancelSubscription**
  - Data: SubscriptionId, CancellationDate, Reason
  - Validation: Subscription exists and is active

- **RenewSubscription**
  - Data: SubscriptionId, RenewalDate
  - Validation: Subscription exists and is active

- **UpdateSubscriptionAmount**
  - Data: SubscriptionId, NewAmount, EffectiveDate
  - Validation: NewAmount > 0

- **ChangeBillingCycle**
  - Data: SubscriptionId, NewCycle, EffectiveDate
  - Validation: Valid BillingCycle

- **RecordSubscriptionPayment**
  - Data: SubscriptionId, Amount, PaymentDate, Method
  - Validation: Amount matches subscription amount

#### Actors (Yellow)

- **User**
  - Issues most commands directly

- **System/Scheduler**
  - Automates renewals and reminders

#### Aggregates (Purple)

- **SubscriptionAggregate**
  - Identity: SubscriptionId
  - Entities: Subscription
  - Value Objects: Money, BillingCycle, SubscriptionStatus
  - Invariants:
    - Amount must be positive
    - Cannot cancel already cancelled subscription
    - Billing cycle must be valid (monthly, quarterly, yearly)

#### Policies/Reactions (Green)

- **When SubscriptionCreated →**
  - Schedule first renewal reminder (7 days before NextBillingDate)
  - Update monthly spending projections

- **When SubscriptionRenewed →**
  - Schedule next renewal reminder
  - Update subscription metrics

- **When approaching renewal date →**
  - Send renewal notification to user

- **When SubscriptionCancelled →**
  - Remove pending renewal reminders
  - Update monthly spending projections

#### Read Models (Pink)

- **ActiveSubscriptionsView**
  - Purpose: Show current active subscriptions
  - Used by: Dashboard, Subscription List
  - Updated by: SubscriptionCreated, SubscriptionCancelled

- **UpcomingRenewalsView**
  - Purpose: Show subscriptions due for renewal soon
  - Used by: Dashboard, Calendar View
  - Updated by: SubscriptionCreated, SubscriptionRenewed, SubscriptionCancelled

- **SubscriptionHistoryView**
  - Purpose: Show history of subscription changes
  - Used by: Subscription Detail View
  - Updated by: All subscription-related events

- **TotalMonthlySubscriptionCostView**
  - Purpose: Show total monthly spending on subscriptions
  - Used by: Dashboard, Budget View
  - Updated by: SubscriptionCreated, SubscriptionCancelled, SubscriptionAmountUpdated

### Bills Domain

#### Domain Events (Orange)

- **BillCreated**
  - Data: BillId, UserId, Payee, Amount, DueDate, Category
  - Significance: New payment obligation established

- **BillPaid**
  - Data: BillId, PaymentDate, Amount, Method
  - Significance: Bill obligation satisfied

- **BillPaymentScheduled**
  - Data: BillId, ScheduledDate, Amount
  - Significance: Future payment planned

- **BillDueDateUpdated**
  - Data: BillId, OldDueDate, NewDueDate
  - Significance: Timeline for payment changed

- **BillAmountUpdated**
  - Data: BillId, OldAmount, NewAmount
  - Significance: Payment obligation amount changed

- **BillMarkedOverdue**
  - Data: BillId, DueDate, DaysOverdue
  - Significance: Payment deadline missed

- **RecurringBillGenerated**
  - Data: TemplateBillId, NewBillId, DueDate, Amount
  - Significance: New bill instance created from template

#### Commands (Blue)

- **CreateBill**
  - Data: Payee, Amount, DueDate, Category, Notes
  - Validation: Amount > 0, DueDate valid

- **PayBill**
  - Data: BillId, PaymentDate, Amount, Method
  - Validation: Bill exists, Amount <= BillAmount

- **ScheduleBillPayment**
  - Data: BillId, ScheduledDate, Amount
  - Validation: ScheduledDate not in past

- **SetupRecurringBill**
  - Data: Payee, Amount, FirstDueDate, Frequency, Category
  - Validation: Amount > 0, FirstDueDate valid, Frequency valid

- **UpdateBillDueDate**
  - Data: BillId, NewDueDate
  - Validation: NewDueDate not in past (if unpaid)

- **UpdateBillAmount**
  - Data: BillId, NewAmount
  - Validation: NewAmount > 0

#### Actors (Yellow)

- **User**
  - Issues most commands directly

- **System/Scheduler**
  - Generates recurring bills
  - Marks bills as overdue

#### Aggregates (Purple)

- **BillAggregate**
  - Identity: BillId
  - Entities: Bill
  - Value Objects: Money, BillStatus, PaymentMethod
  - Invariants:
    - Cannot pay more than bill amount
    - Cannot pay already fully paid bill
    - Due date cannot be in past for new bills

- **RecurringBillAggregate**
  - Identity: RecurringBillId
  - Entities: RecurringBillTemplate
  - Value Objects: Money, Frequency
  - Invariants:
    - Frequency must be valid
    - Amount must be positive

#### Policies/Reactions (Green)

- **When BillCreated →**
  - Schedule due date reminder (3 days before)
  - Update monthly expense projections

- **When BillPaid →**
  - Remove due date reminders
  - Update payment history
  - Update budget tracking

- **When BillDueDateApproaching →**
  - Send payment reminder to user

- **When DueDatePassed and BillUnpaid →**
  - Mark bill as overdue
  - Send overdue notification

- **When RecurringBillDue →**
  - Generate new bill instance

#### Read Models (Pink)

- **UpcomingBillsView**
  - Purpose: Show bills due soon
  - Used by: Dashboard, Bills List
  - Updated by: BillCreated, BillPaid, BillDueDateUpdated

- **OverdueBillsView**
  - Purpose: Show unpaid bills past due date
  - Used by: Dashboard, Bills List
  - Updated by: BillMarkedOverdue, BillPaid

- **BillPaymentHistoryView**
  - Purpose: Show history of bill payments
  - Used by: Reports, Bill Detail View
  - Updated by: BillPaid

- **MonthlyBillsSummaryView**
  - Purpose: Show total bills by month
  - Used by: Dashboard, Budget View
  - Updated by: BillCreated, BillPaid, BillAmountUpdated

### Expenses Domain

#### Domain Events (Orange)

- **ExpenseRecorded**
  - Data: ExpenseId, UserId, Amount, Date, Description, ReceiptUrl
  - Significance: Money spent and recorded

- **ExpenseCategorized**
  - Data: ExpenseId, Category, PreviousCategory
  - Significance: Expense classified for reporting

- **ExpenseReceiptAttached**
  - Data: ExpenseId, ReceiptUrl, UploadDate
  - Significance: Documentation added to expense

- **ExpenseAmountUpdated**
  - Data: ExpenseId, OldAmount, NewAmount
  - Significance: Expense value corrected

- **ExpenseDateUpdated**
  - Data: ExpenseId, OldDate, NewDate
  - Significance: Expense timing corrected

- **BudgetLimitReached**
  - Data: Category, BudgetAmount, CurrentSpending, Date
  - Significance: Spending threshold hit in category

- **MonthlyExpenseSummaryGenerated**
  - Data: Month, Year, TotalSpent, BreakdownByCategory
  - Significance: Period spending analyzed

#### Commands (Blue)

- **RecordExpense**
  - Data: Amount, Date, Description, Category, ReceiptImage
  - Validation: Amount > 0, Date not in future

- **CategorizeExpense**
  - Data: ExpenseId, Category
  - Validation: Category is valid

- **AttachReceipt**
  - Data: ExpenseId, ReceiptImage
  - Validation: File is image, ExpenseId exists

- **UpdateExpenseAmount**
  - Data: ExpenseId, NewAmount
  - Validation: NewAmount > 0

- **UpdateExpenseDate**
  - Data: ExpenseId, NewDate
  - Validation: NewDate not in future

- **SetBudgetLimit**
  - Data: Category, Amount, Period
  - Validation: Amount > 0, Period valid

- **GenerateExpenseReport**
  - Data: StartDate, EndDate, GroupBy
  - Validation: StartDate <= EndDate

#### Actors (Yellow)

- **User**
  - Records and categorizes expenses
  - Sets budget limits

- **System/Scheduler**
  - Generates monthly reports
  - Monitors budget limits

#### Aggregates (Purple)

- **ExpenseAggregate**
  - Identity: ExpenseId
  - Entities: Expense
  - Value Objects: Money, Category, ExpenseDate
  - Invariants:
    - Amount must be positive
    - Date cannot be in future
    - Category must be valid

- **BudgetAggregate**
  - Identity: BudgetId (Category + Period)
  - Entities: Budget
  - Value Objects: Money, BudgetPeriod, Category
  - Invariants:
    - Limit must be positive
    - Period must be valid (weekly, monthly, yearly)

#### Policies/Reactions (Green)

- **When ExpenseRecorded →**
  - Update budget tracker for category
  - Update monthly spending totals

- **When ExpenseCategorized →**
  - Update budget trackers for old and new categories
  - Recalculate category spending metrics

- **When ExpenseAmountUpdated →**
  - Update budget trackers
  - Recalculate spending metrics

- **When BudgetLimitReached →**
  - Send alert notification to user
  - Flag category in budget view

- **When Month Ends →**
  - Generate monthly expense summary
  - Reset monthly budget trackers

#### Read Models (Pink)

- **ExpensesByCategory**
  - Purpose: Show expenses grouped by category
  - Used by: Reports, Category Detail View
  - Updated by: ExpenseRecorded, ExpenseCategorized

- **MonthlyExpenseBreakdown**
  - Purpose: Show expenses for current month
  - Used by: Dashboard, Monthly Report
  - Updated by: ExpenseRecorded, ExpenseAmountUpdated, ExpenseDateUpdated

- **BudgetProgressTracker**
  - Purpose: Show spending vs budget by category
  - Used by: Dashboard, Budget View
  - Updated by: ExpenseRecorded, ExpenseCategorized, BudgetLimitSet

- **YearlyExpenseTrends**
  - Purpose: Show spending patterns over time
  - Used by: Analytics View
  - Updated by: ExpenseRecorded, MonthlyExpenseSummaryGenerated

### Investments Domain

#### Domain Events (Orange)

- **InvestmentCreated**
  - Data: InvestmentId, UserId, Name, Type, PurchaseDate, PurchaseValue, Quantity
  - Significance: New investment asset acquired

- **InvestmentValuationUpdated**
  - Data: InvestmentId, PreviousValue, NewValue, ValuationDate
  - Significance: Investment value changed

- **InvestmentSold**
  - Data: InvestmentId, SellDate, SellValue, Quantity
  - Significance: Investment asset (partially) liquidated

- **DividendReceived**
  - Data: InvestmentId, Amount, Date
  - Significance: Income from investment received

- **PortfolioRebalanced**
  - Data: PortfolioId, Date, OldAllocation, NewAllocation
  - Significance: Investment mix changed

- **InvestmentPerformanceCalculated**
  - Data: InvestmentId/PortfolioId, StartDate, EndDate, ReturnRate, GainLoss
  - Significance: Investment performance measured

#### Commands (Blue)

- **CreateInvestment**
  - Data: Name, Type, PurchaseDate, PurchaseValue, Quantity, Notes
  - Validation: PurchaseValue > 0, Quantity > 0

- **UpdateValuation**
  - Data: InvestmentId, NewValue, ValuationDate
  - Validation: NewValue >= 0, ValuationDate not in future

- **SellInvestment**
  - Data: InvestmentId, SellDate, SellValue, Quantity
  - Validation: Quantity <= CurrentQuantity, SellValue >= 0

- **RecordDividend**
  - Data: InvestmentId, Amount, Date
  - Validation: Amount > 0, Date not in future

- **RebalancePortfolio**
  - Data: PortfolioId, NewAllocation
  - Validation: Allocation percentages sum to 100%

- **CalculatePerformance**
  - Data: InvestmentId/PortfolioId, StartDate, EndDate
  - Validation: StartDate < EndDate

#### Actors (Yellow)

- **User**
  - Records investments and updates
  - Tracks performance

- **System/Scheduler**
  - Regular valuation updates (for some asset types)
  - Periodic performance calculations

#### Aggregates (Purple)

- **InvestmentAggregate**
  - Identity: InvestmentId
  - Entities: Investment
  - Value Objects: Money, InvestmentType, Quantity
  - Invariants:
    - Cannot sell more than owned quantity
    - Valuation cannot be negative
    - Purchase date cannot be in future

- **PortfolioAggregate**
  - Identity: PortfolioId
  - Entities: Portfolio, PortfolioAllocation
  - Value Objects: Money, AllocationPercentage
  - Invariants:
    - Allocation percentages must sum to 100%
    - Portfolio must contain at least one investment

#### Policies/Reactions (Green)

- **When InvestmentCreated →**
  - Update portfolio allocation
  - Update net worth calculation

- **When InvestmentValuationUpdated →**
  - Update portfolio total value
  - Update performance metrics
  - Update net worth calculation

- **When InvestmentSold →**
  - Update portfolio allocation
  - Record capital gain/loss
  - Update net worth calculation

- **When DividendReceived →**
  - Update total returns
  - Add to income reports

- **When QuarterEnds →**
  - Generate quarterly performance report
  - Calculate time-weighted returns

#### Read Models (Pink)

- **CurrentPortfolioAllocation**
  - Purpose: Show current investment mix
  - Used by: Portfolio Dashboard
  - Updated by: InvestmentCreated, InvestmentSold, PortfolioRebalanced

- **InvestmentPerformanceView**
  - Purpose: Show performance of investments
  - Used by: Performance Dashboard
  - Updated by: InvestmentValuationUpdated, InvestmentPerformanceCalculated

- **DividendHistoryView**
  - Purpose: Show history of dividend income
  - Used by: Income Reports
  - Updated by: DividendReceived

- **AssetAllocationView**
  - Purpose: Show allocation by asset type
  - Used by: Portfolio Dashboard
  - Updated by: InvestmentCreated, InvestmentSold, InvestmentValuationUpdated

### Job Applications Domain

#### Domain Events (Orange)

- **JobApplicationSubmitted**
  - Data: ApplicationId, UserId, Company, Position, SubmissionDate, JobDescription
  - Significance: New job opportunity pursued

- **InterviewScheduled**
  - Data: ApplicationId, InterviewDate, InterviewType, ContactPerson
  - Significance: Progress in application process

- **ApplicationStatusChanged**
  - Data: ApplicationId, OldStatus, NewStatus, StatusDate
  - Significance: Application progress updated

- **ApplicationRejected**
  - Data: ApplicationId, RejectionDate, Reason
  - Significance: Application unsuccessful

- **ApplicationAccepted**
  - Data: ApplicationId, AcceptanceDate, OfferDetails
  - Significance: Successful job offer received

- **FollowUpEmailSent**
  - Data: ApplicationId, EmailDate, EmailContent
  - Significance: Communication with employer

- **ApplicationDeadlineApproaching**
  - Data: ApplicationId, Deadline, DaysRemaining
  - Significance: Time-sensitive action required

- **ApplicationNoteAdded**
  - Data: ApplicationId, NoteDate, NoteContent
  - Significance: Additional information recorded

#### Commands (Blue)

- **SubmitApplication**
  - Data: Company, Position, SubmissionDate, JobDescription, ApplicationLink, Deadline
  - Validation: Company and Position required

- **ScheduleInterview**
  - Data: ApplicationId, InterviewDate, InterviewType, ContactPerson, Location/Link
  - Validation: InterviewDate not in past

- **UpdateApplicationStatus**
  - Data: ApplicationId, NewStatus, StatusDate, Notes
  - Validation: Status transition is valid

- **RecordRejection**
  - Data: ApplicationId, RejectionDate, Reason
  - Validation: Application exists and not already rejected

- **RecordOffer**
  - Data: ApplicationId, OfferDate, Salary, Benefits, Position, StartDate
  - Validation: Application exists and in appropriate status

- **SendFollowUp**
  - Data: ApplicationId, EmailDate, EmailContent
  - Validation: Application exists

- **AddApplicationNote**
  - Data: ApplicationId, NoteContent
  - Validation: Application exists

#### Actors (Yellow)

- **User**
  - Tracks applications and progress
  - Records interactions and outcomes

- **System/Scheduler**
  - Deadline reminders
  - Follow-up suggestions

#### Aggregates (Purple)

- **JobApplicationAggregate**
  - Identity: ApplicationId
  - Entities: JobApplication, Interview
  - Value Objects: ApplicationStatus, InterviewType
  - Invariants:
    - Cannot schedule interview for rejected application
    - Status transitions must follow valid paths
    - Interview date cannot be in past when scheduled

#### Policies/Reactions (Green)

- **When JobApplicationSubmitted →**
  - Set initial status to "Applied"
  - If deadline exists, schedule reminder

- **When InterviewScheduled →**
  - Add to calendar
  - Schedule pre-interview reminder
  - Update application status

- **When ApplicationStatusChanged →**
  - Update application timeline
  - If status is "Awaiting Response", schedule follow-up reminder

- **When ApplicationDeadlineApproaching →**
  - Send deadline reminder

- **When NoResponse (2 weeks) →**
  - Suggest follow-up action

#### Read Models (Pink)

- **ActiveApplicationsView**
  - Purpose: Show current applications in progress
  - Used by: Applications Dashboard
  - Updated by: JobApplicationSubmitted, ApplicationStatusChanged

- **UpcomingInterviewsView**
  - Purpose: Show scheduled interviews
  - Used by: Calendar, Applications Dashboard
  - Updated by: InterviewScheduled

- **ApplicationStatusDashboard**
  - Purpose: Show applications by status
  - Used by: Applications Dashboard
  - Updated by: ApplicationStatusChanged, ApplicationRejected, ApplicationAccepted

- **ApplicationTimelineView**
  - Purpose: Show history of application
  - Used by: Application Detail View
  - Updated by: All application-related events

## Session 3: Cross-Domain Integration

### User Authentication & Authorization

#### Domain Events

- **UserLoggedIn**
- **UserRegistered**
- **PasswordReset**
- **UserPreferencesUpdated**

#### Commands

- **Login**
- **Register**
- **ResetPassword**
- **UpdatePreferences**

#### Policies

- **When UserRegistered →**
  - Setup default expense categories
  - Create welcome notification
  - Initialize empty portfolio

### Notification System

#### Domain Events

- **NotificationCreated**
- **NotificationRead**
- **NotificationDismissed**
- **NotificationSettingsUpdated**

#### Commands

- **CreateNotification**
- **MarkNotificationRead**
- **DismissNotification**
- **UpdateNotificationPreferences**

#### Policies

- **When [Any Important Event] →**
  - Create appropriate notification based on event type and user preferences

### Dashboard & Analytics

#### Domain Events

- **DashboardViewed**
- **ReportGenerated**
- **DashboardCustomized**

#### Commands

- **GenerateReport**
- **CustomizeDashboard**

#### Read Models

- **FinancialOverviewDashboard**
- **MonthlySpendingReport**
- **UpcomingPaymentsCalendar**
- **NetWorthTracker**

### Real-Time Update Integration

#### Domain Events

- **WebSocketConnected**
- **WebSocketDisconnected**
- **EventBroadcast**

#### Commands

- **ConnectWebSocket**
- **DisconnectWebSocket**

#### Policies

- **When [Domain Event] →**
  - Transform to broadcast event
  - Broadcast to appropriate channels
  - Include correlation ID for optimistic UI updates

## Session 4: Technical Modeling

### Event Sourcing Implementation

#### Event Versioning Strategy

- Explicit versioning (V1, V2) for event classes
- Upcaster implementation for transforming older events
- Version metadata in event store

#### Snapshot Implementation

- Snapshot frequency: Every 100 events or daily
- Snapshot storage alongside event store
- Snapshot creation during aggregate persistence
- Snapshot loading optimization

#### Projection Rebuilding

- Blue/green deployment for zero-downtime rebuilds
- Temporary table creation during rebuild
- Atomic switch after rebuild complete
- Rebuild job queuing and management

### Command Validation Strategy

#### Command Structure

- Command DTO with validation logic
- Command ID for correlation
- Metadata for tracking and auditing

#### Validation Approach

- Self-validating commands
- Domain-specific validation in aggregates
- Error collection and reporting

#### Error Handling

- ValidationException with structured errors
- User-friendly error messages
- Command failure logging

### Projections Implementation

#### Projection Types

- Real-time projections (immediate updates)
- Background projections (eventual consistency)
- Computed projections (on-demand calculations)

#### Denormalization Strategy

- Read model optimization for query patterns
- Denormalized structures for performance
- Redundancy for frequent queries

#### Query Patterns

- Direct read model queries
- Custom query handlers for complex queries
- Filtering, sorting, and pagination support

### WebSocket Integration with Laravel Reverb

#### Event-to-Broadcast Mapping

- Domain event to broadcast event transformation
- Payload size optimization
- Client-friendly structure

#### Channel Security

- Private channels for user-specific data
- Channel authorization middleware
- Connection authentication

#### Optimistic UI Updates

- Command-event correlation through IDs
- Immediate UI feedback mechanism
- Event confirmation handling
- Conflict resolution strategy

## Session 5: Hotspot Resolution

### Address Red Stickies

- Resolve identified business rule questions
- Clarify complex policy interactions
- Address technical constraints
- Document decisions and rationales

### Refine and Simplify

- Consolidate similar events where appropriate
- Ensure consistent naming conventions
- Verify aggregate boundaries are correctly drawn
- Optimize read models for query needs

### Final Alignment

- Verify ubiquitous language consistency
- Ensure proper event sequence flows
- Validate projection completeness
- Confirm appropriate policy triggers

## Post-Event Storming Activities

### Documentation

#### Ubiquitous Language Glossary

- Events, commands, and concepts with clear definitions
- Domain terminology standardization
- Relationship between terms

#### Aggregate Specifications

- Business rules and invariants
- Command handling logic
- Event application behavior
- Value object validation

#### Command/Event Schema Documentation

- Structure and validation rules
- Required and optional fields
- Dependencies and relationships

### Technical Planning

#### Development Environment Setup

- Event store configuration
- Projection database setup
- WebSocket infrastructure
- Development tools and libraries

#### Testing Strategy

- Unit testing for aggregates and value objects
- Event sourcing testing approach
- Projection testing methodology
- Integration testing for domains

## Implementation Roadmap

### Phase 1: Foundation (Weeks 1-2)

- Core infrastructure setup
- Event sourcing framework implementation
- Base aggregate and event patterns
- Authentication and user management

### Phase 2: First Domain - Subscription (Weeks 3-4)

- Subscription aggregate implementation
- Subscription commands and events
- Subscription projections
- Basic subscription UI

### Phase 3: Real-Time Infrastructure (Weeks 5-6)

- Laravel Reverb setup
- WebSocket integration
- Real-time update patterns
- Optimistic UI implementation

### Phase 4: Bills & Expenses (Weeks 7-10)

- Bill domain implementation
- Expense tracking implementation
- Budget management
- Reports and analytics

### Phase 5: Investments & Portfolio (Weeks 11-14)

- Investment tracking implementation
- Portfolio management
- Performance calculations
- Asset allocation tracking

### Phase 6: Job Applications (Weeks 15-16)

- Job application tracking
- Interview management
- Application status workflows
- Timeline visualization

### Phase 7: Dashboard & Integration (Weeks 17-18)

- Comprehensive dashboard
- Cross-domain integration
- Notification system
- Final polishing and optimization 
