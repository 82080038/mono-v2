# HTML5 Complete Guide

## 🎯 Overview

HTML5 adalah versi terbaru dari HTML yang menyediakan semantic elements, multimedia support, dan API untuk aplikasi web modern. HTML5 dirancang untuk membuat web yang lebih accessible, interactive, dan powerful.

## 📱 Semantic Elements

### **What are Semantic Elements?**
Semantic elements adalah elemen HTML yang jelas mendeskripsikan makna kontennya baik untuk browser maupun developer.

### **Non-Semantic vs Semantic Elements**
```html
<!-- Non-semantic elements (tidak jelas maknanya) -->
<div id="header"></div>
<div class="nav"></div>
<div class="content"></div>
<div id="footer"></div>

<!-- Semantic elements (jelas maknanya) -->
<header></header>
<nav></nav>
<main></main>
<footer></footer>
```

### **Core Semantic Elements**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KSP Lam Gabe Jaya</title>
</head>
<body>
    <!-- Header section -->
    <header>
        <h1>KSP Lam Gabe Jaya</h1>
        <nav>
            <ul>
                <li><a href="#home">Beranda</a></li>
                <li><a href="#about">Tentang</a></li>
                <li><a href="#contact">Kontak</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main content -->
    <main>
        <section>
            <h2>Layanan Kami</h2>
            <article>
                <h3>Simpanan</h3>
                <p>Layanan simpanan dengan bunga kompetitif...</p>
            </article>
            <article>
                <h3>Pinjaman</h3>
                <p>Pinjaman dengan proses mudah dan cepat...</p>
            </article>
        </section>
    </main>

    <!-- Sidebar -->
    <aside>
        <h3>Informasi</h3>
        <p>Hubungi kami untuk informasi lebih lanjut.</p>
    </aside>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 KSP Lam Gabe Jaya. All rights reserved.</p>
    </footer>
</body>
</html>
```

### **Detailed Semantic Elements**
```html
<!-- Section - Bagian tematik dari konten -->
<section>
    <h1>Pinjaman</h1>
    <p>Informasi lengkap tentang layanan pinjaman kami.</p>
    
    <article>
        <h2>Pinjaman Mikro</h2>
        <p>Pinjaman kecil untuk usaha mikro...</p>
        <time datetime="2024-01-15">15 Januari 2024</time>
    </article>
    
    <article>
        <h2>Pinjaman Koperasi</h2>
        <p>Pinjaman untuk anggota koperasi...</p>
        <time datetime="2024-01-20">20 Januari 2024</time>
    </article>
</section>

<!-- Figure - Gambar dengan caption -->
<figure>
    <img src="images/kantor.jpg" alt="Kantor KSP Lam Gabe Jaya">
    <figcaption>Kantor pusat KSP Lam Gabe Jaya</figcaption>
</figure>

<!-- Details & Summary - Collapsible content -->
<details>
    <summary>Syarat Pinjaman</summary>
    <ul>
        <li>Warga negara Indonesia</li>
        <li>Usia minimal 21 tahun</li>
        <li>Memiliki penghasilan tetap</li>
        <li>Menyerahkan dokumen lengkap</li>
    </ul>
</details>

