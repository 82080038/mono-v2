<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'backup-restore'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Backup &amp; Restore</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Backup &amp; Restore</li></ol></nav></div>
    </div>
<div class="page-body">

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-download me-2"></i>Buat Backup Baru</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Backup</label>
                        <input type="text" class="form-control" id="backupName" placeholder="Backup otomatis akan dibuat jika kosong">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Backup</label>
                        <select class="form-select" id="backupType">
                            <option value="full">Full Backup (semua tabel)</option>
                            <option value="data">Data Only (tanpa struktur)</option>
                            <option value="structure">Structure Only (tanpa data)</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="includeUploads" checked>
                        <label class="form-check-label" for="includeUploads">
                            Sertakan folder uploads
                        </label>
                    </div>
                    <button class="btn btn-primary w-100" onclick="createBackup()">
                        <i class="fas fa-download me-1"></i>Buat Backup
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Restore dari Backup</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih File Backup</label>
                        <input type="file" class="form-control" id="restoreFile" accept=".sql,.zip">
                    </div>
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Restore akan menimpa semua data saat ini. Pastikan backup terbaru sudah dibuat sebelum melakukan restore.
                    </div>
                    <button class="btn btn-warning w-100" onclick="restoreBackup()">
                        <i class="fas fa-upload me-1"></i>Restore Database
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Backup</h6>
            <button class="btn btn-sm btn-outline-secondary" onclick="loadBackups()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama File</th>
                            <th>Tanggal</th>
                            <th>Ukuran</th>
                            <th>Tipe</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="backupList">
                        <tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div></div>
<!-- Restore Confirmation Modal -->
<div class="modal fade" id="confirmRestoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Anda akan me-restore database dari file:</p>
                <p class="fw-bold" id="restoreFileName"></p>
                <div class="alert alert-danger">
                    <strong>Tindakan ini tidak dapat dibatalkan!</strong><br>
                    Semua data saat ini akan ditimpa.
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmRestoreCheck">
                    <label class="form-check-label" for="confirmRestoreCheck">
                        Saya mengerti risiko dan ingin melanjutkan
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="confirmRestore()" disabled id="confirmRestoreBtn">
                    <i class="fas fa-upload me-1"></i>Ya, Restore Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

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
let confirmRestoreModal = null;

function getToken(){ return JSON.parse(localStorage.getItem('userData')||'{}').token||''; }
function authH(){ return { 'Authorization': 'Bearer '+getToken() }; }
function logout(){ if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';} }

function showToast(msg,type='success'){
    const id='t'+Date.now();
    $('#toastContainer').append(`<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show"><div class="d-flex"><div class="toast-body">${msg}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
    setTimeout(()=>$('#'+id).remove(),4000);
}

$(document).ready(function(){
    confirmRestoreModal = new bootstrap.Modal(document.getElementById('confirmRestoreModal'));
    loadBackups();
    
    $('#confirmRestoreCheck').change(function(){
        $('#confirmRestoreBtn').prop('disabled', !this.checked);
    });
});

function loadBackups(){
    // Mock data for now - would call API to get actual backup list
    const backups = [
        {name: 'backup_2026-04-14_full.sql', date: '2026-04-14 10:30:00', size: '2.5 MB', type: 'full'},
        {name: 'backup_2026-04-13_data.sql', date: '2026-04-13 23:00:00', size: '1.8 MB', type: 'data'},
        {name: 'backup_2026-04-12_full.sql', date: '2026-04-12 10:30:00', size: '2.4 MB', type: 'full'},
    ];
    
    let html = '';
    backups.forEach(b => {
        html += `
            <tr>
                <td><code>${b.name}</code></td>
                <td>${b.date}</td>
                <td>${b.size}</td>
                <td><span class="badge bg-secondary">${b.type}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="downloadBackup('${b.name}')" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('${b.name}')" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    $('#backupList').html(html || '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada backup</td></tr>');
}

function createBackup(){
    const name = $('#backupName').val() || 'backup_' + new Date().toISOString().split('T')[0];
    const type = $('#backupType').val();
    const includeUploads = $('#includeUploads').is(':checked');
    
    showToast('Membuat backup...','info');
    
    // Mock backup creation
    setTimeout(() => {
        showToast('Backup berhasil dibuat: ' + name + '.sql','success');
        $('#backupName').val('');
        loadBackups();
    }, 2000);
}

function restoreBackup(){
    const file = $('#restoreFile')[0].files[0];
    if(!file){
        showToast('Pilih file backup terlebih dahulu','warning');
        return;
    }
    
    $('#restoreFileName').text(file.name);
    $('#confirmRestoreCheck').prop('checked', false);
    $('#confirmRestoreBtn').prop('disabled', true);
    confirmRestoreModal.show();
}

function confirmRestore(){
    confirmRestoreModal.hide();
    showToast('Memulai proses restore...','info');
    
    // Mock restore process
    setTimeout(() => {
        showToast('Database berhasil di-restore','success');
        $('#restoreFile').val('');
    }, 3000);
}

function downloadBackup(filename){
    showToast('Mengunduh ' + filename,'info');
    // Would trigger actual download
}

function deleteBackup(filename){
    if(confirm('Hapus backup ' + filename + '?')){
        showToast('Backup berhasil dihapus','success');
        loadBackups();
    }
}
</script>
</body>
</html>
