<?php
/**
 * Sidebar partial untuk semua halaman admin.
 *
 * Cara pakai di halaman .php:
 *   <?php
 *     $activePage = 'dashboard'; // ganti sesuai halaman
 *     require __DIR__ . '/partials/sidebar.php';
 *   ?>
 *
 * Daftar nilai $activePage:
 *   dashboard, members, savings, loans, verifikasi, approval-workflow,
 *   accounting, laporan-shu, analytics, bi-analytics, reports, laporan-umum,
 *   npl, risk-fraud, capacity, users, settings, audit-log, role-access,
 *   system-config, backup-restore, live-tracking
 */
$activePage = $activePage ?? '';
function sidebarActive(string $page, string $current): string {
    return $page === $current ? ' active' : '';
}
?>
<nav id="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="fas fa-building text-white" style="font-size:.9rem"></i>
        </div>
        <div class="brand-text">KSP Lam Gabe Jaya<small>Admin Panel v2.0</small></div>
    </div>

    <div class="overflow-auto flex-grow-1 py-1">

        <div class="nav-section">Utama</div>
        <a href="dashboard.php" class="nav-link<?= sidebarActive('dashboard', $activePage) ?>">
            <i class="fas fa-tachometer-alt"></i>Dashboard
        </a>

        <div class="nav-section">Koperasi</div>
        <a href="members.php" class="nav-link<?= sidebarActive('members', $activePage) ?>">
            <i class="fas fa-users"></i>Anggota
        </a>
        <a href="savings.php" class="nav-link<?= sidebarActive('savings', $activePage) ?>">
            <i class="fas fa-piggy-bank"></i>Simpanan
        </a>
        <a href="loans.php" class="nav-link<?= sidebarActive('loans', $activePage) ?>">
            <i class="fas fa-hand-holding-usd"></i>Pinjaman
        </a>
        <a href="verifikasi.php" class="nav-link<?= sidebarActive('verifikasi', $activePage) ?>">
            <i class="fas fa-clipboard-check"></i>Verifikasi
        </a>

        <div class="nav-section">Phase 2 — Akuntansi</div>
        <a href="accounting.php" class="nav-link<?= sidebarActive('accounting', $activePage) ?>">
            <i class="fas fa-book"></i>Jurnal &amp; Laporan
        </a>
        <a href="laporan-shu.php" class="nav-link<?= sidebarActive('laporan-shu', $activePage) ?>">
            <i class="fas fa-file-invoice-dollar"></i>SHU &amp; Distribusi
        </a>
        <a href="approval-workflow.php" class="nav-link<?= sidebarActive('approval-workflow', $activePage) ?>">
            <i class="fas fa-tasks"></i>Approval Workflow
            <span class="badge bg-warning text-dark" id="sidebarPendingBadge" style="display:none">0</span>
        </a>
        <a href="audit-log.php" class="nav-link<?= sidebarActive('audit-log', $activePage) ?>">
            <i class="fas fa-shield-alt"></i>Audit Trail
        </a>

        <div class="nav-section">Phase 3 — Analytics</div>
        <a href="analytics.php" class="nav-link<?= sidebarActive('analytics', $activePage) ?>">
            <i class="fas fa-chart-line"></i>Analytics &amp; Statistik
        </a>
        <a href="bi-analytics.php" class="nav-link<?= sidebarActive('bi-analytics', $activePage) ?>">
            <i class="fas fa-chart-bar"></i>BI Analytics
        </a>

        <div class="nav-section">Laporan</div>
        <a href="reports.php" class="nav-link<?= sidebarActive('reports', $activePage) ?>">
            <i class="fas fa-file-export"></i>Laporan Umum
        </a>
        <a href="laporan-umum.php" class="nav-link<?= sidebarActive('laporan-umum', $activePage) ?>">
            <i class="fas fa-file-alt"></i>Laporan Operasional
        </a>
        <a href="npl.php" class="nav-link<?= sidebarActive('npl', $activePage) ?>">
            <i class="fas fa-exclamation-triangle"></i>Monitoring NPL
        </a>
        <a href="risk-fraud.php" class="nav-link<?= sidebarActive('risk-fraud', $activePage) ?>">
            <i class="fas fa-user-shield"></i>Risk &amp; Fraud
        </a>
        <a href="capacity.php" class="nav-link<?= sidebarActive('capacity', $activePage) ?>">
            <i class="fas fa-sliders-h"></i>Kapasitas
        </a>

        <div class="nav-section">Sistem</div>
        <a href="users.php" class="nav-link<?= sidebarActive('users', $activePage) ?>">
            <i class="fas fa-user-cog"></i>Manajemen User
        </a>
        <a href="role-access.php" class="nav-link<?= sidebarActive('role-access', $activePage) ?>">
            <i class="fas fa-key"></i>Role &amp; Akses
        </a>
        <a href="system-config.php" class="nav-link<?= sidebarActive('system-config', $activePage) ?>">
            <i class="fas fa-cog"></i>Konfigurasi Sistem
        </a>
        <a href="backup-restore.php" class="nav-link<?= sidebarActive('backup-restore', $activePage) ?>">
            <i class="fas fa-database"></i>Backup &amp; Restore
        </a>
        <a href="settings.php" class="nav-link<?= sidebarActive('settings', $activePage) ?>">
            <i class="fas fa-sliders-h"></i>Pengaturan
        </a>

    </div>

    <div id="userCard">
        <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-user text-white" style="font-size:.85rem"></i>
            </div>
            <div style="overflow:hidden">
                <div class="text-white fw-semibold small text-truncate" id="sidebarUserName">—</div>
                <div class="text-white opacity-50" style="font-size:.7rem" id="sidebarUserRole">—</div>
            </div>
            <button class="btn btn-sm btn-outline-light ms-auto py-0 px-2" onclick="logout()" title="Keluar">
                <i class="fas fa-sign-out-alt" style="font-size:.75rem"></i>
            </button>
        </div>
    </div>
</nav>