<!-- Mark - Highlight text -->
<p>Bunga <mark>kompetitif</mark> untuk semua jenis pinjaman.</p>
```

## 🎨 Forms & Input Types

### **Modern Input Types**
```html
<form id="loan-form">
    <!-- Text inputs -->
    <div>
        <label for="nama">Nama Lengkap:</label>
        <input type="text" id="nama" name="nama" required>
    </div>
    
    <!-- Email -->
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <!-- Tel -->
    <div>
        <label for="telepon">Telepon:</label>
        <input type="tel" id="telepon" name="telepon" pattern="[0-9]{10,13}">
    </div>
    
    <!-- Number -->
    <div>
        <label for="jumlah">Jumlah Pinjaman:</label>
        <input type="number" id="jumlah" name="jumlah" min="100000" max="50000000" step="10000">
    </div>
    
    <!-- Date -->
    <div>
        <label for="tanggal">Tanggal Lahir:</label>
        <input type="date" id="tanggal" name="tanggal">
    </div>
    
    <!-- Range -->
    <div>
        <label for="jangka-waktu">Jangka Waktu (bulan):</label>
        <input type="range" id="jangka-waktu" name="jangka-waktu" min="1" max="36" value="12">
        <span id="jangka-waktu-value">12 bulan</span>
    </div>
    
    <!-- Color -->
    <div>
        <label for="theme">Tema:</label>
        <input type="color" id="theme" name="theme" value="#007bff">
    </div>
    
    <!-- Search -->
    <div>
        <label for="search">Cari Nasabah:</label>
        <input type="search" id="search" name="search" placeholder="Masukkan nama nasabah...">
    </div>
    
    <!-- URL -->
    <div>
        <label for="website">Website:</label>
        <input type="url" id="website" name="website" placeholder="https://example.com">
    </div>
    
    <!-- File -->
    <div>
        <label for="dokumen">Upload Dokumen:</label>
        <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.png" multiple>
    </div>
    
    <!-- Radio buttons -->
    <div>
        <fieldset>
            <legend>Jenis Pinjaman:</legend>
            <input type="radio" id="mikro" name="jenis" value="mikro" checked>
            <label for="mikro">Pinjaman Mikro</label>
            
            <input type="radio" id="konsumtif" name="jenis" value="konsumtif">
            <label for="konsumtif">Pinjaman Konsumtif</label>
            
            <input type="radio" id="produktif" name="jenis" value="produktif">
            <label for="produktif">Pinjaman Produktif</label>
        </fieldset>
    </div>
    
    <!-- Checkboxes -->
    <div>
        <fieldset>
            <legend>Dokumen yang Diperlukan:</legend>
            <input type="checkbox" id="ktp" name="dokumen[]" value="ktp">
            <label for="ktp">KTP</label>
            
            <input type="checkbox" id="kk" name="dokumen[]" value="kk">
            <label for="kk">Kartu Keluarga</label>
            
            <input type="checkbox" id="slip-gaji" name="dokumen[]" value="slip-gaji">
            <label for="slip-gaji">Slip Gaji</label>
        </fieldset>
    </div>
    
    <!-- Select -->
    <div>
        <label for="pendidikan">Pendidikan Terakhir:</label>
        <select id="pendidikan" name="pendidikan">
            <option value="">-- Pilih --</option>
            <option value="sd">SD</option>
            <option value="smp">SMP</option>
            <option value="sma">SMA</option>
            <option value="d3">D3</option>
            <option value="s1">S1</option>
            <option value="s2">S2</option>
        </select>
    </div>
    
    <!-- Textarea -->
    <div>
        <label for="keterangan">Keterangan:</label>
        <textarea id="keterangan" name="keterangan" rows="4" cols="50" 
                  placeholder="Masukkan keterangan tambahan..."></textarea>
    </div>
    
    <!-- Submit button -->
    <div>
        <button type="submit">Ajukan Pinjaman</button>
        <button type="reset">Reset Form</button>
    </div>
</form>
```

### **Form Validation**
```html
<form id="validation-form">
    <!-- Required fields -->
    <div>
        <label for="required-field">Field Wajib:</label>
        <input type="text" id="required-field" name="required-field" required>
        <small class="error-message">Field ini wajib diisi</small>
    </div>
    
    <!-- Pattern validation -->
    <div>
        <label for="nik">NIK:</label>
        <input type="text" id="nik" name="nik" 
               pattern="[0-9]{16}" 
               title="NIK harus 16 digit angka"
               required>
        <small class="error-message">Format NIK tidak valid</small>
    </div>
    
    <!-- Min/Max length -->
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" 
               minlength="3" maxlength="20" required>
        <small class="error-message">Username 3-20 karakter</small>
    </div>
    
    <!-- Custom validation -->
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" 
               minlength="8" 
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
               title="Password harus mengandung huruf besar, kecil, dan angka"
               required>
        <small class="error-message">Password tidak memenuhi syarat</small>
    </div>
    
    <button type="submit">Submit</button>
</form>

<script>
// Custom validation
document.getElementById('validation-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Password tidak cocok!');
    }
});
</script>
```

## 🎯 Media Elements

### **Images**
```html
<!-- Basic image -->
<img src="images/logo.png" alt="Logo KSP Lam Gabe Jaya">

