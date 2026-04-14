<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking Mantri - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #mainContent{margin-left:0}
        #map{height:400px;border-radius:8px}
        .mantri-card{cursor:pointer;transition:all 0.2s}
        .mantri-card:hover{background:#f8f9fa}
        .mantri-card.active{background:#e3f2fd;border-color:#2196f3}
        .status-dot{width:10px;height:10px;border-radius:50%;display:inline-block}
        .status-online{background:#4caf50}
        .status-offline{background:#9e9e9e}
        .status-moving{background:#2196f3}
    </style>
</head>
<body>
<?php $activePage = 'live-tracking'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Live Tracking Mantri</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Live Tracking</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-sm btn-outline-primary" onclick="refreshAll()"><i class="fas fa-sync me-1"></i>Refresh</button></div>
    </div>
    <div class="page-body">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small">Total Mantri</div>
                        <div class="fs-4 fw-bold" id="mTotal">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small">Online</div>
                        <div class="fs-4 fw-bold text-success" id="mOnline">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small">Sedang Jalan</div>
                        <div class="fs-4 fw-bold text-primary" id="mMoving">—</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small">Offline</div>
                        <div class="fs-4 fw-bold text-muted" id="mOffline">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Map -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center">
                        <span class="fw-semibold"><i class="fas fa-map me-2"></i>Peta Real-time</span>
                        <span class="badge bg-secondary ms-auto" id="mapBadge">Auto-refresh 30s</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>
            </div>

            <!-- Mantri List -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold"><i class="fas fa-users me-2"></i>Daftar Mantri</span>
                        <input type="text" id="searchMantri" class="form-control form-control-sm mt-2" placeholder="Cari nama mantri..." oninput="filterMantri()">
                    </div>
                    <div class="card-body p-0" style="max-height:400px;overflow-y:auto">
                        <div id="mantriList">
                            <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-history me-2"></i>Aktivitas Terkini</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Waktu</th><th>Mantri</th><th>Lokasi</th><th>Aktivitas</th></tr>
                        </thead>
                        <tbody id="activityBody">
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada aktivitas</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script>
(function(){
    const u=JSON.parse(localStorage.getItem('userData')||'{}');
    const r=(u.role||'').toLowerCase().replace(' ','_');
    if(!u.token||!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}
    document.getElementById('sidebarUserName').textContent=u.name||'Admin';
    document.getElementById('sidebarUserRole').textContent=u.role||'';
})();
function logout(){if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';}}

const BASE = (window.APP_CONFIG?.baseUrl || '') + '/api';
function getToken() { try { return JSON.parse(localStorage.getItem('userData') || '{}').token || ''; } catch(e) { return ''; } }
function authH() { return { 'Authorization': 'Bearer ' + getToken() }; }

let map, markers = {}, mantriData = [];
let refreshInterval;

function initMap() {
    map = L.map('map').setView([-6.200000, 106.816666], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
}

function loadMantri() {
    $.ajax({ url: BASE + '/gps_tracking.php', method: 'GET', data: { action: 'get_nearby_locations' }, headers: authH() })
        .done(res => {
            mantriData = res.data?.locations || res.data || [];
            renderMantriList();
            updateMap();
            updateSummary();
        })
        .fail(() => $('#mantriList').html('<div class="p-3 text-danger">Gagal memuat data</div>'));
}

function renderMantriList() {
    const search = $('#searchMantri').val().toLowerCase();
    const filtered = mantriData.filter(m => (m.name || '').toLowerCase().includes(search) || (m.role || '').toLowerCase().includes(search));
    if (!filtered.length) {
        $('#mantriList').html('<div class="p-3 text-muted">Tidak ada mantri ditemukan</div>');
        return;
    }
    $('#mantriList').html(filtered.map(m => {
        const status = m.is_online ? (m.is_moving ? 'moving' : 'online') : 'offline';
        const statusClass = status === 'online' ? 'status-online' : status === 'moving' ? 'status-moving' : 'status-offline';
        const statusText = status === 'online' ? 'Online' : status === 'moving' ? 'Sedang Jalan' : 'Offline';
        return `<div class="p-3 border-bottom mantri-card" data-id="${m.id}" onclick="focusMantri(${m.id})">
            <div class="d-flex align-items-center gap-2">
                <div class="status-dot ${statusClass}"></div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${m.name || 'Mantri ' + m.id}</div>
                    <div class="text-muted small">${statusText} • ${m.role || 'Mantri'}</div>
                </div>
                <div class="text-muted small">${(m.last_seen || '').substring(11, 16)}</div>
            </div>
        </div>`;
    }).join(''));
}

function updateMap() {
    Object.values(markers).forEach(m => map.removeLayer(m));
    markers = {};
    mantriData.forEach(m => {
        if (m.latitude && m.longitude) {
            const marker = L.marker([m.latitude, m.longitude]).addTo(map)
                .bindPopup(`<b>${m.name}</b><br>Status: ${m.is_online ? 'Online' : 'Offline'}<br>Last: ${m.last_seen || '-'}`);
            markers[m.id] = marker;
        }
    });
    if (mantriData.length > 0 && mantriData[0].latitude) {
        map.setView([mantriData[0].latitude, mantriData[0].longitude], 13);
    }
}

function updateSummary() {
    const total = mantriData.length;
    const online = mantriData.filter(m => m.is_online && !m.is_moving).length;
    const moving = mantriData.filter(m => m.is_moving).length;
    const offline = total - online - moving;
    $('#mTotal').text(total);
    $('#mOnline').text(online);
    $('#mMoving').text(moving);
    $('#mOffline').text(offline);
}

function focusMantri(id) {
    const m = mantriData.find(x => x.id === id);
    if (m && m.latitude && m.longitude) {
        map.setView([m.latitude, m.longitude], 16);
        if (markers[id]) markers[id].openPopup();
        $('.mantri-card').removeClass('active');
        $(`.mantri-card[data-id="${id}"]`).addClass('active');
    }
}

function filterMantri() { renderMantriList(); }
function refreshAll() { loadMantri(); }

$(function() {
    initMap();
    loadMantri();
    refreshInterval = setInterval(loadMantri, 30000);
});
</script>
</body>
</html>
