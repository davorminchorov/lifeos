# LifeOS Design System

This document outlines the comprehensive design system for LifeOS, providing guidelines for visual elements, components, and patterns to ensure consistency across the application.

## Brand Overview

LifeOS is a life management platform that helps users organize and automate various aspects of their lives including subscriptions, bills, investments, job applications, and expenses. The design system embodies our core values:

- **Clarity**: Information is presented clearly and intuitively
- **Efficiency**: Interactions are streamlined and purposeful
- **Integration**: Different life areas connect seamlessly
- **Insight**: Data visualization enhances understanding

## Color System

Our color palette is designed to be accessible, balanced, and meaningful, with colors that convey specific information and states.

### Primary Colors

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| Deep Teal | #0F766E | rgb(15, 118, 110) | Primary brand color, buttons, key UI elements |
| Slate Blue | #1E293B | rgb(30, 41, 59) | Headers, navigation, secondary elements |
| Warm White | #F8FAFC | rgb(248, 250, 252) | Background color for main content areas |

### Accent Colors

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| Sunrise Orange | #F97316 | rgb(249, 115, 22) | Call-to-actions, highlights, important actions |
| Mint Green | #10B981 | rgb(16, 185, 129) | Success states, positive metrics, growth indicators |
| Ocean Blue | #3B82F6 | rgb(59, 130, 246) | Interactive elements, links, data visualization |

### Functional Colors

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| Alert Red | #EF4444 | rgb(239, 68, 68) | Errors, warnings, critical notifications |
| Notice Yellow | #F59E0B | rgb(245, 158, 11) | Caution states, pending actions |
| Success Green | #10B981 | rgb(16, 185, 129) | Confirmations, completed tasks |

### Neutral Scale

| Color | Hex | RGB | Usage |
|-------|-----|-----|-------|
| Charcoal | #1E293B | rgb(30, 41, 59) | Primary text, headings |
| Slate | #475569 | rgb(71, 85, 105) | Secondary text, labels |
| Silver | #94A3B8 | rgb(148, 163, 184) | Disabled states, tertiary text |
| Light Gray | #E2E8F0 | rgb(226, 232, 240) | Borders, dividers, subtle backgrounds |
| Off White | #F8FAFC | rgb(248, 250, 252) | Background variations, cards |

### Color Usage Guidelines

- Maintain contrast ratios of at least 4.5:1 for normal text and 3:1 for large text
- Use primary colors for main interface elements
- Reserve accent colors for emphasis and drawing attention
- Functional colors should be used consistently for their designated states
- Limit the use of accent colors to maintain visual hierarchy

## Typography

Our typography system uses Inter, a versatile and highly readable typeface that works across all screen sizes and platforms.

### Font Family

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
```

### Type Scale

| Name | Size | Line Height | Weight | Usage |
|------|------|-------------|--------|-------|
| xs | 12px / 0.75rem | 16px / 1rem | 400, 500 | Small labels, footnotes, metadata |
| sm | 14px / 0.875rem | 20px / 1.25rem | 400, 500 | Secondary text, table content |
| base | 16px / 1rem | 24px / 1.5rem | 400, 500 | Body text, form controls |
| lg | 18px / 1.125rem | 28px / 1.75rem | 500, 600 | Section headings, emphasis |
| xl | 20px / 1.25rem | 28px / 1.75rem | 600 | Card headings, minor section headers |
| 2xl | 24px / 1.5rem | 32px / 2rem | 600 | Page headings |
| 3xl | 30px / 1.875rem | 36px / 2.25rem | 600, 700 | Dashboard main headings |
| 4xl | 36px / 2.25rem | 40px / 2.5rem | 700 | Welcome screens, major headers |

### Font Weights

- 400 (Regular): Default body text
- 500 (Medium): Emphasis, interactive elements
- 600 (Semi-bold): Headings, important text
- 700 (Bold): Extra emphasis, primary action text

### Text Styles

#### Headings

```css
.heading-4xl {
  font-size: 2.25rem;
  line-height: 2.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: #1E293B;
}

