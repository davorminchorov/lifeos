# Dashboard Redesign Plan

Last updated: 2025-11-10
Owner: Davor Minchorov
Status: Draft for implementation

## 1. Executive Summary

The LifeOS dashboard redesign aims to create a comprehensive, user-centric overview that integrates existing modules (Subscriptions, Contracts, Warranties, Investments, Expenses, Utility Bills, IOUs, Budgets, Job Applications) with planned modules (Invoicing, Fitness/Nutrition) into a cohesive, actionable interface.

**Goals:**
- Provide at-a-glance financial health and life management status
- Surface actionable items requiring immediate attention
- Enable quick access to most-used features
- Display meaningful analytics and insights
- Support personalized dashboard layouts
- Maintain mobile responsiveness and accessibility
- Integrate seamlessly with existing dark mode and design system

## 2. Current Dashboard Analysis

### Existing Features:
- Advanced analytics with interactive Chart.js visualizations
- Quick stats cards (subscriptions, contracts, investments)
- Alerts & notifications section
- Recent expenses and upcoming bills
- Quick action buttons for creating new entries
- Export functionality (PDF, Excel)
- Period selection (3 months, 6 months, 1 year, 2 years)

### Strengths:
- Comprehensive analytics coverage
- Clean visual hierarchy
- Responsive design with Tailwind CSS
- Interactive charts with Chart.js
- Quick actions for common tasks

### Limitations:
- No widgets for Budgets, IOUs, Job Applications
- Missing Invoicing and Fitness modules
- Fixed layout (no customization)
- Limited real-time data
- No goal tracking or progress indicators
- Analytics focus on past data, less predictive insights

## 3. Redesigned Dashboard Architecture

### 3.1 Layout Structure

**Three-tier layout:**
1. **Header Section** - Key metrics banner
2. **Main Content** - Modular widget grid
3. **Quick Actions** - Persistent action bar

### 3.2 Widget System

Implement a modular widget framework where each widget is:
- Self-contained component
- Configurable (show/hide, size, position)
- Refreshable independently
- Exportable individually

**Widget Categories:**

#### Financial Widgets:
1. **Financial Overview** - Net worth, income vs expenses, savings rate
2. **Subscriptions Summary** - Active count, monthly cost, upcoming renewals
3. **Budget Status** - Current period progress, over/under budget categories
4. **Investments Portfolio** - Portfolio value, return %, recent performance
5. **Upcoming Bills** - Next 7 days with payment status
6. **IOUs Balance** - Owed to you vs owed by you
7. **Expense Trends** - Category breakdown, comparison to previous period
8. **Invoicing Dashboard** - Issued, paid, overdue invoices with revenue

#### Life Management Widgets:
9. **Contracts Calendar** - Expiring contracts, renewal actions needed
10. **Warranties Tracker** - Expiring warranties, claim status
11. **Job Applications Pipeline** - Active applications by stage, interviews scheduled
12. **Fitness Dashboard** - Today's macro targets, workout scheduled, progress streak
13. **Meal Planning** - Today's meal plan, prep reminders, adherence

#### Analytics Widgets:
14. **Spending Trends Chart** - Time series with multiple periods
15. **Category Breakdown** - Pie/donut chart with filters
16. **Portfolio Performance** - Line chart with benchmarks
17. **Monthly Comparison** - Radar chart for category analysis
18. **Cash Flow Forecast** - Predictive analysis for next 3-6 months

#### Action Widgets:
19. **Alerts & Notifications** - Prioritized action items
20. **Quick Capture** - Fast entry form for expenses/meals/workouts
21. **Recent Activity** - Timeline of latest actions across modules

## 4. Widget Specifications

### 4.1 Financial Overview Widget

**Purpose:** Single-pane view of overall financial health

**Metrics:**
- Net Worth (assets - liabilities)
- Monthly Income (from budgets/tracked income)
- Monthly Expenses (actual spend)
- Savings Rate percentage
- Month-over-month change indicators

**Visual:** Card grid with large numbers, trend indicators, mini sparklines

**Actions:**
- Click metric to drill into details
- Export financial summary
- Set financial goals

### 4.2 Fitness Dashboard Widget

**Purpose:** Today's nutrition and workout overview

**Metrics:**
- Macro progress rings (protein, carbs, fat, calories)
- Today's workout (scheduled/completed)
- Current streak (days)
- Weight trend mini chart

