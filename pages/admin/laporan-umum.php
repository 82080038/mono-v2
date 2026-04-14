<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Operasional - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/sidebar.css" rel="stylesheet">
    <style>
        #mainContent{margin-left:0}
    </style>
</head>
<body>
<?php $activePage = 'laporan-umum'; require __DIR__ . '/partials/sidebar.php'; ?>
<div id="mainContent">
    <div class="topbar">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>
        <div><div style="font-weight:700;font-size:1.1rem;color:#1e293b">Laporan Operasional</div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0" style="font-size:.8rem"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Lap. Operasional</li></ol></nav></div>
        <div class="ms-auto"><button class="btn btn-outline-secondary btn-sm" onclick="load()"><i class="fas fa-sync me-1"></i>Refresh</button></div>
    </div>
    <div class="page-body">
        <!-- Filter Periode -->
        <div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto"><label class="form-label mb-0 small fw-semibold">Periode:</label></div>
                <div class="col-auto"><input type="date" class="form-control form-control-sm" id="dateFrom" onchange="load()"></div>
                <div class="col-auto"><span class="text-muted small">s/d</span></div>
                <div class="col-auto"><input type="date" class="form-control form-control-sm" id="dateTo" onchange="load()"></div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-secondary" onclick="setPeriod('today')">Hari Ini</button>
                    <button class="btn btn-sm btn-outline-secondary ms-1" onclick="setPeriod('week')">Minggu Ini</button>
                    <button class="btn btn-sm btn-outline-secondary ms-1" onclick="setPeriod('month')">Bulan Ini</button>
                </div>
            </div>
        </div></div>
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Transaksi Simpanan</div><div class="fs-5 fw-bold text-success" id="sTxnSavings">—</div><div class="text-muted" style="font-size:.75rem" id="sTxnSavingsCount">— transaksi</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Transaksi Pinjaman</div><div class="fs-5 fw-bold text-warning" id="sTxnLoans">—</div><div class="text-muted" style="font-size:.75rem" id="sTxnLoansCount">— transaksi</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Pendapatan Bunga</div><div class="fs-5 fw-bold text-primary" id="sInterest">—</div><div class="text-muted" style="font-size:.75rem">estimasi</div></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Anggota Baru</div><div class="fs-2 fw-bold text-info" id="sNewMembers">—</div><div class="text-muted" style="font-size:.75rem">dalam periode</div></div></div></div>
        </div>
        <!-- Recent Transactions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-exchange-alt me-2 text-primary"></i>Transaksi Simpanan Terbaru</div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Tanggal</th><th>Anggota</th><th>No. Rekening</th><th>Tipe</th><th>Jumlah</th><th>Saldo Akhir</th></tr></thead>
                    <tbody id="savingsTxnBody"><tr><td colspan="6" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Memuat...</td></tr></tbody>
                </table>
            </div></div>
        </div>
        <!-- Pinjaman Cair Periode -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-money-bill-wave me-2 text-warning"></i>Pinjaman Dicairkan dalam Periode</div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Tanggal Cair</th><th>Anggota</th><th>Jumlah</th><th>Tenor</th><th>Status</th></tr></thead>
                    <tbody id="loansDisbBody"><tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Memuat...</td></tr></tbody>
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

function setPeriod(p){
    const now=new Date();let from=new Date();
    if(p==='today'){from=new Date();}
    else if(p==='week'){from.setDate(now.getDate()-7);}
    else if(p==='month'){from=new Date(now.getFullYear(),now.getMonth(),1);}
    $('#dateFrom').val(from.toISOString().substring(0,10));
    $('#dateTo').val(now.toISOString().substring(0,10));
    load();
}

function load(){
    const from=$('#dateFrom').val();const to=$('#dateTo').val();
    // Load simpanan transactions
    $.ajax({url:BASE+'/savings.php',method:'GET',data:{action:'get_transactions',date_from:from,date_to:to,limit:20},headers:authH()}).done(res=>{
        const rows=res.data?.transactions||res.data||[];
        if(!rows.length){$('#savingsTxnBody').html('<tr><td colspan="6" class="text-center py-3 text-muted">Tidak ada transaksi</td></tr>');$('#sTxnSavings').text(fmt(0));$('#sTxnSavingsCount').text('0 transaksi');return;}
        const deposits=rows.filter(t=>t.transaction_type==='Deposit'||t.type==='deposit');
        const totalDep=deposits.reduce((s,t)=>s+parseFloat(t.amount||0),0);
        $('#sTxnSavings').text(fmt(totalDep));$('#sTxnSavingsCount').text(rows.length+' transaksi');
        $('#savingsTxnBody').html(rows.map(t=>`<tr><td class="text-muted small">${(t.transaction_date||t.created_at||'').substring(0,10)}</td><td>${t.member_name||t.full_name||'-'}</td><td><span class="badge bg-light text-dark border">${t.account_number||'-'}</span></td><td><span class="badge ${t.transaction_type==='Deposit'?'bg-success':'bg-warning text-dark'}">${t.transaction_type||t.type||'-'}</span></td><td class="${t.transaction_type==='Deposit'?'text-success':'text-danger'}">${fmt(t.amount)}</td><td>${fmt(t.balance_after||0)}</td></tr>`).join(''));
    }).fail(()=>$('#savingsTxnBody').html('<tr><td colspan="6" class="text-center py-3 text-muted">Gagal memuat</td></tr>'));
    // Load pinjaman cair
    $.ajax({url:BASE+'/loans.php',method:'GET',data:{action:'get_loans',status:'disbursed',limit:15},headers:authH()}).done(res=>{
        const rows=res.data?.loans||res.data||[];
        const total=rows.reduce((s,l)=>s+parseFloat(l.amount||l.principal_amount||0),0);
        $('#sTxnLoans').text(fmt(total));$('#sTxnLoansCount').text(rows.length+' pinjaman');
        if(!rows.length){$('#loansDisbBody').html('<tr><td colspan="5" class="text-center py-3 text-muted">Tidak ada pinjaman cair</td></tr>');return;}
        $('#loansDisbBody').html(rows.map(l=>`<tr><td class="text-muted small">${(l.disbursement_date||l.updated_at||'').substring(0,10)}</td><td><strong>${l.member_name||l.full_name||'-'}</strong></td><td>${fmt(l.amount||l.principal_amount)}</td><td>${l.tenor||l.duration_months||'—'} bln</td><td><span class="badge bg-primary">Cair</span></td></tr>`).join(''));
    }).fail(()=>$('#loansDisbBody').html('<tr><td colspan="5" class="text-center py-3 text-muted">Gagal memuat</td></tr>'));
    // Members stats
    $.ajax({url:BASE+'/members.php',method:'GET',data:{action:'get_members',limit:1},headers:authH()}).done(res=>{
        const meta=res.data?.pagination||{};$('#sNewMembers').text(meta.total||res.data?.length||'—');
        $('#sInterest').text(fmt((meta.total||0)*50000));
    });
}

// Init: bulan ini
const now=new Date();
$('#dateFrom').val(new Date(now.getFullYear(),now.getMonth(),1).toISOString().substring(0,10));
$('#dateTo').val(now.toISOString().substring(0,10));
$(function(){load();});
</script>
</body>
</html>
