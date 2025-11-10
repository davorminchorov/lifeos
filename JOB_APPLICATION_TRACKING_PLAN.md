# Job Application Tracking Module — Planning Document

Last updated: 2025-11-10
Owner: Junie (JetBrains)
Status: **UPDATED** - Ready for implementation

## 1. Objective & Scope

### Purpose
Provide authenticated users with a comprehensive system to track job applications from initial interest through offer/decision, integrated into the LifeOS personal life management platform.

### Success Metrics
- Track 100% of job applications in a centralized location
- Reduce missed follow-ups by 80% through automated reminders
- Visualize application pipeline with clear status tracking
- Provide actionable insights on application effectiveness
- Time-to-offer visibility and trend analysis

### In-Scope (v1)
- **Data model**: Migrations, models with relationships, factories, seeders
- **CRUD operations**: Full create, read, update, delete for all entities
- **Lifecycle workflows**: Application status pipeline with stage history tracking
- **UI views**: List view, Kanban board, detail page, timeline visualization
- **Search & Filtering**: By company, role, status, source, dates, tags
- **Notifications**: Integration with existing notification system for reminders
- **File attachments**: Resume, cover letter, portfolio uploads using existing file system
- **Analytics**: Application funnel, time-in-stage metrics, source effectiveness
- **Multi-currency**: Salary expectations and offers using existing currency system
- **Dark mode**: Full support following existing design patterns
- **Mobile responsive**: Touch-optimized UI following existing mobile conventions

### Out-of-Scope (v1)
- Resume parsing (OCR/AI extraction) - manual upload only
- Job board scraping or automated imports
- ATS integrations (LinkedIn, Greenhouse, Lever)
- Shared/team application tracking (single user only)
- Email inbox integration for automated updates

## 2. Architecture Principles (Laravel 12)

### LifeOS Project Conventions
- **Single-user context**: All models have `user_id` foreign key; data scoped to authenticated user
- **Model structure**: Use `casts()` method (not `$casts` property), HasFactory trait, SoftDeletes where appropriate
- **Relationships**: Type-hinted relationship methods following existing patterns
- **Validation**: Form Request classes for all create/update operations with custom error messages
- **Database**: SQLite database, avoid DB:: facade, use Eloquent models and query builder
- **File attachments**: Use existing file_attachments array field pattern across modules
- **Tags**: JSON array field on models (not pivot tables) for simplicity
- **Enums**: PHP backed enums with string values, cast in model `casts()` method (first use in project)
- **Currency**: Leverage existing multi-currency system with automatic conversion
- **Notifications**: Integrate with existing notification center and preferences system
- **Design system**: Follow cream/warm neutral colors, Laravel red accents, Instrument Sans font
- **Dark mode**: Use `dark:` classes following project conventions
- **Mobile**: Touch-optimized (44px targets), responsive breakpoints, card layouts for tables

### Laravel 12 Specific
- **No separate Kernel files**: Routes in `routes/`, scheduling in `bootstrap/app.php` or `routes/console.php`
- **Commands auto-register**: Files in `app/Console/Commands/` automatically available
- **Column modifications**: Include all attributes when modifying columns (or they will be lost)
- **Queue jobs**: Use ShouldQueue interface for time-consuming operations
- **Events & Listeners**: For domain changes (status transitions, reminders) to decouple side effects

## 3. Domain Model (Simplified for LifeOS Single-User Context)

### Core Entities

#### 1. **JobApplication** (primary entity)
All-in-one model containing job, company, and application details to simplify single-user tracking.

**Key Fields:**
- `user_id` (foreign key, required) - owner of the application
- `company_name` (string, required) - company name
- `company_website` (string, nullable) - company URL
- `job_title` (string, required) - position applied for
- `job_description` (text, nullable) - job description/requirements
- `job_url` (string, nullable) - job posting URL
- `location` (string, nullable) - job location
- `remote` (boolean, default false) - remote position flag
- `salary_min` (decimal, nullable) - minimum salary
- `salary_max` (decimal, nullable) - maximum salary
- `currency` (string, default 'USD') - salary currency code
- `status` (enum, required) - current application status
- `source` (enum, required) - how you found this job
- `applied_at` (date, nullable) - when application was submitted
- `next_action_at` (datetime, nullable) - next follow-up reminder
- `priority` (tinyint, default 0) - 0=low, 1=medium, 2=high, 3=urgent
- `contact_name` (string, nullable) - recruiter/hiring manager name
- `contact_email` (string, nullable) - contact email
- `contact_phone` (string, nullable) - contact phone
- `notes` (text, nullable) - general notes
- `tags` (json, nullable) - array of tag strings
- `file_attachments` (json, nullable) - array of file paths (resume, cover letter, etc.)
- `archived_at` (timestamp, nullable) - soft archive

