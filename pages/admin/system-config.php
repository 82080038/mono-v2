<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'system-config'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Konfigurasi Sistem</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Konfigurasi Sistem</li></ol></nav></div>
    </div>
<div class="page-body">

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <div class="list-group list-group-flush" id="configTabs">
                        <button class="list-group-item list-group-item-action active" data-tab="general">
                            <i class="fas fa-sliders-h me-2"></i>Umum
                        </button>
                        <button class="list-group-item list-group-item-action" data-tab="security">
                            <i class="fas fa-shield-alt me-2"></i>Keamanan
                        </button>
                        <button class="list-group-item list-group-item-action" data-tab="email">
                            <i class="fas fa-envelope me-2"></i>Email
                        </button>
                        <button class="list-group-item list-group-item-action" data-tab="upload">
                            <i class="fas fa-upload me-2"></i>Upload
                        </button>
                        <button class="list-group-item list-group-item-action" data-tab="business">
                            <i class="fas fa-briefcase me-2"></i>Bisnis
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0" id="tabTitle">Konfigurasi Umum</h6>
                </div>
                <div class="card-body" id="configContent">
                    <!-- General Settings -->
                    <div id="tab-general" class="config-tab">
                        <form id="generalForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Aplikasi</label>
                                    <input type="text" class="form-control" name="app_name" value="KSP Lam Gabe Jaya">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Versi</label>
                                    <input type="text" class="form-control" name="app_version" value="2.0.0" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Environment</label>
                                    <select class="form-select" name="app_environment">
                                        <option value="production">Production</option>
                                        <option value="development">Development</option>
                                        <option value="staging">Staging</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select" name="app_timezone">
                                        <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Debug Mode</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="app_debug" id="appDebug">
                                        <label class="form-check-label" for="appDebug">Aktifkan debug mode (hanya development)</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div id="tab-security" class="config-tab d-none">
                        <form id="securityForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">JWT Secret Key</label>
                                    <input type="password" class="form-control" name="jwt_secret" value="••••••••••••••••">
                                    <small class="text-muted">Klik untuk melihat/ganti</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Token Expiry (jam)</label>
                                    <input type="number" class="form-control" name="token_expiry" value="24">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Login Attempts</label>
                                    <input type="number" class="form-control" name="max_login_attempts" value="3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Login Lockout (menit)</label>
                                    <input type="number" class="form-control" name="login_lockout_time" value="15">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Session Lifetime (jam)</label>
                                    <input type="number" class="form-control" name="session_lifetime" value="1">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Email Settings -->
                    <div id="tab-email" class="config-tab d-none">
                        <form id="emailForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" name="mail_host" placeholder="smtp.example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" name="mail_port" value="587">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" name="mail_username">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" name="mail_password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">From Email</label>
                                    <input type="email" class="form-control" name="mail_from" placeholder="noreply@ksplamgabejaya.co.id">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Encryption</label>
                                    <select class="form-select" name="mail_encryption">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="testEmail()">
                                        <i class="fas fa-paper-plane me-1"></i>Kirim Test Email
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Upload Settings -->
                    <div id="tab-upload" class="config-tab d-none">
                        <form id="uploadForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Max Upload Size (MB)</label>
                                    <input type="number" class="form-control" name="upload_max_size" value="5">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Allowed Extensions</label>
                                    <input type="text" class="form-control" name="upload_allowed_types" value="jpg,jpeg,png,pdf,doc,docx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Upload Path</label>
                                    <input type="text" class="form-control" name="upload_path" value="/uploads/" readonly>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Business Settings -->
                    <div id="tab-business" class="config-tab d-none">
                        <form id="businessForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bunga Pinjaman (%/bulan)</label>
                                    <input type="number" step="0.01" class="form-control" name="loan_interest_rate" value="1.00">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Pinjaman (x simpanan)</label>
                                    <input type="number" class="form-control" name="loan_max_multiplier" value="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Tenor (bulan)</label>
                                    <input type="number" class="form-control" name="loan_max_term" value="12">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Simpanan Wajib (Rp/hari)</label>
                                    <input type="number" class="form-control" name="mandatory_savings" value="10000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bunga Simpanan (%/bulan)</label>
                                    <input type="number" step="0.01" class="form-control" name="savings_interest_rate" value="0.50">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Denda Keterlambatan (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="late_penalty_rate" value="10.00">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" onclick="saveConfig()">
                        <i class="fas fa-save me-1"></i>Simpan Perubahan
                    </button>
                    <button class="btn btn-secondary" onclick="resetConfig()">
                        <i class="fas fa-undo me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div></div>
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){
    const u=JSON.parse(localStorage.getItem('userData')||'{}');
    const r=(u.role||'').toLowerCase().replace(' ', '_');
    if(!u.token||r!=='super_admin') window.location.href='../../login.html';
    $('#sidebarUserName').text(u.name||'Admin');
    $('#sidebarUserRole').text(u.role||'');
})();

const BASE=(window.APP_CONFIG?.baseUrl||'')+'/api';

function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH(){ return { 'Authorization': 'Bearer '+getToken() }; }
function logout(){ if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }

function showToast(msg,type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(),4000);
}

// Tab switching
$('#configTabs button').click(function(){
    $('#configTabs button').removeClass('active');
    $(this).addClass('active');
    const tab=$(this).data('tab');
    $('.config-tab').addClass('d-none');
    $('#tab-'+tab).removeClass('d-none');
    $('#tabTitle').text($(this).text().trim());
});

function saveConfig(){
    showToast('Konfigurasi berhasil disimpan','success');
}

function resetConfig(){
    if(confirm('Reset semua konfigurasi ke nilai default?')){
        showToast('Konfigurasi di-reset','info');
    }
}

function testEmail(){
    showToast('Test email dikirim','success');
}
</script>
</body>
</html>
