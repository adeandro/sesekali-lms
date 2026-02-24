# SIDEBAR NAVIGATION FEATURE

## Overview
Sidebar navigasi baru telah ditambahkan ke aplikasi SesekaliCBT untuk memberikan akses yang mudah dan intuitif ke semua fitur. Sidebar dirancang responsif dan dapat beradaptasi dengan semua ukuran layar.

## Features

### 1. **Responsive Design**
- Desktop (≥1024px): Sidebar tetap tampil di samping kiri
- Mobile/Tablet: Sidebar dapat dibuka/ditutup dengan toggle button
- Auto-collapse saat screen kecil
- Smooth transitions dan animations

### 2. **User Profile Card**
- Menampilkan nama pengguna
- Menampilkan role dengan badge warna-warni:
  - 🔴 Superadmin (Red)
  - 🔵 Admin (Blue)
  - 🟢 Student (Green)

### 3. **Navigation Menu**
Menu navigasi terbagi menjadi beberapa bagian:

#### Dashboard Section
- Dashboard (Icon: th-large)

#### Management Section (Admin & Superadmin Only)
- Students Management (Icon: users)
- Subjects Management (Icon: book)
- Questions Management (Icon: question-circle)
- Exams Management (Icon: file-alt)

#### Account Section
- Settings (Icon: cog)
- Logout (Icon: sign-out-alt)

### 4. **Active Page Indicator**
- Menu item aktif ditandai dengan warna biru
- Border kiri berwarna biru untuk identifikasi visual
- Chevron indicator di menu item aktif

### 5. **Interactive Features**
- Click pada menu item otomatis menutup sidebar di mobile
- Overlay click untuk menutup sidebar
- Smooth animation saat buka/tutup
- Keyboard-friendly navigation

## Files Modified

```
resources/views/layouts/app.blade.php       - Main layout dengan sidebar
resources/views/dashboard/admin.blade.php   - Added page-title
resources/views/dashboard/superadmin.blade.php - Added page-title
resources/views/dashboard/student.blade.php - Added page-title
resources/views/admin/students/index.blade.php - Added page-title
resources/views/admin/subjects/index.blade.php - Added page-title
resources/views/admin/questions/index.blade.php - Added page-title
resources/views/admin/exams/index.blade.php - Added page-title
```

## HTML Structure

### Mobile Toggle Button
```html
<button id="toggleSidebarBtn" class="lg:hidden">
    <i class="fas fa-bars"></i>  <!-- Hamburger menu icon -->
</button>
```

### Sidebar Container
```html
<aside id="sidebar" class="sidebar-transition sidebar-hidden lg:translate-x-0">
    <!-- User Profile Card -->
    <!-- Navigation Menu -->
</aside>
```

### Mobile Overlay
```html
<div id="sidebarOverlay" class="sidebar-mobile-overlay lg:hidden"></div>
```

## JavaScript Functionality

### Toggle Sidebar
- `toggleSidebarBtn`: Membuka sidebar di mobile
- `closeSidebarBtn`: Menutup sidebar dari tombol X di sidebar header
- `sidebarOverlay`: Menutup sidebar saat overlay diklik

### Auto-collapse
- Sidebar otomatis muncul saat resize ke desktop (min-width: 1024px)
- Sidebar otomatis menutup saat klik menu item di mobile

## CSS Classes

### Sidebar States
- `.sidebar-hidden`: Sidebar tersembunyi (mobile)
- `.sidebar-transition`: Smooth transition animation
- `.sidebar-mobile-overlay.active`: Overlay overlay aktif

### Menu Items
- `.menu-item-active`: Style untuk menu item yang aktif
- `.submenu-item`: Style untuk submenu items
- `.submenu-item-active`: Style untuk submenu aktif

## Icons Used (Font Awesome 6)
- fa-th-large: Dashboard
- fa-users: Students
- fa-book: Subjects
- fa-question-circle: Questions
- fa-file-alt: Exams
- fa-cog: Settings
- fa-sign-out-alt: Logout
- fa-shield-alt: Role badge
- fa-bars: Mobile menu toggle
- fa-times: Close button
- fa-chevron-right: Active indicator

## Responsive Breakpoints

```tailwind
- Mobile (< 768px): Full responsive, sidebar hidden by default
- Tablet (768px - 1023px): Sidebar toggleable
- Desktop (≥ 1024px): Sidebar always visible, lg:static
```

## Usage

### Adding New Menu Items

To add a new menu item in the sidebar, edit `resources/views/layouts/app.blade.php`:

```blade
<a href="{{ route('your.route') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs('your.route.*') ? 'menu-item-active' : '' }}">
    <i class="fas fa-icon-name w-5 text-lg mr-3"></i>
    <span class="font-medium">Menu Label</span>
    @if(request()->routeIs('your.route.*'))
        <i class="fas fa-chevron-right ml-auto"></i>
    @endif
</a>
```

### Conditional Menu Based on Role

```blade
@if(in_array(Auth::user()->role, ['admin', 'superadmin']))
    <!-- Menu items for admin/superadmin -->
@endif
```

## Features Coming Soon

- [ ] Submenu items dengan nested navigation
- [ ] Search functionality di sidebar
- [ ] Recent pages section
- [ ] Saved bookmarks/favorites
- [ ] Theme toggle (light/dark mode)

## Browser Compatibility

- Chrome/Chromium ✓
- Firefox ✓
- Safari ✓
- Edge ✓
- Mobile browsers ✓

## Performance

- No external libraries required (using Font Awesome CDN only)
- Lightweight CSS animations
- Minimal JavaScript overhead
- Responsive CSS using Tailwind utilities

---
Created: 2026-02-14
Last Updated: 2026-02-14