**Timestamps:** `created_at`, `updated_at`, `deleted_at` (soft delete)

#### 2. **JobApplicationStatusHistory**
Track status changes over time for timeline and analytics.

**Key Fields:**
- `user_id` (foreign key, required)
- `job_application_id` (foreign key, required)
- `from_status` (enum, nullable) - previous status (null for initial)
- `to_status` (enum, required) - new status
- `changed_at` (datetime, required) - when change occurred
- `notes` (text, nullable) - reason for change

**Timestamps:** `created_at`, `updated_at`

#### 3. **JobApplicationInterview**
Track interview events and feedback.

**Key Fields:**
- `user_id` (foreign key, required)
- `job_application_id` (foreign key, required)
- `type` (enum, required) - interview type
- `scheduled_at` (datetime, required) - interview date/time
- `duration_minutes` (integer, nullable) - interview length
- `location` (string, nullable) - physical location or "Remote"
- `video_link` (string, nullable) - video call URL
- `interviewer_name` (string, nullable) - interviewer(s)
- `notes` (text, nullable) - preparation notes (before)
- `feedback` (text, nullable) - interview feedback (after)
- `outcome` (enum, nullable) - positive, neutral, negative, pending
- `completed` (boolean, default false) - mark as completed

**Timestamps:** `created_at`, `updated_at`, `deleted_at` (soft delete)

#### 4. **JobApplicationOffer**
Track job offers received.

**Key Fields:**
- `user_id` (foreign key, required)
- `job_application_id` (foreign key, required)
- `base_salary` (decimal, required) - base annual salary
- `bonus` (decimal, nullable) - signing/annual bonus
- `equity` (string, nullable) - stock options/equity details
- `currency` (string, default 'USD') - offer currency
- `benefits` (text, nullable) - benefits description
- `start_date` (date, nullable) - proposed start date
- `decision_deadline` (date, nullable) - when decision is due
- `status` (enum, required) - pending, accepted, declined, expired
- `notes` (text, nullable) - negotiation notes

**Timestamps:** `created_at`, `updated_at`

### Enums (PHP Backed Enums with String Values)

**ApplicationStatus:** (app/Enums/ApplicationStatus.php)
- `WISHLIST` - 'wishlist' - Interested but not applied yet
- `APPLIED` - 'applied' - Application submitted
- `SCREENING` - 'screening' - Initial screening/recruiter call
- `INTERVIEW` - 'interview' - Interview stage(s)
- `ASSESSMENT` - 'assessment' - Technical test/assignment
- `OFFER` - 'offer' - Offer received
- `ACCEPTED` - 'accepted' - Offer accepted
- `REJECTED` - 'rejected' - Rejected by company
- `WITHDRAWN` - 'withdrawn' - Withdrew application
- `ARCHIVED` - 'archived' - Archived/closed

**ApplicationSource:** (app/Enums/ApplicationSource.php)
- `LINKEDIN` - 'linkedin'
- `COMPANY_WEBSITE` - 'company_website'
- `JOB_BOARD` - 'job_board' - Indeed, Monster, etc.
- `REFERRAL` - 'referral'
- `RECRUITER` - 'recruiter'
- `NETWORKING` - 'networking'
- `OTHER` - 'other'

**InterviewType:** (app/Enums/InterviewType.php)
- `PHONE` - 'phone'
- `VIDEO` - 'video'
- `ONSITE` - 'onsite'
- `PANEL` - 'panel'
- `TECHNICAL` - 'technical'
- `BEHAVIORAL` - 'behavioral'
- `FINAL` - 'final'

**InterviewOutcome:** (app/Enums/InterviewOutcome.php)
- `PENDING` - 'pending'
- `POSITIVE` - 'positive'
- `NEUTRAL` - 'neutral'
- `NEGATIVE` - 'negative'

**OfferStatus:** (app/Enums/OfferStatus.php)
- `PENDING` - 'pending'
- `NEGOTIATING` - 'negotiating'
- `ACCEPTED` - 'accepted'
- `DECLINED` - 'declined'
- `EXPIRED` - 'expired'

