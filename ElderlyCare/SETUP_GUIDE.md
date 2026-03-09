# Quick Setup Guide - Elderly Care Platform

## 🚀 Quick Start (5 Minutes)

### Step 1: Database Setup
```bash
# Open phpMyAdmin or MySQL command line
# Import the database schema
mysql -u root -p < database/schema.sql
```
Or import `database/schema.sql` through phpMyAdmin interface.

### Step 2: Configure Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change if needed
define('DB_PASS', '');          // Change if needed
define('DB_NAME', 'elderly_care_db');
```

### Step 3: Start Web Server
```bash
# For XAMPP: Start Apache and MySQL from XAMPP Control Panel
# For WAMP: Start all services from WAMP icon
# For Laragon: Start all services
```

### Step 4: Access Application
Open browser: `http://localhost/elderly_care/`

Or if using a different path:
- `http://localhost/nishi_gadha/`
- `http://localhost/[your-folder-name]/`

## 🔑 Login Credentials

### Admin Dashboard
- **URL:** `http://localhost/elderly_care/index.php?page=login`
- **Username:** `admin`
- **Password:** `admin123`

### Elderly User Dashboard
- **URL:** `http://localhost/elderly_care/index.php?page=login`
- **Username:** `elderly1`
- **Password:** `elderly123`
- **Select:** "Elderly User" from dropdown

## 📁 Important Folders

1. **assets/images/** - Place resident photos here
2. **config/** - Configuration files (already set up)
3. **database/** - SQL schema file
4. **php/** - Backend PHP files
5. **pages/** - Frontend pages

## ⚙️ Configuration Files

### Database Configuration
**File:** `config/database.php`
- Change `DB_USER` if your MySQL username is different
- Change `DB_PASS` if you have a MySQL password
- Change `DB_NAME` only if you want a different database name

### Site Configuration
**File:** `config/config.php`
- `SITE_URL` - Update if your installation path is different
- `PREMIUM_PACKAGE_PRICE` - Change premium price (default: 10,000 BDT)

## 🧪 Testing Checklist

After setup, test these features:

### Elderly User:
1. ✅ Login with elderly credentials
2. ✅ Update health information (sugar, BP, condition)
3. ✅ Select meals (breakfast, lunch, snacks, dinner)
4. ✅ View health history
5. ✅ View meal history
6. ✅ Click emergency button (tests alert system)

### Admin:
1. ✅ Login with admin credentials
2. ✅ View dashboard overview
3. ✅ Check residents list
4. ✅ View today's health updates
5. ✅ View today's meal selections
6. ✅ Check emergency alerts
7. ✅ View financial summaries
8. ✅ Generate monthly report

## 🐛 Troubleshooting

### "Database connection failed"
- Check MySQL is running
- Verify credentials in `config/database.php`
- Ensure database exists (import schema.sql)

### "Page not found"
- Check Apache is running
- Verify file paths
- Check .htaccess file exists

### "CSS/JS not loading"
- Check file paths in HTML
- Clear browser cache
- Verify css/ and js/ folders exist

### "Session errors"
- Ensure `session_start()` is called
- Check PHP session configuration
- Verify file permissions

### "Can't login"
- Use default credentials: admin/admin123 or elderly1/elderly123
- Check database has sample data
- Verify users table has records

## 📝 Default Sample Data

The database schema includes:
- 1 Admin user (`admin`)
- 1 Elderly user (`elderly1`)
- 3 Sample residents
- Sample medical info
- Sample account balances
- Sample meal plans for today

## 🔄 Updating Sample Data

To add more sample data, edit `database/schema.sql` and re-import, or use phpMyAdmin to:
- Add residents in `residents` table
- Link users in `users` table
- Add medical info in `medical_info` table
- Initialize account balances in `account_balance` table

## 🎓 For Academic Presentation

### What to Show:
1. **Home Page** - Landing page with features
2. **Login** - Show both user types
3. **Elderly Dashboard** - Health update, meal selection
4. **Admin Dashboard** - All features (overview, residents, health, meals, emergencies, finance, reports)
5. **Database** - Show tables and relationships in phpMyAdmin
6. **Reports** - Generate a sample monthly report

### Key Points to Highlight:
- ✅ Complete feature implementation
- ✅ Role-based access control
- ✅ Database design (12 tables)
- ✅ Security (prepared statements, XSS protection)
- ✅ Responsive design
- ✅ Clean, commented code
- ✅ Comprehensive documentation

## 📞 Support

For issues:
1. Check README.md for detailed documentation
2. Review PROJECT_SUMMARY.md for feature list
3. Check database schema.sql for table structures
4. Review code comments for implementation details

## ✅ Ready to Use!

Once setup is complete, the platform is fully functional and ready for:
- ✅ Academic presentation
- ✅ Exam submission
- ✅ Demo/testing
- ✅ Further development

**Happy Coding! 🎉**
