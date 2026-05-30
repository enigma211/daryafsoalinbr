# Complete Guide: Offline Laravel Filament System with RTL Math Rendering

This document serves as a definitive guide for building a completely offline Laravel + Filament v3 application that supports rich-text editing and mathematical formula rendering (via KaTeX) within an RTL (Right-to-Left) environment.

By following this guide, you will avoid the common pitfalls associated with bidirectional text algorithms (Bidi) and Livewire SPA navigations.

---

### 1. بک‌اند (PHP / Laravel)
*   **نسخه PHP:** نسخه `^8.2` (8.2 و بالاتر)
*   **فریم‌ورک Laravel:** نسخه `^11.10`
*   **فریم‌ورک Filament:** نسخه `3.2.*` (فریم‌ورکی برای ساخت سریع پنل‌های مدیریت و فرم‌ها با استفاده از TALL Stack)
*   **کتابخانه morilog/jalali:** نسخه `3.5.0` (برای مدیریت تاریخ‌های شمسی/جلالی)
*   **کتابخانه flowframe/laravel-trend:** نسخه `^0.4.0` (احتمالا برای تولید نمودارها و ترندها در پنل فیلامنت)

### 2. فرانت‌اند و ابزارها (JavaScript / CSS)
*   **بیلد تول Vite:** نسخه `^6.0.11` (به همراه پلاگین `laravel-vite-plugin` نسخه `^1.2.0`)
*   **فریم‌ورک Tailwind CSS:** نسخه `^3.4.13` (برای استایل‌دهی)
*   **پلاگین PostCSS:** نسخه `^8.4.47` و **Autoprefixer:** نسخه `^10.4.20` (برای پردازش کدهای CSS)
*   **کتابخانه Axios:** نسخه `^1.7.4` (برای درخواست‌های HTTP در سمت کاربر در صورت نیاز)

### 3. ابزارهای توسعه و تست (Development / Testing)
*   **Pest PHP:** نسخه `^3.8` (فریم‌ورک تست مدرن و ساده)
*   **PHPUnit:** نسخه `^11.0.1`
*   **Laravel Sail:** نسخه `^1.26` (برای محیط توسعه مبتنی بر داکر)
*   **Faker:** نسخه `^1.23` (برای تولید داده‌های جعلی/تستی)

در مجموع، این یک پروژه کاملا به‌روز بر پایه **Laravel 11** و **Filament v3** است که از **Vite 6** و **Tailwind 3** برای کارهای فرانت‌اند استفاده می‌کند.

---
## 1. Choosing the Right Editor & Math Renderer

For an offline, highly customizable, and RTL-friendly environment, the best stack is:

1. **Editor**: **CKEditor 5** (Custom implementation via NPM, not CDN).
   - *Why?* CKEditor 5 outputs clean HTML, handles RTL text natively, and can be fully bundled offline via Vite.
2. **Math Renderer**: **KaTeX** (via NPM).
   - *Why?* KaTeX is significantly faster than MathJax, renders beautifully, and its `auto-render` extension allows you to type raw LaTeX (e.g., `$ V_u = 320 $`) directly in the editor and render it on the view page. 

**Important Concept**: You do *not* need a heavy math plugin inside the editor itself. It is much more stable to let users type raw LaTeX between dollar signs `$` in the editor, and use KaTeX to render those formulas automatically on the frontend (Infolist/View pages).

---

## 2. Setting Up Completely Offline (No Internet Required)

To ensure the system works in isolated environments, **never use CDNs**. Install everything locally via Node.js (NPM).

### Step 2.1: Install Dependencies
Run the following in your Laravel project root:
```bash
npm install ckeditor5 @ckeditor/ckeditor5-paste-from-office
npm install katex
```

### Step 2.2: Configure `app.js`
In your `resources/js/app.js`, import the packages directly from your `node_modules`. This ensures Vite bundles them for offline use.

```javascript
import './bootstrap';

// 1. Import CKEditor & CSS
import { ClassicEditor, Essentials, Paragraph, Bold, Italic /* add other plugins */ } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';
import 'ckeditor5/translations/fa.js'; // Persian translation

// 2. Import KaTeX & CSS
import katex from 'katex';
import renderMathInElement from 'katex/dist/contrib/auto-render.mjs';
import 'katex/dist/katex.min.css'; // This bundles all KaTeX fonts locally!

// 3. Make them globally available
window.ClassicEditor = ClassicEditor;
window.renderMathInElement = renderMathInElement;

// 4. Define the Math Render Function
window.renderMath = () => {
    renderMathInElement(document.body, {
        delimiters: [
            {left: '$$', right: '$$', display: true},
            {left: '$', right: '$', display: false},
        ],
        // Do not render inside the editor itself while typing
        ignoredClasses: ['ck', 'ck-content', 'ck-editor__editable'], 
        throwOnError: false
    });
};

// 5. Trigger KaTeX on initial load and Livewire SPA navigations
document.addEventListener("DOMContentLoaded", window.renderMath);
document.addEventListener("livewire:navigated", window.renderMath);

// 6. Trigger KaTeX after Filament Modals / Component Updates (Livewire 3)
document.addEventListener("livewire:init", () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(() => {
                window.renderMath();
            }, 50); // Small delay to ensure DOM is updated
        });
    });
});
```

---

## 3. Fixing the RTL vs LTR Bidi Problem (The "300 mm" Issue)

### The Problem
When Filament is set to Persian, it applies `dir="rtl"` to the `<body>` or wrapper containers. According to the Unicode Bidirectional Algorithm, if a math block (like `300 mm`) is placed inside an RTL container without explicit LTR instructions, the text order flips visually (e.g., `mm 300`). 

### The Solution
You must force the KaTeX wrapper class to render as Left-To-Right (LTR), isolating it from Filament's global RTL rules.

In your `resources/css/app.css`, add:

```css
@import 'tailwindcss';

/* Your Tailwind imports... */

/* --- CRITICAL BUG FIX FOR RTL MATH --- */
.katex {
    direction: ltr !important;
    unicode-bidi: embed;
    display: inline-block;
}
```

---

## 4. Injecting Assets into Filament

Filament panels do not load your frontend `app.css` and `app.js` by default. You must explicitly inject your compiled offline Vite assets into the panel.

Open `app/Providers/AppServiceProvider.php` and add the Render Hook:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Inject Vite assets at the end of the <body> in all Filament panels
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@vite(['resources/css/app.css', 'resources/js/app.js'])")
        );
    }
}
```

---

## 5. Compiling for Production (Offline Mode)

Once everything is configured, compile your assets. This step grabs all CSS, JS, and **KaTeX Fonts** from `node_modules` and packs them into the `public/build` directory.

```bash
npm run build
```
Once `npm run build` is complete, the `public/build` folder contains everything needed. You can move the entire project to your isolated, offline server, and it will work perfectly without internet.

---

## Summary Checklist for Next Project

1. [ ] Install `ckeditor5` and `katex` via NPM.
2. [ ] Import KaTeX CSS and JS in `resources/js/app.js`.
3. [ ] Configure `window.renderMath` with `renderMathInElement`.
4. [ ] Bind `window.renderMath` to `DOMContentLoaded`, `livewire:navigated`, and `Livewire.hook('commit', ...)` for Filament Modals.
5. [ ] Add `.katex { direction: ltr !important; unicode-bidi: embed; display: inline-block; }` to `resources/css/app.css`.
6. [ ] Register the Vite render hook in `AppServiceProvider`.
7. [ ] Run `npm run build` to pull all assets offline.