### Relationships

**JobApplication:**
- `belongsTo(User::class)`
- `hasMany(JobApplicationStatusHistory::class)`
- `hasMany(JobApplicationInterview::class)`
- `hasOne(JobApplicationOffer::class)`

**JobApplicationStatusHistory:**
- `belongsTo(User::class)`
- `belongsTo(JobApplication::class)`

**JobApplicationInterview:**
- `belongsTo(User::class)`
- `belongsTo(JobApplication::class)`

**JobApplicationOffer:**
- `belongsTo(User::class)`
- `belongsTo(JobApplication::class)`

### Indexes

**job_applications:**
- `[user_id, status]` - filter by status
- `[user_id, applied_at]` - sort by application date
- `[user_id, next_action_at]` - reminder queries
- `[user_id, priority]` - priority filtering
- `[user_id, archived_at]` - active vs archived

**job_application_status_histories:**
- `[job_application_id, changed_at]` - timeline queries

**job_application_interviews:**
- `[job_application_id]`
- `[user_id, scheduled_at]` - upcoming interviews

**job_application_offers:**
- `[job_application_id]`
- `[user_id, status]`

## 4. Workflows & Business Logic

### Status Pipeline
Default application lifecycle (user can move between any states freely):
1. **WISHLIST** → Initial interest, not yet applied
2. **APPLIED** → Application submitted
3. **SCREENING** → Initial recruiter contact or phone screen
4. **INTERVIEW** → One or more interview rounds
5. **ASSESSMENT** → Take-home assignment or technical test
6. **OFFER** → Offer received, pending decision
7. **ACCEPTED** → Offer accepted, job secured! 🎉
8. **REJECTED** → Company rejected application
9. **WITHDRAWN** → User withdrew from process
10. **ARCHIVED** → Closed/archived for reference

### Automatic Actions
- **Status change**: Create JobApplicationStatusHistory record automatically
- **Next action reminders**: Command checks `next_action_at` field daily, sends notifications for due items
- **Interview reminders**: Notification 24h before scheduled interview
- **Offer deadline warnings**: Alert 3 days, 1 day before decision_deadline
- **Stale application detection**: Flag applications with no status change in 14+ days

### Manual Actions
- **Update status**: Move application through pipeline (triggers history entry)
- **Set priority**: Flag urgent applications
- **Add interview**: Schedule interview events
- **Record offer**: Add offer details when received
- **Archive**: Mark application as archived (soft close, not deleted)
- **Add notes**: Update notes field with any relevant info
- **Upload files**: Attach resume, cover letter, portfolio via file_attachments

## 5. Security & Data Ownership

### Single-User Context
- All data belongs to authenticated user (user_id on all models)
- Middleware ensures users only access their own data
- Global query scopes on models auto-filter by authenticated user

### Authorization
- Simple policy: User can only view/edit/delete their own job applications
- No role-based permissions needed (single user per account)
- Standard Laravel authentication via web middleware

### Data Privacy
- All application data is private to the user
- File uploads stored securely, only accessible to owner
- Soft deletes allow recovery if accidentally deleted
- Archived applications remain searchable but marked as closed

## 6. UI/UX Plan (Following LifeOS Design System)