**Visual:** Progress rings, workout card, streak counter

**Actions:**
- Quick log meal
- Start workout session
- View weekly plan
- Log weight check-in

### 4.3 Job Applications Pipeline Widget

**Purpose:** Visualize application funnel

**Metrics:**
- Applications by stage (wishlist, applied, interview, offer)
- Interviews this week
- Pending offers
- Average time in stage

**Visual:** Horizontal funnel or vertical column counts

**Actions:**
- View kanban board
- Add new application
- Update application status

### 4.4 Invoicing Dashboard Widget

**Purpose:** Revenue and receivables overview

**Metrics:**
- Revenue this month
- Outstanding invoices (amount + count)
- Overdue invoices (amount + count)
- Payment success rate

**Visual:** Metric cards with status indicators

**Actions:**
- Create new invoice
- View overdue list
- Send reminders
- Generate revenue report

### 4.5 Cash Flow Forecast Widget

**Purpose:** Predictive financial planning

**Metrics:**
- Projected income (next 30/60/90 days)
- Projected expenses (subscriptions, bills, contracts)
- Expected cash position
- Shortfall/surplus alerts

**Visual:** Area chart with income/expense bands, balance line

**Data sources:**
- Recurring subscriptions
- Scheduled utility bills
- Contract payment schedules
- Budget projections
- Historical expense patterns

**Actions:**
- Adjust projections
- Export forecast
- Set alerts for low balance

## 5. Personalization & Configuration

### 5.1 Widget Management

**Features:**
- Drag-and-drop widget reordering
- Show/hide individual widgets
- Resize widgets (small, medium, large, full-width)
- Save multiple dashboard presets (e.g., "Financial Focus", "Fitness Mode", "Job Hunting")

**Implementation:**
- User preferences stored in database (JSON column)
- Frontend: Alpine.js or Vue.js for drag-drop
- Backend: DashboardPreference model with user relationship

### 5.2 Default Layouts

**Financial Focus:**
- Financial Overview (large)
- Budget Status (medium)
- Spending Trends (large)
- Investments Portfolio (medium)
- Cash Flow Forecast (large)

**Life Management:**
- Alerts & Notifications (full-width)
- Job Applications Pipeline (medium)
- Contracts Calendar (medium)
- Fitness Dashboard (medium)
- Recent Activity (medium)

**Balanced (Default):**
- Financial Overview (medium)
- Fitness Dashboard (medium)
- Budget Status (small)
- Alerts & Notifications (full-width)
- Spending Trends (large)
- Quick Capture (small)

## 6. Technical Implementation

### 6.1 Backend Architecture

**Controllers:**
- DashboardController - Main dashboard rendering
- DashboardWidgetController - Individual widget data endpoints
- DashboardPreferenceController - Save/load user preferences

**Services:**
- DashboardService - Aggregate data from all modules
- WidgetDataService - Fetch and format widget-specific data
- CashFlowForecastService - Predictive calculations

**Models:**
- DashboardPreference - User widget configuration
- WidgetCache - Cache widget data with TTL

**API Endpoints:**
```
GET /api/v1/dashboard/widgets - List available widgets
GET /api/v1/dashboard/widgets/{widget}/data - Fetch widget data
POST /api/v1/dashboard/preferences - Save layout preferences
GET /api/v1/dashboard/preferences - Load user preferences
```

### 6.2 Frontend Architecture

**Components (Blade/Alpine.js):**
- `dashboard-grid.blade.php` - Main grid container
- `widget-{name}.blade.php` - Individual widget templates
- `widget-empty-state.blade.php` - Empty widget placeholders

**JavaScript:**
- `dashboard.js` - Grid management, drag-drop, AJAX refresh
- `widgets.js` - Widget-specific interactions
- `chart-configs.js` - Chart.js configurations

**State Management:**
- Alpine.js for reactive widget visibility and data refresh
- Local storage for temporary layout changes
- API sync for permanent preferences

### 6.3 Caching Strategy

**Widget Data Cache:**
- Cache expensive queries (investments, analytics)
- TTL: 5 minutes for financial data, 1 hour for analytics
- Cache tags by module for selective invalidation
- Real-time data (alerts, notifications) not cached

