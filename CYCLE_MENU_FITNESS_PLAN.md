# Cycle Menu Fitness Plan Module — Planning Document

Last updated: 2025-11-07
Owner: Davor Minchorov
Status: Draft for implementation

## 1. Objective & Scope

- **Objective**: Provide a comprehensive meal planning and fitness tracking system that supports cyclical nutrition and workout programs aligned with fitness goals (cutting, bulking, maintenance, recomposition).
- **Success metrics**:
  - 90% of planned meals and workouts logged within 24 hours
  - Caloric adherence within ±5% of daily targets over 7-day rolling average
  - Body composition progress photos captured at least weekly
  - <3 min average time to log a meal or workout
- **In-scope**:
  - Cyclical nutrition plans with macronutrient targets that vary by training/rest days
  - Meal planning with recipes, ingredients, portions, and nutrition tracking
  - Workout program templates and session logging (exercises, sets, reps, weight, RPE)
  - Progress tracking: body weight, measurements, photos, performance metrics
  - Meal prep scheduling and shopping list generation
  - Adherence analytics and goal progress visualization
  - Template library for common meals and workout routines
  - API endpoints and mobile-friendly UI
- **Out-of-scope (v1)**:
  - AI meal generation or automated macro calculations (manual entry/templates)
  - Integration with fitness trackers (Apple Health, Fitbit) — phase 2
  - Social features, meal/workout sharing — phase 2
  - Advanced periodization models (DUP, block periodization) — v1 focuses on linear

## 2. Architecture Principles (Laravel 12)

- Use Eloquent models with proper relationships; avoid DB:: facade for domain logic
- Validation via Form Requests with custom error messages
- Authorization via policies for all CRUD operations
- Queue jobs for meal prep reminders, progress photo notifications, weekly digests
- Domain events for tracking adherence, milestone achievements, cycle phase transitions
- Service layer for complex calculations (macros, TDEE, progressive overload recommendations)
- Follow project conventions for casts, enums, and factories

## 3. Domain Model

Core entities and relationships:

- **FitnessPlan** (hasMany NutritionCycle, WorkoutProgram, ProgressEntry)
  - User's master plan with goals, duration, start/end dates
  
- **NutritionCycle** (belongsTo FitnessPlan; hasMany DailyTemplate, MealLog)
  - Defines cyclical macro targets (e.g., training vs rest days)
  
- **DailyTemplate** (belongsTo NutritionCycle; hasMany Templatemeal)
  - Macro targets and meal structure for a specific day type
  
- **TemplateMeal** (belongsTo DailyTemplate; belongsTo Recipe)
  - Pre-planned meal with portions
  
- **Recipe** (hasMany RecipeIngredient; hasMany MealLog)
  - Reusable meal definition with instructions and nutrition
  
- **RecipeIngredient** (belongsTo Recipe, belongsTo Ingredient)
  - Ingredients with quantities for a recipe
  
- **Ingredient** (hasMany RecipeIngredient)
  - Base food items with nutrition per 100g/serving
  
- **MealLog** (belongsTo NutritionCycle; belongsTo Recipe optional)
  - Actual meals consumed with timestamp and portions
  
- **WorkoutProgram** (belongsTo FitnessPlan; hasMany WorkoutTemplate)
  - Training program structure (e.g., PPL, Upper/Lower split)
  
- **WorkoutTemplate** (belongsTo WorkoutProgram; hasMany TemplateExercise)
  - Single workout session template
  
- **TemplateExercise** (belongsTo WorkoutTemplate; belongsTo Exercise)
  - Exercise with prescribed sets/reps/rest
  
- **Exercise** (hasMany TemplateExercise, WorkoutLog)
  - Exercise library with muscle groups, equipment needed
  
- **WorkoutSession** (belongsTo WorkoutProgram; hasMany WorkoutLog)
  - Completed workout instance
  
- **WorkoutLog** (belongsTo WorkoutSession; belongsTo Exercise)
  - Individual exercise performance log (weight, reps, RPE)
  
- **ProgressEntry** (belongsTo FitnessPlan; hasMany ProgressMeasurement, ProgressPhoto)
  - Progress check-in snapshot
  
- **ProgressMeasurement** (belongsTo ProgressEntry)
  - Body weight, body fat %, measurements
  
- **ProgressPhoto** (belongsTo ProgressEntry)
  - Progress photos with front/side/back views
  
- **MealPrepSchedule** (belongsTo NutritionCycle; hasMany PrepRecipe)
  - Batch cooking schedule
  
