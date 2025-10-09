# Job Application Tracking Module — Planning Document

Last updated: 2025-10-09
Owner: Junie (JetBrains)
Status: Draft for review

## 1. Objective & Scope
- Objective: Provide an end-to-end system to capture, track, and analyze job applications from initial interest through offer/decision.
- Success metrics:
  - Time-to-offer (median) decrease by 20% after 60 days adoption
  - 95% of applications progress with no stage “stalls” > 14 days
  - <2% duplicate applications per candidate and role
  - >80% of actions logged via UI, API, or automations
- In-scope:
  - Data model, migrations, factories, seeders
  - CRUD and lifecycle workflows (default pipeline + customization)
  - UI views (list, kanban, detail, timeline), filters, search, tags
  - Notifications, reminders, and digest
  - Import/export; basic integrations (email, calendar hooks)
  - Analytics: funnel, time-in-stage, source effectiveness
  - API endpoints + webhooks
- Out-of-scope (v1):
  - In-app resume parsing (use upload + external placeholder)
  - Job-board scraping; advanced ATS integrations (phase 2)

## 2. Architecture Principles (Laravel 12)
- Use Eloquent models, relationships, and API Resources; avoid DB:: facade for domain logic.
- Validation via Form Requests. Authorization via policies and gates.
- Queue time-consuming tasks (emails, imports). Use ShouldQueue.
- Feature flags for phased rollout.
- Events for domain changes (ApplicationStageChanged, OfferAccepted, etc.) and listeners to decouple side effects (notifications, analytics snapshots).

## 3. Domain Model
Core entities and relationships (arrows point to the “one” side):
- Company (hasMany JobPosting, Contact)
- JobPosting (belongsTo Company; hasMany Application)
- Application (belongsTo JobPosting, belongsTo Candidate, hasMany StageHistory, Interview, Note, Document, Task; hasOne Offer)
- Candidate (hasMany Application, Contact, Document)
- StageHistory (belongsTo Application)
- Interview (belongsTo Application)
- Offer (belongsTo Application)
- Contact (polymorphic to Company or Candidate)
- Note (polymorphic to Application, Candidate, JobPosting, Company)
- Task (polymorphic; assigned_to user)
- Document (polymorphic; stores CV/cover letter/portfolio)
- Tag (morphToMany across Application, Candidate, JobPosting)

Recommended enums (PHP backed enums + casts):
- ApplicationStatus: draft, applied, screening, interview, assignment, reference_check, offer, accepted, rejected, withdrawn
- InterviewType: phone, video, onsite, panel, presentation
- OfferStatus: pending, accepted, declined, expired
- Source: referral, linkedin, greenhouse, lever, website, other

Key fields (selected):
- applications: job_posting_id, candidate_id, status(enum), source(enum), applied_at, next_action_at, priority(tinyint 0–3), salary_expectation, currency, location, remote(bool), notes_count, attachments_count, assigned_to, archived_at, duplicate_of_id
- stage_histories: application_id, from_status, to_status, changed_by, changed_at, reason
- interviews: application_id, type(enum), scheduled_start, scheduled_end, timezone, location, video_link, feedback, outcome
- offers: application_id, base, bonus, equity, currency, start_date, status(enum), expires_at
- tags: name (unique)

Indexes:
- applications: [job_posting_id, status], [candidate_id], [source], [next_action_at], [assigned_to], [archived_at], fulltext(name, title, company) if supported
- tags pivot: [taggable_type, taggable_id], [tag_id]

Soft deletes: Candidate, Application, JobPosting, Company.
Auditing: created_by, updated_by on major entities; consider activity log via events.

## 4. Workflows
- Default pipeline: draft → applied → screening → interview → assignment → reference_check → offer → accepted/rejected
- Customization: per-user or global pipelines (phase 2). v1: fixed pipeline with ability to skip certain stages via transition rules.
- State transitions: enforce via service class + policy checks. Log StageHistory event.
- Reminders: next_action_at with background worker to enqueue notifications daily; snooze action sets next_action_at.
- Duplicate detection: unique key on (candidate_id, job_posting_id) and heuristic check on email/title/company.

## 5. Permissions & Security
- Roles: owner, admin, member, viewer.
- Policies: view/update/delete Application, Candidate, JobPosting; private notes; export.
- PII: candidate contact info marked sensitive; avoid exposing via index endpoints unless authorized.
- Data retention: redact or purge documents on delete; configurable retention windows.

