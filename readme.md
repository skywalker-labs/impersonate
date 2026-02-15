<div align="center">

# 🎭 Laravel Impersonate: Pro Stealth
### *Switch Users with Elite Precision and Zero-Config UI Injection*

[![Latest Version](https://img.shields.io/badge/version-1.0.0-gold.svg?style=for-the-badge)](https://packagist.org/packages/skywalker-labs/impersonate)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg?style=for-the-badge)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.4+-777bb4.svg?style=for-the-badge)](https://php.net)
[![UI Status](https://img.shields.io/badge/UI-Automatic_Injection-green.svg?style=for-the-badge)](https://github.com/skywalker-labs/impersonate)

---

**Laravel Impersonate Pro** is the ultimate developer tool for troubleshooting user issues. It allows you to step into your users' shoes without knowing their passwords, while maintaining absolute security and a seamless audit trail.

</div>

## ✨ Why Pro Impersonate?

Most impersonation packages are "dumb"—they just swap session IDs. Our **Elite Architect** approach ensures:
- 🤫 **Quiet Auth:** Impersonate without triggering "Login" events or updating `last_login_at` (Optional).
- 🍪 **Persistent Persona:** Remembers your admin state even if the impersonated user's session expires.
- 💉 **Auto-UI Injection:** A sleek, non-intrusive floating bar appears only during impersonation.
- 🛡️ **TTL Guards:** Set a time-to-live for impersonation sessions to prevent "Forgot to Leave" security leaks.

---

## 🔥 Killer Features

### 1. Multi-Guard Support
Seamlessly switch between `web`, `admin`, or custom guards without losing context.

### 2. Blade Directives for Elite DX
```blade
@impersonating
    <div class="alert alert-warning">
        You are currently viewing as <strong>{{ Auth::user()->name }}</strong>.
        <a href="{{ route('impersonate.leave') }}">Return to Admin</a>
    </div>
@endImpersonating
```

### 3. Integrated Audit Logging
Never guess who did what. Our package logs every impersonation cycle with ID, IP, and timestamps.

---

## ⚡ Performance & Security

| Feature | Industry Standard | Skywalker Elite |
| :--- | :--- | :--- |
| **Logic Conflict** | High (Session overriding) | **Low (Stealth Session Separation)** |
| **UI Setup** | Manual Views | **Zero-Config Injection** |
| **Security** | Simple Guard Check | **Multi-factor Gate Protection** |

---

## 🛠️ Elite Implementation (PHP 8.4+)

### Take Control
Start impersonation with high-level API:

```php
public function loginAs(User $user): bool 
{
    return impersonate()->take(
        from: auth()->user(), 
        to: $user,
        guardName: 'web'
    );
}
```

### Global Middleware Protection
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        \Skywalker\Impersonate\Middleware\ProtectFromImpersonation::class,
    ],
];
```

---

## 🛡️ Enterprise Privacy
- **Encrypted Session Keys:** Impersonation tokens are salted and rotated.
- **Event-Driven Hooks:** Fire internal webhooks when a support agent starts an impersonation session.
- **Auto-Cleanup:** Automatically clears impersonation data on master logout.

---

Created & Maintained by **Skywalker-Labs**. Built for Developers, Trusted by Admins.