- **PrepRecipe** (belongsTo MealPrepSchedule; belongsTo Recipe)
  - Recipes with batch quantities and storage instructions

**Recommended enums** (PHP backed enums):
- FitnessGoal: cutting, bulking, maintenance, recomposition
- DayType: training, rest, heavy_training, light_training, refeed
- ExerciseType: compound, isolation, cardio
- MuscleGroup: chest, back, legs, shoulders, arms, core
- Equipment: barbell, dumbbell, machine, bodyweight, cable, resistance_band
- ProgressPhotoView: front, side, back, pose
- MeasurementType: weight, body_fat, chest, waist, hips, arms, thighs, calves
- MealType: breakfast, morning_snack, lunch, afternoon_snack, dinner, evening_snack, pre_workout, post_workout
- IntensityMetric: rpe_1_10, rir, percentage_1rm

**Key fields** (selected):

- **fitness_plans**: goal(enum), start_date, target_end_date, starting_weight, target_weight, current_tdee, activity_level, notes, status(active/completed/paused)

- **nutrition_cycles**: name, description, cycle_length_days, default_protein_g, default_carbs_g, default_fat_g, calories_total, day_type_mapping_json

- **daily_templates**: day_type(enum), calories_target, protein_g, carbs_g, fat_g, fiber_g, meal_count

- **recipes**: name, description, instructions, servings, prep_time_mins, cook_time_mins, calories_per_serving, protein_g, carbs_g, fat_g, fiber_g, is_meal_prep_friendly, cuisine, meal_type(enum)

- **ingredients**: name, calories_per_100g, protein_g_per_100g, carbs_g_per_100g, fat_g_per_100g, fiber_g_per_100g, serving_size_g, serving_description

- **meal_logs**: logged_at, day_type, meal_type(enum), recipe_id(nullable), servings, calories, protein_g, carbs_g, fat_g, notes

- **workout_programs**: name, description, split_type, weeks_duration, days_per_week, goal(enum)

- **workout_templates**: program_day, name, focus_muscle_group(enum), estimated_duration_mins, difficulty_level

- **exercises**: name, type(enum), primary_muscle_group(enum), secondary_muscle_groups_json, equipment(enum), instructions, video_url

- **workout_logs**: exercise_id, set_number, reps, weight_kg, rest_seconds, rpe, notes, completed_at

- **progress_entries**: measured_at, weight_kg, body_fat_percent, notes, feeling_rating_1_10

- **progress_measurements**: measurement_type(enum), value_cm, value_kg

**Indexes**:
- meal_logs: [logged_at], [nutrition_cycle_id, logged_at], [recipe_id]
- workout_logs: [workout_session_id], [exercise_id], [completed_at]
- progress_entries: [fitness_plan_id, measured_at]
- recipes: fulltext(name, description) if supported
- exercises: [type], [primary_muscle_group], [equipment]

**Soft deletes**: FitnessPlan, Recipe, WorkoutProgram, Exercise
**Auditing**: created_by, updated_by on major entities; activity log via events

## 4. Workflows

### Nutrition Workflow:
1. Create FitnessPlan with goal and targets
2. Set up NutritionCycle with cyclical macro targets
3. Create DailyTemplates for each day type (training/rest)
4. Build Recipe library with ingredients and macros
5. Assign TemplateMeals to DailyTemplates
6. Daily: Log meals against templates or create custom entries
7. Weekly: Review adherence analytics and adjust as needed

### Workout Workflow:
1. Create or select WorkoutProgram template
2. Define WorkoutTemplates for each training day
3. Add TemplateExercises with prescribed volume
4. Daily: Start workout session, log sets/reps/weight
5. Track progressive overload via performance charts
6. Deload weeks automatically suggested based on fatigue markers

### Progress Tracking Workflow:
1. Weekly check-ins: Create ProgressEntry
2. Log weight, measurements, photos (front/side/back)
3. View progress charts: weight trend, measurement changes
4. Milestone achievements trigger notifications
5. Adjust nutrition/training based on progress rate

### Meal Prep Workflow:
1. Select recipes for the week
2. Generate shopping list with aggregated ingredients
3. Create MealPrepSchedule with batch quantities
4. Mark prep tasks completed
5. Log meals from prepped inventory

## 5. Calculations & Business Rules

