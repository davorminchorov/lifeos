# LifeOS Design System: Material Design Implementation

This document outlines our implementation of Material Design principles for LifeOS, providing guidelines for visual elements, components, and patterns to ensure consistency across the application.

## Design Philosophy

LifeOS is adopting Material Design principles while maintaining our brand identity. This strategic shift provides several advantages:

- **User Familiarity**: Leveraging established design patterns that users already understand
- **Coherent Experience**: Creating a unified and predictable interface across all platforms
- **Accessibility**: Built on principles that prioritize inclusive design
- **Scalability**: A comprehensive system that can grow with our product

## Material Design Core Principles

### 1. Material as a Metaphor
Our interface is based on the tactile reality of paper and ink, with the added benefit of digital capabilities. We use surfaces, edges, shadows, and motion to create intuitive experiences.

### 2. Bold, Graphic, Intentional
We emphasize user actions through deliberate color choices, edge-to-edge imagery, and intentional whitespace to create hierarchy and meaning.

### 3. Motion Provides Meaning
Motion is purposeful and reinforces the user's actions, providing feedback and guiding attention appropriately.

## Color System

Our color system applies Material Design color principles while maintaining our brand's teal-focused palette.

### Primary Color and Variants

| Role | Color | Hex | Usage |
|------|-------|-----|-------|
| Primary | Deep Teal | #0F766E | Primary actions, key UI elements |
| Primary Light | Teal 500 | #14B8A6 | Hover states, secondary emphasis |
| Primary Dark | Teal 700 | #0D9488 | Active states, contrast elements |
| Primary Container | Teal 100 | #99F6E4 | Backgrounds for primary content |
| On Primary | White | #FFFFFF | Text/icons on primary color |
| On Primary Container | Teal 900 | #022C26 | Text/icons on primary container |

### Secondary Color and Variants

| Role | Color | Hex | Usage |
|------|-------|-----|-------|
| Secondary | Slate 600 | #475569 | Secondary actions, less emphasis |
| Secondary Light | Slate 500 | #64748B | Hover states for secondary elements |
| Secondary Dark | Slate 700 | #334155 | Active states for secondary elements |
| Secondary Container | Slate 200 | #E2E8F0 | Backgrounds for secondary content |
| On Secondary | White | #FFFFFF | Text/icons on secondary color |
| On Secondary Container | Slate 800 | #1E293B | Text/icons on secondary container |

### Surface Colors

| Role | Color | Hex | Usage |
|------|-------|-----|-------|
| Surface | White | #FFFFFF | Main content areas, cards |
| Surface Variant | Slate 50 | #F8FAFC | Alternative surfaces, subtle backgrounds |
| On Surface | Slate 800 | #1E293B | Primary text on surfaces |
| On Surface Variant | Slate 600 | #475569 | Secondary text on surfaces |

### State Colors

| Role | Color | Hex | Usage |
|------|-------|-----|-------|
| Error | Red 500 | #EF4444 | Error states, destructive actions |
| Error Container | Red 100 | #FECACA | Error backgrounds |
| Success | Green 500 | #10B981 | Success states, confirmations |
| Success Container | Green 100 | #D1FAE5 | Success backgrounds |
| Warning | Amber 500 | #F59E0B | Warning states, cautionary elements |
| Warning Container | Amber 100 | #FEF3C7 | Warning backgrounds |
| Info | Blue 500 | #3B82F6 | Information states, neutral alerts |
| Info Container | Blue 100 | #DBEAFE | Information backgrounds |

### Color Usage Guidelines

- Follow Material Design's color usage patterns for consistent recognition
- Maintain the 60-30-10 rule (60% primary surfaces, 30% secondary elements, 10% accent highlights)
- Ensure all color combinations meet WCAG 2.1 AA standards for contrast (4.5:1 for normal text)
- Use colors semantically (e.g., red for errors, green for success) to reinforce meaning
- Apply color to reinforce hierarchy and focus attention

## Typography

Our typography system follows Material Design type scale while using Inter as our primary typeface.

### Type Scale