**Implementation:**
```php
Cache::tags(['dashboard', 'investments'])
    ->remember("widget.portfolio.{$userId}", 300, fn() => $this->getPortfolioData());
```

## 7. Data Integration

### 7.1 Module Data Sources

**Subscriptions:**
- Active count, monthly cost, next renewal dates
- Query: `Subscription::active()->where('user_id', $userId)->sum('cost')`

**Budgets:**
- Current period budgets, spent vs allocated
- Query: `Budget::current()->with('expenses')->get()`

**Investments:**
- Portfolio value, total return, recent transactions
- Query: `Investment::with('transactions')->get()`

**Job Applications:**
- Applications by status, upcoming interviews
- Query: `JobApplication::where('user_id', $userId)->groupBy('status')->count()`

**Fitness:**
- Today's macro targets, workout schedule, streak
- Query: `FitnessPlan::active()->with('nutritionCycle', 'workoutProgram')->first()`

**Invoicing:**
- Issued/paid/overdue invoice counts and totals
- Query: `Invoice::where('status', 'overdue')->sum('amount_due')`

### 7.2 Cross-Module Analytics

**Financial Health Score:**
- Calculation: (Savings Rate × 0.3) + (Budget Adherence × 0.3) + (Investment Return × 0.2) + (Debt-Free Score × 0.2)
- Range: 0-100
- Display: Progress bar with color gradient

**Life Management Score:**
- Calculation: Weighted average of module completion rates
- Factors: Contract renewals handled, warranties tracked, applications updated
- Display: Circular progress indicator

## 8. Alerts & Notifications System

### 8.1 Alert Prioritization

**Critical (Red):**
- Overdue invoices (>30 days)
- Contract expiring in <7 days
- Budget exceeded by >20%
- Negative cash flow forecast

**Warning (Yellow):**
- Bill due in <3 days
- Warranty expiring in <30 days
- Application stale (>14 days no update)
- Missed workout (>2 days)

**Info (Blue):**
- Subscription renewal in <7 days
- Goal milestone reached
- Weekly fitness digest available
- New investment dividend

### 8.2 Alert Widget

**Features:**
- Grouped by priority
- Expandable detail view
- Quick actions (mark as read, snooze, dismiss)
- Filter by module
- Notification count badge

**Display:**
- Top 5 alerts on dashboard
- "View all" link to full notification center

## 9. Quick Actions Enhancement

### 9.1 Expanded Quick Actions

**Current Actions:**
- Add Subscription
- Add Expense
- Add Contract
- Add Warranty
- Add Investment
- Add Utility Bill

**New Actions:**
- Add Budget
- Add IOU
- Add Job Application
- Create Invoice
- Log Meal
- Log Workout
- Add Progress Photo

**Layout:**
- Responsive grid: 2 cols (mobile) → 4 cols (tablet) → 7 cols (desktop)
- Icon + label design consistent with existing
- Hover effects and transitions

### 9.2 Contextual Quick Actions

**Smart suggestions based on:**
- Time of day (meal logging in morning/evening)
- Missing data (workout not logged after scheduled time)
- Patterns (expense entry on weekday evenings)

**Implementation:**
- Machine learning model (future) or rule-based initially
- Display 3-5 suggested actions above regular grid
- Dismissible suggestions

## 10. Mobile Optimization

### 10.1 Mobile Dashboard Layout

**Constraints:**
- Single column layout
- Collapsible widgets
- Swipeable charts
- Sticky header with key metrics
- Bottom navigation bar for quick actions

**Widget Priority (Mobile):**
1. Alerts & Notifications
2. Financial Overview
3. Fitness Dashboard (if active plan)
4. Budget Status
5. Recent Activity
6. Quick Actions (sticky bottom)

### 10.2 Mobile Interactions

**Gestures:**
- Swipe left/right on charts to change period
- Pull to refresh widget data
- Long press widget for options (hide, resize, move)
- Swipe alert to dismiss

**Touch Targets:**
- Minimum 44px height for all buttons
- Larger tap areas for chart legends
- Accessible form inputs with proper labels

## 11. Accessibility & Performance

### 11.1 Accessibility (WCAG 2.1 AA)

**Requirements:**
- Keyboard navigation for all widgets
- ARIA labels for charts and data visualizations
- Focus indicators on interactive elements
- Color contrast ratios ≥4.5:1 for text
- Screen reader announcements for dynamic updates
- Skip links to main content sections