### Macro Calculations:
- TDEE = BMR × Activity Multiplier
- BMR (Mifflin-St Jeor): Men: 10×weight(kg) + 6.25×height(cm) - 5×age + 5
- Caloric surplus (bulk): TDEE + 300-500 cal
- Caloric deficit (cut): TDEE - 500-750 cal
- Protein: 2.0-2.5g per kg bodyweight
- Fat: 20-30% of total calories
- Carbs: remaining calories ÷ 4

### Progressive Overload:
- Increase weight when RPE ≤ 7 for 2 consecutive sessions
- Increase reps when target reps achieved for 2 sessions at RPE ≤ 8
- Suggest deload when average RPE > 8.5 for 3+ sessions

### Adherence Scoring:
- Daily adherence: actual macros within ±10% of targets = 100%
- Weekly adherence: average of daily scores
- Workout adherence: completed sessions / planned sessions × 100

## 6. Permissions & Security

- **Roles**: owner only (personal app; future multi-user would add coach/client)
- **Policies**: view/create/update/delete for all entities
- **PII**: Progress photos private; body metrics sensitive
- **Data retention**: Progress photos kept indefinitely unless manually deleted; meal/workout logs kept 2+ years

## 7. UI/UX Plan

### Navigation:
- Main tabs: Dashboard, Nutrition, Workouts, Progress, Templates

### Dashboard View:
- Today's macro targets and progress ring charts
- Today's workout scheduled
- Recent progress photo comparison (this week vs 4 weeks ago)
- Adherence streak counter
- Quick log buttons (meal, workout, weight)

### Nutrition Views:
- **Meal Log**: List of today's meals with macro totals vs targets; quick add from templates
- **Weekly Plan**: Calendar view with daily templates and actual logged meals
- **Recipe Library**: Searchable/filterable recipe cards with nutrition info
- **Shopping List**: Auto-generated from meal plan; check-off items

### Workout Views:
- **Active Session**: Live workout logging with rest timer, previous performance reference
- **Program Overview**: Current program schedule, upcoming workouts
- **Exercise Library**: Searchable exercises with instructions and demo videos
- **History**: Past workout sessions with volume/intensity trends

### Progress Views:
- **Dashboard**: Weight chart, measurement trends, progress photo gallery
- **Check-in Form**: Weight, measurements, photos, notes input
- **Analytics**: Rate of change, projected timeline to goal, adherence correlation

### Template Management:
- **Meal Templates**: Create/edit daily meal plans
- **Workout Templates**: Build workout programs and sessions
- **Import/Export**: JSON export for backup and template sharing

### Mobile Optimizations:
- Quick log widgets for meals and workouts
- Voice notes for workout feedback
- Offline mode for workout logging (sync when online)
- Push notifications for meal prep reminders and workout time

## 8. Notifications & Automations

- **Channels**: in-app, email, push (future)
- **Triggers**:
  - Daily: Meal prep reminder (if scheduled)
  - Daily: Workout reminder 30 mins before scheduled time
  - Weekly: Progress check-in reminder (Sunday AM)
  - Weekly: Meal plan for upcoming week (Saturday)
  - Milestone: Weight goal milestone reached
  - Alert: 3+ days no meal logs
  - Alert: 2+ missed workouts in a row
- **Weekly Digest**: Sunday evening; includes week's adherence, progress summary, next week's plan

## 9. Integrations (Future — v2)

- **Fitness Trackers**: Apple Health, Fitbit, Garmin for automated weight/activity sync
- **Barcode Scanner**: Scan food barcodes for quick ingredient/meal logging
- **Recipe Import**: Parse recipe URLs to auto-create recipes with nutrition
- **AI Meal Suggestions**: Generate meal plans based on preferences and restrictions
- **Form Check**: Video upload with pose estimation for exercise form feedback

## 10. Analytics & Reporting

### Nutrition Analytics:
- Daily/weekly/monthly macro trends vs targets
- Calorie adherence percentage
- Meal timing patterns
- Most logged recipes
- Macro distribution by meal type

### Workout Analytics:
- Volume progression by muscle group (sets × reps × weight)
- Frequency heatmap (workouts per week over time)
- Exercise PR tracking
- Average RPE trends (fatigue indicator)
- Time under tension calculations

### Progress Analytics:
- Weight trend with moving average
- Body composition changes (lean mass vs fat estimates)
- Measurement changes by body part
- Progress photo timeline comparisons
- Goal projection based on current rate

### Export Options:
- CSV export for meals, workouts, progress
- PDF report generation (weekly/monthly summaries)
- JSON backup of all data

## 11. API Surface (REST, versioned)

