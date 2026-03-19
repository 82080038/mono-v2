# Koperasi SaaS Application

## Directory Structure

### Core Application
- `core/api/` - API endpoints
- `core/config/` - Configuration files
- `core/utils/` - Utility functions
- `core/helpers/` - Helper classes
- `core/models/` - Data models

### Frontend
- `frontend/pages/` - Application pages
- `frontend/dashboards/` - Role-specific dashboards
- `frontend/components/` - Reusable components
- `frontend/assets/` - CSS, JS, images

### Documentation
- `docs/` - All documentation
- `docs/user-guides/` - User documentation
- `docs/technical/` - Technical documentation
- `docs/reports/` - Generated reports

### Testing
- `tests/` - Test files and reports

### Scripts
- `scripts/` - Utility and deployment scripts

### Archive
- `archive/` - Old files and backups

## Quick Start

1. Access the application: `http://localhost/mono`
2. Admin dashboard: `http://localhost/mono/frontend/dashboards/admin/admin_dashboard.html`
3. API documentation: `docs/api/`
4. User manual: `docs/user-guides/USER_MANUAL.md`

## Development

- Run tests: `./scripts/maintenance/run-tests.sh`
- Setup development: `./scripts/deployment/development-setup.sh`
- Start dev server: `./scripts/deployment/start-dev-server.sh`
