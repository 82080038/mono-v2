# Bootstrap 5 Complete Guide

## 🎯 Overview

Bootstrap adalah framework CSS front-end yang paling populer untuk membuat website yang responsif dan mobile-first. Bootstrap 5 adalah versi terbaru dengan banyak perbaikan dan fitur baru.

## 🚀 Quick Start

### **CDN Implementation**
```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" 
          crossorigin="anonymous">
</head>
<body>
    <h1>Hello, world!</h1>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" 
            crossorigin="anonymous"></script>
</body>
</html>
```

### **Separate JS Files**
```html
<!-- Popper.js (required for dropdowns, popovers, tooltips) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" 
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" 
        crossorigin="anonymous"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" 
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" 
        crossorigin="anonymous"></script>
```

## 📱 Responsive Design

### **Viewport Meta Tag**
```html
<meta name="viewport" content="width=device-width, initial-scale=1">
```

### **Breakpoint System**
| Breakpoint | Class infix | Dimensions |
|------------|------------|------------|
| X-Small | None | <576px |
| Small | `sm` | ≥576px |
| Medium | `md` | ≥768px |
| Large | `lg` | ≥992px |
| Extra large | `xl` | ≥1200px |
| XX large | `xxl` | ≥1400px |

### **Grid System**
```html
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-lg-3">
            <!-- Column content -->
        </div>
        <div class="col-sm-6 col-md-8 col-lg-9">
            <!-- Column content -->
        </div>
    </div>
</div>
```

## 🎨 Bootstrap Icons

### **Installation**

#### **CDN**
```html
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- CSS Import -->
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css");
```

#### **NPM**
```bash
npm install bootstrap-icons
```

#### **Composer**
```bash
composer require twbs/bootstrap-icons
```

