// Notification Service for Koperasi Keliling
class NotificationService {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.permission = 'default';
        this.swRegistration = null;
    }

    // Initialize notification service
    async initialize() {
        await this.requestPermission();
        await this.loadNotifications();
        this.setupServiceWorker();
        this.setupEventListeners();
        this.startAutomatedNotifications();
    }

    // Request notification permission
    async requestPermission() {
        if ('Notification' in window) {
            this.permission = await Notification.requestPermission();
            console.log('Notification permission:', this.permission);
        }
    }

    // Setup service worker
    async setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.ready();
                console.log('Service worker ready for notifications');
            } catch (error) {
                console.error('Service worker error:', error);
            }
        }
    }

    // Load notifications
    async loadNotifications() {
        try {
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
            if (!currentUser.id) return;

            const response = await fetch(`/api/notification-service.php?action=list&user_id=${currentUser.id}`);
            const result = await response.json();
            
            if (result.success) {
                this.notifications = result.data;
                this.unreadCount = result.unread_count;
                this.updateNotificationUI();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    // Update notification UI
    updateNotificationUI() {
        this.updateNotificationBadge();
        this.renderNotificationList();
    }

    // Update notification badge
    updateNotificationBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            badge.textContent = this.unreadCount;
            badge.style.display = this.unreadCount > 0 ? 'block' : 'none';
        }
    }

    // Render notification list
    renderNotificationList() {
        const container = document.getElementById('notification-list');
        if (!container) return;

        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="no-notifications">
                    <i class="fas fa-bell-slash fa-3x text-muted"></i>
                    <p class="text-muted">Tidak ada notifikasi</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.notifications.map(notification => `
            <div class="notification-item ${notification.status === 'read' ? 'read' : 'unread'}" 
                 data-id="${notification.id}">
                <div class="notification-icon">
                    <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="notification-content">
                    <h6>${notification.title}</h6>
                    <p>${notification.message}</p>
                    <small class="text-muted">${this.formatTime(notification.created_at)}</small>
                </div>
                <div class="notification-actions">
                    ${notification.status === 'sent' ? `
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="notificationService.markAsRead(${notification.id})">
                            Tandai dibaca
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    // Get notification icon
    getNotificationIcon(type) {
        const icons = {
            'payment_reminder': 'fa-money-bill-wave text-warning',
            'loan_approved': 'fa-check-circle text-success',
            'collection_reminder': 'fa-hand-holding-usd text-info',
            'payment_received': 'fa-coins text-success',
            'general': 'fa-info-circle text-primary'
        };
        return icons[type] || 'fa-bell text-secondary';
    }

    // Format time
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Baru saja';
        if (diff < 3600000) return `${Math.floor(diff / 60000)} menit lalu`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)} jam lalu`;
        return date.toLocaleDateString('id-ID');
    }

    // Send notification
    async sendNotification(userId, title, message, type = 'general') {
        try {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('title', title);
            formData.append('message', message);
            formData.append('type', type);

            const response = await fetch('/api/notification-service.php?action=send', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Show browser notification if permission granted
                this.showBrowserNotification(title, message, type);
                
                // Reload notifications
                await this.loadNotifications();
                
                return true;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error sending notification:', error);
            return false;
        }
    }

    // Show browser notification
    showBrowserNotification(title, message, type) {
        if (this.permission !== 'granted') return;

        const notification = new Notification(title, {
            body: message,
            icon: '/icons/icon-192x192.png',
            badge: '/icons/icon-72x72.png',
            tag: type,
            requireInteraction: type === 'payment_reminder'
        });

        notification.onclick = () => {
            window.focus();
            notification.close();
            this.handleNotificationClick(type);
        };

        // Auto close after 5 seconds
        setTimeout(() => {
            notification.close();
        }, 5000);
    }

    // Handle notification click
    handleNotificationClick(type) {
        switch (type) {
            case 'payment_reminder':
                // Navigate to payment page
                window.location.href = '/pages/member/dashboard.html#payments';
                break;
            case 'loan_approved':
                // Navigate to loan status page
                window.location.href = '/pages/member/dashboard.html#loans';
                break;
            case 'collection_reminder':
                // Navigate to staff dashboard
                window.location.href = '/pages/staff/dashboard.html';
                break;
            default:
                // Navigate to dashboard
                window.location.href = '/pages/member/dashboard.html';
                break;
        }
    }

    // Mark as read
    async markAsRead(notificationId) {
        try {
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
            
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            formData.append('user_id', currentUser.id);

            const response = await fetch('/api/notification-service.php?action=mark_read', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all as read
    async markAllAsRead() {
        const unreadNotifications = this.notifications.filter(n => n.status === 'sent');
        
        for (const notification of unreadNotifications) {
            await this.markAsRead(notification.id);
        }
    }

    // Start automated notifications
    startAutomatedNotifications() {
        // Check for payment reminders every hour
        setInterval(async () => {
            await this.checkPaymentReminders();
        }, 3600000); // 1 hour

        // Check for collection reminders every 30 minutes
        setInterval(async () => {
            await this.checkCollectionReminders();
        }, 1800000); // 30 minutes

        // Send daily summary at 8 AM
        this.scheduleDailySummary();
    }

    // Check payment reminders
    async checkPaymentReminders() {
        try {
            const response = await fetch('/api/notification-service.php?action=automated');
            const result = await response.json();
            
            if (result.success) {
                console.log('Automated notifications processed');
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error checking payment reminders:', error);
        }
    }

    // Check collection reminders
    async checkCollectionReminders() {
        const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
        
        if (currentUser.role === 'staff') {
            const hour = new Date().getHours();
            
            // Send reminder at 8 AM and 2 PM
            if (hour === 8 || hour === 14) {
                await this.sendNotification(
                    currentUser.id,
                    'Pengingat Kunjungan',
                    'Jangan lupa untuk melakukan kunjungan dan penagihan harian sesuai rute yang telah ditentukan.',
                    'collection_reminder'
                );
            }
        }
    }

    // Schedule daily summary
    scheduleDailySummary() {
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(8, 0, 0, 0);
        
        const msUntilTomorrow = tomorrow - now;
        
        setTimeout(() => {
            this.sendDailySummary();
            // Schedule next day
            this.scheduleDailySummary();
        }, msUntilTomorrow);
    }

    // Send daily summary
    async sendDailySummary() {
        try {
            const response = await fetch('/api/notification-service.php?action=automated');
            const result = await response.json();
            
            if (result.success) {
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error sending daily summary:', error);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Notification panel toggle
        const notificationToggle = document.getElementById('notification-toggle');
        if (notificationToggle) {
            notificationToggle.addEventListener('click', () => {
                this.toggleNotificationPanel();
            });
        }

        // Mark all as read button
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Notification settings
        const notificationSettings = document.getElementById('notification-settings');
        if (notificationSettings) {
            notificationSettings.addEventListener('click', () => {
                this.showNotificationSettings();
            });
        }
    }

    // Toggle notification panel
    toggleNotificationPanel() {
        const panel = document.getElementById('notification-panel');
        if (panel) {
            panel.classList.toggle('show');
        }
    }

    // Show notification settings
    showNotificationSettings() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pengaturan Notifikasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="payment-reminders" checked>
                            <label class="form-check-label" for="payment-reminders">
                                Pengingat Pembayaran
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="collection-reminders" checked>
                            <label class="form-check-label" for="collection-reminders">
                                Pengingat Penagihan
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="daily-summary" checked>
                            <label class="form-check-label" for="daily-summary">
                                Ringkasan Harian
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="browser-notifications" checked>
                            <label class="form-check-label" for="browser-notifications">
                                Notifikasi Browser
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="notificationService.saveSettings()">Simpan</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Save notification settings
    saveSettings() {
        const settings = {
            paymentReminders: document.getElementById('payment-reminders').checked,
            collectionReminders: document.getElementById('collection-reminders').checked,
            dailySummary: document.getElementById('daily-summary').checked,
            browserNotifications: document.getElementById('browser-notifications').checked
        };

        localStorage.setItem('notificationSettings', JSON.stringify(settings));
        
        // Close modal
        const modal = document.querySelector('.modal.show');
        if (modal) {
            bootstrap.Modal.getInstance(modal).hide();
        }

        // Show success message
        this.showBrowserNotification('Pengaturan Disimpan', 'Pengaturan notifikasi berhasil disimpan', 'general');
    }

    // Load notification settings
    loadSettings() {
        const settings = JSON.parse(localStorage.getItem('notificationSettings') || '{}');
        
        return {
            paymentReminders: settings.paymentReminders !== false,
            collectionReminders: settings.collectionReminders !== false,
            dailySummary: settings.dailySummary !== false,
            browserNotifications: settings.browserNotifications !== false
        };
    }
}

// Initialize notification service when page loads
let notificationService = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('notification-toggle') || document.getElementById('notification-panel')) {
        notificationService = new NotificationService();
        notificationService.initialize();
    }
});
