#!/usr/bin/env node

/**
 * Auto-Fix System Issues
 * Fix all identified issues from testing
 */

const fs = require('fs');
const path = require('path');

class SystemFixer {
    constructor() {
        this.projectRoot = '/opt/lampp/htdocs/mono-v2';
        this.fixes = [];
    }

    async fixAllIssues() {
        console.log('🔧 Starting Auto-Fix for All System Issues...\n');
        
        // Fix 1: Add missing charset meta tags
        await this.fixMissingCharset();
        
        // Fix 2: Add jQuery to pages that need it
        await this.addJQueryToPages();
        
        // Fix 3: Add dashboard headers and sidebars
        await this.addDashboardComponents();
        
        // Fix 4: Add Bootstrap to pages that need it
        await this.addBootstrapToPages();
        
        // Fix 5: Add Font Awesome to pages that need it
        await this.addFontAwesomeToPages();
        
        console.log('\n🎉 All fixes completed!');
        console.log(`📊 Total fixes applied: ${this.fixes.length}`);
        
        return this.fixes;
    }

    async fixMissingCharset() {
        console.log('🔧 Fix 1: Adding missing charset meta tags...');
        
        const htmlFiles = this.getAllHtmlFiles();
        
        for (const file of htmlFiles) {
            try {
                let content = fs.readFileSync(file, 'utf8');
                
                if (!content.includes('charset')) {
                    const metaCharset = '<meta charset="UTF-8">';
                    
                    // Add after <head> or before </head>
                    if (content.includes('<head>')) {
                        content = content.replace('<head>', `<head>\n    ${metaCharset}`);
                    } else if (content.includes('</head>')) {
                        content = content.replace('</head>', `${metaCharset}\n    </head>`);
                    } else {
                        // Add at the beginning if no head tag
                        content = `${metaCharset}\n${content}`;
                    }
                    
                    fs.writeFileSync(file, content);
                    this.fixes.push({
                        type: 'added_charset',
                        file: file,
                        success: true
                    });
                    
                    console.log(`   ✅ Added charset to ${path.relative(this.projectRoot, file)}`);
                }
            } catch (error) {
                console.log(`   ❌ Error fixing charset in ${file}: ${error.message}`);
            }
        }
    }