<!-- Responsive image -->
<img src="images/banner.jpg" 
     alt="Banner KSP" 
     class="responsive-image">

<!-- Picture element for responsive images -->
<picture>
    <source media="(min-width: 768px)" srcset="images/banner-large.jpg">
    <source media="(min-width: 480px)" srcset="images/banner-medium.jpg">
    <img src="images/banner-small.jpg" alt="Banner KSP">
</picture>

<!-- Lazy loading -->
<img src="images/content.jpg" 
     alt="Content image" 
     loading="lazy"
     width="800" 
     height="600">

<!-- Image with figure -->
<figure>
    <img src="images/kantor.jpg" 
         alt="Kantor KSP" 
         width="800" 
         height="600">
    <figcaption>Kantor pusat KSP Lam Gabe Jaya di Jakarta</figcaption>
</figure>
```

### **Audio**
```html
<!-- Basic audio -->
<audio controls>
    <source src="audio/panduan.mp3" type="audio/mpeg">
    <source src="audio/panduan.ogg" type="audio/ogg">
    Your browser does not support the audio element.
</audio>

<!-- Audio with autoplay and loop -->
<audio autoplay loop muted>
    <source src="audio/background-music.mp3" type="audio/mpeg">
</audio>

<!-- Audio with custom controls -->
<audio id="myAudio" src="audio/panduan.mp3"></audio>
<div class="audio-controls">
    <button onclick="document.getElementById('myAudio').play()">Play</button>
    <button onclick="document.getElementById('myAudio').pause()">Pause</button>
    <button onclick="document.getElementById('myAudio').volume = 0.5">Volume 50%</button>
</div>
```

### **Video**
```html
<!-- Basic video -->
<video controls width="640" height="360">
    <source src="video/tutorial.mp4" type="video/mp4">
    <source src="video/tutorial.webm" type="video/webm">
    Your browser does not support the video tag.
</video>

<!-- Video with poster and autoplay -->
<video autoplay muted loop poster="images/video-poster.jpg" width="640" height="360">
    <source src="video/intro.mp4" type="video/mp4">
</video>

<!-- Video with tracks (subtitles) -->
<video controls width="640" height="360">
    <source src="video/panduan.mp4" type="video/mp4">
    <track kind="subtitles" src="subtitles/id.vtt" srclang="id" label="Bahasa Indonesia">
    <track kind="subtitles" src="subtitles/en.vtt" srclang="en" label="English">
</video>

<!-- Video with custom controls -->
<video id="myVideo" width="640" height="360">
    <source src="video/presentation.mp4" type="video/mp4">
</video>
<div class="video-controls">
    <button onclick="playPause()">Play/Pause</button>
    <button onclick="muteUnmute()">Mute/Unmute</button>
    <input type="range" min="0" max="1" step="0.1" onchange="changeVolume(this.value)">
</div>

<script>
function playPause() {
    const video = document.getElementById('myVideo');
    if (video.paused) {
        video.play();
    } else {
        video.pause();
    }
}

function muteUnmute() {
    const video = document.getElementById('myVideo');
    video.muted = !video.muted;
}

function changeVolume(value) {
    document.getElementById('myVideo').volume = value;
}
</script>
```

## 📊 Tables

### **Modern Table Structure**
```html
<table class="data-table">
    <caption>Daftar Nasabah KSP Lam Gabe Jaya</caption>
    <thead>
        <tr>
            <th scope="col">No</th>
            <th scope="col">Nama</th>
            <th scope="col">Alamat</th>
            <th scope="col">Telepon</th>
            <th scope="col">Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Ahmad Wijaya</td>
            <td>Jl. Merdeka No. 123, Jakarta</td>
            <td>0812-3456-7890</td>
            <td><span class="status active">Aktif</span></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Siti Nurhaliza</td>
            <td>Jl. Sudirman No. 456, Bandung</td>
            <td>0813-9876-5432</td>
            <td><span class="status active">Aktif</span></td>
        </tr>
        <tr>
            <td>3</td>
            <td>Budi Santoso</td>
            <td>Jl. Gatot Subroto No. 789, Surabaya</td>
            <td>0814-6543-2109</td>
            <td><span class="status inactive">Tidak Aktif</span></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5">Total: 3 Nasabah</th>
        </tr>
    </tfoot>
