<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risk & Fraud Detection - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
        .alert-row:hover{background:#fff8f8}
    </style>
</head>
<body>
<?php $activePage = 'risk-fraud'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Risk & Fraud Detection</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Risk & Fraud</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-outline-secondary btn-sm" onclick="load()"><i class="fas fa-sync me-1"></i>Refresh</button></div>
    </div>
    <div class="page-body">
        <!-- Risk Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6"><div class="card border-0 shadow-sm border-start border-danger border-4"><div class="card-body"><div class="text-muted small">Alert Aktif</div><div class="fs-2 fw-bold text-danger" id="kAlerts">—</div></div></div></div>
            <div class="col-md-3 col-6"><div class="card border-0 shadow-sm border-start border-warning border-4"><div class="card-body"><div class="text-muted small">Pinjaman Risiko Tinggi</div><div class="fs-2 fw-bold text-warning" id="kHighRisk">—</div></div></div></div>
            <div class="col-md-3 col-6"><div class="card border-0 shadow-sm border-start border-info border-4"><div class="card-body"><div class="text-muted small">Rasio NPL</div><div class="fs-2 fw-bold text-info" id="kNpl">—%</div></div></div></div>
            <div class="col-md-3 col-6"><div class="card border-0 shadow-sm border-start border-success border-4"><div class="card-body"><div class="text-muted small">Score Risiko Rata-rata</div><div class="fs-2 fw-bold text-success" id="kScore">—</div></div></div></div>
        </div>
        <!-- Risk Alerts -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="fas fa-bell text-danger me-2"></i>Alert Risiko Terkini</span>
                <span class="badge bg-danger" id="alertBadge">0</span>
            </div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Anggota</th><th>Tipe Alert</th><th>Keterangan</th><th>Level</th><th>Waktu</th></tr></thead>
                    <tbody id="alertBody"><tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                </table>
            </div></div>
        </div>
        <!-- Pinjaman Risiko Tinggi -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Pinjaman Risiko Tinggi (Overdue + NPL)</div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Anggota</th><th>Pokok</th><th>Sisa</th><th>Hari Telat</th><th>Kolektabilitas</th><th>Credit Score</th></tr></thead>
                    <tbody id="riskBody"><tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
                </table>
            </div></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/config.js"></script>
<script src="../../assets/js/auth-fixed.js"></script>
<script>
(function(){const u=JSON.parse(localStorage.getItem('userData')||'{}');if(!u.token){window.location.href='../../login.html';return;}const r=(u.role||'').toLowerCase().replace(' ','_');if(!['admin','super_admin','manager'].includes(r)){window.location.href='../../login.html';return;}$('#sidebarUserName').text(u.name||'Admin');$('#sidebarUserRole').text(u.role||'');})();
const BASE=(window.APP_CONFIG?.baseUrl||'')+'/api';
function getToken(){return JSON.parse(localStorage.getItem('userData')||'{}').token||'';}
function authH(){return{'Authorization':'Bearer '+getToken()};}
function logout(){if(confirm('Keluar?')){localStorage.removeItem('userData');window.location.href='../../login.html';}}
function fmt(n){return'Rp '+parseFloat(n||0).toLocaleString('id-ID');}

function load(){
    // Load portofolio untuk summary
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loan_portfolio'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data;
        const highRisk=(d.overdue_count||0)+(d.npl_count||0);
        $('#kHighRisk').text(highRisk);
        $('#kNpl').text((d.npl_ratio||((d.npl_count/Math.max(d.total_loans,1))*100).toFixed(1))+'%');
    });
    // Load notifications sebagai alerts
    $.ajax({url:BASE+'/notifications.php',method:'GET',data:{action:'get_notifications',limit:20},headers:authH()}).done(res=>{
        const items=res.data?.notifications||res.data||[];
        const riskAlerts=items.filter(n=>['overdue','npl','fraud','risk','alert'].some(k=>(n.type||n.notification_type||'').toLowerCase().includes(k)||(n.message||n.body||'').toLowerCase().includes(k)));
        $('#kAlerts').text(riskAlerts.length);$('#alertBadge').text(riskAlerts.length);
        if(!riskAlerts.length){$('#alertBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success me-2"></i>Tidak ada alert risiko aktif</td></tr>');return;}
        const levelBadge=n=>{const msg=(n.message||n.body||'').toLowerCase();return msg.includes('npl')||msg.includes('macet')?'<span class="badge bg-danger">TINGGI</span>':msg.includes('overdue')||msg.includes('telat')?'<span class="badge bg-warning text-dark">SEDANG</span>':'<span class="badge bg-info">RENDAH</span>';};
        $('#alertBody').html(riskAlerts.map(n=>`<tr class="alert-row"><td>${n.member_name||n.recipient||'-'}</td><td>${n.type||n.notification_type||'Sistem'}</td><td>${n.message||n.body||'-'}</td><td>${levelBadge(n)}</td><td class="text-muted small">${(n.created_at||'').substring(0,16)}</td></tr>`).join(''));
    }).fail(()=>{$('#kAlerts').text(0);$('#alertBody').html('<tr><td colspan="5" class="text-center text-muted py-3">Gagal memuat alert</td></tr>');});
    // Load pinjaman overdue + npl
    const risk=[];
    const doneCount={n:0};
    ['overdue','npl'].forEach(st=>{
        $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loans',status:st,limit:15},headers:authH()}).done(res=>{
            const rows=res.data?.loans||res.data||[];risk.push(...rows);
            doneCount.n++;
            if(doneCount.n===2){
                $('#kScore').text('—');
                if(!risk.length){$('#riskBody').html('<tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success me-2"></i>Tidak ada pinjaman risiko tinggi</td></tr>');return;}
                $('#riskBody').html(risk.map(l=>`<tr><td><strong>${l.member_name||l.full_name||'-'}</strong></td><td>${fmt(l.amount||l.principal_amount)}</td><td class="text-danger fw-bold">${fmt(l.outstanding_balance||l.remaining_balance)}</td><td class="text-danger">${l.days_overdue||'-'} hari</td><td>${l.status==='npl'?'<span class="badge bg-danger">K5 - Macet</span>':'<span class="badge bg-warning text-dark">K3 - Kurang Lancar</span>'}</td><td>${l.credit_score?`<span class="badge ${l.credit_score<500?'bg-danger':l.credit_score<700?'bg-warning text-dark':'bg-success'}">${l.credit_score}</span>`:'—'}</td></tr>`).join(''));
            }
        });
    });
}
$(function(){load();});
</script>
</body>
</html>
