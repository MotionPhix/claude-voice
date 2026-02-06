# Settings Architecture

## Overview

The settings system has been redesigned to provide a unified, industry-grade user experience inspired by modern SaaS applications like Drongo. All settings are now accessible from a single entry point with clear categorization.

## Structure

```
/settings
├── /account (User Settings)
│   ├── /profile          - Name, email, avatar, account deletion
│   ├── /security         - Password, 2FA, active sessions
│   ├── /notifications    - Email & system notification preferences
│   └── /appearance       - Theme, UI preferences
│
├── /organization (Organization Settings)
│   ├── /general          - Organization info, logo, details
│   ├── /team             - Members, invitations, roles
│   ├── /branding         - Colors, invoice templates, email templates
│   ├── /integrations     - PayChangu, APIs, webhooks
│   └── /currencies       - Currency management
│
└── /billing (Billing Settings)
    ├── /subscription     - Plan, usage, billing cycle
    ├── /payment-methods  - Cards, PayChangu integration
    └── /invoices         - Billing history, receipts
```

## Components Created

### Layout Components

1. **SettingsLayout.vue** (`resources/js/layouts/SettingsLayout.vue`)
   - Main layout with sidebar navigation
   - Three-section organization (Account, Organization, Billing)
   - Active state highlighting
   - Dark mode support

2. **SettingsSection.vue** (`resources/js/components/settings/SettingsSection.vue`)
   - Reusable card-based section component
   - Header with title, description, and optional actions
   - Footer slot for buttons
   - Uses custom Card component

### Account Settings Pages

1. **Profile** (`resources/js/pages/settings/account/Profile.vue`)
   - ✅ Profile photo upload
   - ✅ Personal information (name, email)
   - ✅ Account deletion with confirmation modal
   - Uses: ModalRoot, ModalHeader, ModalScrollable, ModalFooter

2. **Security** (`resources/js/pages/settings/account/Security.vue`)
   - ✅ Password change
   - ✅ 2FA placeholder (coming soon)
   - ✅ Active sessions management

3. **Notifications** (`resources/js/pages/settings/account/Notifications.vue`)
   - ✅ Email notification preferences
   - ✅ Browser notification toggle
   - ✅ Granular notification controls

4. **Appearance** (`resources/js/pages/settings/Appearance.vue`)
   - ✅ Updated to use new SettingsLayout
   - ✅ Theme switching with AppearanceTabs

### Custom Components Used

All cards and modals use components from `resources/js/components/custom/`:
- ✅ **Card.vue** - Versatile card component with variants
- ✅ **ModalRoot.vue** - Modal container
- ✅ **ModalHeader.vue** - Modal header with icon and close button
- ✅ **ModalScrollable.vue** - Scrollable modal content area
- ✅ **ModalFooter.vue** - Modal footer with action buttons

## Key Features

### 1. Unified Navigation
- Single settings entry point
- Clear categorization by scope (Account → Organization → Billing)
- Sidebar navigation with active states
- Responsive design

### 2. Consistent UX
- All pages use SettingsLayout
- Consistent section headers and descriptions
- Uniform button placement and actions
- Success/error messaging

### 3. Modern UI Patterns
- Card-based sections
- Beautiful modals with custom components
- Icon-based navigation
- Dark mode support throughout

### 4. Industry-Grade Design
Inspired by Drongo and other modern SaaS apps:
- Clean, minimal interface
- Clear visual hierarchy
- Intuitive categorization
- Professional polish

## What's Remaining

### Organization Settings (TODO)
- [ ] General - Organization info, logo
- [ ] Team - Member management (already exists, needs migration)
- [ ] Branding - Invoice/email templates, colors
- [ ] Integrations - PayChangu, API keys
- [ ] Currencies - Move from settings/currencies (already exists)

### Billing Settings (TODO)
- [ ] Subscription - Plan selection, usage tracking
- [ ] Payment Methods - PayChangu integration, saved cards
- [ ] Invoices - Billing history

### Routes (TODO)
Update `routes/settings.php` to include:
```php
// Account settings
Route::get('settings/account/profile', ...)->name('settings.account.profile');
Route::patch('settings/account/profile', ...)->name('settings.account.profile.update');
Route::delete('settings/account/profile', ...)->name('settings.account.profile.destroy');

Route::get('settings/account/security', ...)->name('settings.account.security');
Route::put('settings/account/security', ...)->name('settings.account.security.update');

Route::get('settings/account/notifications', ...)->name('settings.account.notifications');
Route::put('settings/account/notifications', ...)->name('settings.account.notifications.update');

Route::get('settings/account/appearance', ...)->name('settings.account.appearance');

// Organization settings
Route::get('settings/organization/general', ...)->name('settings.organization.general');
Route::get('settings/organization/team', ...)->name('settings.organization.team');
Route::get('settings/organization/branding', ...)->name('settings.organization.branding');
Route::get('settings/organization/integrations', ...)->name('settings.organization.integrations');
Route::get('settings/organization/currencies', ...)->name('settings.organization.currencies');

// Billing settings
Route::get('settings/billing/subscription', ...)->name('settings.billing.subscription');
Route::get('settings/billing/payment-methods', ...)->name('settings.billing.payment-methods');
Route::get('settings/billing/invoices', ...)->name('settings.billing.invoices');
```

### Navigation Update (TODO)
Update main navigation to:
- Remove redundant settings links
- Add single "Settings" link to `/settings`
- Update user dropdown if needed

## Benefits

1. **Better UX** - Single entry point, clear organization
2. **Scalability** - Easy to add new settings sections
3. **Consistency** - Reusable components, uniform patterns
4. **Professional** - Matches industry-standard designs
5. **Maintainable** - Clear structure, well-organized code

## Migration Notes

### Old Structure → New Structure

| Old Path | New Path |
|----------|----------|
| `/settings/profile` | `/settings/account/profile` |
| `/settings/password` | `/settings/account/security` |
| `/settings/appearance` | `/settings/account/appearance` |
| `/settings/currencies` | `/settings/organization/currencies` |
| `/organizations/{org}/settings` | `/settings/organization/general` |
| (new) | `/settings/account/notifications` |
| (new) | `/settings/billing/*` |

### Breaking Changes
- All settings routes have changed
- Update any hardcoded links in the application
- Update navigation components
- Redirect old URLs to new structure

## Next Steps

1. Create organization settings pages
2. Create billing settings pages
3. Update routes in `routes/settings.php`
4. Update navigation components
5. Add redirects for old URLs
6. Test all flows
7. Update documentation