.heading-3xl {
  font-size: 1.875rem;
  line-height: 2.25rem;
  font-weight: 600;
  letter-spacing: -0.02em;
  color: #1E293B;
}

/* Continue for other heading sizes */
```

#### Body Text

```css
.body-base {
  font-size: 1rem;
  line-height: 1.5rem;
  font-weight: 400;
  color: #1E293B;
}

.body-base-medium {
  font-size: 1rem;
  line-height: 1.5rem;
  font-weight: 500;
  color: #1E293B;
}

/* Continue for other body text styles */
```

### Typography Guidelines

- Maintain consistent use of the type scale
- Use appropriate weights to create hierarchy
- Ensure proper line height for readability
- Limit line length to 60-75 characters for optimal readability
- Maintain sufficient contrast between text and background

## Iconography

LifeOS uses Phosphor Icons, a flexible and consistent icon system that complements our design aesthetic.

### Icon Styles

- **Regular**: 1.5px stroke weight, used for most UI elements
- **Bold**: 2px stroke weight, used for emphasis or smaller sizes
- **Fill**: Solid version, used sparingly for active states or emphasis

### Icon Sizes

| Size | Dimensions | Usage |
|------|------------|-------|
| Small | 16 x 16px | Inline text, dense UI areas |
| Medium | 20 x 20px | Secondary actions, complementary to text |
| Standard | 24 x 24px | Primary interface elements, navigation |
| Large | 32 x 32px | Feature introductions, empty states |

### Icon Categories

- **Navigation**: home, menu, arrow-left, arrow-right, etc.
- **Actions**: plus, minus, edit, trash, download, share, etc.
- **Financial**: currency-dollar, credit-card, bank, receipt, etc.
- **Communication**: envelope, chat, bell, etc.
- **Status**: check, x, warning, info, question, etc.
- **Time**: calendar, clock, hourglass, etc.

### Icon Usage Guidelines

- Maintain consistent size and style within contexts
- Use appropriate semantic icons (e.g., trash for delete)
- Ensure sufficient touch target size (min 44x44px) for interactive icons
- Pair icons with text for clarity when possible
- Apply color consistently based on function

## Spacing & Layout

LifeOS uses a consistent spacing system based on a 4px grid to create rhythm and balance in the interface.

### Spacing Scale

| Name | Size | Usage |
|------|------|-------|
| xs | 4px | Minimal spacing, tight elements |
| sm | 8px | Close elements, internal padding |
| md | 16px | Standard spacing, component padding |
| lg | 24px | Section separation, generous padding |
| xl | 32px | Major section separation |
| 2xl | 48px | Layout-level spacing |
| 3xl | 64px | Major layout divisions |

### Grid System

The LifeOS interface uses a 12-column grid system that adapts to different viewport sizes:

- **Container width**: Responsive with max-width of 1440px
- **Columns**: 12 columns
- **Gutters**: 24px standard (adjusts at different breakpoints)
- **Margins**: Responsive, minimum 16px on smallest screens

### Breakpoints

| Name | Width | Description |
|------|-------|-------------|
| xs | <640px | Mobile devices |
| sm | ≥640px | Small tablets, large mobile |
| md | ≥768px | Tablets, portrait |
| lg | ≥1024px | Tablets landscape, small desktops |
| xl | ≥1280px | Desktops |
| 2xl | ≥1536px | Large desktops |

### Layout Guidelines

- Use consistent spacing within component types
- Maintain alignment with the grid system
- Allow for proper whitespace to create visual hierarchy
- Scale spacing appropriately at different breakpoints
- Use established patterns for common layouts

## Components

### Buttons

Buttons are a key interactive element in LifeOS, with different variants for different purposes.

#### Button Variants

| Variant | Description | Usage |
|---------|-------------|-------|
| Primary | Filled Deep Teal background | Main actions, form submissions |
| Secondary | Outlined with Slate Blue | Alternative actions, secondary options |
| Tertiary | Text-only with Ocean Blue | Subtle actions, cancel options |
| Destructive | Alert Red | Delete, remove, cancel subscriptions |
| Success | Success Green | Complete, confirm, approve |

#### Button Sizes

| Size | Height | Padding | Font Size | Usage |
|------|--------|---------|-----------|-------|
| Small | 32px | 12px | 14px | Compact areas, inline actions |
| Medium | 40px | 16px | 16px | Most interface actions |
| Large | 48px | 20px | 18px | Primary page actions, CTAs |

#### Button States

- **Default**: Base styling
- **Hover**: Slightly darker/lighter background
- **Active/Pressed**: Deeper color change, slight scale reduction
- **Focus**: Focus ring (2px) in a complementary color
- **Disabled**: Reduced opacity, non-interactive styling

#### Button with Icon

```html
<button class="btn btn-primary btn-icon-right">
  <span>Add Subscription</span>
  <PhosphorIcon name="plus" size="20" />