</table>
```

### **Responsive Table**
```html
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nasabah</th>
                <th>Simpanan</th>
                <th>Pinjaman</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="Nasabah">Ahmad Wijaya</td>
                <td data-label="Simpanan">Rp 5.000.000</td>
                <td data-label="Pinjaman">Rp 10.000.000</td>
                <td data-label="Status">Aktif</td>
            </tr>
        </tbody>
    </table>
</div>

<style>
.table-responsive {
    overflow-x: auto;
}

@media screen and (max-width: 600px) {
    .table-responsive thead {
        display: none;
    }
    
    .table-responsive, .table-responsive thead, .table-responsive tbody, .table-responsive th, .table-responsive td, .table-responsive tr {
        display: block;
    }
    
    .table-responsive tr {
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }
    
    .table-responsive td {
        border: none;
        border-bottom: 1px solid #eee;
        position: relative;
        padding-left: 50%;
    }
    
    .table-responsive td:before {
        position: absolute;
        top: 6px;
        left: 6px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: bold;
    }
    
    .table-responsive td:nth-of-type(1):before { content: "Nasabah"; }
    .table-responsive td:nth-of-type(2):before { content: "Simpanan"; }
    .table-responsive td:nth-of-type(3):before { content: "Pinjaman"; }
    .table-responsive td:nth-of-type(4):before { content: "Status"; }
}
</style>
```

## 🎨 Canvas & SVG

### **Canvas Drawing**
```html
<canvas id="myCanvas" width="400" height="300" style="border: 1px solid #ccc;">
    Your browser does not support the canvas element.
</canvas>

<script>
const canvas = document.getElementById('myCanvas');
const ctx = canvas.getContext('2d');

// Draw rectangle
ctx.fillStyle = '#007bff';
ctx.fillRect(50, 50, 100, 50);

// Draw circle
ctx.beginPath();
ctx.arc(200, 100, 30, 0, 2 * Math.PI);
ctx.fillStyle = '#28a745';
ctx.fill();

// Draw text
ctx.font = '16px Arial';
ctx.fillStyle = '#333';
ctx.fillText('KSP Lam Gabe Jaya', 120, 200);

// Draw line
ctx.beginPath();
ctx.moveTo(50, 250);
ctx.lineTo(350, 250);
ctx.strokeStyle = '#dc3545';
ctx.lineWidth = 2;
ctx.stroke();

// Draw gradient
const gradient = ctx.createLinearGradient(0, 0, 400, 0);
gradient.addColorStop(0, '#007bff');
gradient.addColorStop(1, '#28a745');
ctx.fillStyle = gradient;
ctx.fillRect(50, 150, 300, 50);
</script>
```

### **SVG Graphics**
```html
<!-- Inline SVG -->
<svg width="200" height="200" viewBox="0 0 200 200">
    <!-- Circle -->
    <circle cx="100" cy="100" r="50" fill="#007bff" />
    
    <!-- Rectangle -->
    <rect x="50" y="50" width="100" height="100" fill="#28a745" opacity="0.5" />
    
    <!-- Text -->
    <text x="100" y="105" text-anchor="middle" fill="white" font-size="16" font-weight="bold">
        KSP
    </text>
    
    <!-- Path -->
    <path d="M 20 180 Q 100 20 180 180" stroke="#dc3545" stroke-width="2" fill="none" />
</svg>

<!-- SVG as image -->
<img src="images/logo.svg" alt="Logo" width="100" height="100">
```

## 📱 Geolocation API

### **Getting User Location**
```html
<div id="location-info">
    <p>Klik tombol untuk mendapatkan lokasi Anda:</p>
    <button onclick="getLocation()">Dapatkan Lokasi</button>
    <div id="location-result"></div>
</div>