**Implementation:**
- Use semantic HTML5 elements
- Add `role`, `aria-label`, `aria-describedby` attributes
- Test with screen readers (VoiceOver, NVDA)

### 11.2 Performance Targets

**Metrics:**
- Initial page load: <2 seconds
- Widget data refresh: <500ms
- Chart rendering: <200ms
- Layout shift (CLS): <0.1
- First Input Delay (FID): <100ms

**Optimizations:**
- Lazy load below-fold widgets
- Virtualize long lists (recent activity)
- Debounce chart resize events
- Use web workers for heavy calculations
- Implement service worker for offline cache

### 11.3 Progressive Enhancement

**Core functionality without JavaScript:**
- Display static widget data (server-rendered)
- Basic navigation links
- Form submissions

**Enhanced with JavaScript:**
- Interactive charts
- Drag-drop layout
- Real-time updates
- Inline editing

## 12. Design System Integration

### 12.1 Color Palette

**Using existing design system:**
- Primary: Blue tones (var(--color-primary-*))
- Accent: Teal (var(--color-accent-*))
- Success: Green
- Warning: Yellow/Orange
- Danger: Red
- Info: Light Blue

**Widget Themes:**
- Financial widgets: Primary blue
- Life management: Accent teal
- Analytics: Mixed (chart-specific)
- Alerts: Status-based colors

### 12.2 Typography & Spacing

**Consistency:**
- Headings: text-lg (widgets), text-3xl (page title)
- Body text: text-sm (compact), text-base (readable)
- Spacing: gap-4 (mobile), gap-6 (tablet), gap-8 (desktop)
- Padding: p-4 (widgets), p-6 (cards)

**Dark Mode:**
- All widgets support dark mode with design system variables
- Charts use dark-mode-aware color schemes
- Sufficient contrast in both modes

## 13. Analytics & Insights

### 13.1 Dashboard Usage Analytics

**Track:**
- Most viewed widgets
- Most used quick actions
- Average time on dashboard
- Widget interaction rates
- Export frequency

**Purpose:**
- Optimize default layouts
- Identify underutilized features
- Improve widget relevance

### 13.2 Predictive Insights

**Financial Predictions:**
- Cash flow shortfalls (based on recurring expenses)
- Budget overspend risk (trend-based)
- Investment rebalancing suggestions
- Subscription cost optimization opportunities

**Lifestyle Predictions:**
- Contract renewal recommendations
- Fitness goal achievement timeline
- Job application conversion estimates

**Display:**
- Insight cards with explanation and action items
- Dismissible with feedback collection
- Learning system to improve predictions

## 14. Testing Strategy

### 14.1 Unit Tests

**Coverage:**
- WidgetDataService methods
- CashFlowForecastService calculations
- DashboardPreference model
- Widget caching logic

**Example:**
```php
test('financial overview widget calculates net worth correctly', function () {
    // Setup test data
    // Assert net worth calculation
});
```

### 14.2 Feature Tests

**Scenarios:**
- Dashboard loads with default layout for new user
- User can reorder widgets and save preferences
- Widget data refreshes via AJAX
- Export functionality works for each widget
- Mobile responsive layout renders correctly

### 14.3 Browser Testing

**Browsers:**
- Chrome/Edge (Chromium)
- Firefox
- Safari (iOS and macOS)

**Devices:**
- Desktop (1920×1080, 1366×768)
- Tablet (iPad, Android tablets)
- Mobile (iPhone, Android phones)

### 14.4 Performance Testing

**Tools:**
- Lighthouse CI for automated checks
- WebPageTest for detailed analysis
- Chrome DevTools for profiling

**Benchmarks:**
- Dashboard page load under various network conditions
- Widget refresh performance with large datasets
- Chart rendering with 365+ data points

## 15. Migration & Rollout Plan

### 15.1 Phase 1: Foundation (Week 1-2)

**Tasks:**
- Create widget system architecture
- Implement DashboardPreference model
- Build widget data services
- Create responsive grid layout

**Deliverables:**
- Widget framework functional
- 3-5 basic widgets implemented
- User preferences working

### 15.2 Phase 2: Core Widgets (Week 3-4)

