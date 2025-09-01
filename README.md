# 🧾 Laravel Invoice Management System

A comprehensive, modern invoice management system built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind CSS v4. This system provides complete invoice lifecycle management with robust features for businesses of all sizes.

## ✨ Features

### 📋 Core Invoice Management
- **Complete CRUD Operations** - Create, view, edit, duplicate, and delete invoices
- **Multiple Invoice States** - Draft, Sent, Paid, Overdue, Cancelled
- **Professional PDF Generation** - Automatic PDF generation with customizable templates
- **Email Integration** - Send invoices directly to clients with PDF attachments
- **Multi-Currency Support** - Handle invoices in different currencies with exchange rates
- **Tax & Discount Calculations** - Flexible tax rates and discount options

### 👥 Client Management
- **Client Database** - Comprehensive client information management
- **Client Profiles** - Store contact details, addresses, and preferences
- **Client History** - View all invoices and payments for each client
- **Quick Client Creation** - Add clients directly from invoice creation

### 💰 Payment Tracking
- **Payment Recording** - Track partial and full payments
- **Payment Methods** - Support for various payment methods (cash, check, bank transfer, etc.)
- **Payment History** - Complete audit trail of all payments
- **Automatic Status Updates** - Invoices automatically update to 'paid' when fully paid

### 🔄 Recurring Invoices
- **Automated Generation** - Set up recurring invoices with flexible schedules
- **Multiple Frequencies** - Daily, weekly, monthly, quarterly, yearly options
- **Cycle Management** - Set maximum cycles or end dates
- **Template System** - Reuse invoice templates for recurring billing

### 📊 Reports & Analytics
- **Invoice Statistics** - Comprehensive stats on invoice performance
- **Revenue Reports** - Track revenue by date ranges and clients
- **Outstanding Reports** - Monitor unpaid and overdue invoices
- **Payment Analytics** - Analyze payment patterns and methods
- **Export Options** - Export reports to PDF for record keeping

### 🔔 Notifications System
- **System Notifications** - In-app notifications for important events
- **Overdue Alerts** - Automatic notifications for overdue invoices
- **Payment Confirmations** - Notifications when payments are received
- **Email Notifications** - Automated email alerts and reminders

### ⚙️ Advanced Features
- **API Access** - Full REST API for third-party integrations
- **Browser Testing** - Comprehensive Pest v4 browser tests
- **Multi-Language Ready** - Prepared for internationalization
- **Dark Mode Support** - Built-in dark/light theme switching
- **Mobile Responsive** - Fully responsive design for all devices
- **Data Export** - Backup and export functionality

## 🛠 Technology Stack

### Backend
- **Laravel 12** - Latest Laravel framework with modern PHP 8.4
- **Inertia.js v2** - Modern single-page app without API complexity
- **Form Requests** - Robust validation with custom error messages
- **Service Classes** - Clean architecture with dedicated service layers
- **Mail System** - Queued email sending with PDF attachments
- **Console Commands** - Automated tasks for recurring invoices and overdue management

### Frontend
- **Vue 3** - Modern reactive JavaScript framework
- **Tailwind CSS v4** - Latest utility-first CSS framework
- **TypeScript Support** - Type-safe development
- **Lucide Icons** - Beautiful, consistent icon set
- **Shadcn/ui Components** - Modern, accessible UI components

### Testing
- **Pest v4** - Modern PHP testing with browser testing capabilities
- **Feature Tests** - Complete end-to-end testing
- **Unit Tests** - Comprehensive model and service testing
- **Browser Tests** - Real browser testing for critical workflows
- **Factory Pattern** - Rich test data generation