</button>
```

### Form Elements

#### Text Inputs

- Height: 40px (medium), 48px (large)
- Border radius: 8px
- Border: 1px solid Light Gray (#E2E8F0)
- Padding: 12px 16px
- Focus state: Border color changes to Deep Teal (#0F766E)

#### Checkboxes & Radio Buttons

- Size: 20px x 20px
- Border radius: 4px (checkbox), 50% (radio)
- Selected state: Deep Teal background (#0F766E)
- Focus state: 2px Ocean Blue focus ring

#### Toggle Switches

- Height: 24px
- Width: 44px
- Border radius: 12px
- Off state: Light Gray (#E2E8F0)
- On state: Deep Teal (#0F766E)
- Transition: Smooth 150ms transition

#### Dropdowns & Select

- Same base styling as text inputs
- Dropdown icon: Phosphor "caret-down"
- Open state: Border color Deep Teal (#0F766E)
- Options: 40px height, highlighted on hover

### Cards

Cards are container components used throughout LifeOS to group related information.

#### Card Variants

| Variant | Description | Usage |
|---------|-------------|-------|
| Standard | Basic container with padding | Most content grouping |
| Interactive | Clickable card with hover state | Navigational elements |
| Featured | Highlighted card with accent border | Drawing attention to specific content |
| Dashboard | Specialized for metrics display | Key performance indicators |

#### Card Properties

- Border radius: 8px (standard), 12px (featured)
- Padding: 24px standard
- Background: Off White (#F8FAFC)
- Shadow: 0 1px 3px rgba(0,0,0,0.1) (light), 0 4px 6px rgba(0,0,0,0.1) (medium)
- Border: 1px solid Light Gray (#E2E8F0) (optional)

#### Card Structure

```html
<div class="card card-standard">
  <div class="card-header">
    <h3 class="heading-xl">Card Title</h3>
    <button class="btn btn-tertiary btn-icon-only">
      <PhosphorIcon name="dots-three-vertical" size="20" />
    </button>
  </div>
  <div class="card-content">
    <!-- Card content goes here -->
  </div>
  <div class="card-footer">
    <!-- Optional footer content -->
  </div>
