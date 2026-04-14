<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BI & KPI Analytics - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        #mainContent{margin-left:0}
        .kpi-card{border-left:4px solid transparent;border-radius:8px}
    </style>
</head>
<body>
<?php $activePage = 'bi-analytics'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">BI & KPI Analytics</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">BI & KPI</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-outline-secondary btn-sm" onclick="loadAll()"><i class="fas fa-sync me-1"></i>Refresh</button></div>
    </div>
    <div class="page-body">
        <!-- KPI Scorecard -->
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#2563eb"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">TOTAL ANGGOTA</div><div class="fs-4 fw-bold text-primary" id="biMembers">—</div></div></div></div>
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#10b981"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">DANA SIMPANAN</div><div class="fs-6 fw-bold text-success" id="biSavings">—</div></div></div></div>
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#f59e0b"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">SISA PINJAMAN</div><div class="fs-6 fw-bold text-warning" id="biLoans">—</div></div></div></div>
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#ef4444"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">RASIO NPL</div><div class="fs-4 fw-bold text-danger" id="biNpl">—%</div></div></div></div>
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#8b5cf6"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">TRANSAKSI HARI INI</div><div class="fs-4 fw-bold" style="color:#8b5cf6" id="biTxn">—</div></div></div></div>
            <div class="col-md-2 col-6"><div class="card border-0 shadow-sm kpi-card" style="border-left-color:#06b6d4"><div class="card-body py-2 px-3"><div class="text-muted" style="font-size:.7rem">ANGGOTA BARU/BLN</div><div class="fs-4 fw-bold" style="color:#06b6d4" id="biNewMem">—</div></div></div></div>
        </div>
        <!-- Charts Row 1 -->
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-semibold mb-3">Tren Pertumbuhan Anggota</h6><canvas id="memberChart" height="180"></canvas></div></div></div>
            <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-semibold mb-3">Tren Simpanan & Pinjaman</h6><canvas id="savLoanChart" height="180"></canvas></div></div></div>
        </div>
        <!-- Charts Row 2 -->
        <div class="row g-3">
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-semibold mb-3">Distribusi Status Pinjaman</h6><canvas id="loanStatusChart" height="220"></canvas></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-semibold mb-3">Top 5 Peminjam</h6><canvas id="topBorrChart" height="220"></canvas></div></div></div>
            <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-semibold mb-3">Tren Transaksi Bulanan</h6><canvas id="txnChart" height="220"></canvas></div></div></div>
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
const charts={};
function mkChart(id,cfg){if(charts[id]){charts[id].destroy();}charts[id]=new Chart(document.getElementById(id),cfg);}

function loadAll(){
    // Summary
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'summary'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data;
        $('#biMembers').text(d.total_members||'—');
        $('#biSavings').text(fmt(d.total_savings));
        $('#biLoans').text(fmt(d.total_outstanding));
        $('#biNpl').text((d.npl_ratio||0)+'%');
        $('#biTxn').text(d.txn_today||0);
        $('#biNewMem').text(d.new_members_this_month||0);
    });
    // Member growth
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'member_growth'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data||[];
        mkChart('memberChart',{type:'line',data:{labels:d.map(x=>x.month),datasets:[{label:'Anggota Baru',data:d.map(x=>x.new_members),borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,.1)',fill:true,tension:.4}]},options:{plugins:{legend:{position:'bottom'}}}});
    });
    // Savings trend
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'savings_trend'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data||[];
        mkChart('savLoanChart',{type:'bar',data:{labels:d.map(x=>x.month),datasets:[{label:'Simpanan',data:d.map(x=>x.deposits),backgroundColor:'rgba(16,185,129,.6)'},{label:'Penarikan',data:d.map(x=>x.withdrawals),backgroundColor:'rgba(239,68,68,.6)'}]},options:{plugins:{legend:{position:'bottom'}},scales:{y:{ticks:{callback:v=>v>=1e6?(v/1e6).toFixed(1)+'jt':v}}}}});
    });
    // Loan by status
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'loan_by_status'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data||[];
        mkChart('loanStatusChart',{type:'doughnut',data:{labels:d.map(x=>x.status),datasets:[{data:d.map(x=>x.count),backgroundColor:['#2563eb','#10b981','#f59e0b','#ef4444','#6b7280','#8b5cf6']}]},options:{plugins:{legend:{position:'bottom'}}}});
    });
    // Top borrowers
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'top_borrowers'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data||[];
        mkChart('topBorrChart',{type:'bar',data:{labels:d.map(x=>x.member_name||x.name),datasets:[{label:'Sisa Pinjaman',data:d.map(x=>x.outstanding),backgroundColor:'rgba(245,158,11,.7)'}]},options:{indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{ticks:{callback:v=>v>=1e6?(v/1e6).toFixed(1)+'jt':v}}}}});
    });
    // Txn trend
    $.ajax({url:BASE+'/analytics.php',method:'GET',data:{action:'txn_trend'},headers:authH()}).done(res=>{
        if(!res.success)return;const d=res.data||[];
        mkChart('txnChart',{type:'line',data:{labels:d.map(x=>x.month),datasets:[{label:'Jumlah Transaksi',data:d.map(x=>x.count),borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,.1)',fill:true,tension:.4}]},options:{plugins:{legend:{position:'bottom'}}}});
    });
}
$(function(){loadAll();});
</script>
</body>
</html>