**Tasks:**
- Implement Financial Overview widget
- Implement Budget Status widget
- Implement Fitness Dashboard widget
- Implement Alerts & Notifications widget
- Implement Quick Actions enhancement

**Deliverables:**
- 8-10 widgets functional
- Default layouts defined
- Mobile optimization complete

### 15.3 Phase 3: Advanced Features (Week 5-6)

**Tasks:**
- Implement Cash Flow Forecast
- Add drag-drop customization
- Build analytics widgets
- Implement widget caching

**Deliverables:**
- All planned widgets complete
- Customization fully functional
- Performance optimized

### 15.4 Phase 4: Integration (Week 7-8)

**Tasks:**
- Integrate Invoicing module (when available)
- Integrate Fitness module (when available)
- Add predictive insights
- Implement dashboard analytics tracking

**Deliverables:**
- Full module integration
- Insights system functional
- Production-ready dashboard

### 15.5 Rollout Strategy

**Approach:**
- Feature flag: enable new dashboard for subset of users
- A/B test: 50% new dashboard, 50% current (if multi-user)
- Gradual rollout: 10% → 50% → 100% over 2 weeks
- Feedback collection: in-app survey after 1 week of use

**Rollback Plan:**
- Keep current dashboard code intact
- Feature flag to switch back instantly
- Database migrations reversible

## 16. Future Enhancements (v2)

### 16.1 Advanced Personalization

**Features:**
- AI-powered widget recommendations
- Automatic layout optimization based on usage
- Time-based layouts (work hours vs evening)
- Goal-driven dashboards (e.g., "Debt Payoff Mode")

### 16.2 Collaboration Features

**For shared accounts (future multi-user):**
- Shared widgets (household budget, joint investments)
- Individual vs. shared view toggle
- Activity feed of all users
- Collaborative goal setting

### 16.3 Integrations

**External Services:**
- Bank account sync (Plaid/Yodlee)
- Investment portfolio sync (brokerage APIs)
- Fitness tracker sync (Apple Health, Fitbit)
- Calendar integration (Google Calendar for bills, interviews)

### 16.4 Advanced Analytics

**Machine Learning:**
- Anomaly detection (unusual spending, investment volatility)
- Predictive modeling (goal achievement probability)
- Recommendation engine (budget adjustments, contract negotiations)
- Natural language insights ("You spent 30% more on dining out this month")

## 17. Success Metrics

### 17.1 Engagement Metrics

**Target KPIs:**
- Dashboard visit frequency: Daily active use >60%
- Average session duration: >3 minutes
- Widget interaction rate: >40% of visits
- Quick action usage: >5 actions per week
- Export usage: >10% of active users monthly

### 17.2 User Satisfaction

**Measurement:**
- In-app satisfaction survey (NPS score target: >40)
- Feature request tracking
- Bug report rate (target: <2% of sessions)
- Support ticket reduction for dashboard navigation

### 17.3 Business Metrics

**Financial Module:**
- Budget adherence improvement: +15%
- Expense logging frequency: +30%
- Financial goal completion rate: +20%

**Fitness Module:**
- Daily meal logging rate: >70%
- Workout completion rate: >85%
- Progress check-in adherence: >80%

**Job Applications:**
- Application update frequency: +50%
- Interview preparation time: +30%
- Offer acceptance rate improvement: +10%

## 18. Open Questions

1. Should widgets support third-party plugins in the future?
2. What level of granularity for widget permissions (if multi-user)?
3. Should there be a public/shareable dashboard view?
4. Integration priority: Bank sync or fitness tracker first?
5. Export format preferences: PDF, Excel, JSON, or all?
6. Should widgets support annotations/notes directly on charts?

## 19. Conclusion

This dashboard redesign transforms LifeOS from a module-centric application into a unified life management platform. The modular widget system provides flexibility while maintaining consistency. The integration of financial, health, and career management creates a holistic view of personal progress.

**Key Benefits:**
- **Actionable Insights:** Surface what matters most
- **Personalization:** Adapt to individual needs and goals
- **Efficiency:** Quick access to frequent tasks
- **Motivation:** Visual progress tracking and goal achievement
- **Scalability:** Easy addition of new modules and features

**Next Steps:**
1. Stakeholder review and approval
2. Create detailed wireframes and mockups
3. Begin Phase 1 implementation
4. Regular progress reviews at end of each phase
