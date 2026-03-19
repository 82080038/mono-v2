
// Role-Specific Dashboard Widgets
const roleWidgets = {
    'super_admin': [
        'system_health',
        'user_management',
        'audit_logs',
        'system_settings',
        'backup_status',
        'security_monitoring'
    ],
    'admin': [
        'member_management',
        'loan_management',
        'savings_management',
        'financial_reports',
        'staff_management',
        'compliance_status'
    ],
    'mantri': [
        'field_operations',
        'member_registration',
        'loan_processing',
        'collection_status',
        'visit_schedule',
        'performance_metrics'
    ],
    'member': [
        'account_balance',
        'loan_status',
        'transaction_history',
        'savings_summary',
        'notifications',
        'profile_settings'
    ],
    // New roles based on documentation analysis
    'kasir': [
        'cash_transactions',
        'payment_processing',
        'loan_disbursement',
        'cash_management',
        'daily_reconciliation',
        'transaction_reports'
    ],
    'teller': [
        'member_registration',
        'savings_management',
        'account_inquiries',
        'customer_service',
        'document_processing',
        'service_queue'
    ],
    'surveyor': [
        'survey_management',
        'member_verification',
        'field_data_collection',
        'geographic_tracking',
        'survey_reports',
        'verification_status'
    ],
    'collector': [
        'collection_targets',
        'overdue_accounts',
        'route_planning',
        'collection_reports',
        'payment_tracking',
        'member_communication'
    ]
};

function getRoleWidgets(role) {
    return roleWidgets[role] || [];
}