### Database
- **MySQL/PostgreSQL** - Robust relational database support
- **Migrations** - Version-controlled database schemas
- **Seeders** - Development data population
- **Factories** - Test data generation

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.4 or higher
- Composer
- Node.js 18+ & npm
- MySQL 8.0+ or PostgreSQL 13+
- Laravel Herd (recommended) or similar local development environment

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone <repository-url> invoice-system
   cd invoice-system
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Configuration**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=invoice_system
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed Development Data (Optional)**
   ```bash
   php artisan db:seed --class=DevelopmentSeeder
   ```

8. **Build Frontend Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

9. **Configure Mail Settings**
   Update `.env` for email functionality:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-email
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email
   MAIL_FROM_NAME="Your Company"
   ```

10. **Start the Application**
    ```bash
    # If using Laravel Herd
    # Application will be available at https://invoice-system.test
    
    # Or using artisan serve
    php artisan serve
    ```

### Default Login (Development Seeder)
- **Email:** admin@example.com
- **Password:** password

## 📱 Usage

### Creating Your First Invoice

1. **Navigate to Invoices** → Click "Create Invoice"
2. **Select or Create Client** → Choose existing client or create new one
3. **Add Invoice Details** → Set dates, currency, tax rates
4. **Add Line Items** → Add products/services with quantities and prices
5. **Review Totals** → Check calculations including tax and discounts
6. **Save & Send** → Save as draft or send directly to client

### Managing Payments

1. **Open Invoice** → Click on any sent/overdue invoice
2. **Record Payment** → Click "Record Payment" button
3. **Enter Details** → Amount, date, method, reference number
4. **Save Payment** → Invoice status updates automatically

### Setting Up Recurring Invoices

1. **Go to Recurring Invoices** → Navigate from main menu
2. **Create Template** → Set up client, items, and schedule
3. **Configure Frequency** → Choose daily, weekly, monthly, etc.
4. **Set Parameters** → End date, maximum cycles, payment terms
5. **Activate** → Enable automatic generation

## 🔧 Configuration

### Email Templates
Customize email templates in `resources/views/emails/invoice.blade.php`

### PDF Templates  
Modify PDF layout in `resources/views/invoices/pdf.blade.php`

### Company Settings
Update company information in the Settings panel or via configuration files

### Currency Settings
Add new currencies through the Currencies management section

## 🚨 Maintenance

### Automated Tasks
Set up cron jobs for automated maintenance:

```bash
# Mark overdue invoices (run daily)
0 9 * * * php artisan invoices:mark-overdue --notify

# Generate recurring invoices (run daily)  
0 8 * * * php artisan invoices:generate-recurring

# Send payment reminders (if implemented)
0 10 * * * php artisan invoices:send-reminders
```

### Database Maintenance
```bash
# Create database backup
php artisan settings:backup

# Clear application cache
php artisan optimize:clear

# Update currency exchange rates (if implemented)
php artisan currencies:update-rates
```

## 🧪 Testing

### Run All Tests
```bash
vendor/bin/pest
```

### Run Specific Test Categories
```bash
# Feature tests only
vendor/bin/pest tests/Feature

# Unit tests only  
vendor/bin/pest tests/Unit

# Browser tests only
vendor/bin/pest tests/Browser

# API tests only
vendor/bin/pest tests/Feature/Api
```

### Run Tests with Coverage
```bash
vendor/bin/pest --coverage
```

### Browser Testing
Browser tests require Chrome/Chromium:
```bash
vendor/bin/pest tests/Browser --parallel
```

## 📡 API Documentation

### Base URL
```
/api/
```

### Authentication
All API endpoints require authentication via session or API token.

### Invoice Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/invoices` | List all invoices with filtering |
| POST | `/api/invoices` | Create new invoice |
| GET | `/api/invoices/{id}` | Get specific invoice |
| PUT | `/api/invoices/{id}` | Update invoice (draft only) |
| DELETE | `/api/invoices/{id}` | Delete invoice |
| POST | `/api/invoices/{id}/send` | Send invoice to client |
| POST | `/api/invoices/{id}/mark-paid` | Mark invoice as paid |
| GET | `/api/invoices/stats` | Get invoice statistics |

