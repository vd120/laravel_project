# Multilingual System Documentation

## Overview

This document describes the complete multilingual (internationalization/localization) system implemented for the Nexus social platform.

## Features Implemented

✅ **Supported Languages:**
- English (default)
- Arabic (with RTL support)

✅ **System Capabilities:**
- Session-based language persistence
- Database storage for authenticated users
- Browser language detection
- RTL/LTR layout switching
- Production-ready architecture

---

## File Structure

```
resources/lang/
├── en/
│   ├── messages.php       # General messages
│   ├── auth.php           # Authentication texts
│   ├── navigation.php     # Navigation labels
│   ├── notifications.php  # Notification messages
│   ├── validation.php     # Validation messages
│   └── json               # JSON translations (en.json)
├── ar/
│   ├── messages.php       # Arabic general messages
│   ├── auth.php           # Arabic authentication texts
│   ├── navigation.php     # Arabic navigation labels
│   ├── notifications.php  # Arabic notification messages
│   ├── validation.php     # Arabic validation messages
│   └── json               # JSON translations (ar.json)
```

---

## Core Components

### 1. SetLocale Middleware

**Location:** `app/Http/Middleware/SetLocale.php`

**Responsibilities:**
- Reads language from session, authenticated user, or browser preferences
- Sets application locale for each request
- Configures Carbon for localized dates
- Shares locale with all views

**Priority Order:**
1. Route parameter (`/lang/{locale}`)
2. Session storage
3. Authenticated user's database preference
4. Browser's `Accept-Language` header
5. Default locale (`en`)

### 2. Language Controller

**Location:** `app/Http/Controllers/LanguageController.php`

**Methods:**
- `switch(Request $request, string $locale)` - Changes language and redirects back
- `getSupportedLocales()` - Returns array of supported languages with metadata

**Route:**
```php
GET /lang/{locale}
```

### 3. Language Switcher Component

**Location:** `resources/views/partials/language-switcher.blade.php`

**Features:**
- Dropdown menu with flags and language names
- Active language indicator
- Smooth animations
- Light/dark theme support
- RTL-aware positioning

**Usage:**
```blade
@include('partials.language-switcher')
```

### 4. User Model

**Location:** `app/Models/User.php`

**Added Field:**
```php
'language' // string, default: 'en'
```

---

## Configuration

### Middleware Registration

**Location:** `bootstrap/app.php`

```php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
]);
```

### Routes

**Location:** `routes/web.php`

```php
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
```

### Layout Updates

**Location:** `resources/views/layouts/app.blade.php`

```html
<html lang="{{ app()->getLocale() }}" 
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
```

---

## Usage Guide

### In Blade Templates

#### Using Translation Keys
```blade
{{ __('messages.welcome') }}
{{ __('auth.sign_in') }}
{{ __('navigation.home') }}
```

#### Using @lang Directive
```blade
@lang('messages.welcome')
@lang('auth.sign_in')
```

#### Using JSON Translations
```blade
{{ __('Edit Profile') }}
{{ __('Delete Account') }}
```

#### With Parameters
```blade
{{ __('messages.copyright', ['year' => date('Y')]) }}
{{ __('notifications.new_follower', ['user' => $user->name]) }}
```

### In Controllers
```php
// Get current locale
$locale = app()->getLocale();

// Set locale programmatically
app()->setLocale('ar');

// Translate
$message = __('messages.success');
```

### In JavaScript (via data attributes)
```html
<div data-translate="welcome">{{ __('messages.welcome') }}</div>
```

---

## RTL Support

### CSS Classes

The system automatically applies RTL styles when Arabic is active:

```css
html[lang="ar"] {
    direction: rtl;
    font-family: 'Cairo', 'Inter', sans-serif;
}

html[lang="ar"] .header,
html[lang="ar"] nav,
html[lang="ar"] .nav-links {
    direction: rtl;
    text-align: right;
}
```

### Fonts

- **English:** Inter font family
- **Arabic:** Cairo font family (with Inter fallback)

---

## Adding New Languages

### Step 1: Create Translation Files