### Nutrition Endpoints:
- GET /api/v1/fitness-plans
- POST /api/v1/fitness-plans
- GET /api/v1/nutrition-cycles/{id}
- GET /api/v1/meal-logs [filters: date_from, date_to, meal_type]
- POST /api/v1/meal-logs
- GET /api/v1/recipes [search, filters]
- POST /api/v1/recipes
- GET /api/v1/daily-macros [date parameter]

### Workout Endpoints:
- GET /api/v1/workout-programs
- POST /api/v1/workout-programs
- GET /api/v1/workout-sessions/{id}
- POST /api/v1/workout-sessions
- POST /api/v1/workout-logs
- GET /api/v1/exercises [search, filters: muscle_group, equipment]
- GET /api/v1/workout-history [date_from, date_to, exercise_id]

### Progress Endpoints:
- GET /api/v1/progress-entries [date_from, date_to]
- POST /api/v1/progress-entries
- POST /api/v1/progress-photos (multipart upload)
- GET /api/v1/progress-analytics [metrics: weight, measurements, adherence]

### Template Endpoints:
- GET /api/v1/daily-templates
- GET /api/v1/workout-templates
- POST /api/v1/templates/duplicate

**Pagination, filtering, sorting**: Standard patterns; rate limits via middleware

## 12. Migrations, Factories, Seeders

### Migrations:
- Create migrations for all 20+ entities with proper foreign keys and indexes
- Include all attributes when modifying columns (Laravel 12 requirement)
- Use JSON columns for flexible fields (muscle_groups, day_type_mapping)
- Add unique constraints (e.g., unique recipe names per user)

### Factories:
- FitnessPlan::factory()->cutting() / ->bulking() / ->maintenance()
- Recipe::factory()->highProtein() / ->mealPrepFriendly() / ->withIngredients(5)
- WorkoutProgram::factory()->ppl() / ->upperLower() / ->fullBody()
- Exercise::factory()->compound() / ->isolation() / ->cardio()
- ProgressEntry::factory()->withPhotos() / ->withMeasurements()

### Seeders:
- Demo FitnessPlan with complete 12-week program
- Recipe library (50+ common fitness meals)
- Exercise library (200+ exercises with all major movements)
- Sample meal logs and workout logs for 30 days
- Progress entries showing realistic transformation timeline

## 13. Validation & Controllers

### Form Requests:
- StoreRecipeRequest: name required, macros numeric, servings min:1
- StoreMealLogRequest: logged_at required, macros validate sum
- StoreWorkoutLogRequest: reps min:1 max:100, weight min:0, rpe between:1,10
- StoreProgressEntryRequest: weight required, measurements optional array

### Controllers:
- Thin controllers; delegate to services:
  - MacroCalculationService: TDEE, daily targets, adjustments
  - AdherenceService: scoring, streak calculation
  - ProgressiveOverloadService: weight/rep recommendations
  - MealPrepService: shopping list generation
- Use API Resources for serialization
- Eager load relationships to prevent N+1:
  - Recipe with ingredients
  - WorkoutSession with logs and exercises
  - ProgressEntry with measurements and photos

## 14. Testing Strategy

### Feature Tests:
- Complete nutrition cycle workflow (create plan → log meals → view adherence)
- Workout session logging with progressive overload
- Progress tracking with photo uploads
- Meal prep scheduling and shopping list generation
- API endpoints for all CRUD operations
- Authentication and authorization for all routes

### Unit Tests:
- MacroCalculationService: TDEE, macro splits, various formulas
- AdherenceService: scoring edge cases, streak logic
- ProgressiveOverloadService: recommendation accuracy
- NutritionCycle day type mapping logic
- Recipe macro calculations from ingredients

### Edge Cases:
- Zero-calorie meals (water, black coffee)
- Missing workout logs (partial sessions)
- Progress entries without photos
- Recipe scaling with decimal servings
- Negative weight progression (strength loss)

### Performance:
- Meal log queries with 365+ days of data
- Exercise library search with 500+ exercises
- Progress photo gallery with 52+ photos
- Analytics dashboard aggregations

## 15. Events & Listeners

### Domain Events:
- FitnessPlanCreated
- MealLogged (check daily adherence)
- WorkoutSessionCompleted (check weekly volume)
- ProgressEntryCreated (milestone detection)
- CyclePhaseChanged (training day → rest day)
- AdherenceStreakAchieved (7, 14, 30, 90 days)
- WeightMilestoneReached (goal weight achieved)
- PersonalRecordBroken (exercise PR)

