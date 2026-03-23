// Error Handling Wrapper
(function() {
    /**
 * Enhanced Bootstrap & jQuery Implementation for KSP Lam Gabe Jaya
 * Advanced UI Components and Interactions - Fixed Version
 */

class EnhancedUI {
    constructor() {
        this.init();
    }

    init() {
        this.enhanceDashboard();
        this.enhanceTables();
        this.enhanceForms();
        this.enhanceModals();
        this.enhanceNotifications();
        this.enhanceCharts();
    }

    // Enhanced Dashboard with Bootstrap Components
    enhanceDashboard() {
        this.enhancedStatCards();
        this.enhancedProgressBars();
        this.enhanceCharts();
        this.startRealTimeUpdates();
    }

    enhancedStatCards() {
        $('.stat-card').each(function() {
            const $card = $(this);
            
            $card.hover(
                function() {
                    $(this).addClass('shadow-lg').addClass('border-primary');
                    $(this).find('.fa').addClass('fa-bounce');
                },
                function() {
                    $(this).removeClass('shadow-lg').removeClass('border-primary');
                    $(this).find('.fa').removeClass('fa-bounce');
                }
            );
            
            $card.on('click', function() {
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => {
                    $(this).removeClass('animate__animated animate__pulse');
                }, 1000);
            });
        });
    }

    enhancedProgressBars() {
        $('.progress-bar').each(function() {
            const $bar = $(this);
            const width = $bar.attr('style')?.match(/width:\s*(\d+%)/);
            
            if (width) {
                $bar.css('width', '0%');
                setTimeout(() => {
                    $bar.css('width', width[1]);
                }, 100);
            }
            
            if ($bar.hasClass('progress-bar-striped')) {
                $bar.addClass('progress-bar-animated');
            }
        });
    }

    // Enhanced Tables with Bootstrap
    enhanceTables() {
        this.enhanceDataTable();
        this.enhanceTableSearch();
        this.enhanceTableSorting();
        this.enhanceTablePagination();
    }

    enhanceDataTable() {
        $('table').addClass('table table-hover table-striped');
        $('table').wrap('<div class="table-responsive"></div>');
        $('thead').addClass('table-dark');
        
        $('tbody tr').hover(
            function() {
                $(this).addClass('table-active');
            },
            function() {
                $(this).removeClass('table-active');
            }
        );
    }

    enhanceTableSearch() {
        $('.table-responsive').each(function() {
            const $table = $(this).find('table');
            const tableId = $table.attr('id') || 'table-' + Math.random().toString(36).substr(2, 9);
            $table.attr('id', tableId);
            
            const searchHtml = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control table-search" 
                                   placeholder="Search table..." 
                                   data-table="${tableId}">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm table-export" 
                                    data-table="${tableId}">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm table-refresh" 
                                    data-table="${tableId}">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $(this).before(searchHtml);
        });
        
        $('.table-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const tableId = $(this).data('table');
            const $table = $('#' + tableId);
            
            $table.find('tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm));
            });
        });
        
        $('.table-export').on('click', function() {
            const tableId = $(this).data('table');
            this.exportTable(tableId);
        }.bind(this));
        
        $('.table-refresh').on('click', function() {
            const $btn = $(this);
            const $icon = $btn.find('i');
            
            $icon.addClass('fa-spin');
            
            setTimeout(() => {
                $icon.removeClass('fa-spin');
                this.showNotification('Table refreshed successfully', 'success');
            }, 1000);
        }.bind(this));
    }

    exportTable(tableId) {
        const $table = $('#' + tableId);
        
        let csv = [];
        
        $table.find('thead th').each(function() {
            csv.push($(this).text());
        });
        
        $table.find('tbody tr').each(function() {
            const row = [];
            $(this).find('td').each(function() {
                row.push($(this).text());
            });
            csv.push(row.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'table-export.csv';
        a.click();
        
        window.URL.revokeObjectURL(url);
        
        this.showNotification('Table exported successfully', 'success');
    }

    enhanceTableSorting() {
        $('th.sortable').each(function() {
            const $th = $(this);
            
            $th.append(' <i class="fas fa-sort ms-1"></i>');
            
            $th.on('click', function() {
                const $table = $(this).closest('table');
                const columnIndex = $(this).index();
                const isAscending = $(this).hasClass('sort-asc');
                
                $table.find('th').removeClass('sort-asc sort-desc');
                $table.find('th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
                
                if (isAscending) {
                    $(this).addClass('sort-desc');
                    $(this).find('i').removeClass('fa-sort').addClass('fa-sort-down');
                    this.sortTable($table, columnIndex, false);
                } else {
                    $(this).addClass('sort-asc');
                    $(this).find('i').removeClass('fa-sort').addClass('fa-sort-up');
                    this.sortTable($table, columnIndex, true);
                }
            }.bind(this));
        }.bind(this));
    }

    sortTable($table, columnIndex, ascending) {
        const $tbody = $table.find('tbody');
        const $rows = $tbody.find('tr');
        
        $rows.sort(function(a, b) {
            const aValue = $(a).find('td').eq(columnIndex).text();
            const bValue = $(b).find('td').eq(columnIndex).text();
            
            const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return ascending ? aNum - bNum : bNum - aNum;
            } else {
                return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            }
        });
        
        $tbody.empty().append($rows);
    }

    enhanceTablePagination() {
        $('table').each(function() {
            const $table = $(this);
            const $rows = $table.find('tbody tr');
            const rowsPerPage = 10;
            const totalPages = Math.ceil($rows.length / rowsPerPage);
            
            if ($rows.length > rowsPerPage) {
                const paginationHtml = `
                    <nav aria-label="Table pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                        </ul>
                    </nav>
                `;
                
                $table.after(paginationHtml);
                
                this.addPaginationFunctionality($table, rowsPerPage, totalPages);
            }
        }.bind(this));
    }

    addPaginationFunctionality($table, rowsPerPage, totalPages) {
        let currentPage = 1;
        const $rows = $table.find('tbody tr');
        
        this.showPage($table, currentPage, rowsPerPage);
        
        const $pagination = $table.next('.pagination');
        
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = `
                <li class="page-item ${i === 1 ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
            $pagination.find('ul').append(pageItem);
        }
        
        const nextLastHtml = `
            <li class="page-item">
                <a class="page-link" href="#" data-page="next">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#" data-page="last">Last</a>
            </li>
        `;
        $pagination.find('ul').append(nextLastHtml);
        
        $pagination.find('.page-link').on('click', function(e) {
            e.preventDefault();
            
            const page = $(this).data('page');
            
            if (page === 'next') {
                currentPage = Math.min(currentPage + 1, totalPages);
            } else if (page === 'last') {
                currentPage = totalPages;
            } else if (page === 'prev') {
                currentPage = Math.max(currentPage - 1, 1);
            } else if (page === 'first') {
                currentPage = 1;
            } else {
                currentPage = page;
            }
            
            this.showPage($table, currentPage, rowsPerPage);
            this.updatePaginationState($pagination, currentPage, totalPages);
        }.bind(this));
    }

    showPage($table, page, rowsPerPage) {
        const $rows = $table.find('tbody tr');
        const startIndex = (page - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        
        $rows.hide();
        $rows.slice(startIndex, endIndex).show();
    }

    updatePaginationState($pagination, currentPage, totalPages) {
        $pagination.find('.page-item').removeClass('active');
        $pagination.find(`[data-page="${currentPage}"]`).closest('.page-item').addClass('active');
        
        if (currentPage === 1) {
            $pagination.find('[data-page="prev"]').closest('.page-item').addClass('disabled');
        } else {
            $pagination.find('[data-page="prev"]').closest('.page-item').removeClass('disabled');
        }
        
        if (currentPage === totalPages) {
            $pagination.find('[data-page="next"]').closest('.page-item').addClass('disabled');
            $pagination.find('[data-page="last"]').closest('.page-item').addClass('disabled');
        } else {
            $pagination.find('[data-page="next"]').closest('.page-item').removeClass('disabled');
            $pagination.find('[data-page="last"]').closest('.page-item').removeClass('disabled');
        }
    }

    // Enhanced Forms with Bootstrap Validation
    enhanceForms() {
        this.enhanceFormValidation();
        this.enhanceFormInputs();
        this.enhanceFileUploads();
    }

    enhanceFormValidation() {
        $('form.needs-validation').each(function() {
            const $form = $(this);
            
            $form.on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                $form.addClass('was-validated');
            });
        });
        
        $('input, textarea, select').each(function() {
            const $input = $(this);
            
            $input.on('blur', function() {
                this.validateField($input);
            }.bind(this));
            
            $input.on('input', function() {
                if ($input.hasClass('is-invalid') || $input.hasClass('is-valid')) {
                    this.validateField($input);
                }
            }.bind(this));
        }.bind(this));
    }

    validateField($input) {
        const value = $input.val().trim();
        const type = $input.attr('type');
        const required = $input.prop('required');
        
        $input.removeClass('is-valid is-invalid');
        $input.next('.invalid-feedback, .valid-feedback').remove();
        
        let isValid = true;
        let message = '';
        
        if (required && !value) {
            isValid = false;
            message = 'This field is required.';
        } else if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address.';
        } else if (type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number.';
        }
        
        const feedbackClass = isValid ? 'valid-feedback' : 'invalid-feedback';
        const feedbackHtml = `<div class="${feedbackClass}">${message}</div>`;
        
        $input.addClass(isValid ? 'is-valid' : 'is-invalid').after(feedbackHtml);
        
        return isValid;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }

    enhanceFormInputs() {
        $('.form-floating input, .form-floating textarea').each(function() {
            const $input = $(this);
            const $label = $input.next('label');
            
            this.updateFloatingLabel($input, $label);
            
            $input.on('input', function() {
                this.updateFloatingLabel($input, $label);
            }.bind(this));
        }.bind(this));
    }

    updateFloatingLabel($input, $label) {
        if ($input.val()) {
            $label.addClass('active');
        } else {
            $label.removeClass('active');
        }
    }

    enhanceFileUploads() {
        $('input[type="file"]').each(function() {
            const $input = $(this);
            const $formGroup = $input.closest('.form-group');
            
            const fileUploadHtml = `
                <div class="file-upload-area border rounded p-4 text-center bg-light">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <p class="mb-2">Drag and drop files here or click to browse</p>
                    <button type="button" class="btn btn-outline-primary">Choose Files</button>
                    <div class="file-list mt-3"></div>
                </div>
            `;
            
            $input.hide();
            $formGroup.append(fileUploadHtml);
            
            const $uploadArea = $formGroup.find('.file-upload-area');
            
            $uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('border-primary bg-primary bg-opacity-10');
            });
            
            $uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('border-primary bg-primary bg-opacity-10');
            });
            
            $uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('border-primary bg-primary bg-opacity-10');
                
                const files = e.originalEvent.dataTransfer.files;
                this.handleFileSelect($input, files);
            }.bind(this));
            
            $uploadArea.find('button').on('click', function() {
                $input.click();
            });
            
            $input.on('change', function() {
                const files = this.files;
                this.handleFileSelect($input, files);
            }.bind(this));
        }.bind(this));
    }

    handleFileSelect($input, files) {
        const $fileList = $input.closest('.form-group').find('.file-list');
        $fileList.empty();
        
        Array.from(files).forEach(file => {
            const fileHtml = `
                <div class="file-item d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                    <div>
                        <i class="fas fa-file me-2"></i>
                        <span>${file.name}</span>
                        <small class="text-muted d-block">(${this.formatFileSize(file.size)})</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-file">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $fileList.append(fileHtml);
        });
        
        $fileList.find('.remove-file').on('click', function() {
            $(this).closest('.file-item').remove();
        });
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Enhanced Modals
    enhanceModals() {
        $('.modal').each(function() {
            const $modal = $(this);
            
            $modal.on('show.bs.modal', function() {
                $modal.find('.modal-dialog').addClass('animate__animated animate__fadeInDown');
            });
            
            $modal.on('hide.bs.modal', function() {
                $modal.find('.modal-dialog').addClass('animate__animated animate__fadeOutUp');
            });
        });
        
        this.addModalConfirmation();
        this.addModalLoading();
    }

    addModalConfirmation() {
        $('[data-confirm]').each(function() {
            const $element = $(this);
            const message = $element.data('confirm');
            
            $element.on('click', function(e) {
                e.preventDefault();
                
                const $modal = $(`
                    <div class="modal fade" id="confirmModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Action</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>${message}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary confirm-action">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                $('body').append($modal);
                
                const modal = new bootstrap.Modal($modal[0]);
                modal.show();
                
                $modal.find('.confirm-action').on('click', function() {
                    const originalAction = $element.attr('href') || $element.data('action');
                    if (originalAction) {
                        window.location.href = originalAction;
                    } else {
                        $element[0].click();
                    }
                    modal.hide();
                });
                
                $modal.on('hidden.bs.modal', function() {
                    $modal.remove();
                });
            });
        });
    }

    addModalLoading() {
        $('[data-loading]').each(function() {
            const $element = $(this);
            
            $element.on('click', function() {
                const $modal = $element.closest('.modal');
                const $content = $modal.find('.modal-content');
                
                const loadingHtml = `
                    <div class="modal-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-90">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Processing...</p>
                        </div>
                    </div>
                `;
                
                $content.css('position', 'relative').append(loadingHtml);
                
                setTimeout(() => {
                    $content.find('.modal-loading-overlay').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 2000);
            });
        });
    }

    // Enhanced Notifications
    enhanceNotifications() {
        if (!$('#notification-container').length) {
            $('body').append(`
                <div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                </div>
            `);
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const $container = $('#notification-container');
        
        const notificationId = 'notification-' + Date.now();
        const alertClass = 'alert-' + type;
        const iconClass = this.getNotificationIcon(type);
        
        const notificationHtml = `
            <div id="${notificationId}" class="alert ${alertClass} alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $container.append(notificationHtml);
        
        setTimeout(() => {
            const $notification = $(`#${notificationId}`);
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }

    // Enhanced Charts
    enhanceCharts() {
        this.addChartSupport();
        this.addMiniCharts();
    }

    addChartSupport() {
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            document.head.appendChild(script);
        }
    }

    addMiniCharts() {
        const self = this;
        
        // Find all existing chart containers and create charts for them
        $('.mini-chart canvas').each(function() {
            const $canvas = $(this);
            const canvasId = $canvas.attr('id');
            
            if (canvasId) {
                // Extract card ID from canvas ID (remove "-chart" suffix)
                const cardId = canvasId.replace('-chart', '');
                
                setTimeout(() => {
                    self.createMiniChart(cardId);
                }, 100);
            }
        });
        
        // Handle larger charts like performanceChart and loanDistributionChart
        $('#performanceChart, #loanDistributionChart').each(function() {
            const $canvas = $(this);
            const canvasId = $canvas.attr('id');
            
            if (canvasId) {
                setTimeout(() => {
                    self.createLargeChart(canvasId);
                }, 150);
            }
        });
        
        // Also add charts to cards that don't have charts yet
        $('.stat-card:not(:has(.mini-chart))').each(function() {
            const $card = $(this);
            const cardId = $card.attr('id') || 'card-' + Math.random().toString(36).substr(2, 9);
            $card.attr('id', cardId);
            
            const chartHtml = `
                <div class="mini-chart mt-3" style="height: 60px;">
                    <canvas id="${cardId}-chart"></canvas>
                </div>
            `;
            
            $card.find('.card-body').append(chartHtml);
            
            setTimeout(() => {
                self.createMiniChart(cardId);
            }, 100);
        });
    }

    createMiniChart(cardId) {
        const ctx = document.getElementById(`${cardId}-chart`);
        if (!ctx) return;
        
        // Wait for Chart.js to be available
        const checkChart = () => {
            if (typeof Chart !== 'undefined') {
                // Destroy existing chart if it exists
                const existingChart = Chart.getChart(ctx);
                if (existingChart) {
                    existingChart.destroy();
                }
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['', '', '', '', '', '', '', ''],
                        datasets: [{
                            data: [12, 19, 3, 5, 2, 3, 7],
                            borderColor: 'rgba(54, 162, 235, 0.8)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 0,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    }
                });
            } else {
                // Chart.js not loaded yet, wait and retry
                setTimeout(checkChart, 100);
            }
        };
        
        checkChart();
    }

    createLargeChart(canvasId) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        // Wait for Chart.js to be available
        const checkChart = () => {
            if (typeof Chart !== 'undefined') {
                // Destroy existing chart if it exists
                const existingChart = Chart.getChart(ctx);
                if (existingChart) {
                    existingChart.destroy();
                }
                
                if (canvasId === 'performanceChart') {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                label: 'Pinjaman',
                                data: [65, 78, 90, 81, 95, 105, 112, 125, 135, 145, 155, 168],
                                borderColor: 'rgba(54, 162, 235, 0.8)',
                                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            }, {
                                label: 'Simpanan',
                                data: [45, 52, 58, 65, 72, 78, 85, 92, 98, 105, 112, 125],
                                borderColor: 'rgba(75, 192, 192, 0.8)',
                                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                tooltip: {
                                    enabled: true
                                }
                            },
                            scales: {
                                x: {
                                    display: true
                                },
                                y: {
                                    display: true,
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                } else if (canvasId === 'loanDistributionChart') {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Produktif', 'Konsumtif', 'Investasi', 'Modal Kerja'],
                            datasets: [{
                                data: [45, 25, 20, 10],
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(75, 192, 192, 0.8)',
                                    'rgba(255, 206, 86, 0.8)',
                                    'rgba(255, 99, 132, 0.8)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                },
                                tooltip: {
                                    enabled: true
                                }
                            }
                        }
                    });
                }
            } else {
                // Chart.js not loaded yet, wait and retry
                setTimeout(checkChart, 100);
            }
        };
        
        checkChart();
    }

    // Real-time Updates
    startRealTimeUpdates() {
        setInterval(() => {
            this.updateStatistics();
        }, 5000);
        
        setInterval(() => {
            this.updateCharts();
        }, 10000);
    }

    updateStatistics() {
        $('.stat-card .h4').each(function() {
            const $value = $(this);
            const currentValue = parseFloat($value.text().replace(/[^0-9.-]/g, ''));
            const change = (Math.random() - 0.5) * 10;
            const newValue = currentValue + (currentValue * change / 100);
            
            $value.addClass('text-primary');
            $value.text(this.formatNumber(newValue));
            
            setTimeout(() => {
                $value.removeClass('text-primary');
            }, 1000);
        }.bind(this));
    }

    updateCharts() {
        $('.mini-chart canvas').each(function() {
            const chart = Chart.getChart(this);
            if (chart) {
                chart.data.datasets[0].data = chart.data.datasets[0].data.map(() => 
                    Math.floor(Math.random() * 20) + 1
                );
                chart.update();
            }
        });
    }

    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        } else {
            return num.toFixed(0);
        }
    }
}

// Initialize Enhanced UI when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
        window.enhancedUI = new EnhancedUI();
    } else {
        console.error('jQuery is required for Enhanced UI');
    }
});

})();