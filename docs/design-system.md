# LifeOS Design System & Branding Guidelines

## Core Brand Identity

LifeOS is a personal operating system that helps users manage their finances, tasks, and life operations in a unified platform. The design system emphasizes clarity, functionality, and user empowerment.

### Brand Values
- **Simplicity**: Clean interfaces with purposeful design elements
- **Reliability**: Consistent behavior and visual language
- **Empowerment**: Enabling users to gain control over their financial and personal data
- **Intelligence**: Smart features that provide meaningful insights

## Color System

### Primary Colors
- **Primary Blue**: `hsl(221, 83%, 40%)` - Main brand color, used for primary actions and key UI elements
- **Secondary Teal**: `hsl(174, 87%, 31%)` - Used for secondary actions and complementary UI elements
- **Accent Purple**: `hsl(263, 70%, 63%)` - Used sparingly for emphasis and special features

### Semantic Colors
- **Success**: `hsl(142, 76%, 45%)` - Positive indicators, confirmations
- **Warning**: `hsl(38, 92%, 50%)` - Alerts, cautions, notifications
- **Destructive**: `hsl(0, 84%, 60%)` - Errors, destructive actions

### Module-Specific Colors
- **Investments**: `#8B5CF6` (Soft purple)
- **Expenses**: `#F97316` (Orange)
- **Bills**: `#06B6D4` (Cyan)
- **Subscriptions**: `#14B8A6` (Teal)
- **Jobs**: `#4F46E5` (Indigo)

### Neutral Colors
- **Background**: `hsl(210, 40%, 98%)`
- **Foreground**: `hsl(222, 47%, 11%)`
- **Card**: `hsl(0, 0%, 100%)`
- **Muted**: `hsl(210, 40%, 96.1%)`
- **Border**: `hsl(220, 13%, 91%)`

## Typography

### Font Families
- **Primary**: 'Inter', sans-serif - Used for all general UI text
- **Monospace**: 'Roboto Mono', monospace - Used for code, financial figures, and data

### Font Sizes
- **xs**: 0.75rem (12px)
- **sm**: 0.875rem (14px)
- **base**: 1rem (16px)
- **lg**: 1.125rem (18px)
- **xl**: 1.25rem (20px)
- **2xl**: 1.5rem (24px)
- **3xl**: 1.875rem (30px)
- **4xl**: 2.25rem (36px)

### Font Weights
- **Regular**: 400
- **Medium**: 500
- **Semi-bold**: 600
- **Bold**: 700

## Component Guidelines

### Buttons
- Use appropriate variants based on action importance:
  - **Default**: Primary color for main actions
  - **Secondary**: Secondary color for alternative actions
  - **Outline**: For less emphasized actions
  - **Destructive**: For destructive actions
  - **Success**: For confirming positive actions
- Button sizes should match the importance and context
- Always include hover and focus states

### Cards
- Cards should have consistent padding (1.5rem)
- Use subtle shadows (0 1px 3px rgba(0, 0, 0, 0.1))
- Consider adding module-specific color indicators
- Cards can contain headers, content, and footers

### Forms & Inputs
- Form groups should include properly associated labels
- Input fields should have consistent styling
- Error states should be clearly indicated
- Required fields should be marked with an asterisk

### Dashboard Elements
- Use consistent spacing between dashboard items
- Group related information in cards
- Implement responsive layouts for all screen sizes
- Use color coding for different data categories

## Layout & Spacing

### Spacing Scale
- Base spacing unit: 8px
- Use consistent multiples: 4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px

### Border Radius
- **Small**: 0.125rem (2px)
- **Default**: 0.25rem (4px)
- **Medium**: 0.375rem (6px)
- **Large**: 0.5rem (8px)

### Shadows
- **Small**: 0 1px 2px 0 rgba(0, 0, 0, 0.05)
- **Default**: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)
- **Medium**: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)
- **Large**: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)

## Coding Standards

### CSS/Tailwind
- Utilize Tailwind CSS 4 utility classes for styling
- Follow a consistent utility ordering pattern:
  1. Layout (display, positioning)
  2. Spacing (margin, padding)
  3. Sizing (width, height)
  4. Typography
  5. Visual (colors, backgrounds)
  6. Interactive states
- Use Tailwind's configuration system for customization
- Leverage Tailwind's modern Just-In-Time engine for performance

### React Components
- Create reusable, composable components using React 19 patterns
- Use modern React features like hooks and function components
- Implement proper keyboard accessibility
- Follow conventional naming patterns

## Dark Mode Support
- All components should support both light and dark modes
- Dark mode uses adjusted color values for better contrast
- Test all components in both modes before implementation

## Icons
- Use consistent icon style throughout the app
- Icon size should match surrounding text size
- Include proper aria-labels for accessibility

## Responsiveness
- Mobile-first approach to all components
- Test on multiple device sizes
- Critical actions should be easily accessible on all devices

## Accessibility
- Maintain WCAG 2.1 AA compliance
- Ensure proper contrast ratios
- Use semantic HTML elements
- Include appropriate aria attributes
- Ensure keyboard navigability

## Best Practices
- Always refer to this guide when creating new components
- Review existing components before creating new ones
- Follow established patterns and conventions
- Prioritize consistency over novelty
- Document any deviations from these guidelines 