### Listeners:
- SendAdherenceNotification
- CalculateDailyMacros
- UpdateProgressiveOverload
- GenerateWeeklyDigest
- DetectMilestones
- UpdateAdherenceStreak

## 16. Configuration

**Config file**: fitness.php
```php
return [
    'tdee_activity_multipliers' => [
        'sedentary' => 1.2,
        'lightly_active' => 1.375,
        'moderately_active' => 1.55,
        'very_active' => 1.725,
        'extra_active' => 1.9,
    ],
    'macro_adherence_threshold_percent' => 10,
    'deload_week_frequency' => 4, // every 4 weeks
    'progress_photo_reminder_day' => 'sunday',
    'meal_prep_reminder_time' => '10:00',
    'workout_reminder_minutes_before' => 30,
    'adherence_streak_milestones' => [7, 14, 30, 60, 90, 180, 365],
];
```

## 17. Risks & Mitigations

- **Inaccurate nutrition data** → Validate against USDA database; allow user corrections
- **User burnout from logging** → Quick-log shortcuts, voice input, template reuse
- **Progress plateaus** → Automated recommendations for diet/training adjustments
- **Data loss** → Regular automated backups; export functionality
- **Injury from poor form** → Include form cues, video demos, RPE guidance
- **Body image concerns** → Positive language, focus on performance, optional photo feature

## 18. Milestones & Timeline (indicative)

- **M1 (Week 1)**: Migrations, models, factories for nutrition domain; recipe CRUD
- **M2 (Week 2)**: Meal logging, daily macro tracking, adherence calculations
- **M3 (Week 3)**: Workout program models, exercise library, session logging
- **M4 (Week 4)**: Progress tracking, photos, measurements, weight trends
- **M5 (Week 5)**: Template system, meal prep scheduling, shopping lists
- **M6 (Week 6)**: Analytics dashboards, charts, progressive overload logic
- **M7 (Week 7)**: Notifications, reminders, weekly digests
- **M8 (Week 8)**: API endpoints, mobile UI polish, offline support
- **M9 (Week 9)**: Testing (feature, unit, edge cases), performance optimization
- **M10 (Week 10)**: Documentation, demo data seeding, user onboarding flow

## 19. Acceptance Criteria

- User can create a fitness plan with specific goal and calculate initial TDEE/macros
- User can build daily meal templates and log actual meals with macro tracking
- User can create workout program and log training sessions with progressive overload
- User can track weekly progress with weight, measurements, and photos
- User can generate shopping list from meal plan and schedule meal prep
- Dashboard shows today's targets, progress, and quick actions
- Analytics show adherence trends, progress over time, and milestone achievements
- API returns consistent resources with proper eager loading
- Factories/seeders create realistic demo data for 12-week program
- Mobile UI is responsive and supports offline meal/workout logging
- Notifications remind user of meal prep, workouts, and weekly check-ins

## 20. Open Questions

- Should we support multiple fitness plans simultaneously or force one active plan?
- Which body composition formula should be default (Navy method, Jackson-Pollock, DEXA estimates)?
- Do we need barcode scanning for v1 or defer to v2?
- Should progress photos require specific poses or be freeform?
- Integration preferences for future fitness tracker sync?
- Should we include supplement tracking (protein shakes, creatine, etc.)?

## 21. Data Privacy & Ethics

- Progress photos stored securely with restricted access
- Option to disable progress photo feature entirely
- Body metrics presented neutrally without judgment
- Focus on health and performance over aesthetics
- Clear data deletion options
- No social comparison or public sharing in v1

## 22. Observability

- Structured logging for macro calculations, adherence scoring, and recommendations
- Metrics: daily active users, meals logged per day, workouts completed per week, average adherence %, API response times
- Alerts: high API error rates, failed notification sends, large data exports

## 23. Next Steps

1. Review and confirm scope with stakeholder (Davor)
2. Create feature branch: `feature/cycle-menu-fitness-plan`
3. Start with M1: migrations and models for nutrition domain
4. Build Recipe and Ingredient CRUD with comprehensive factories
5. Implement MacroCalculationService with unit tests
6. Progressive implementation following milestone timeline
7. Regular demos at end of each milestone for feedback

---

This document defines the v1 scope for the Cycle Menu Fitness Plan module. Implementation should follow Laravel 12 best practices with proper testing, validation, and API resource patterns. The module integrates seamlessly into the LifeOS personal management platform.

