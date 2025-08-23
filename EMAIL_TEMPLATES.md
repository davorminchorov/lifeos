# LifeOS Email Templates Documentation

## Overview

This document describes the branded email template system implemented for LifeOS, designed to match the project's design system and provide a consistent, professional user experience across all email communications.

## Design System Integration

### Visual Identity
- **Typography**: Instrument Sans font family (consistent with the LifeOS design system)
- **Color Palette**: Cream/warm neutrals with Laravel red accents
- **Branding**: Consistent LifeOS logo and tagline in email headers
- **Responsive Design**: Mobile-optimized layouts with proper breakpoints
- **Dark Mode Support**: Automatic dark mode detection and styling

### Color Scheme
```css
/* Primary Colors */
Background: #F8F7F4 (warm cream)
Container: #FDFDFC (main background)
Text Primary: #1B1B18 (dark text)
Text Secondary: #706F6C (muted text)
Accent: #F53003 (Laravel red)
Borders: #E3E3E0 (warm neutral)
```

## File Structure

```
resources/views/emails/
├── layouts/
│   └── base.blade.php              # Main email layout template
├── components/
│   ├── button.blade.php            # Reusable button component
│   └── detail-list.blade.php       # Structured information display
└── notifications/
    ├── subscription-renewal-alert.blade.php
    ├── warranty-expiration-alert.blade.php
    ├── contract-expiration-alert.blade.php
    └── utility-bill-due-alert.blade.php
```

## Base Layout Features

### Header Section
- LifeOS logo with brand red color
- Consistent tagline: "Your Personal Life Management System"
- Subtle gradient background

### Content Area
- Clean typography hierarchy
- Structured information display
- Highlight boxes for important information
- Responsive detail lists

### Footer Section
- Notification preferences link
- Dashboard and homepage links
- Copyright information
- Unsubscribe information

### Responsive Design
- Mobile-optimized layouts (max-width: 600px)
- Flexible button sizes
- Stack detail items on mobile
- Appropriate padding adjustments

### Accessibility
- High contrast color ratios
- Semantic HTML structure
- Screen reader friendly
- Clear interactive states

## Email Components

### Button Component
```blade
<x-emails.components.button :url="$url" type="primary">
    Button Text
</x-emails.components.button>
```

**Props:**
- `url` (required): Target URL for the button
- `type` (optional): 'primary' or 'secondary', defaults to 'primary'

### Detail List Component
```blade
<x-emails.components.detail-list :items="$details" />
```

**Props:**
- `items` (required): Associative array of label => value pairs

## Notification Templates

### 1. Subscription Renewal Alert
**File:** `subscription-renewal-alert.blade.php`
**Triggers:** Subscription approaching renewal date
**Data Required:**
- `user`: User model instance
- `subscription`: Subscription model instance
- `daysUntilRenewal`: Integer (days remaining)
- `subject`: Email subject line

**Features:**
- Conditional messaging for same-day vs future renewals
- Auto-renewal status handling
- Cost and payment method display
- Direct link to subscription management

### 2. Warranty Expiration Alert
**File:** `warranty-expiration-alert.blade.php`
**Triggers:** Warranty approaching expiration date
**Data Required:**
- `user`: User model instance
- `warranty`: Warranty model instance
- `daysUntilExpiration`: Integer (days remaining)
- `subject`: Email subject line

**Features:**
- Urgency messaging for expiring warranties
- Complete product information display
- Actionable advice for warranty extension
- Links to warranty details

### 3. Contract Expiration Alert
**File:** `contract-expiration-alert.blade.php`
**Triggers:** Contract expiration or notice period deadlines
**Data Required:**
- `user`: User model instance
- `contract`: Contract model instance
- `daysUntilExpiration`: Integer (days remaining)
- `isNoticeAlert`: Boolean (notice period vs expiration)
- `subject`: Email subject line

**Features:**
- Dual-mode support (notice period vs expiration)
- Auto-renewal status handling
- Contract value and counterparty information
- Strategic advice for renewal decisions

### 4. Utility Bill Due Alert
**File:** `utility-bill-due-alert.blade.php`
**Triggers:** Utility bill approaching due date
**Data Required:**
- `user`: User model instance
- `bill`: UtilityBill model instance
- `daysTillDue`: Integer (days remaining)
- `subject`: Email subject line

**Features:**
- Budget alert highlighting for over-budget bills
- Auto-pay status indication
- Usage information display
- Energy-saving recommendations for high bills

## Implementation Details

### Notification Class Updates
All notification classes have been updated to use custom views instead of Laravel's default MailMessage builder:

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject($subject)
        ->view('emails.notifications.template-name', [
            'user' => $notifiable,
            'data' => $this->data,
            'subject' => $subject,
        ]);
}
```

### Email Client Compatibility
- Tested for major email clients (Gmail, Outlook, Apple Mail)
- Fallback fonts for systems without Instrument Sans
- Inline CSS for maximum compatibility
- Progressive enhancement for modern features

## Customization Guidelines

### Adding New Templates
1. Create new Blade template in `resources/views/emails/notifications/`
2. Extend the base layout: `@extends('emails.layouts.base')`
3. Use existing components for consistency
4. Update corresponding notification class to use the new view
5. Follow naming convention: kebab-case matching notification class

### Modifying Existing Templates
- Maintain consistent structure and styling
- Test changes across multiple email clients
- Ensure mobile responsiveness
- Verify accessibility standards

### Brand Customization
- Update colors in base layout CSS variables
- Replace logo and tagline in header section
- Modify footer links as needed
- Adjust typography weights and sizes

## Testing

### Preview Templates
Email templates can be previewed by creating test routes or commands that render the templates with sample data.

### Recommended Testing
- Test across multiple email clients
- Verify mobile responsiveness
- Check dark mode compatibility
- Validate accessibility with screen readers
- Test with various data scenarios (empty fields, long text, etc.)

## Future Enhancements

### Potential Additions
- Welcome/onboarding email templates
- Password reset email template
- General notification template for miscellaneous alerts
- Email preference management interface
- A/B testing capabilities for email effectiveness

### Maintenance
- Regular review of email client compatibility
- Updates to design system changes
- Performance optimization for email rendering
- User feedback integration for improvements

## Support

For questions or issues with the email template system:
1. Check this documentation first
2. Review the base layout and component files
3. Test template rendering with sample data
4. Verify notification class implementation

The email template system follows Laravel best practices and integrates seamlessly with the existing notification infrastructure while providing a branded, professional user experience.