### Query Parameters
- `status` - Filter by invoice status
- `client_id` - Filter by client
- `search` - Search invoice numbers and client names  
- `date_from` / `date_to` - Date range filtering
- `per_page` - Results per page (max 100)
- `page` - Page number for pagination

### Example API Usage
```javascript
// Get all paid invoices
fetch('/api/invoices?status=paid')
  .then(response => response.json())
  .then(data => console.log(data));

// Create new invoice
fetch('/api/invoices', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    client_id: 1,
    issue_date: '2024-01-15',
    due_date: '2024-02-15',
    items: [
      {
        description: 'Web Development',
        quantity: 1,
        unit_price: 1000
      }
    ]
  })
});
```

## 🔒 Security

### Form Request Validation
- All inputs validated through dedicated Form Request classes
- Custom validation rules and error messages
- Protection against mass assignment vulnerabilities

### Authorization
- Route-level authentication requirements
- Model-based authorization for sensitive operations
- CSRF protection on all state-changing operations

### Data Protection  
- Sensitive data properly encrypted
- SQL injection protection via Eloquent ORM
- XSS protection through proper output escaping

## 📈 Performance

### Database Optimization
- Proper indexing on frequently queried columns
- Eager loading to prevent N+1 query problems
- Database query optimization with select specific columns

### Caching
- Built-in Laravel caching for improved performance
- Asset optimization with Vite
- Browser caching for static assets

### Queue System
- Email sending via queues to improve response times
- Background processing for heavy operations
- Failed job handling and retry logic

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality  
4. Ensure all tests pass
5. Run code formatting: `vendor/bin/pint`
6. Submit pull request

### Code Standards
- Follow PSR-12 coding standards
- Use Laravel's conventions and best practices
- Write comprehensive tests for all features
- Document public methods and complex logic
- Use TypeScript for frontend development

### Testing Requirements
- All new features must include tests
- Maintain minimum 80% code coverage
- Include browser tests for user-facing features
- Test both happy path and error scenarios

## 📞 Support

### Documentation
- In-app help documentation
- API documentation available at `/api/docs` (if implemented)
- Video tutorials and guides (if available)

### Common Issues
- **Email not sending**: Check mail configuration and queue workers
- **PDF generation fails**: Verify DomPDF requirements and memory limits  
- **Frontend not updating**: Run `npm run build` and clear browser cache
- **Database errors**: Check migrations and seeder status

### Getting Help
- Check the issue tracker for known problems
- Review the documentation thoroughly
- Create detailed bug reports with reproduction steps
- Include system information and error logs

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🎯 Roadmap

### Planned Features
- [ ] Multi-tenant support for agencies
- [ ] Advanced reporting dashboard with charts
- [ ] Integration with popular payment gateways (Stripe, PayPal)
- [ ] Mobile app for iOS and Android
- [ ] Advanced workflow automation
- [ ] Integration with accounting software (QuickBooks, Xero)
- [ ] Multi-language support with translations
- [ ] Advanced permission system with roles
- [ ] Invoice templates with custom branding
- [ ] Time tracking integration
- [ ] Expense management module
- [ ] Client portal for self-service
- [ ] Advanced notification system with webhooks
- [ ] Bulk operations and batch processing
- [ ] Advanced search with full-text indexing

### Recent Updates
- ✅ Laravel 12 upgrade with modern architecture
- ✅ Pest v4 browser testing implementation
- ✅ Enhanced API with comprehensive endpoints
- ✅ Modern Vue 3 + TypeScript frontend
- ✅ Tailwind CSS v4 with improved performance
- ✅ Comprehensive test coverage
- ✅ Professional PDF generation system
- ✅ Email notification system with queues
- ✅ Multi-currency support with exchange rates
- ✅ Recurring invoice automation
- ✅ Console commands for maintenance tasks

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies**