</div>
```

### Navigation

#### Main Navigation

- Background: Slate Blue (#1E293B)
- Text: Off White (#F8FAFC)
- Active item: Deep Teal (#0F766E) background or indicator
- Item height: 48px
- Icon placement: Left of text, 24px size

#### Tab Navigation

- Text: Charcoal (#1E293B)
- Active tab: Deep Teal (#0F766E) underline, text color
- Hover: Light gray background
- Tab padding: 16px 20px
- Optional icon: Left of text, 20px size

#### Breadcrumbs

- Text: Slate (#475569)
- Separator: Custom slash icon or "/"
- Current page: Charcoal (#1E293B), semi-bold weight
- Hover: Deep Teal (#0F766E)

### Data Visualization

#### Charts & Graphs

- **Color usage**: Primary brand colors for main data, accent colors for emphasis
- **Typography**: Consistent with main typography system
- **Legends**: Clear, concise labels in sm (14px) text size
- **Tooltips**: Appear on hover with detailed information
- **Empty states**: Helpful messaging when no data is available

#### Data Tables

- Header background: Light Gray (#E2E8F0)
- Header text: Charcoal (#1E293B), semi-bold
- Row height: 48px standard
- Zebra striping: Alternate rows with Off White (#F8FAFC) and slightly darker shade
- Borders: 1px solid Light Gray (#E2E8F0)
- Hover state: Subtle highlight background

## Patterns

### Empty States

- Illustrated icon (48px or larger)
- Clear, concise heading (xl or 2xl)
- Brief explanation text
- Primary action button when applicable
- Warm, supportive tone

### Notifications & Alerts

#### Toast Notifications

- Position: Top-right corner
- Width: 320px max
- Duration: 5 seconds default (configurable)
- Variants: Success, Error, Warning, Info
- Animation: Slide in, fade out

#### Modal Alerts

- Center screen positioning
- Background overlay: Semi-transparent black
- Width: 480px standard (responsive)
- Close methods: X button, overlay click, escape key
- Focus management: Trap focus within modal

### Loading States

- **Inline loading**: Small spinner, same height as replaced content
- **Page loading**: Centered spinner with optional progress indication
- **Skeleton screens**: Content placeholder animations for perceived speed
- **Button loading**: Replace text with spinner, maintain button width

### Responsive Patterns

- **Navigation**: Collapses to hamburger menu on mobile
- **Tables**: Horizontal scroll or card layout on small screens
- **Grids**: Reduce columns on smaller screens
- **Images**: Appropriately sized for different viewports

## Logo & Branding

### Logo Specifications

- **Wordmark**: "LifeOS" in Inter font, semi-bold weight
- **Symbol**: Overlapping geometric shapes (circle, square) representing different life areas
- **Colors**: Primary logo uses Deep Teal (#0F766E)
- **Contrast versions**: White for dark backgrounds, Charcoal for light backgrounds
- **Clear space**: Minimum padding equal to 'O' height in all directions
- **Minimum size**: 80px width for digital, 1 inch for print

### Brand Voice

- **Tone**: Supportive, efficient, insightful, organized
- **Language style**: Clear, concise, friendly but professional
- **Person**: First-person for user actions, second-person in guidance
- **Tense**: Present tense, active voice

## Accessibility

LifeOS is committed to WCAG 2.1 AA compliance across the application.

### Color & Contrast

- Maintain minimum contrast ratios (4.5:1 for normal text, 3:1 for large text)
- Don't rely solely on color to convey information
- Provide sufficient contrast between foreground and background elements

### Keyboard Navigation

- Ensure all interactive elements are keyboard accessible
- Maintain logical tab order
- Provide visible focus states for all interactive elements
- Support standard keyboard shortcuts and patterns

### Screen Readers

- Use semantic HTML elements appropriately
- Provide alternative text for images and icons
- Ensure ARIA attributes are used correctly when needed
- Test with common screen readers (NVDA, JAWS, VoiceOver)

### Motion & Animation

- Respect user preferences for reduced motion
- Keep animations subtle and purposeful
- Provide alternatives for motion-based interactions

## Implementation Resources

### Design Assets

- Figma UI Kit: [Link to Figma file]
- Icon library: [Link to icon package]
- Color palette: [Link to color system file]

### Code Resources

- CSS variables for theming
- Component library implementation
- Utility classes for spacing and typography
- CSS framework integration (Tailwind configuration)

### Development Guidelines

- Component implementation checklist
- Accessibility testing procedures
- Responsive testing guidelines
- Performance benchmarks

## Version History

- **v1.0.0** (2023-05-15): Initial design system release
- **v1.1.0** (2023-07-10): Added data visualization components
- **v1.2.0** (2023-09-22): Expanded accessibility guidelines
- **v1.3.0** (2023-12-05): Added mobile patterns and responsive improvements

---

This design system is a living document that will evolve as LifeOS grows. All team members are encouraged to provide feedback and suggestions for improvement. 