| Material Name | Size | Line Height | Weight | Role |
|---------------|------|-------------|--------|------|
| Display Large | 57px / 3.56rem | 64px | Light | Hero headers, splash screens |
| Display Medium | 45px / 2.81rem | 52px | Light | Major feature introductions |
| Display Small | 36px / 2.25rem | 44px | Regular | Large headlines |
| Headline Large | 32px / 2rem | 40px | Regular | Main page headings |
| Headline Medium | 28px / 1.75rem | 36px | Regular | Section headings |
| Headline Small | 24px / 1.5rem | 32px | Regular | Card headings, minor sections |
| Title Large | 22px / 1.375rem | 28px | Medium | Major UI elements, dialog titles |
| Title Medium | 16px / 1rem | 24px | Medium | Button text, emphasized elements |
| Title Small | 14px / 0.875rem | 20px | Medium | Minor UI elements, section labels |
| Body Large | 16px / 1rem | 24px | Regular | Primary body text |
| Body Medium | 14px / 0.875rem | 20px | Regular | Secondary body text |
| Body Small | 12px / 0.75rem | 16px | Regular | Captions, annotations |
| Label Large | 14px / 0.875rem | 20px | Medium | Important labels, form fields |
| Label Medium | 12px / 0.75rem | 16px | Medium | Secondary labels |
| Label Small | 11px / 0.6875rem | 16px | Medium | Smallest UI text elements |

### Typography Implementation

```css
/* Font families */
--md-sys-typescale-font-family-base: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
--md-sys-typescale-font-family-brand: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

/* Display styles */
.display-large {
  font-family: var(--md-sys-typescale-font-family-brand);
  font-size: 3.56rem;
  line-height: 4rem;
  font-weight: 300;
  letter-spacing: -0.016em;
}

.display-medium {
  font-family: var(--md-sys-typescale-font-family-brand);
  font-size: 2.81rem;
  line-height: 3.25rem;
  font-weight: 300;
  letter-spacing: -0.008em;
}

/* Continue for all other type styles */
```

### Typography Guidelines

- Use type scale consistently to maintain visual hierarchy
- Ensure legibility with proper contrast and line spacing
- Limit to 3 font weights for a clean design (Light 300, Regular 400, Medium 500)
- Apply proper text alignment principles (typically left-aligned for LTR languages)
- Maintain optimal line lengths (60-75 characters) for better readability

## Shape System

Material Design uses intentional shape to create meaning and hierarchy.

### Corner Radius Scale

| Name | Size | Usage |
|------|------|-------|
| Extra Small | 4px | Small UI elements, chips |
| Small | 8px | Cards, dialogs, buttons |
| Medium | 12px | Large cards, emphasized elements |
| Large | 16px | Floating action buttons, featured elements |
| Extra Large | 28px | Large promotional elements |

### Shape Categories

- **Containers**: Use small radius (8px) for most containers like cards and dialogs
- **Buttons**: Use pill shape (full rounded) for primary actions, small radius (8px) for secondary actions
- **Text Fields**: Use small radius (8px) for consistent input styling
- **Bottom Sheets**: Use medium radius (12px) only on top corners

## Elevation System

Material Design uses elevation (shadows) to establish hierarchy and states.

### Elevation Levels

| Level | Shadow | Usage |
|-------|--------|-------|
| 0 | None | Disabled elements, dividers |
| 1 | 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.14) | Cards, buttons at rest |
| 2 | 0 3px 6px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.10) | Top app bars, emphasized cards |
| 3 | 0 10px 20px rgba(0,0,0,0.12), 0 3px 6px rgba(0,0,0,0.10) | Floating action buttons, navigation drawers |
| 4 | 0 15px 25px rgba(0,0,0,0.12), 0 5px 10px rgba(0,0,0,0.10) | Dialogs, menus, dropdowns |

### Elevation Guidelines

- Use elevation purposefully to create hierarchy
- Increase elevation on interaction (hover, focus)
- Maintain consistent elevations for similar components
- Consider light source (top-left) for consistent shadows

## Components

Our components follow Material Design 3 (M3) specifications with customizations to maintain our brand identity.

### Buttons

Material Design buttons have specific variants for different purposes and emphasis levels.

#### Button Variants

