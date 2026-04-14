<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capacity Planning - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        #mainContent{margin-left:0}
        .progress-label{font-size:.75rem;font-weight:600}
    </style>
</head>
<body>
<?php $activePage = 'capacity'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Capacity Planning</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Capacity Planning</li></ol></nav></div>
    </div>
    <div class="page-body">
        <!-- Utilization -->
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="fas fa-users text-primary me-2"></i>Utilisasi Kapasitas Anggota</h6>
                <div class="d-flex justify-content-between mb-1"><span class="progress-label">Anggota Aktif</span><span class="progress-label text-primary" id="capMembers">—/—</span></div>
                <div class="progress mb-3" style="height:12px"><div class="progress-bar bg-primary" id="capMembersBar" role="progressbar" style="width:0%"></div></div>
                <h6 class="fw-semibold mb-3"><i class="fas fa-hand-holding-usd text-warning me-2"></i>Utilisasi Dana Pinjaman</h6>
                <div class="d-flex justify-content-between mb-1"><span class="progress-label">Dana Tersalurkan</span><span class="progress-label text-warning" id="capLoans">—%</span></div>
                <div class="progress mb-3" style="height:12px"><div class="progress-bar bg-warning" id="capLoansBar" role="progressbar" style="width:0%"></div></div>
                <h6 class="fw-semibold mb-3"><i class="fas fa-piggy-bank text-success me-2"></i>Utilisasi Simpanan</h6>
                <div class="d-flex justify-content-between mb-1"><span class="progress-label">Dana Simpanan Aktif</span><span class="progress-label text-success" id="capSavings">—</span></div>
                <div class="progress" style="height:12px"><div class="progress-bar bg-success" id="capSavingsBar" role="progressbar" style="width:70%"></div></div>
            </div></div></div>
            <div class="col-md-8"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-semibold mb-3">Proyeksi Pertumbuhan (12 Bulan ke Depan)</h6>
                <canvas id="projectionChart" height="220"></canvas>
            </div></div></div>
        </div>
        <!-- Metrics Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-table me-2 text-primary"></i>Metrik Kapasitas Koperasi</div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Metrik</th><th>Nilai Saat Ini</th><th>Target</th><th>Status</th><th>Catatan</th></tr></thead>
                    <tbody id="metricsBody"><tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat...</td></tr></tbody>
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

$(function(){
    // Load summary data
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'summary'},headers:authH()}).done(res=>{
        if(!res.success)return;
        const d=res.data;
        const maxMem=500; // target anggota
        const memPct=Math.min(100,((d.total_members||0)/maxMem*100)).toFixed(0);
        $('#capMembers').text(`${d.total_members||0}/${maxMem}`);
        $('#capMembersBar').css('width',memPct+'%');
        const loanPct=Math.min(100,((d.total_outstanding||0)/(d.total_savings||1)*100)).toFixed(0);
        $('#capLoans').text(loanPct+'%');
        $('#capLoansBar').css('width',loanPct+'%').addClass(loanPct>80?'bg-danger':loanPct>60?'bg-warning':'bg-success');
        $('#capSavings').text(fmt(d.total_savings));

        // Metrics Table
        const metrics=[
            {m:'Total Anggota',v:d.total_members||0,t:maxMem,ok:(d.total_members||0)>=maxMem*0.8,note:'Target: '+maxMem+' anggota'},
            {m:'Dana Simpanan',v:fmt(d.total_savings),t:'—',ok:true,note:'Sumber dana utama'},
            {m:'Pinjaman Tersalurkan',v:fmt(d.total_outstanding),t:'—',ok:true,note:'Utilisasi dana'},
            {m:'Rasio NPL',v:(d.npl_ratio||0)+'%',t:'< 5%',ok:(d.npl_ratio||0)<5,note:'Standar OJK < 5%'},
            {m:'Anggota Baru/Bulan',v:d.new_members_this_month||0,t:'≥ 10',ok:(d.new_members_this_month||0)>=10,note:'Pertumbuhan organik'},
        ];
        $('#metricsBody').html(metrics.map(m=>`<tr><td class="fw-semibold">${m.m}</td><td>${m.v}</td><td>${m.t}</td><td>${m.ok?'<span class="badge bg-success">Baik</span>':'<span class="badge bg-warning text-dark">Perlu Perhatian</span>'}</td><td class="text-muted small">${m.note}</td></tr>`).join(''));
    });
    // Projection chart
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'member_growth'},headers:authH()}).done(res=>{
        const d=res.data||[];
        const last=d.slice(-6);
        // simple linear projection
        const vals=last.map(x=>parseInt(x.new_members||x.cumulative||0));
        const avg=vals.reduce((a,b)=>a+b,0)/Math.max(vals.length,1);
        const months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const nowM=new Date().getMonth();
        const projLabels=Array.from({length:12},(_,i)=>months[(nowM+i)%12]);
        const histData=Array(12).fill(null);
        const projData=Array(12).fill(null);
        last.forEach((x,i)=>histData[i]=parseInt(x.new_members||0));
        Array.from({length:12},(_,i)=>projData[i]=Math.round(avg*(1+i*0.02)));
        new Chart(document.getElementById('projectionChart'),{type:'line',data:{labels:projLabels,datasets:[{label:'Historis',data:histData,borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,.1)',fill:true,tension:.4},{label:'Proyeksi',data:projData,borderColor:'#10b981',backgroundColor:'rgba(16,185,129,.05)',borderDash:[5,5],tension:.4}]},options:{plugins:{legend:{position:'bottom'}}}});
    });
});
</script>
</body>
</html>