Create new directory in `resources/lang/`:

```
resources/lang/fr/
├── messages.php
├── auth.php
├── navigation.php
├── notifications.php
├── validation.php
└── json
```

### Step 2: Update Supported Locales

In `LanguageController.php`:

```php
const SUPPORTED_LOCALES = ['en', 'ar', 'fr'];

public static function getSupportedLocales(): array
{
    return [
        // ... existing locales
        'fr' => [
            'name' => 'French',
            'native_name' => 'Français',
            'direction' => 'ltr',
            'flag' => '🇫🇷',
        ],
    ];
}
```

### Step 3: Add RTL Support (if needed)

If the new language is RTL (like Hebrew, Persian), add CSS rules:

```css
html[lang="fa"] {
    direction: rtl;
}
```

---

## Best Practices

### 1. Never Hardcode Text

❌ **Wrong:**
```blade
<h1>Welcome to Nexus</h1>
```

✅ **Correct:**
```blade
<h1>{{ __('messages.welcome_to_nexus') }}</h1>
```

### 2. Use Descriptive Keys

❌ **Wrong:**
```php
'welcome' => 'Welcome'
```

✅ **Correct:**
```php
'homepage.welcome' => 'Welcome'
'auth.welcome' => 'Welcome back'
```

### 3. Handle Plurals

```php
// In translation file
'comments' => ':count comment|:count comments',

// In Blade
{{ __('messages.comments', ['count' => $commentCount]) }}
```

### 4. Use Parameters for Dynamic Content

```php
// In translation file
'greeting' => 'Hello, :name!',

// In Blade
{{ __('messages.greeting', ['name' => auth()->user()->name]) }}
```

---

## Testing

### Test Language Switching

1. Visit any page
2. Click language switcher in navbar
3. Select Arabic
4. Verify:
   - Text is translated
   - Layout is RTL
   - Font changed to Cairo
   - URL preserved

### Test Persistence

1. Switch to Arabic
2. Refresh page
3. Verify Arabic persists

### Test User Preference

1. Login as user
2. Switch language
3. Check database: `SELECT language FROM users WHERE id = ?`
4. Logout and login again
5. Verify language persists

---

## Performance Considerations

1. **Translation Caching:**
   ```bash
   php artisan config:cache
   php artisan view:cache
   ```

2. **Locale Cookie:** Session-based (no additional DB queries for guests)

3. **User Preference:** Single column in users table (indexed)

4. **Font Loading:** Google Fonts with preconnect hints

---

## Troubleshooting

### Issue: Language not changing

**Solution:**
1. Clear caches: `php artisan cache:clear`
2. Check session is working
3. Verify middleware is registered

### Issue: RTL not applying

**Solution:**
1. Check `app()->getLocale()` returns 'ar'
2. Verify `dir="rtl"` in HTML tag
3. Check CSS is loaded

### Issue: Translations not showing

**Solution:**
1. Verify translation file exists
2. Check key spelling
3. Clear view cache: `php artisan view:clear`

---

## API Reference

### LanguageController Methods

```php
// Switch language
GET /lang/{locale}

// Get supported locales (static)
LanguageController::getSupportedLocales()
// Returns: ['en' => [...], 'ar' => [...]]
```

### Helper Functions

```php
// Get current locale
app()->getLocale()

// Check if locale is Arabic
app()->getLocale() === 'ar'

// Get direction
app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
```

---

## Security Notes

1. **Locale Validation:** Only supported locales are accepted (400 error for invalid)
2. **SQL Injection:** Locale is validated against whitelist before use
3. **XSS:** All translations are escaped by Blade's `{{ }}` syntax

---

## Future Enhancements

- [ ] User language preference UI in settings
- [ ] Automatic content translation
- [ ] Language-specific email templates
- [ ] Right-to-left form validation styling
- [ ] Language switcher keyboard shortcut
- [ ] Translation management interface

---

## Credits

- Laravel Localization Documentation
- Cairo Font (Google Fonts)
- Inter Font (Google Fonts)

---

**Last Updated:** March 8, 2026
**Version:** 1.0.0
