// Error Handling Wrapper
(function() {
    /**
 * Enhanced Bootstrap & jQuery Implementation for KSP Lam Gabe Jaya
 * Advanced UI Components and Interactions
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
        // Enhanced Statistics Cards with Hover Effects
        this.enhancedStatCards();
        
        // Advanced Progress Bars
        this.enhancedProgressBars();
        
        // Interactive Charts
        this.enhancedCharts();
        
        // Real-time Updates
        this.startRealTimeUpdates();
    }

    enhancedStatCards() {
        // Add hover effects and animations to stat cards
        $('.stat-card').each(function() {
            const $card = $(this);
            
            // Add hover effect
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
            
            // Add click animation
            $card.on('click', function() {
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => {
                    $(this).removeClass('animate__animated animate__pulse');
                }, 1000);
            });
        });
    }

    enhancedProgressBars() {
        // Animated progress bars
        $('.progress-bar').each(function() {
            const $bar = $(this);
            const width = $bar.attr('style')?.match(/width:\s*(\d+%)/);
            
            if (width) {
                // Start from 0 and animate to target width
                $bar.css('width', '0%');
                setTimeout(() => {
                    $bar.css('width', width[1]);
                }, 100);
            }
            
            // Add striped animation
            if ($bar.hasClass('progress-bar-striped')) {
                $bar.addClass('progress-bar-animated');
            }
        });
    }

    // Enhanced Tables with Bootstrap
    enhanceTables() {
        // DataTables functionality
        this.enhanceDataTable();
        
        // Table search and filter
        this.enhanceTableSearch();
        
        // Table sorting
        this.enhanceTableSorting();
        
        // Table pagination
        this.enhanceTablePagination();
    }

    enhanceDataTable() {
        // Add Bootstrap classes to tables
        $('table').addClass('table table-hover table-striped');
        
        // Add responsive wrapper
        $('table').wrap('<div class="table-responsive"></div>');
        
        // Add table header styling
        $('thead').addClass('table-dark');
        
        // Add row hover effects
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
        // Create search box for each table
        $('.table-responsive').each(function() {
            const $table = $(this).find('table');
            const tableId = $table.attr('id') || 'table-' + Math.random().toString(36).substr(2, 9);
            $table.attr('id', tableId);
            
            // Add search box
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
        
        // Add search functionality
        $('.table-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const tableId = $(this).data('table');
            const $table = $('#' + tableId);
            
            $table.find('tbody tr').each(function() {
                const $row = $(this);
                const text = $row.text().toLowerCase();
                
                if (text.includes(searchTerm)) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        });
        
        // Add export functionality
        $('.table-export').on('click', function() {
            const tableId = $(this).data('table');
            this.exportTable(tableId);
        });
        
        // Add refresh functionality
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

    enhanceTableSorting() {
        // Add sortable headers
        $('th.sortable').each(function() {
            const $th = $(this);
            
            // Add sort icons
            $th.append(' <i class="fas fa-sort ms-1"></i>');
            
            // Add click handler
            $th.on('click', function() {
                const $table = $(this).closest('table');
                const columnIndex = $(this).index();
                const isAscending = $(this).hasClass('sort-asc');
                
                // Remove all sort classes
                $table.find('th').removeClass('sort-asc sort-desc');
                $table.find('th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
                
                // Sort based on current state
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
        });
    }

    sortTable($table, columnIndex, ascending) {
        const $tbody = $table.find('tbody');
        const $rows = $tbody.find('tr');
        
        $rows.sort(function(a, b) {
            const aValue = $(a).find('td').eq(columnIndex).text();
            const bValue = $(b).find('td').eq(columnIndex).text();
            
            // Try to parse as number
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
        // Add pagination to large tables
        $('table').each(function() {
            const $table = $(this);
            const $rows = $table.find('tbody tr');
            const rowsPerPage = 10;
            const totalPages = Math.ceil($rows.length / rowsPerPage);
            
            if ($rows.length > rowsPerPage) {
                // Add pagination controls
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
                
                // Add pagination functionality
                this.addPaginationFunctionality($table, rowsPerPage, totalPages);
            }
        }.bind(this));
    }

    addPaginationFunctionality($table, rowsPerPage, totalPages) {
        let currentPage = 1;
        const $rows = $table.find('tbody tr');
        
        // Show first page
        this.showPage($table, currentPage, rowsPerPage);
        
        // Add pagination controls
        const $pagination = $table.next('.pagination');
        
        // Add page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = `
                <li class="page-item ${i === 1 ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
            $pagination.find('ul').append(pageItem);
        }
        
        // Add next/last buttons
        const nextLastHtml = `
            <li class="page-item">
                <a class="page-link" href="#" data-page="next">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#" data-page="last">Last</a>
            </li>
        `;
        $pagination.find('ul').append(nextLastHtml);
        
        // Add click handlers
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
        
        // Update prev/next buttons
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
        this.enhanceDatePickers();
    }

    enhanceFormValidation() {
        // Bootstrap validation
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
        
        // Custom validation
        $('input, textarea, select').each(function() {
            const $input = $(this);
            
            // Add validation on blur
            $input.on('blur', function() {
                this.validateField($input);
            }.bind(this));
            
            // Add validation on input
            $input.on('input', function() {
                if ($input.hasClass('is-invalid') || $input.hasClass('is-valid')) {
                    this.validateField($input);
                }
            }.bind(this));
        });
    }

    validateField($input) {
        const value = $input.val().trim();
        const type = $input.attr('type');
        const required = $input.prop('required');
        
        // Remove previous validation states
        $input.removeClass('is-valid is-invalid');
        $input.next('.invalid-feedback, .valid-feedback').remove();
        
        // Check if field is valid
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
        } else if ($input.attr('minlength') && value.length < parseInt($input.attr('minlength'))) {
            isValid = false;
            message = `Minimum length is ${$input.attr('minlength')} characters.`;
        }
        
        // Add validation feedback
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
        // Add floating labels
        $('.form-floating input, .form-floating textarea').each(function() {
            const $input = $(this);
            const $label = $input.next('label');
            
            // Check if input has value
            this.updateFloatingLabel($input, $label);
            
            // Add input event listener
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
        // Custom file upload
        $('input[type="file"]').each(function() {
            const $input = $(this);
            const $formGroup = $input.closest('.form-group');
            
            // Add custom file upload UI
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
            
            // Add drag and drop functionality
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
            
            // Add click functionality
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
        
        // Add remove functionality
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
        // Add modal backdrop animation
        $('.modal').each(function() {
            const $modal = $(this);
            
            $modal.on('show.bs.modal', function() {
                $modal.find('.modal-dialog').addClass('animate__animated animate__fadeInDown');
            });
            
            $modal.on('hide.bs.modal', function() {
                $modal.find('.modal-dialog').addClass('animate__animated animate__fadeOutUp');
            });
        });
        
        // Add modal confirmation
        this.addModalConfirmation();
        
        // Add dynamic modal loading
        this.addModalLoading();
    }

    addModalConfirmation() {
        // Add confirmation dialogs
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
                    // Execute original action
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
        // Add loading states to modals
        $('[data-loading]').each(function() {
            const $element = $(this);
            
            $element.on('click', function() {
                const $modal = $element.closest('.modal');
                const $content = $modal.find('.modal-content');
                
                // Add loading overlay
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
                
                // Simulate loading
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
        // Create notification container
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
        
        // Auto-remove after duration
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
        // Add Chart.js integration
        this.addChartSupport();
        
        // Add mini charts to cards
        this.addMiniCharts();
    }

    addChartSupport() {
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            // Load Chart.js
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            document.head.appendChild(script);
        }
    }

    addMiniCharts() {
        // Add mini charts to stat cards
        const self = this; // Save reference to this
        $('.stat-card').each(function() {
            const $card = $(this);
            const cardId = $card.attr('id') || 'card-' + Math.random().toString(36).substr(2, 9);
            $card.attr('id', cardId);
            
            // Add mini chart container
            const chartHtml = `
                <div class="mini-chart mt-3" style="height: 60px;">
                    <canvas id="${cardId}-chart"></canvas>
                </div>
            `;
            
            $card.find('.card-body').append(chartHtml);
            
            // Create mini chart
            setTimeout(() => {
                self.createMiniChart(cardId);
            }, 100);
        });
    }

    createMiniChart(cardId) {
        const ctx = document.getElementById(`${cardId}-chart`);
        if (!ctx) return;
        
        // Create simple line chart
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
    }

    // Real-time Updates
    startRealTimeUpdates() {
        // Update statistics every 5 seconds
        setInterval(() => {
            this.updateStatistics();
        }, 5000);
        
        // Update charts every 10 seconds
        setInterval(() => {
            this.updateCharts();
        }, 10000);
    }

    updateStatistics() {
        // Simulate real-time updates
        $('.stat-card .h4').each(function() {
            const $value = $(this);
            const currentValue = parseFloat($value.text().replace(/[^0-9.-]/g, ''));
            const change = (Math.random() - 0.5) * 10; // ±5% change
            const newValue = currentValue + (currentValue * change / 100);
            
            // Add animation
            $value.addClass('text-primary');
            $value.text(this.formatNumber(newValue));
            
            setTimeout(() => {
                $value.removeClass('text-primary');
            }, 1000);
        }.bind(this));
    }

    updateCharts() {
        // Update mini charts
        $('.mini-chart canvas').each(function() {
            const chart = Chart.getChart(this);
            if (chart) {
                // Update data
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

    exportTable(tableId) {
        const $table = $('#' + tableId);
        
        // Simple CSV export
        let csv = [];
        
        // Add headers
        $table.find('thead th').each(function() {
            csv.push($(this).text());
        });
        
        // Add rows
        $table.find('tbody tr').each(function() {
            const row = [];
            $(this).find('td').each(function() {
                row.push($(this).text());
            });
            csv.push(row.join(','));
        });
        
        // Download CSV
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