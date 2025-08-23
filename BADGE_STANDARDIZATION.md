# Badge Standardization Analysis

## Current Badge Inconsistencies Found

### 1. Subscriptions Index (`subscriptions/index.blade.php`)
**Status Badges:**
- Padding: `px-2 py-1`
- Text: `text-xs font-semibold`
- Colors: `-600` variants (e.g., `text-[color:var(--color-success-600)]`)
- Structure: `inline-flex px-2 py-1 text-xs font-semibold rounded-full`

**Category Badges:**
- Same styling as status badges
- Uses info color scheme

### 2. Contracts Index (`contracts/index.blade.php`)
**Status Badges:**
- Padding: `px-2.5 py-0.5` (different padding)
- Text: `text-xs font-medium` (medium vs semibold)
- Colors: `-800` variants (e.g., `text-[color:var(--color-success-800)]`)
- Structure: `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium`

**Type Badges:**
- Uses `px-2 py-1` (inconsistent with status badges in same file)
- Uses `-600` colors like subscriptions

### 3. Warranties Index (`warranties/index.blade.php`)
**Status Badges:**
- Padding: `px-2 py-1` (matches subscriptions)
- Text: `text-xs font-semibold` (matches subscriptions)
- Colors: `-600` variants (matches subscriptions)
- Structure: `inline-flex px-2 py-1 text-xs font-semibold rounded-full`

**Brand Badges:**
- Same styling as status badges

## Standardized Badge Design

Based on the design system and most common patterns, here's the proposed standard:

### Standard Badge Classes
```html
<span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-{type}-50)] text-[color:var(--color-{type}-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-{type}-500)]">
    Badge Text
</span>
```

### Color Mappings
- **Success/Active**: `--color-success-50` bg, `--color-success-600` text
- **Warning/Paused/Pending**: `--color-warning-50` bg, `--color-warning-600` text  
- **Danger/Cancelled/Expired**: `--color-danger-50` bg, `--color-danger-600` text
- **Info/Categories/Types**: `--color-info-50` bg, `--color-info-600` text
- **Accent/Special**: `--color-accent-50` bg, `--color-accent-600` text
- **Default/Other**: `--color-primary-200` bg, `--color-primary-700` text

### Dark Mode
All badges use: `dark:bg-[color:var(--color-dark-300)]` with appropriate text color

## Files to Update
1. `/resources/views/subscriptions/index.blade.php` - Update contracts-style badges
2. `/resources/views/contracts/index.blade.php` - Standardize padding and colors  
3. `/resources/views/warranties/index.blade.php` - Already mostly consistent
4. Check other index files for badge usage

## Implementation Notes
- Use `px-2 py-1` padding consistently
- Use `text-xs font-semibold` consistently
- Use `-600` color variants for light mode text
- Use `-500` color variants for dark mode text
- Include `items-center` for proper alignment
- Maintain `rounded-full` for pill shape