## 24. Cycle Menu MVP — Implementation Plan (Focused Scope)

Status: Approved plan; ready to implement

### 24.1 Scope Summary
- Add a new navigation entry for Cycle Menu (or reorganize under Nutrition if preferred).
- Support multiple items per day within a cycle menu.
- Send a daily notification at 09:00 about today’s menu.

Assumptions (pending future iteration if needed):
- Items can be free‑text with an optional link to `Recipe` if available.
- Use application timezone for 09:00 notifications initially; per‑user timezones can be added later.
- In‑app notifications enabled; email optional if mail is configured.

### 24.2 Information Architecture & Navigation
- Add primary or secondary navigation entry labeled “Cycle Menu”.
- Use named route helpers for URL generation and active state highlighting.
- Follow Tailwind v4 and existing dark mode conventions defined in `DESIGN_SYSTEM.md`.

### 24.3 Data Model & Migrations (Laravel 12)
- Tables:
  - `cycle_menus`: id, name, starts_on (date), cycle_length_days (int), is_active (bool), notes, timestamps.
  - `cycle_menu_days`: id, cycle_menu_id (FK), day_index (int), notes, unique([cycle_menu_id, day_index]), timestamps.
  - `cycle_menu_items`: id, cycle_menu_day_id (FK), title, meal_type (enum: breakfast, lunch, dinner, snack, other), time_of_day (time nullable), quantity (string), recipe_id (FK nullable), position (int), timestamps.
- Indexes and foreign keys for all relationships. Defer soft deletes unless needed.
- Factories for each model for testing and seed data.

### 24.4 Eloquent Models & Relationships
- `CycleMenu` hasMany `CycleMenuDay`; scope `active()`.
- `CycleMenuDay` belongsTo `CycleMenu`; hasMany `CycleMenuItem` (ordered by `position`).
- `CycleMenuItem` belongsTo `CycleMenuDay`; optional belongsTo `Recipe`.
- Use `casts()` methods per project conventions.

### 24.5 Authorization & Validation
- Policies for CRUD (owner‑only) on CycleMenu, Day, and Item.
- Form Requests for store/update on all three resources; include custom error messages.

### 24.6 Controllers & Routes
- `CycleMenuController`: index, create, store, show, edit, update, destroy.
- `CycleMenuDayController`: edit/update and reordering endpoint.
- `CycleMenuItemController`: store, update, destroy, reorder.
- Define named routes in `routes/web.php`; add API routes later if needed.

### 24.7 Views (Blade + Tailwind v4)
- Index: list of cycle menus, active badge, quick actions.
- Show: grid (e.g., 7‑day) with per‑day columns showing multiple items and supporting drag/drop ordering.
- Edit Day modal: repeatable item fields; maintain `position` for ordering.
- Ensure accessibility and dark mode support per `DESIGN_SYSTEM.md`.

### 24.8 Daily 09:00 Notification
- Notification class `DailyMenuNotification` (in‑app; mail optional) implements `ShouldQueue`.
- Scheduler in `bootstrap/app.php`: `dailyAt('09:00')` using app timezone.
- Logic: For active CycleMenu(s), compute today’s `day_index` from `starts_on` and `cycle_length_days`; eager load items and notify the user with today’s list.

### 24.9 Tests (Pest/Feature)
- Factories and relationship tests for the three models.
- Feature tests: create menu with multiple items per day; reorder items; view today’s items.
- Notification test: scheduler dispatches `DailyMenuNotification` at 09:00; assert queued.

### 24.10 Seeders & Demo Data
- Demo Cycle Menu: 7 days with several items per day.
- Update `DatabaseSeeder` to conditionally seed demo data in non‑production.

### 24.11 Performance & N+1
- Eager load `days.items` for index/show.
- Proper indexes on FKs; batch queries in the scheduler to avoid per‑day loops.

### 24.12 Rollout Steps
1) Generate migrations, models, factories, policies, and form requests via `php artisan make:` with `--no-interaction`.
2) Implement controllers, routes, and views.
3) Implement the notification and scheduler.
4) Write feature and unit tests; run and fix.
5) Seed demo data; verify UX.
6) Run Pint (`vendor/bin/pint`).
7) Update README with usage instructions.

### 24.13 Timeline
- Day 1: Data model, migrations, models, factories.
- Day 2: Controllers, validation, routes, initial views, navigation update.
- Day 3: Notifications + scheduler, tests, polish.
- Day 4: QA, docs, demo.