### Design System Integration
- **Colors**: Cream/warm neutrals (#FDFDFC, #F8F7F4) for backgrounds, Laravel red (#F53003) for accents
- **Typography**: Instrument Sans font, consistent with existing modules
- **Components**: Follow existing card, button, form, and table patterns
- **Dark mode**: Full support using `dark:` classes throughout
- **Mobile**: Touch-optimized (44px minimum touch targets), responsive breakpoints, card-based mobile layouts

### Navigation
Add "Job Applications" to main navigation menu alongside existing modules (Subscriptions, Contracts, Investments, etc.)

### Pages & Views

#### 1. **Applications Index** (`/job-applications`)
- **Layout**: Table view with sortable columns
  - Company Name | Job Title | Status Badge | Source | Applied Date | Next Action | Priority | Actions
- **Filters**: Status dropdown, source dropdown, date range picker, priority filter, search by company/title
- **Actions**: Create New button (primary Laravel red), bulk archive, export CSV
- **Mobile**: Transform to card layout showing key info, swipe actions
- **Status badges**: Color-coded pills (green=offer, blue=interview, gray=wishlist, red=rejected)

#### 2. **Kanban Board** (`/job-applications/kanban`)
- **Layout**: Horizontal columns for each status (Wishlist, Applied, Screening, Interview, Assessment, Offer)
- **Drag & drop**: Move cards between columns to update status
- **Cards**: Show company, job title, days in status, priority indicator, next action date
- **Mobile**: Vertical scrolling columns, swipe to change status

#### 3. **Application Detail** (`/job-applications/{id}`)
- **Header Section**: Company name, job title, status dropdown, priority selector, action buttons (Edit, Archive, Delete)
- **Tabs**:
  - **Overview**: Job details, contact info, salary range, location, application notes
  - **Timeline**: Status history with dates and notes (newest first)
  - **Interviews**: List of scheduled/completed interviews with outcomes
  - **Offer**: Offer details if received (salary breakdown, benefits, deadlines)
  - **Files**: Uploaded documents (resume, cover letter, portfolio)
- **Sidebar**: Quick stats (days since applied, time in current status), tags, next action reminder

#### 4. **Create/Edit Application** (`/job-applications/create`, `/job-applications/{id}/edit`)
- **Form sections**:
  - Company & Job Info (company name, job title, job URL, location, remote toggle)
  - Application Details (status, source, applied date, priority)
  - Salary Expectations (min/max with currency selector using existing currency system)
  - Contact Information (recruiter name, email, phone)
  - Files (upload resume, cover letter using existing file upload system)
  - Notes & Tags (textarea for notes, tags input)
  - Next Action (date/time picker for follow-up reminder)
- **Validation**: Real-time validation with helpful error messages
- **Mobile**: Single column, larger form controls, date pickers optimized for mobile

#### 5. **Analytics Dashboard** (`/job-applications/analytics`)
- **Widgets**:
  - Application funnel chart (counts per status)
  - Source effectiveness table (conversion rates by source)
  - Time-in-stage metrics (average days per status)
  - Recent activity timeline (last 10 status changes)
  - Success rate (offers received vs applications sent)
  - Active applications count, offers pending
- **Filters**: Date range selector
- **Export**: Download analytics as CSV

### Accessibility
- Keyboard navigation for all actions
- ARIA labels on interactive elements
- Focus indicators following design system
- Screen reader friendly table headers and status badges

## 7. Notifications & Reminders

### Integration with Existing Notification System
Leverage LifeOS's existing notification center with customizable preferences.

### Notification Types

#### Database Notifications (In-App)
- **Next action due**: "Follow up on [Job Title] at [Company] is due today"
- **Interview reminder**: "Interview with [Company] scheduled for tomorrow at [Time]"
- **Offer deadline**: "Decision deadline for [Company] offer is in 3 days"
- **Stale application**: "[Job Title] at [Company] hasn't been updated in 14 days"
- **Status change**: "[Job Title] at [Company] moved to [New Status]"

#### Email Notifications (Optional)
User can enable/disable via notification preferences:
- Daily digest at 8:00 AM with upcoming actions and interviews
- Immediate alerts for urgent items (interview tomorrow, offer deadline soon)

### Command Schedule
- **Daily reminder check**: `CheckJobApplicationReminders` command runs at 8:00 AM
  - Checks `next_action_at` field for today/overdue
  - Checks interviews scheduled for next 24 hours
  - Checks offer `decision_deadline` within 3 days
  - Creates notifications via existing notification system

## 8. Analytics & Reporting

### Dashboard Metrics

**Application Funnel**
- Count of applications in each status
- Conversion rate between stages
- Visual funnel chart

**Time Analysis**
- Average days in each status
- Total time from apply to offer
- Applications by status over time (trend chart)

**Source Effectiveness**
- Applications per source
- Offer rate by source
- Most successful sources

**Outcome Metrics**
- Total applications submitted
- Interviews secured (%)
- Offers received (%)
- Acceptance rate

**Quick Stats Cards**
- Active applications count
- Upcoming interviews (next 7 days)
- Pending offers
- Average time to offer

### Export Options
- Export all applications to CSV
- Export filtered applications
- Export analytics report (PDF/CSV)

## 9. Implementation Plan

### Phase 1: Core Foundation (Week 1-2)
**Database & Models**
- Create enums (ApplicationStatus, ApplicationSource, InterviewType, InterviewOutcome, OfferStatus)
- Create migrations for 4 tables with indexes
- Create models with relationships, casts(), scopes
- Create factories with realistic fake data
- Create seeder with sample applications

**Basic CRUD**
- Controllers: JobApplicationController with resource methods
- Form Requests: StoreJobApplicationRequest, UpdateJobApplicationRequest
- Routes: Resource routes + web middleware
- Views: Index (table), create, edit forms following design system

### Phase 2: Status Management & Timeline (Week 2-3)
**Status Transitions**
- Observer to auto-create StatusHistory on status changes
- Timeline view showing all status changes
- Kanban board view with drag-and-drop
- Status badges with appropriate colors

**Interviews & Offers**
- Interview CRUD (within application detail)
- Offer CRUD (single offer per application)
- Interview reminders
- Offer deadline warnings

### Phase 3: Notifications & Reminders (Week 3-4)
**Command & Notifications**
- Create CheckJobApplicationReminders command
- Register command in console schedule
- Create notification classes
- Integrate with existing notification preferences
- Daily digest email option

### Phase 4: Analytics & Polish (Week 4-5)
**Analytics Dashboard**
- Aggregate queries for metrics
- Charts/visualizations
- Export functionality
- Filters and date ranges

**Final Polish**
- Mobile responsive testing and fixes
- Dark mode verification
- Accessibility audit
- Performance optimization (query N+1 checks)
- Run Laravel Pint for code formatting
- Comprehensive feature tests

### Phase 5: Documentation & Testing (Week 5)
**Testing**
- Feature tests for all CRUD operations
- Test status transitions and history
- Test notifications and reminders
- Test analytics calculations
- Factory states testing

**Documentation**
- Update README with Job Applications feature
- Add to navigation and dashboard
- User guide for common workflows

## 10. Technical Implementation Details

### Controllers
- `JobApplicationController` - Main CRUD resource controller
- `JobApplicationInterviewController` - Nested resource for interviews
- `JobApplicationOfferController` - Single resource for offers
- `JobApplicationKanbanController` - Kanban board view with status updates
- `JobApplicationAnalyticsController` - Analytics dashboard

### Form Requests
- `StoreJobApplicationRequest` - Validation for creating applications
- `UpdateJobApplicationRequest` - Validation for updating applications
- `StoreInterviewRequest` - Interview validation
- `StoreOfferRequest` - Offer validation

### Policies (Optional)
- Simple policy ensuring user can only access their own data
- Can use global scope on models instead

### Events & Listeners
- `JobApplicationStatusChanged` event → creates StatusHistory record
- `InterviewScheduled` event → creates reminder notification
- `OfferReceived` event → creates notification

### Commands
- `CheckJobApplicationReminders` - Daily command to check for due reminders

### Blade Components
- `job-application-card` - Reusable card for list/kanban views
- `status-badge` - Status indicator with colors
- `priority-indicator` - Visual priority flag
- `timeline-item` - Timeline entry component

## 11. Success Criteria

### Functional Requirements ✓
- User can create, view, edit, delete job applications
- User can track status changes with history
- User can schedule interviews and record outcomes
- User can record offers with detailed compensation
- User receives timely reminders and notifications
- User can visualize pipeline via list and kanban views
- User can analyze application effectiveness
- All data is mobile-accessible and responsive

### Non-Functional Requirements ✓
- Follows LifeOS design system exactly
- Dark mode support throughout
- Mobile-optimized touch interfaces
- Fast page loads (< 200ms for index)
- No N+1 queries (eager loading)
- Comprehensive test coverage (> 80%)
- Accessibility compliant (WCAG 2.1 AA)

### Integration Requirements ✓
- Uses existing notification system
- Uses existing currency system
- Uses existing file upload system
- Follows existing authentication patterns
- Matches navigation and layout structure

## 12. Future Enhancements (Post-v1)

### Phase 2 Ideas
- Email integration to auto-update from inbox
- Browser extension to quick-add from job boards
- Resume parsing for auto-fill
- Job board search integration
- Interview preparation checklist
- Salary comparison against market data
- Application templates for common industries
- Custom status pipeline per user
- Share application stats (anonymously)
- Export to PDF portfolio

---

**Document Status**: UPDATED AND READY FOR IMPLEMENTATION
**Last Updated**: 2025-11-10
**Next Step**: Begin Phase 1 implementation starting with enums and migrations
