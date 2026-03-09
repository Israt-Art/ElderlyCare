# Elderly Care Residence Management & Emergency Support Platform

A comprehensive web-based platform for managing elderly care residences with health monitoring, meal management, emergency support, and financial tracking features.

## Features

### 1. Authentication System
- Role-based login (Elderly User / Admin)
- Secure session management
- Role-based redirection after login

### 2. Elderly User Dashboard
- Update daily health information (sugar level, blood pressure, overall condition)
- Select meals for breakfast, lunch, snacks, and dinner
- View health history and meal history
- Emergency alert button (24/7 support)
- View account balance

### 3. Admin/Management Dashboard
- View today's health updates from all residents
- View today's meal selections
- Resident list with room numbers and health conditions
- Emergency alert management
- Financial summaries and transaction history
- Generate monthly reports (health, meals, financial, emergencies)
- View resident statistics and analytics

### 4. Resident Management
- Store resident information (name, age, photo, room number)
- Medical information (diabetes, blood pressure, allergies)
- Daily health logs
- Medicine schedule tracking

### 5. Food & Kitchen Management
- Automatic grouping based on diet type:
  - Diabetes-friendly
  - Low-salt
  - Soft food
  - Heart patient diet
- Daily and weekly meal plans
- Meal history tracking

### 6. Emergency Support System
- Red emergency button for each resident
- Emergency alert logs with timestamp and room number
- Admin response tracking
- Emergency incident history

### 7. Financial Transparency
- Resident account management
- Multiple payment methods (Card, bKash, Rocket, Cash)
- Deposit and withdrawal tracking
- Service charges
- Monthly financial reports
- View-only access for family members

### 8. Premium Services
- Premium package: ৳10,000 BDT
- Benefits:
  - Extra checkups
  - Priority medical response
  - Private caregiver
  - Special meals
- Automatic billing adjustment

### 9. Rating & Feedback
- Rate food service, cleanliness, and medical care
- Automatic ranking calculation
- Feedback tracking

### 10. Tracking & Reports
- Daily tracking:
  - Sleep time
  - Medicine intake
  - Sugar level
  - Blood pressure
- Health improvement history
- Monthly reports per resident

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache/Nginx with PHP

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP extensions: mysqli, pdo_mysql, mbstring

### Setup Steps

1. **Clone or Download the Project**
   ```bash
   # Place the project files in your web server directory
   # For XAMPP: C:\xampp\htdocs\elderly_care
   # For WAMP: C:\wamp64\www\elderly_care
   ```

2. **Create Database**
   - Open phpMyAdmin or MySQL command line
   - Import the database schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Or import `database/schema.sql` through phpMyAdmin

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'elderly_care_db');
   ```

4. **Configure Site URL**
   - Edit `config/config.php`
   - Update if needed:
   ```php
   define('SITE_URL', 'http://localhost/elderly_care');
   ```

5. **Set Permissions**
   - Ensure `assets/images` folder is writable (for photo uploads)
   ```bash
   chmod 755 assets/images
   ```

6. **Access the Application**
   - Open browser: `http://localhost/elderly_care`
   - Or: `http://localhost/elderly_care/index.php`

## Default Login Credentials

### Admin
- **Username:** `admin`
- **Password:** `admin123`

### Elderly User
- **Username:** `elderly1`
- **Password:** `elderly123`

**Note:** These are default credentials from sample data. Change passwords in production!

## Project Structure

```
elderly_care/
├── config/
│   ├── config.php          # General configuration
│   └── database.php        # Database configuration
├── css/
│   ├── style.css          # Main stylesheet
│   └── admin.css          # Admin-specific styles
├── js/
│   ├── main.js            # Common JavaScript
│   ├── dashboard_elderly.js  # Elderly dashboard JS
│   └── dashboard_admin.js    # Admin dashboard JS
├── php/
│   ├── login.php          # Login processing
│   ├── logout.php         # Logout processing
│   ├── update_health.php  # Health update handler
│   ├── submit_meals.php   # Meal selection handler
│   ├── emergency.php      # Emergency alert handler
│   ├── get_health_history.php  # Health history API
│   ├── get_meal_history.php    # Meal history API
│   ├── get_meal_plans.php      # Meal plans API
│   └── admin/
│       ├── respond_emergency.php  # Emergency response handler
│       └── generate_report.php    # Report generator
├── pages/
│   ├── home.php           # Home page
│   ├── login.php          # Login page
│   ├── about.php          # About page
│   ├── services.php       # Services page
│   ├── contact.php        # Contact page
│   └── admin/
│       ├── overview.php   # Admin overview
│       ├── residents.php  # Residents list
│       ├── health.php     # Health updates
│       ├── meals.php      # Meal selections
│       ├── emergencies.php # Emergency alerts
│       ├── finance.php    # Financial management
│       └── reports.php    # Reports & analytics
├── database/
│   └── schema.sql         # Database schema
├── assets/
│   └── images/            # Resident photos (create this folder)
├── index.php              # Main entry point
├── dashboard_elderly.php  # Elderly user dashboard
├── dashboard_admin.php    # Admin dashboard
└── README.md             # This file
```

## Database Schema

The database includes the following tables:
- `users` - Authentication and user accounts
- `residents` - Resident information
- `medical_info` - Medical conditions and diet types
- `health_records` - Daily health tracking
- `meal_choices` - Meal selections
- `meal_plans` - Available meal plans
- `emergency_logs` - Emergency incidents
- `payments` - Financial transactions
- `account_balance` - Resident account balances
- `premium_services` - Premium subscriptions
- `ratings` - Ratings and feedback
- `medicine_schedule` - Medicine schedules

## Usage

### For Elderly Users:
1. Login with elderly credentials
2. Update daily health information
3. Select meals for the day
4. View health and meal history
5. Use emergency button if needed

### For Admin:
1. Login with admin credentials
2. View overview dashboard
3. Monitor residents' health updates
4. Manage emergency alerts
5. View financial summaries
6. Generate monthly reports

## Security Notes

- **Password Hashing:** In production, ensure all passwords are properly hashed using `password_hash()`
- **SQL Injection:** All queries use prepared statements
- **XSS Protection:** Output is escaped using `htmlspecialchars()`
- **Session Security:** Ensure secure session configuration in production
- **File Uploads:** Implement proper validation for file uploads

## Customization

### Changing Premium Package Price
Edit `config/config.php`:
```php
define('PREMIUM_PACKAGE_PRICE', 10000.00);
```

### Adding More Diet Types
Update the `diet_type` ENUM in `medical_info` table and `meal_plans` table.

### Modifying Meal Types
Update the `meal_type` ENUM in `meal_choices` and `meal_plans` tables.

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database name exists

### Session Errors
- Ensure `session_start()` is called before any output
- Check PHP session configuration
- Verify file permissions

### CSS/JS Not Loading
- Check file paths (relative paths are used)
- Ensure CSS and JS folders exist
- Clear browser cache

## Future Enhancements

- PDF report generation
- Email notifications
- SMS alerts for emergencies
- Mobile app integration
- Real-time chat support
- Appointment scheduling
- Medication reminders
- Family member portal

## License

This is an academic project for educational purposes.

## Support

For issues or questions:
- Check the documentation
- Review the code comments
- Contact the development team

## Credits

Developed as a comprehensive full-stack web application using HTML, CSS, PHP, and MySQL.

---

**Note:** This is an academic project. For production use, implement additional security measures, error handling, and testing.