## 6. UI/UX Plan
- Navigation: “Applications” app with tabs (List, Kanban, Calendar, Analytics).
- List view: sortable columns (Company, Role, Status, Source, Applied, Next Action, Assignee, Priority, Tags). Bulk actions (stage change, assign, tag, archive). Saved filters.
- Kanban: columns by status, drag-and-drop triggers transition with confirm modal for required fields.
- Detail view: header (role, company, status, actions), sub-tabs (Timeline, Interviews, Documents, Notes, Tasks). Timeline aggregates events.
- Create/Edit: sticky sidebar for candidate/contact, attachments, source; main form for posting & status.
- Accessibility: keyboard shortcuts for stage changes; ARIA roles; dark mode respecting project conventions.

## 7. Notifications & Automations
- Channels: in-app, email, daily digest.
- Triggers: stale applications (no activity > 7 days), next_action_at due, stage changes, interviews scheduled/changed, offer expiring.
- Digest: daily at 08:00 local; includes due tasks, stale items, upcoming interviews, expiring offers.

## 8. Integrations (v1 focus; v2 noted)
- v1: Calendar links (Google/Outlook) via ICS generation; mailto templates; import CSV.
- v2: Inbox ingestion (IMAP webhook or provider API), LinkedIn/Greenhouse/Lever imports, resume parsing via 3rd party.

## 9. Analytics & Reporting
- Funnel: counts per stage, conversion rates.
- Time-in-stage: avg/median, percent stalled.
- Source effectiveness: conversion by source.
- Offer metrics: acceptance rate, time-to-offer.
- Export CSV; API for aggregates.

## 10. API Surface (REST, versioned)
- /api/v1/applications [GET, POST]
- /api/v1/applications/{id} [GET, PATCH, DELETE]
- /api/v1/applications/{id}/transition [POST]
- /api/v1/applications/{id}/notes [GET, POST]
- /api/v1/tags [GET]
- Pagination, filtering (status, source, company, assignee, next_action_at range), sorting; rate limits via middleware.

## 11. Migrations, Factories, Seeders
- Create migrations for all core entities; include full attributes when modifying columns (Laravel 12 rule).
- Factories: Candidate, Company, JobPosting, Application, Interview, Offer, Note, Document with realistic states (e.g., Application::factory()->applied()->withInterview()).
- Seeders: Demo pipeline data; sample companies and postings; staged applications.

## 12. Validation & Controllers
- Use Form Requests for create/update per entity with custom messages; follow project convention for array vs string rules.
- Controllers thin; delegate to services (ApplicationService, TransitionService) and domain events.
- API Resources for consistent serialization; include minimal related data with optional include[]= params and eager loading to prevent N+1.

## 13. Testing Strategy
- Feature tests for endpoints and UI flows (Happy/Edge paths).
- Unit tests for TransitionService, duplicate detection, reminders.
- Use model factories and states; Faker for realistic data. Ensure eager loading in controllers to avoid N+1.
- Performance: test large lists with pagination and indexes.

## 14. Risks & Mitigations
- PII exposure → audit policies, redact in resources by default
- Email/calendar vendor changes → isolate in integration layer; feature flags
- Stale data and reminders spam → digest + snooze, per-user preferences
- Legal considerations on scraping → defer to v2 and require user-provided exports

## 15. Milestones & Timeline (indicative)
- M1 (Week 1): Migrations, models, factories, basic CRUD, list view
- M2 (Week 2): Pipeline & transitions, Kanban, stage history, notes
- M3 (Week 3): Interviews, tasks, reminders, notifications (email + in-app)
- M4 (Week 4): Offers, analytics, exports, API v1
- M5 (Week 5): Polishing, accessibility, tests coverage ≥ 85%, docs

## 16. Acceptance Criteria
- User can create application, move through stages, schedule interview, receive due reminder, record offer decision.
- List/Kanban reflect real-time status; detail shows complete timeline.
- API returns consistent resources; pagination, filtering, and sorting validated.
- Factories/seeders provision a realistic demo dataset.

## 17. Open Questions
- Do we need per-user private pipelines in v1?
- Which email provider is preferred for later inbound processing?
- Should documents be stored on S3 or local disk in this deployment?

---
This document defines v1 scope. Subsequent implementation tasks should be split across PRs per milestone with tests and API resources aligned to Laravel 12 conventions.