    async addJQueryToPages() {
        console.log('\n🔧 Fix 2: Adding jQuery to pages...');
        
        const pagesNeedingJQuery = [
            'index.html',
            'pages/admin/members.html',
            'pages/admin/loans.html',
            'pages/admin/reports.html'
        ];
        
        for (const page of pagesNeedingJQuery) {
            const filePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(filePath)) {
                try {
                    let content = fs.readFileSync(filePath, 'utf8');
                    
                    if (!content.includes('jquery')) {
                        const jqueryScript = '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
                        
                        // Add before </head>
                        content = content.replace('</head>', `${jqueryScript}\n    </head>`);
                        
                        fs.writeFileSync(filePath, content);
                        this.fixes.push({
                            type: 'added_jquery',
                            file: filePath,
                            success: true
                        });
                        
                        console.log(`   ✅ Added jQuery to ${page}`);
                    }
                } catch (error) {
                    console.log(`   ❌ Error adding jQuery to ${page}: ${error.message}`);
                }
            }
        }
    }

    async addBootstrapToPages() {
        console.log('\n🔧 Fix 3: Adding Bootstrap to pages...');
        
        const pagesNeedingBootstrap = [
            'index.html',
            'pages/admin/members.html',
            'pages/admin/loans.html',
            'pages/admin/reports.html'
        ];
        
        for (const page of pagesNeedingBootstrap) {
            const filePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(filePath)) {
                try {
                    let content = fs.readFileSync(filePath, 'utf8');
                    
                    if (!content.includes('bootstrap')) {
                        const bootstrapCSS = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
                        const bootstrapJS = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
                        
                        // Add CSS before </head>
                        content = content.replace('</head>', `${bootstrapCSS}\n    </head>`);
                        
                        // Add JS before </body>
                        content = content.replace('</body>', `${bootstrapJS}\n</body>`);
                        
                        fs.writeFileSync(filePath, content);
                        this.fixes.push({
                            type: 'added_bootstrap',
                            file: filePath,
                            success: true
                        });
                        
                        console.log(`   ✅ Added Bootstrap to ${page}`);
                    }
                } catch (error) {
                    console.log(`   ❌ Error adding Bootstrap to ${page}: ${error.message}`);
                }
            }
        }
    }

    async addFontAwesomeToPages() {
        console.log('\n🔧 Fix 4: Adding Font Awesome to pages...');
        
        const pagesNeedingFA = [
            'index.html',
            'pages/admin/members.html',
            'pages/admin/loans.html',
            'pages/admin/reports.html'
        ];
        
        for (const page of pagesNeedingFA) {
            const filePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(filePath)) {
                try {
                    let content = fs.readFileSync(filePath, 'utf8');
                    
                    if (!content.includes('font-awesome')) {
                        const fontAwesomeCSS = '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">';
                        
                        // Add before </head>
                        content = content.replace('</head>', `${fontAwesomeCSS}\n    </head>`);
                        
                        fs.writeFileSync(filePath, content);
                        this.fixes.push({
                            type: 'added_fontawesome',
                            file: filePath,
                            success: true
                        });
                        
                        console.log(`   ✅ Added Font Awesome to ${page}`);
                    }
                } catch (error) {
                    console.log(`   ❌ Error adding Font Awesome to ${page}: ${error.message}`);
                }
            }
        }
    }

    async addDashboardComponents() {
        console.log('\n🔧 Fix 5: Adding dashboard headers and sidebars...');
        
        const dashboardPages = [
            'pages/admin/dashboard.html',
            'pages/staff/dashboard.html',
            'pages/member/dashboard.html'
        ];
        
        for (const page of dashboardPages) {
            const filePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(filePath)) {
                try {
                    let content = fs.readFileSync(filePath, 'utf8');
                    let modified = false;
                    
                    // Add dashboard header
                    if (!content.includes('dashboard-header')) {
                        const headerHTML = this.getDashboardHeaderHTML();
                        
                        // Add after <body> or before main content
                        if (content.includes('<body>')) {
                            content = content.replace('<body>', `<body>\n        ${headerHTML}`);
                            modified = true;
                        } else if (content.includes('<div class="container')) {
                            content = content.replace('<div class="container', `${headerHTML}\n        <div class="container`);
                            modified = true;
                        }
                        
                        if (modified) {
                            this.fixes.push({
                                type: 'added_dashboard_header',
                                file: filePath,
                                success: true
                            });
                            console.log(`   ✅ Added dashboard header to ${page}`);
                        }
                    }
                    
                    // Add dashboard sidebar
                    if (!content.includes('dashboard-sidebar')) {
                        const sidebarHTML = this.getDashboardSidebarHTML();
                        
                        // Add after header or before main content
                        if (content.includes('dashboard-header')) {
                            content = content.replace('</div>\n        <div class="dashboard-content', `</div>\n        ${sidebarHTML}\n        <div class="dashboard-content`);
                            modified = true;
                        } else if (content.includes('<div class="container')) {
                            content = content.replace('<div class="container', `${sidebarHTML}\n        <div class="container`);
                            modified = true;
                        }
                        
                        if (modified) {
                            this.fixes.push({
                                type: 'added_dashboard_sidebar',
                                file: filePath,
                                success: true
                            });
                            console.log(`   ✅ Added dashboard sidebar to ${page}`);
                        }
                    }
                    
                    if (modified) {
                        fs.writeFileSync(filePath, content);
                    }
                    
                } catch (error) {
                    console.log(`   ❌ Error adding dashboard components to ${page}: ${error.message}`);
                }
            }
        }
    }

    getDashboardHeaderHTML() {
        return `
<!-- Dashboard Header -->
<div class="dashboard-header bg-primary text-white py-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h1>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <button class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i> New
                    </button>
                    <button class="btn btn-outline-light btn-sm">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <button class="btn btn-outline-light btn-sm">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>`;
    }

    getDashboardSidebarHTML() {
        return `
<!-- Dashboard Sidebar -->
<div class="dashboard-sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
    <div class="sidebar-content p-3">
        <div class="sidebar-header text-center mb-4">
            <h5 class="mb-0">
                <i class="fas fa-university me-2"></i>
                KSP Lam Gabe Jaya
            </h5>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active text-white" href="dashboard.html">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="members.html">
                        <i class="fas fa-users me-2"></i> Anggota
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="loans.html">
                        <i class="fas fa-hand-holding-usd me-2"></i> Pinjaman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="savings.html">
                        <i class="fas fa-piggy-bank me-2"></i> Simpanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="transactions.html">
                        <i class="fas fa-exchange-alt me-2"></i> Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="reports.html">
                        <i class="fas fa-chart-bar me-2"></i> Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="settings.html">
                        <i class="fas fa-cog me-2"></i> Pengaturan
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Adjust main content for sidebar -->
<style>
    .dashboard-content {
        margin-left: 250px;
        padding: 20px;
    }
    @media (max-width: 768px) {
        .dashboard-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        .dashboard-content {
            margin-left: 0;
        }
        .dashboard-sidebar.show {
            transform: translateX(0);
        }
    }
</style>`;
    }

    getAllHtmlFiles() {
        const htmlFiles = [];
        
        function findHtmlFiles(dir) {
            try {
                const files = fs.readdirSync(dir);
                
                for (const file of files) {
                    const filePath = path.join(dir, file);
                    const stat = fs.statSync(filePath);
                    
                    if (stat.isDirectory()) {
                        findHtmlFiles(filePath);
                    } else if (file.endsWith('.html')) {
                        htmlFiles.push(filePath);
                    }
                }
            } catch (error) {
                // Skip directories that can't be read
            }
        }
        
        findHtmlFiles(this.projectRoot);
        return htmlFiles;
    }

    generateReport() {
        console.log('\n📊 Generating Fix Report...');
        
        const report = {
            summary: {
                totalFixes: this.fixes.length,
                fixesByType: this.fixes.reduce((types, fix) => {
                    types[fix.type] = (types[fix.type] || 0) + 1;
                    return types;
                }, {}),
                timestamp: new Date().toISOString()
            },
            fixes: this.fixes
        };
        
        // Save report
        const reportPath = path.join(this.projectRoot, 'system-fixes-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Fix report saved: ${reportPath}`);
        console.log(`📊 Total fixes applied: ${report.summary.totalFixes}`);
        
        Object.entries(report.summary.fixesByType).forEach(([type, count]) => {
            console.log(`   ${type}: ${count}`);
        });
        
        return report;
    }
}

// Main execution
async function main() {
    const fixer = new SystemFixer();
    
    try {
        const fixes = await fixer.fixAllIssues();
        const report = fixer.generateReport();
        
        return { fixes, report };
        
    } catch (error) {
        console.error('❌ Fix process failed:', error.message);
        throw error;
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = SystemFixer;