<script>
function getLocation() {
    const resultDiv = document.getElementById('location-result');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            // Success callback
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                resultDiv.innerHTML = `
                    <h3>Lokasi Anda:</h3>
                    <p>Latitude: ${latitude}</p>
                    <p>Longitude: ${longitude}</p>
                    <p>Akurasi: ${accuracy} meter</p>
                    <p><a href="https://maps.google.com/?q=${latitude},${longitude}" target="_blank">Lihat di Google Maps</a></p>
                `;
            },
            // Error callback
            function(error) {
                let errorMessage = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Izin lokasi ditolak";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Informasi lokasi tidak tersedia";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "Request timeout";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "Error tidak diketahui";
                        break;
                }
                resultDiv.innerHTML = `<p style="color: red;">Error: ${errorMessage}</p>`;
            },
            // Options
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        resultDiv.innerHTML = '<p style="color: red;">Browser tidak mendukung Geolocation</p>';
    }
}

// Watch position (continuous tracking)
function watchLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            function(position) {
                console.log('Position updated:', position.coords);
            },
            function(error) {
                console.error('Error watching position:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }
}
</script>
```

## 💾 Local Storage

### **localStorage & sessionStorage**
```html
<div id="storage-demo">
    <h3>Local Storage Demo</h3>
    <input type="text" id="storageInput" placeholder="Masukkan data...">
    <button onclick="saveData()">Simpan</button>
    <button onclick="loadData()">Muat</button>
    <button onclick="clearData()">Hapus</button>
    <div id="storageResult"></div>
</div>

<script>
// Save data to localStorage
function saveData() {
    const input = document.getElementById('storageInput');
    const result = document.getElementById('storageResult');
    
    const data = {
        value: input.value,
        timestamp: new Date().toISOString(),
        type: 'localStorage'
    };
    
    localStorage.setItem('myData', JSON.stringify(data));
    result.innerHTML = `<p style="color: green;">Data tersimpan di localStorage!</p>`;
}

// Load data from localStorage
function loadData() {
    const result = document.getElementById('storageResult');
    const storedData = localStorage.getItem('myData');
    
    if (storedData) {
        const data = JSON.parse(storedData);
        document.getElementById('storageInput').value = data.value;
        result.innerHTML = `
            <p>Data dimuat: ${data.value}</p>
            <p>Tersimpan: ${new Date(data.timestamp).toLocaleString()}</p>
        `;
    } else {
        result.innerHTML = '<p style="color: orange;">Tidak ada data tersimpan</p>';
    }
}

// Clear data from localStorage
function clearData() {
    localStorage.removeItem('myData');
    document.getElementById('storageInput').value = '';
    document.getElementById('storageResult').innerHTML = '<p style="color: red;">Data dihapus!</p>';
}

// SessionStorage example
function saveToSessionStorage() {
    sessionStorage.setItem('sessionData', 'Data sesi ini');
}

function loadFromSessionStorage() {
    const data = sessionStorage.getItem('sessionData');
    console.log('Session data:', data);
}

// Check storage availability
function checkStorageSupport() {
    if (typeof(Storage) !== 'undefined') {
        console.log('Browser mendukung Web Storage');
    } else {
        console.log('Browser tidak mendukung Web Storage');
    }
}

// Storage events (for cross-tab communication)
window.addEventListener('storage', function(e) {
    console.log('Storage changed:', e.key, e.newValue);
});
</script>
```

## 🎯 Web Workers

### **Background Processing**
```html
<div id="worker-demo">
    <h3>Web Worker Demo</h3>
    <button onclick="startWorker()">Mulai Perhitungan</button>
    <button onclick="stopWorker()">Stop Worker</button>
    <div id="workerResult"></div>
</div>

<script>
let worker;

function startWorker() {
    // Check Web Worker support
    if (typeof(Worker) !== 'undefined') {
        // Create Web Worker from inline script
        const workerCode = `
            let counter = 0;
            
            self.onmessage = function(e) {
                if (e.data === 'start') {
                    counter = 0;
                    setInterval(() => {
                        counter++;
                        self.postMessage({count: counter});
                    }, 1000);
                } else if (e.data === 'stop') {
                    self.close();
                }
            };
        `;
        
        const blob = new Blob([workerCode], {type: 'application/javascript'});
        worker = new Worker(URL.createObjectURL(blob));
        
        worker.onmessage = function(e) {
            document.getElementById('workerResult').innerHTML = 
                `<p>Counter: ${e.data.count}</p>`;
        };
        
        worker.postMessage('start');
        
        document.getElementById('workerResult').innerHTML = 
            '<p style="color: green;">Worker dimulai!</p>';
    } else {
        document.getElementById('workerResult').innerHTML = 
            '<p style="color: red;">Browser tidak mendukung Web Workers</p>';
    }
}

function stopWorker() {
    if (worker) {
        worker.postMessage('stop');
        worker = null;
        document.getElementById('workerResult').innerHTML = 
            '<p style="color: orange;">Worker dihentikan!</p>';
    }
}
</script>
```

## 🎨 Best Practices

### **1. Semantic HTML Structure**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KSP Lam Gabe Jaya - Koperasi Simpan Pinjam Digital">
    <title>KSP Lam Gabe Jaya</title>
</head>
<body>
    <header>
        <nav role="navigation">
            <ul>
                <li><a href="#home">Beranda</a></li>
                <li><a href="#about">Tentang</a></li>
            </ul>
        </nav>
    </header>
    
    <main role="main">
        <section aria-labelledby="services-heading">
            <h1 id="services-heading">Layanan Kami</h1>
            <!-- Content -->
        </section>
    </main>
    
    <footer role="contentinfo">
        <p>&copy; 2024 KSP Lam Gabe Jaya</p>
    </footer>
</body>
</html>
```

### **2. Accessibility (ARIA)**
```html
<!-- Form with proper labels -->
<form role="form" aria-labelledby="loan-form-heading">
    <h2 id="loan-form-heading">Form Pengajuan Pinjaman</h2>
    
    <div>
        <label for="nama-lengkap">Nama Lengkap:</label>
        <input type="text" 
               id="nama-lengkap" 
               name="nama" 
               required 
               aria-describedby="nama-help"
               aria-invalid="false">
        <div id="nama-help" class="help-text">
            Masukkan nama lengkap sesuai KTP
        </div>
    </div>
    
    <div>
        <button type="submit" aria-label="Ajukan pinjaman">
            Ajukan Pinjaman
        </button>
    </div>
</form>

<!-- Interactive elements -->
<button aria-expanded="false" aria-controls="details-panel">
    Lihat Detail
</button>
<div id="details-panel" aria-hidden="true">
    <!-- Detail content -->
</div>

<!-- Loading states -->
<div role="status" aria-live="polite" id="loading-status">
    Memuat data...
</div>

<!-- Error messages -->
<div role="alert" aria-live="assertive" id="error-message">
    Terjadi kesalahan. Silakan coba lagi.
</div>
```

### **3. Performance Optimization**
```html
<!-- Lazy loading images -->
<img src="placeholder.jpg" 
     data-src="actual-image.jpg" 
     alt="Description" 
     loading="lazy"
     class="lazy-image">

<!-- Preload critical resources -->
<link rel="preload" href="critical.css" as="style">
<link rel="preload" href="font.woff2" as="font" type="font/woff2" crossorigin>

<!-- Minify and compress -->
<!-- Use minified HTML, CSS, and JS in production -->

<!-- Reduce HTTP requests -->
<!-- Use CSS sprites for small images -->
<!-- Combine multiple CSS/JS files -->
```

### **4. Mobile-First Design**
```html
<!-- Responsive images -->
<picture>
    <source media="(min-width: 768px)" srcset="image-large.jpg">
    <source media="(min-width: 480px)" srcset="image-medium.jpg">
    <img src="image-small.jpg" alt="Description">
</picture>

<!-- Responsive typography -->
<style>
.text-responsive {
    font-size: 16px; /* Base size for mobile */
}

@media (min-width: 768px) {
    .text-responsive {
        font-size: 18px;
    }
}

@media (min-width: 1024px) {
    .text-responsive {
        font-size: 20px;
    }
}
</style>

<!-- Touch-friendly buttons -->
<button class="btn-mobile-friendly">
    <!-- Larger touch targets -->
</button>

<style>
.btn-mobile-friendly {
    min-height: 44px;
    min-width: 44px;
    padding: 12px 24px;
}
</style>
```

---

**📚 Resources:**
- [HTML5 Documentation](https://developer.mozilla.org/en-US/docs/Web/HTML)
- [HTML5 Semantic Elements](https://www.w3schools.com/html/html5_semantic_elements.asp)
- [Web APIs - MDN](https://developer.mozilla.org/en-US/docs/Web/API)