#### **Download**
Download from [GitHub Releases](https://github.com/twbs/icons/releases/latest)

### **Usage Methods**

#### **Icon Font (Recommended)**
```html
<!-- Basic usage -->
<i class="bi bi-alarm"></i>
<i class="bi bi-heart-fill"></i>
<i class="bi bi-person"></i>

<!-- With custom size and color -->
<i class="bi bi-alarm" style="font-size: 2rem; color: cornflowerblue;"></i>

<!-- In buttons -->
<button class="btn btn-primary">
    <i class="bi bi-search"></i> Search
</button>

<!-- In alerts -->
<div class="alert alert-primary d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>Information message</div>
</div>
```

#### **SVG Embedded**
```html
<!-- Direct SVG embedding -->
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
     class="bi bi-emoji-heart-eyes" viewBox="0 0 16 16">
    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
    <path d="M11.315 10.014a.5.5 0 0 1 .548.736A4.498 4.498 0 0 1 7.965 13a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .548-.736h.005l.017.005.067.015.252.055c.215.046.515.108.857.169.693.124 1.522.242 2.152.242.63 0 1.46-.118 2.152-.242a26.58 26.58 0 0 0 1.109-.224l.067-.015.017-.004.005-.002zM4.756 4.566c.763-1.424 4.02-.12.952 3.434-4.496-1.596-2.35-4.298-.952-3.434zm6.488 0c1.398-.864 3.544 1.838-.952 3.434-3.067-3.554.19-4.858.952-3.434z"/>
</svg>
```

#### **SVG Sprite**
```html
<!-- Using SVG sprite -->
<svg class="bi" width="32" height="32" fill="currentColor">
    <use xlink:href="bootstrap-icons.svg#heart-fill"/>
</svg>
<svg class="bi" width="32" height="32" fill="currentColor">
    <use xlink:href="bootstrap-icons.svg#toggles"/>
</svg>
```

#### **External Image**
```html
<!-- As external image -->
<img src="/assets/icons/bootstrap.svg" alt="Bootstrap" width="32" height="32">
```

### **Common Icons**

#### **Navigation & UI Icons**
```html
<!-- Navigation -->
<i class="bi bi-house"></i> <!-- Home -->
<i class="bi bi-person"></i> <!-- User/Person -->
<i class="bi bi-gear"></i> <!-- Settings -->
<i class="bi bi-search"></i> <!-- Search -->
<i class="bi bi-menu-button"></i> <!-- Menu -->
<i class="bi bi-x"></i> <!-- Close -->
<i class="bi bi-chevron-left"></i> <!-- Left arrow -->
<i class="bi bi-chevron-right"></i> <!-- Right arrow -->
<i class="bi bi-chevron-up"></i> <!-- Up arrow -->
<i class="bi bi-chevron-down"></i> <!-- Down arrow -->

<!-- UI Controls -->
<i class="bi bi-plus"></i> <!-- Add -->
<i class="bi bi-dash"></i> <!-- Remove -->
<i class="bi bi-pencil"></i> <!-- Edit -->
<i class="bi bi-trash"></i> <!-- Delete -->
<i class="bi bi-eye"></i> <!-- View -->
<i class="bi bi-eye-slash"></i> <!-- Hide -->
<i class="bi bi-download"></i> <!-- Download -->
<i class="bi bi-upload"></i> <!-- Upload -->
<i class="bi bi-printer"></i> <!-- Print -->
```

#### **Status & Alert Icons**
```html
<!-- Success -->
<i class="bi bi-check-circle"></i>
<i class="bi bi-check-circle-fill"></i>
<i class="bi bi-check-lg"></i>

<!-- Warning -->
<i class="bi bi-exclamation-triangle"></i>
<i class="bi bi-exclamation-triangle-fill"></i>

<!-- Error/Danger -->
<i class="bi bi-x-circle"></i>
<i class="bi bi-x-circle-fill"></i>

<!-- Info -->
<i class="bi bi-info-circle"></i>
<i class="bi bi-info-circle-fill"></i>

<!-- Loading -->
<i class="bi bi-arrow-clockwise"></i>
<i class="bi bi-hourglass-split"></i>
```

#### **Financial Icons (KSP Relevant)**
```html
<!-- Money & Finance -->
<i class="bi bi-cash"></i>
<i class="bi bi-cash-stack"></i>
<i class="bi bi-wallet2"></i>
<i class="bi bi-credit-card"></i>
<i class="bi bi-bank"></i>
<i class="bi bi-piggy-bank"></i>

<!-- Business -->
<i class="bi bi-building"></i>
<i class="bi bi-shop"></i>
<i class="bi bi-briefcase"></i>
<i class="bi bi-graph-up"></i>
<i class="bi bi-graph-down"></i>
<i class="bi bi-bar-chart"></i>

<!-- Documents -->
<i class="bi bi-file-text"></i>
<i class="bi bi-file-earmark-text"></i>
<i class="bi bi-file-earmark-pdf"></i>
<i class="bi bi-clipboard"></i>
<i class="bi bi-receipt"></i>
```

### **Styling Icons**

#### **Size Control**
```html
<!-- Using font-size -->
<i class="bi bi-alarm" style="font-size: 1rem;"></i> <!-- 16px -->
<i class="bi bi-alarm" style="font-size: 1.5rem;"></i> <!-- 24px -->
<i class="bi bi-alarm" style="font-size: 2rem;"></i> <!-- 32px -->
<i class="bi bi-alarm" style="font-size: 3rem;"></i> <!-- 48px -->

<!-- Using Bootstrap text utilities -->
<i class="bi bi-alarm fs-1"></i> <!-- 2.5rem -->
<i class="bi bi-alarm fs-2"></i> <!-- 2rem -->
<i class="bi bi-alarm fs-3"></i> <!-- 1.75rem -->
<i class="bi bi-alarm fs-4"></i> <!-- 1.5rem -->
<i class="bi bi-alarm fs-5"></i> <!-- 1.25rem -->
<i class="bi bi-alarm fs-6"></i> <!-- 1rem -->
```

#### **Color Control**
```html
<!-- Using text color utilities -->
<i class="bi bi-heart text-primary"></i>
<i class="bi bi-heart text-secondary"></i>
<i class="bi bi-heart text-success"></i>
<i class="bi bi-heart text-danger"></i>
<i class="bi bi-heart text-warning"></i>
<i class="bi bi-heart text-info"></i>
<i class="bi bi-heart text-light"></i>
<i class="bi bi-heart text-dark"></i>

<!-- Using custom CSS -->
<i class="bi bi-heart" style="color: #ff6b6b;"></i>
<i class="bi bi-heart" style="color: #4ecdc4;"></i>
```

#### **Animation**
```html
<!-- Spinning animation -->
<i class="bi bi-arrow-clockwise spin"></i>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<!-- Pulse animation -->
<i class="bi bi-heart pulse"></i>

<style>
.pulse {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>
```

### **Accessibility**

#### **Decorative Icons**
```html
<!-- Purely decorative - hide from screen readers -->
<i class="bi bi-heart" aria-hidden="true"></i>
```

#### **Meaningful Icons**
```html
<!-- With alternative text -->
<img src="icons/warning.svg" alt="Warning: Important information" width="16" height="16">

<!-- With aria-label on container -->
<button aria-label="Delete item">
    <i class="bi bi-trash" aria-hidden="true"></i>
</button>

<!-- With role and aria-label for SVG -->
<svg class="bi" role="img" aria-label="Information">
    <use xlink:href="bootstrap-icons.svg#info-circle"/>
</svg>
```

### **KSP Lam Gabe Jaya Icon Examples**

#### **Dashboard Icons**
```html
<!-- Navigation menu -->
<nav class="nav flex-column">
    <a class="nav-link active" href="#">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a class="nav-link" href="#">
        <i class="bi bi-people me-2"></i> Nasabah
    </a>
    <a class="nav-link" href="#">
        <i class="bi bi-cash-stack me-2"></i> Simpanan
    </a>
    <a class="nav-link" href="#">
        <i class="bi bi-credit-card me-2"></i> Pinjaman
    </a>
    <a class="nav-link" href="#">
        <i class="bi bi-graph-up me-2"></i> Laporan
    </a>
    <a class="nav-link" href="#">
        <i class="bi bi-gear me-2"></i> Pengaturan
    </a>
</nav>
```

#### **Status Indicators**
```html
<!-- User status -->
<span class="badge bg-success">
    <i class="bi bi-circle-fill me-1"></i> Aktif
</span>
<span class="badge bg-danger">
    <i class="bi bi-circle-fill me-1"></i> Tidak Aktif
</span>
<span class="badge bg-warning">
    <i class="bi bi-circle-fill me-1"></i> Pending
</span>

<!-- Transaction status -->
<div class="d-flex align-items-center">
    <i class="bi bi-check-circle-fill text-success me-2"></i>
    <span>Transaksi Berhasil</span>
</div>
<div class="d-flex align-items-center">
    <i class="bi bi-x-circle-fill text-danger me-2"></i>
    <span>Transaksi Gagal</span>
</div>
<div class="d-flex align-items-center">
    <i class="bi bi-clock-fill text-warning me-2"></i>
    <span>Menunggu Persetujuan</span>
</div>
```

#### **Action Buttons**
```html
<!-- CRUD actions -->
<button class="btn btn-sm btn-outline-primary">
    <i class="bi bi-eye"></i> Lihat
</button>
<button class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-pencil"></i> Edit
</button>
<button class="btn btn-sm btn-outline-danger">
    <i class="bi bi-trash"></i> Hapus
</button>

<!-- Financial actions -->
<button class="btn btn-success">
    <i class="bi bi-plus-circle me-2"></i> Tambah Simpanan
</button>
<button class="btn btn-primary">
    <i class="bi bi-cash me-2"></i> Ajukan Pinjaman
</button>
<button class="btn btn-info">
    <i class="bi bi-download me-2"></i> Download Laporan
</button>
```

#### **Form Icons**
```html
<!-- Form inputs with icons -->
<div class="input-group">
    <span class="input-group-text">
        <i class="bi bi-person"></i>
    </span>
    <input type="text" class="form-control" placeholder="Nama Lengkap">
</div>

<div class="input-group">
    <span class="input-group-text">
        <i class="bi bi-envelope"></i>
    </span>
    <input type="email" class="form-control" placeholder="Email">
</div>

<div class="input-group">
    <span class="input-group-text">
        <i class="bi bi-telephone"></i>
    </span>
    <input type="tel" class="form-control" placeholder="Telepon">
</div>

<div class="input-group">
    <span class="input-group-text">
        <i class="bi bi-lock"></i>
    </span>
    <input type="password" class="form-control" placeholder="Password">
</div>
```

#### **Alert Messages with Icons**
```html
<!-- Success message -->
<div class="alert alert-success d-flex align-items-center" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <div>
        <strong>Berhasil!</strong> Data nasabah telah disimpan.
    </div>
</div>

<!-- Error message -->
<div class="alert alert-danger d-flex align-items-center" role="alert">
    <i class="bi bi-x-circle-fill me-2"></i>
    <div>
        <strong>Error!</strong> Terjadi kesalahan saat menyimpan data.
    </div>
</div>

<!-- Warning message -->
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div>
        <strong>Perhatian!</strong> Pastikan semua field terisi dengan benar.
    </div>
</div>

<!-- Info message -->
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>
        <strong>Informasi:</strong> Sistem akan melakukan backup otomatis.
    </div>
</div>
```

### **Icon Customization**

#### **CSS Variables**
```css
:root {
    --bs-icon-size: 1rem;
    --bs-icon-color: inherit;
}

.bi {
    font-size: var(--bs-icon-size);
    color: var(--bs-icon-color);
}
```

#### **Custom Icon Classes**
```css
.icon-large {
    font-size: 2rem;
}

.icon-small {
    font-size: 0.75rem;
}

.icon-primary {
    color: var(--bs-primary);
}

.icon-success {
    color: var(--bs-success);
}

.icon-danger {
    color: var(--bs-danger);
}
```

### **Performance Tips**

#### **Optimize Loading**
```html
<!-- Preload icon font -->
<link rel="preload" href="fonts/bootstrap-icons.woff2" as="font" type="font/woff2" crossorigin>

<!-- Use inline SVG for critical icons -->
<svg width="16" height="16" fill="currentColor">
    <use xlink:href="#icon-home"/>
</svg>
```

#### **Reduce HTTP Requests**
```html
<!-- Use SVG sprite instead of individual files -->
<svg style="display: none;">
    <symbol id="icon-home" viewBox="0 0 16 16">
        <!-- SVG path -->
    </symbol>
    <symbol id="icon-user" viewBox="0 0 16 16">
        <!-- SVG path -->
    </symbol>
</svg>
```

---

## 🎨 Components

### **Alerts**
```html
<!-- Basic alerts -->
<div class="alert alert-primary" role="alert">
    A simple primary alert—check it out!
</div>
<div class="alert alert-secondary" role="alert">
    A simple secondary alert—check it out!
</div>
<div class="alert alert-success" role="alert">
    A simple success alert—check it out!
</div>
<div class="alert alert-danger" role="alert">
    A simple danger alert—check it out!
</div>
<div class="alert alert-warning" role="alert">
    A simple warning alert—check it out!
</div>
<div class="alert alert-info" role="alert">
    A simple info alert—check it out!
</div>
<div class="alert alert-light" role="alert">
    A simple light alert—check it out!
</div>
<div class="alert alert-dark" role="alert">
    A simple dark alert—check it out!
</div>

<!-- Dismissible alerts -->
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong>Holy guacamole!</strong> You should check in on some of those fields below.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Alerts with icons -->
<div class="alert alert-primary d-flex align-items-center" role="alert">
    <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Info:">
        <use xlink:href="#info-fill"/>
    </svg>
    <div>An example alert with an icon</div>
</div>
```

### **Buttons**
```html
<!-- Basic buttons -->
<button type="button" class="btn btn-primary">Primary</button>
<button type="button" class="btn btn-secondary">Secondary</button>
<button type="button" class="btn btn-success">Success</button>
<button type="button" class="btn btn-danger">Danger</button>
<button type="button" class="btn btn-warning">Warning</button>
<button type="button" class="btn btn-info">Info</button>
<button type="button" class="btn btn-light">Light</button>
<button type="button" class="btn btn-dark">Dark</button>

<!-- Outline buttons -->
<button type="button" class="btn btn-outline-primary">Primary</button>
<button type="button" class="btn btn-outline-secondary">Secondary</button>
<button type="button" class="btn btn-outline-success">Success</button>
<button type="button" class="btn btn-outline-danger">Danger</button>
<button type="button" class="btn btn-outline-warning">Warning</button>
<button type="button" class="btn btn-outline-info">Info</button>
<button type="button" class="btn btn-outline-light">Light</button>
<button type="button" class="btn btn-outline-dark">Dark</button>

<!-- Button sizes -->
<button type="button" class="btn btn-primary btn-lg">Large button</button>
<button type="button" class="btn btn-primary">Default button</button>
<button type="button" class="btn btn-primary btn-sm">Small button</button>

<!-- Button groups -->
<div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-primary">Left</button>
    <button type="button" class="btn btn-outline-primary">Middle</button>
    <button type="button" class="btn btn-outline-primary">Right</button>
</div>

<!-- Dropdown button -->
<div class="btn-group">
    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown">
        Action
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">Action</a></li>
        <li><a class="dropdown-item" href="#">Another action</a></li>
        <li><a class="dropdown-item" href="#">Something else here</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Separated link</a></li>
    </ul>
</div>
```

### **Cards**
```html
<!-- Basic card -->
<div class="card" style="width: 18rem;">
    <img src="..." class="card-img-top" alt="...">
    <div class="card-body">
        <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
</div>

<!-- Card with header and footer -->
<div class="card">
    <div class="card-header">
        Featured
    </div>
    <div class="card-body">
        <h5 class="card-title">Special title treatment</h5>
        <p class="card-text">With supporting text below as a natural lead-in.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
    <div class="card-footer text-muted">
        2 days ago
    </div>
</div>

<!-- Card with list group -->
<div class="card" style="width: 18rem;">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">An item</li>
        <li class="list-group-item">A second item</li>
        <li class="list-group-item">A third item</li>
    </ul>
    <div class="card-body">
        <a href="#" class="card-link">Card link</a>
        <a href="#" class="card-link">Another link</a>
    </div>
</div>

<!-- Card with image overlay -->
<div class="card" style="width: 18rem;">
    <img src="..." class="card-img" alt="...">
    <div class="card-img-overlay">
        <h5 class="card-title">Card title</h5>
        <p class="card-text">This is a wider card with supporting text.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
</div>
```

### **Modals**
```html
<!-- Modal trigger button -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Launch demo modal
</button>

<!-- Modal structure -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal with different sizes -->
<!-- Small modal -->
<div class="modal fade" id="smallModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <!-- Modal content -->
        </div>
    </div>
</div>

<!-- Large modal -->
<div class="modal fade" id="largeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal content -->
        </div>
    </div>
</div>

<!-- Fullscreen modal -->
<div class="modal fade" id="fullscreenModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Modal content -->
        </div>
    </div>
</div>
```

### **Forms**
```html
<form>
    <div class="mb-3">
        <label for="exampleInputEmail1" class="form-label">Email address</label>
        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
    </div>
    <div class="mb-3">
        <label for="exampleInputPassword1" class="form-label">Password</label>
        <input type="password" class="form-control" id="exampleInputPassword1">
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Check me out</label>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<!-- Form with validation -->
<form class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="validationCustom01" class="form-label">First name</label>
        <input type="text" class="form-control" id="validationCustom01" required>
        <div class="valid-feedback">
            Looks good!
        </div>
    </div>
    <div class="mb-3">
        <label for="validationCustom02" class="form-label">Last name</label>
        <input type="text" class="form-control" id="validationCustom02" required>
        <div class="valid-feedback">
            Looks good!
        </div>
    </div>
    <div class="mb-3">
        <label for="validationCustomUsername" class="form-label">Username</label>
        <div class="input-group">
            <span class="input-group-text" id="inputGroupPrepend">@</span>
            <input type="text" class="form-control" id="validationCustomUsername" required>
        </div>
        <div class="invalid-feedback">
            Please choose a username.
        </div>
    </div>
    <div class="mb-3">
        <label for="validationCustom03" class="form-label">City</label>
        <input type="text" class="form-control" id="validationCustom03" required>
        <div class="invalid-feedback">
            Please provide a valid city.
        </div>
    </div>
    <div class="mb-3">
        <label for="validationCustom04" class="form-label">State</label>
        <select class="form-select" id="validationCustom04" required>
            <option selected disabled value="">Choose...</option>
            <option>...</option>
        </select>
        <div class="invalid-feedback">
            Please select a valid state.
        </div>
    </div>
    <div class="mb-3">
        <label for="validationCustom05" class="form-label">Zip</label>
        <input type="text" class="form-control" id="validationCustom05" required>
        <div class="invalid-feedback">
            Please provide a valid zip.
        </div>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
            <label class="form-check-label" for="invalidCheck">
                Agree to terms and conditions
            </label>
            <div class="invalid-feedback">
                You must agree before submitting.
            </div>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Submit form</button>
</form>
```

### **Navigation**
```html
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pricing</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Library</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data</li>
    </ol>
</nav>

<!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination">
        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item active"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
    </ul>
</nav>
```

### **Progress**
```html
<!-- Basic progress bar -->
<div class="progress">
    <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<!-- Progress bar with label -->
<div class="progress">
    <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
</div>

<!-- Colored progress bars -->
<div class="progress">
    <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress">
    <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress">
    <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="progress">
    <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<!-- Striped progress bar -->
<div class="progress">
    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
</div>

<!-- Animated progress bar -->
<div class="progress">
    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
</div>
```

### **Badges**
```html
<!-- Basic badges -->
<button type="button" class="btn btn-primary">
    Notifications <span class="badge bg-secondary">4</span>
</button>

<!-- Contextual badges -->
<span class="badge bg-primary">Primary</span>
<span class="badge bg-secondary">Secondary</span>
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Danger</span>
<span class="badge bg-warning text-dark">Warning</span>
<span class="badge bg-info">Info</span>
<span class="badge bg-light text-dark">Light</span>
<span class="badge bg-dark">Dark</span>

<!-- Pill badges -->
<span class="badge rounded-pill bg-primary">Primary</span>
<span class="badge rounded-pill bg-secondary">Secondary</span>
```

### **Collapse**
```html
<!-- Collapse trigger -->
<p>
    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button">
        Link with href
    </a>
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample">
        Button with data-bs-target
    </button>
</p>

<!-- Collapsible content -->
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
    </div>
</div>

<!-- Accordion -->
<div class="accordion" id="accordionExample">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                Accordion Item #1
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <strong>This is the first item's accordion body.</strong>
            </div>
        </div>
    </div>
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                Accordion Item #2
            </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <strong>This is the second item's accordion body.</strong>
            </div>
        </div>
    </div>
</div>
```

### **JavaScript Components**

#### **Tooltips**
```html
<!-- Basic tooltip -->
<button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" 
        data-bs-title="Tooltip on top">
    Tooltip on top
</button>

<!-- Tooltip with HTML content -->
<button type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-html="true"
        data-bs-title="<em>Tooltip</em> <u>with</u> <strong>HTML</strong>">
    Tooltip with HTML
</button>

<!-- Initialize tooltips -->
<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
```

#### **Popovers**
```html
<!-- Basic popover -->
<button type="button" class="btn btn-secondary" data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="bottom" data-bs-content="Bottom popover">
    Popover on bottom
</button>

<!-- Popover with title -->
<button type="button" class="btn btn-secondary" data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="right" data-bs-title="Popover title" data-bs-content="Popover content">
    Popover with title
</button>

<!-- Initialize popovers -->
<script>
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
})
</script>
```

#### **Dropdowns**
```html
<!-- Single button dropdown -->
<div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" 
            aria-expanded="false">
        Dropdown button
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">Action</a></li>
        <li><a class="dropdown-item" href="#">Another action</a></li>
        <li><a class="dropdown-item" href="#">Something else here</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Separated link</a></li>
    </ul>
</div>

<!-- Dropdown with form -->
<div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" 
            aria-expanded="false">
        Dropdown form
    </button>
    <div class="dropdown-menu" style="width: 300px;">
        <form class="px-4 py-3">
            <div class="mb-3">
                <label for="exampleDropdownFormEmail1" class="form-label">Email address</label>
                <input type="email" class="form-control" id="exampleDropdownFormEmail1" 
                       placeholder="email@example.com">
            </div>
            <div class="mb-3">
                <label for="exampleDropdownFormPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleDropdownFormPassword1" 
                       placeholder="Password">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="dropdownCheck">
                    <label class="form-check-label" for="dropdownCheck">
                        Remember me
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Sign in</button>
        </form>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#">New around here? Sign up</a>
        <a class="dropdown-item" href="#">Forgot password?</a>
    </div>
</div>
```

#### **Offcanvas**
```html
<!-- Offcanvas trigger -->
<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample">
    Toggle offcanvas
</button>

<!-- Offcanvas structure -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" 
     aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">Offcanvas</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div>
            Some text as placeholder. In real life you can have the elements you have chosen. Like, text, images, lists, etc.
        </div>
        <div class="dropdown mt-3">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Dropdown button
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Offcanvas positions -->
<!-- Top offcanvas -->
<div class="offcanvas offcanvas-top" tabindex="-1" id="offcanvasTop">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Offcanvas top</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        ...
    </div>
</div>

<!-- Bottom offcanvas -->
<div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Offcanvas bottom</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        ...
    </div>
</div>
```

#### **Toasts**
```html
<!-- Toast trigger -->
<button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button>

<!-- Toast structure -->
<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
    <div class="toast-header">
        <img src="..." class="rounded me-2" alt="...">
        <strong class="me-auto">Bootstrap</strong>
        <small>11 mins ago</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>

<!-- Initialize toast -->
<script>
var toastElList = [].slice.call(document.querySelectorAll('.toast'))
var toastList = toastElList.map(function(toastEl) {
    return new bootstrap.Toast(toastEl)
})

// Show toast manually
document.getElementById('liveToastBtn').addEventListener('click', function () {
    var toast = new bootstrap.Toast(document.querySelector('.toast'))
    toast.show()
})
</script>
```

#### **Carousel**
```html
<!-- Basic carousel -->
<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" 
                class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" 
                aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" 
                aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="..." class="d-block w-100" alt="...">
        </div>
        <div class="carousel-item">
            <img src="..." class="d-block w-100" alt="...">
        </div>
        <div class="carousel-item">
            <img src="..." class="d-block w-100" alt="...">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" 
            data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" 
            data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Initialize carousel -->
<script>
var myCarousel = document.querySelector('#carouselExampleIndicators')
var carousel = new bootstrap.Carousel(myCarousel, {
    interval: 2000,
    wrap: false
})
</script>
```

## 🔧 Utilities

### **Spacing**
```html
<!-- Margin -->
<div class="mt-3">Margin top 3</div>
<div class="mb-2">Margin bottom 2</div>
<div class="mx-auto">Margin horizontal auto</div>

<!-- Padding -->
<div class="p-3">Padding all sides 3</div>
<div class="pt-2">Padding top 2</div>

<!-- Gap (for flex/grid) -->
<div class="d-flex gap-3">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

### **Display**
```html
<div class="d-block">Block</div>
<div class="d-inline">Inline</div>
<div class="d-inline-block">Inline-block</div>
<div class="d-none">Hidden</div>
<div class="d-flex">Flex</div>
<div class="d-grid">Grid</div>

<!-- Responsive display -->
<div class="d-none d-md-block">Hidden on mobile, visible on medium and up</div>
```

### **Flexbox**
```html
<div class="d-flex justify-content-between align-items-center">
    <div>Left item</div>
    <div>Right item</div>
</div>

<div class="d-flex flex-column">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>
```

## 🎨 Customization

### **CSS Variables (Bootstrap 5)**
```css
:root {
    --bs-primary: #0d6efd;
    --bs-secondary: #6c757d;
    --bs-success: #198754;
    --bs-info: #0dcaf0;
    --bs-warning: #ffc107;
    --bs-danger: #dc3545;
    --bs-light: #f8f9fa;
    --bs-dark: #212529;
}
```

### **Custom Colors**
```html
<style>
    .custom-primary {
        --bs-primary: #your-color;
    }
</style>

<div class="custom-primary">
    <button class="btn btn-primary">Custom colored button</button>
</div>
```

## 📱 Mobile-First Development

### **Mobile-First Approach**
```html
<!-- Start with mobile layout -->
<div class="row">
    <div class="col-12">
        <!-- Mobile: full width -->
    </div>
</div>

<!-- Add breakpoints for larger screens -->
<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <!-- Mobile: full, Tablet: half, Desktop: third -->
    </div>
</div>
```

### **Responsive Utilities**
```html
<!-- Hide on mobile, show on tablet and up -->
<div class="d-none d-md-block">Desktop only content</div>

<!-- Show on mobile only -->
<div class="d-block d-md-none">Mobile only content</div>

<!-- Text alignment by breakpoint -->
<p class="text-center text-md-start">Center on mobile, left on desktop</p>
```

## 🚀 Performance Tips

### **Optimized Loading**
```html
<!-- Load CSS first -->
<link href="bootstrap.min.css" rel="stylesheet">

<!-- Load JS at the end of body -->
<script src="bootstrap.bundle.min.js"></script>
```

### **Bundle Size Optimization**
```html
<!-- Use only needed components -->
<script src="bootstrap.bundle.min.js"></script>
<!-- or -->
<script src="bootstrap.min.js"></script>
<script src="popper.min.js"></script>
```

## 🔍 Browser Support

### **Supported Browsers**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- IE11 (limited support)

### **Vendor Prefixes**
Bootstrap 5 tidak menggunakan vendor prefixes untuk CSS modern.

## 🎯 Best Practices

### **1. Use Semantic HTML**
```html
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="#">Brand</a>
    </div>
</nav>
```

### **2. Responsive Images**
```html
<img src="image.jpg" class="img-fluid" alt="Responsive image">
```

### **3. Accessible Forms**
```html
<div class="mb-3">
    <label for="formGroupExampleInput" class="form-label">Example label</label>
    <input type="text" class="form-control" id="formGroupExampleInput" 
           placeholder="Example input placeholder">
</div>
```

### **4. Progressive Enhancement**
```html
<!-- Start with basic HTML, enhance with Bootstrap -->
<div class="alert alert-primary" role="alert">
    A simple primary alert—check it out!
</div>
```

## 📋 Common Patterns

### **Navigation Bar**
```html
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

### **Hero Section**
```html
<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Custom jumbotron</h1>
        <p class="col-md-8 fs-4">Using a series of utilities, you can create this jumbotron, just like the one in previous versions of Bootstrap.</p>
        <button class="btn btn-primary btn-lg" type="button">Example button</button>
    </div>
</div>
```

## 🔧 Troubleshooting

### **Common Issues**
1. **JavaScript not working** - Ensure Bootstrap JS is loaded after Popper.js
2. **Dropdowns not working** - Check if Bootstrap JS bundle is included
3. **Responsive issues** - Verify viewport meta tag is present
4. **Custom CSS not overriding** - Use higher specificity or !important

### **Debug Mode**
```html
<!-- Add to head for debugging -->
<style>
    .debug {
        border: 1px solid red !important;
    }
</style>
```

---

**📚 Resources:**
- [Official Documentation](https://getbootstrap.com/docs/5.3/)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [Bootstrap Themes](https://themes.getbootstrap.com/)
