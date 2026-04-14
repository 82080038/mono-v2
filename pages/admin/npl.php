<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring NPL - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'npl'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Monitoring NPL</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">NPL</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-outline-secondary btn-sm" onclick="load()"><i class="fas fa-sync me-1"></i>Refresh</button></div>
    </div>
    <div class="page-body">
        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Rasio NPL</div><div class="fs-2 fw-bold text-danger" id="kNplRatio">—%</div><small class="text-muted">Total portofolio</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pinjaman Macet</div><div class="fs-2 fw-bold text-danger" id="kNplCount">—</div><small class="text-muted">rekening</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Nilai Macet</div><div class="fs-5 fw-bold text-danger" id="kNplAmount">—</div><small class="text-muted">outstanding</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Telat Bayar</div><div class="fs-2 fw-bold text-warning" id="kOverdue">—</div><small class="text-muted">rekening</small></div></div></div>
        </div>
        <!-- Chart + Tabel -->
        <div class="row g-3 mb-4">
            <div class="col-md-5"><div class="card border-0 shadow-sm h-100"><div class="card-body"><h6 class="fw-semibold mb-3">Tren NPL (12 Bulan)</h6><canvas id="nplChart" height="200"></canvas></div></div></div>
            <div class="col-md-7"><div class="card border-0 shadow-sm h-100"><div class="card-body p-0"><div class="px-3 py-2 border-bottom fw-semibold small">Pinjaman Bermasalah</div><div class="table-responsive"><table class="table table-sm mb-0"><thead class="table-light"><tr><th>Anggota</th><th>Pokok</th><th>Sisa</th><th>Hari Telat</th><th>Kolektabilitas</th></tr></thead><tbody id="nplBody"><tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody></table></div></div></div></div>
        </div>
        <div class="row g-3">
            <div class="col-md-12"><div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="px-3 py-2 border-bottom fw-semibold small">Distribusi Kolektabilitas</div><div class="table-responsive"><table class="table mb-0" id="kolTable"><thead class="table-light"><tr><th>Kolektabilitas</th><th>Keterangan</th><th>Jumlah</th><th>Nilai</th><th>%</th></tr></thead><tbody id="kolBody"><tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody></table></div></div></div></div>
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
let nplChartInst;

function load(){
    // Load NPL trend
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'npl_trend'},headers:authH()}).done(res=>{
        if(!res.success)return;
        const d=res.data||[];
        if(nplChartInst)nplChartInst.destroy();
        nplChartInst=new Chart(document.getElementById('nplChart'),{type:'line',data:{labels:d.map(x=>x.month),datasets:[{label:'Rasio NPL (%)',data:d.map(x=>parseFloat(x.npl_ratio||0)),borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,.1)',fill:true,tension:.4}]},options:{plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{callback:v=>v+'%'}}}}});
    });
    // Load pinjaman bermasalah (overdue+npl)
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loans',status:'npl',limit:20},headers:authH()}).done(res=>{
        if(!res.success){$('#nplBody').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat</td></tr>');return;}
        const rows=res.data?.loans||res.data||[];
        if(!rows.length){$('#nplBody').html('<tr><td colspan="5" class="text-center text-muted">Tidak ada pinjaman NPL</td></tr>');return;}
        $('#nplBody').html(rows.map(l=>`<tr><td>${l.member_name||l.full_name||'-'}</td><td>${fmt(l.amount||l.principal_amount)}</td><td class="text-danger">${fmt(l.outstanding_balance||l.remaining_balance)}</td><td class="text-danger fw-bold">${l.days_overdue||'-'}</td><td><span class="badge bg-danger">NPL</span></td></tr>`).join(''));
        $('#kNplCount').text(rows.length);
        const totalNpl=rows.reduce((s,l)=>s+parseFloat(l.outstanding_balance||l.remaining_balance||0),0);
        $('#kNplAmount').text(fmt(totalNpl));
    });
    // Portfolio for ratio
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loan_portfolio'},headers:authH()}).done(res=>{
        if(!res.success)return;
        const d=res.data;
        const ratio=d.npl_ratio||((d.npl_count/Math.max(d.total_loans,1))*100).toFixed(2);
        $('#kNplRatio').text(ratio+'%');
        $('#kOverdue').text(d.overdue_count||0);
        buildKolTable(d);
    });
}

function buildKolTable(d){
    const kol=[
        {k:'1',ket:'Lancar',n:d.active_count||0,v:d.active_amount||0},
        {k:'2',ket:'Dalam Perhatian Khusus',n:d.dpk_count||0,v:d.dpk_amount||0},
        {k:'3',ket:'Kurang Lancar',n:d.overdue_count||0,v:d.overdue_amount||0},
        {k:'4',ket:'Diragukan',n:0,v:0},
        {k:'5',ket:'Macet (NPL)',n:d.npl_count||0,v:d.npl_amount||0},
    ];
    const total=kol.reduce((s,k)=>s+k.n,0)||1;
    $('#kolBody').html(kol.map(k=>`<tr><td><span class="badge ${k.k==='5'?'bg-danger':k.k==='3'?'bg-warning text-dark':k.k==='1'?'bg-success':'bg-secondary'}">K${k.k}</span></td><td>${k.ket}</td><td>${k.n}</td><td>${fmt(k.v)}</td><td>${((k.n/total)*100).toFixed(1)}%</td></tr>`).join(''));
}

$(function(){load();});
</script>
</body>
</html>