| Variant | Description | Usage |
|---------|-------------|-------|
| Contained | Filled with Teal 600, white text | Primary actions, form submissions |
| Outlined | Teal 600 outline, teal text | Secondary actions, less emphasis |
| Text | Text only in Teal 600 | Tertiary actions, minimal emphasis |
| Elevated | White with shadow, teal text | Important actions in busy contexts |
| Tonal | Teal 100 background, Teal 800 text | Feature-specific actions |

#### Button States

- **Enabled**: Base state
- **Hovered**: Slight background lightening/darkening
- **Focused**: Shows focus ring
- **Pressed**: Darker shade, slight scale effect
- **Disabled**: 38% opacity, no interactions

### Cards

Cards contain content and actions about a single subject.

#### Card Types

| Type | Description | Usage |
|------|-------------|-------|
| Elevated | Default card with shadow | Most content grouping |
| Filled | Solid background without shadow | Secondary information |
| Outlined | Border with no shadow | Alternative styling |

#### Card Anatomy

- Container (8px rounded corners)
- Optional header (title, subtitle, avatar)
- Media area (optional)
- Content area
- Actions area (buttons aligned to end)

### Navigation

Following Material Design navigation patterns for consistent and intuitive movement.

#### Navigation Components

- **Top App Bar**: Contains title, navigation icon, and actions
- **Navigation Drawer**: Side panel for main navigation options
- **Bottom Navigation**: For mobile-focused interfaces with 3-5 destinations
- **Tabs**: For switching between related content within the same context

### Form Elements

Implementing Material Design form controls for consistent user input.

#### Text Fields

- Outlined style with 4px corners
- Clear labeling that moves above the field on focus/input
- Supporting text for hints and validation
- Error states with red color and messaging

#### Selection Controls

- Checkboxes, radio buttons, and switches with consistent styling
- Visual feedback on interaction
- Proper spacing for touch targets (min 48x48px)

## Responsive Layout

Material Design uses a responsive grid system that adapts to screen size.

### Breakpoints

- Extra small: 0-599px (mobile portrait)
- Small: 600-904px (mobile landscape, small tablet)
- Medium: 905-1239px (tablet, small desktop)
- Large: 1240-1439px (desktop)
- Extra large: 1440px+ (large desktop)

### Grid System

- 4px baseline grid for element sizing
- 8px grid for spacing
- 12-column layout that resizes at breakpoints
- Gutters that adjust at different breakpoints (8px-24px)
- Margins that adapt to screen size

## Motion

Material Design uses purposeful motion to guide users and create continuity.

### Motion Principles

- **Responsive**: Quick feedback for user interactions
- **Natural**: Physics-based animations that feel intuitive
- **Intentional**: Movement with purpose that guides attention

### Transition Patterns

- **Shared axis**: For related content (forward/backward navigation)
- **Container transform**: For expanding elements to full-screen
- **Fade through**: For content not directly related
- **Fade**: For UI elements entering/exiting

### Duration Guidelines

- Small elements: 100-150ms
- Medium elements: 200-250ms
- Large elements: 250-300ms
- Screen transitions: 300-350ms

## Implementation Phases

Our migration to Material Design will follow this phased approach:

### Phase 1: Design Token System (Current)
- Convert color system to Material Design roles
- Implement typography scale
- Define elevation and shape systems
- Create design tokens in Tailwind config

### Phase 2: Core Component Library
- Implement Material button components
- Create form elements based on Material specs
- Build card components
- Develop feedback components (snackbars, alerts)

### Phase 3: Page Layouts
- Implement responsive grid system
- Create consistent page templates
- Apply proper spacing and alignment

### Phase 4: Motion and Refinement
- Add purposeful animations and transitions
- Fine-tune interaction details
- Ensure system-wide consistency

## Version History

- **v1.0.0** (2023-05-15): Initial design system release
- **v1.1.0** (2023-07-10): Added data visualization components
- **v1.2.0** (2023-09-22): Expanded accessibility guidelines
- **v1.3.0** (2023-12-05): Added mobile patterns and responsive improvements

---

This design system is a living document that will evolve as LifeOS grows. All team members are encouraged to provide feedback and suggestions for improvement. 
