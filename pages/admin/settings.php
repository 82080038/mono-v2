<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .nav-tabs .nav-link.active{color:#2563eb;border-bottom:2px solid #2563eb}
    </style>
</head>
<body>
<?php $activePage = 'settings'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Pengaturan Sistem</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pengaturan</li></ol></nav></div>
    </div>
    <div class="page-body">
        <div class="row g-4">
            <!-- Left: Tab Nav -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="list-group list-group-flush rounded">
                        <a href="#" class="list-group-item list-group-item-action active" onclick="showTab('koperasi',this)"><i class="fas fa-building me-2"></i>Profil Koperasi</a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showTab('bunga',this)"><i class="fas fa-percent me-2"></i>Suku Bunga</a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showTab('notif',this)"><i class="fas fa-bell me-2"></i>Notifikasi</a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showTab('keamanan',this)"><i class="fas fa-lock me-2"></i>Keamanan</a>
                    </div>
                </div>
            </div>
            <!-- Right: Tab Content -->
            <div class="col-md-9">
                <!-- Profil Koperasi -->
                <div id="tab_koperasi" class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold"><i class="fas fa-building me-2 text-primary"></i>Profil Koperasi</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label small fw-semibold">Nama Koperasi</label><input type="text" class="form-control" id="coopName" value="KSP Lam Gabe Jaya"></div>
                            <div class="col-md-6"><label class="form-label small fw-semibold">Nomor Badan Hukum</label><input type="text" class="form-control" id="coopLegal" value=""></div>
                            <div class="col-md-6"><label class="form-label small fw-semibold">Alamat</label><input type="text" class="form-control" id="coopAddress" value=""></div>
                            <div class="col-md-6"><label class="form-label small fw-semibold">Telepon</label><input type="text" class="form-control" id="coopPhone" value=""></div>
                            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" class="form-control" id="coopEmail" value=""></div>
                            <div class="col-md-6"><label class="form-label small fw-semibold">Website</label><input type="text" class="form-control" id="coopWeb" value=""></div>
                            <div class="col-12"><button class="btn btn-primary" onclick="saveSettings('koperasi')"><i class="fas fa-save me-2"></i>Simpan Profil</button></div>
                        </div>
                    </div>
                </div>
                <!-- Suku Bunga -->
                <div id="tab_bunga" class="card border-0 shadow-sm d-none">
                    <div class="card-header bg-white fw-semibold"><i class="fas fa-percent me-2 text-primary"></i>Konfigurasi Suku Bunga</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label small fw-semibold">Bunga Simpanan Pokok (%/th)</label><input type="number" class="form-control" id="ratePokok" value="2" step="0.1"></div>
                            <div class="col-md-4"><label class="form-label small fw-semibold">Bunga Simpanan Wajib (%/th)</label><input type="number" class="form-control" id="rateWajib" value="3" step="0.1"></div>
                            <div class="col-md-4"><label class="form-label small fw-semibold">Bunga Deposito (%/th)</label><input type="number" class="form-control" id="rateDeposito" value="6" step="0.1"></div>
                            <div class="col-md-4"><label class="form-label small fw-semibold">Bunga Pinjaman Reguler (%/bln)</label><input type="number" class="form-control" id="rateLoan" value="1.5" step="0.1"></div>
                            <div class="col-md-4"><label class="form-label small fw-semibold">Bunga Pinjaman Darurat (%/bln)</label><input type="number" class="form-control" id="rateLoanEmergency" value="2" step="0.1"></div>
                            <div class="col-md-4"><label class="form-label small fw-semibold">Denda Keterlambatan (%)</label><input type="number" class="form-control" id="ratePenalty" value="0.5" step="0.1"></div>
                            <div class="col-12"><button class="btn btn-primary" onclick="saveSettings('bunga')"><i class="fas fa-save me-2"></i>Simpan Konfigurasi</button></div>
                        </div>
                    </div>
                </div>
                <!-- Notifikasi -->
                <div id="tab_notif" class="card border-0 shadow-sm d-none">
                    <div class="card-header bg-white fw-semibold"><i class="fas fa-bell me-2 text-primary"></i>Pengaturan Notifikasi</div>
                    <div class="card-body">
                        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notifEmail" checked><label class="form-check-label" for="notifEmail">Notifikasi Email untuk pengajuan pinjaman baru</label></div></div>
                        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notifOverdue" checked><label class="form-check-label" for="notifOverdue">Alert otomatis untuk pinjaman jatuh tempo</label></div></div>
                        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="notifNpl"><label class="form-check-label" for="notifNpl">Alert NPL ketika rasio melebihi 5%</label></div></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Hari sebelum jatuh tempo untuk kirim reminder</label><input type="number" class="form-control" style="max-width:120px" id="reminderDays" value="3"></div>
                        <button class="btn btn-primary" onclick="saveSettings('notif')"><i class="fas fa-save me-2"></i>Simpan Pengaturan</button>
                    </div>
                </div>
                <!-- Keamanan -->
                <div id="tab_keamanan" class="card border-0 shadow-sm d-none">
                    <div class="card-header bg-white fw-semibold"><i class="fas fa-lock me-2 text-primary"></i>Pengaturan Keamanan</div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label small fw-semibold">Durasi Sesi Login (menit)</label><input type="number" class="form-control" style="max-width:180px" id="sessionDuration" value="60"></div>
                        <div class="mb-3"><label class="form-label small fw-semibold">Maksimal Percobaan Login Gagal</label><input type="number" class="form-control" style="max-width:180px" id="maxLoginAttempt" value="5"></div>
                        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="secForce2fa"><label class="form-check-label" for="secForce2fa">Wajibkan 2FA untuk admin</label></div></div>
                        <div class="mb-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="secLogAll" checked><label class="form-check-label" for="secLogAll">Catat semua aktivitas (Audit Trail lengkap)</label></div></div>
                        <button class="btn btn-primary" onclick="saveSettings('keamanan')"><i class="fas fa-save me-2"></i>Simpan Keamanan</button>
                    </div>
                </div>
                <div id="saveToast" class="alert alert-success mt-3 d-none"><i class="fas fa-check me-2"></i>Pengaturan berhasil disimpan.</div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){const u=JSON.parse(localStorage.getItem('userData')||'{}');if(!u.token){window.location.href='../../login.html';return;}const r=(u.role||'').toLowerCase().replace(' ','_');if(!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}$('#sidebarUserName').text(u.name||'Admin');$('#sidebarUserRole').text(u.role||'');})();
function logout(){if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';}}
function showTab(name,el){
    document.querySelectorAll('[id^="tab_"]').forEach(t=>t.classList.add('d-none'));
    document.getElementById('tab_'+name).classList.remove('d-none');
    document.querySelectorAll('.list-group-item').forEach(i=>i.classList.remove('active'));
    el.classList.add('active');return false;
}
function saveSettings(tab){
    $('#saveToast').removeClass('d-none');setTimeout(()=>$('#saveToast').addClass('d-none'),3000);
}
</script>
</body>
</html>
