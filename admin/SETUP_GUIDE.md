# LSUC Admin Dashboard - Setup Guide

## 🎉 COMPREHENSIVE ADMIN DASHBOARD SYSTEM

Welcome to the Lusaka South University College Admin Dashboard - a WordPress-like content management system for your website!

---

## 📋 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [Installation Steps](#installation-steps)
3. [Admin Credentials](#admin-credentials)
4. [Dashboard Features](#dashboard-features)
5. [Module Guide](#module-guide)
6. [Troubleshooting](#troubleshooting)

---

## 🚀 SYSTEM OVERVIEW

### What's Included:

✅ **Complete Admin Dashboard** with sidebar navigation
✅ **Login/Authentication System** with session management
✅ **Activity Logging** for security
✅ **Statistics Dashboard** showing all content metrics
✅ **Quick Actions** for common tasks
✅ **Responsive Design** for mobile/tablet use
✅ **JSON File Storage** (no database required initially)
✅ **Modular Architecture** for easy expansion

### Current Modules:
- 📊 Dashboard Home (with statistics)
- 🏠 Home Page Management (coming in next phase)
- ℹ️ About Section Management (coming soon)
- 📰 Events Management (existing system integrated)
- 🎓 Schools & Programs (coming soon)
- ❓ FAQs Management (coming soon)
- 🖼️ Gallery Management (coming soon)
- 📥 Downloads Management (coming soon)
- ⚙️ Settings (coming soon)

---

## 📦 INSTALLATION STEPS

### Prerequisites:
- XAMPP installed with PHP 7.4 or higher
- Your website in `C:\xampp\htdocs\lsuc_website`
- Modern web browser (Chrome, Firefox, Edge)

### Step 1: Verify Installation

The admin folder has been created at:
```
C:\xampp\htdocs\lsuc_website\admin\
```

With structure:
```
admin/
├── index.php (login page)
├── dashboard.php (main dashboard)
├── logout.php
├── modules/
│   └── dashboard_home.php
├── assets/
│   ├── admin_styles.css
│   └── admin_scripts.js
├── api/
├── uploads/
└── data/
```

### Step 2: Set Permissions (if on Linux/Mac)

```bash
chmod 755 admin/data
chmod 755 admin/uploads
```

For Windows, ensure the folders are writable.

### Step 3: Access Admin Panel

1. Start XAMPP (Apache must be running)
2. Open browser and navigate to:
   ```
   http://localhost/lsuc_website/admin/
   ```
   
   OR if using file protocol:
   ```
   file:///C:/xampp/htdocs/lsuc_website/admin/index.php
   ```

### Step 4: First Login

Use the default credentials:
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change this password after first login!

---

## 🔐 ADMIN CREDENTIALS

### Default Login:
```
Username: admin
Password: admin123
```

### To Change Password:

Edit `admin/index.php` line ~33:
```php
$valid_password_hash = password_hash('YOUR_NEW_PASSWORD', PASSWORD_DEFAULT);
```

Replace `'admin123'` with your desired password.

---

## 🎯 DASHBOARD FEATURES

### 1. **Statistics Cards**
Real-time metrics showing:
- Total Events (broken down by category)
- Number of FAQs
- Gallery Images Count
- Downloads Count

### 2. **Quick Actions**
One-click access to:
- Add New Event
- Add New FAQ
- Upload Images
- Upload Document

### 3. **Recent Activity Feed**
Shows last 10 actions:
- Who logged in/out
- What content was modified
- When changes were made
- IP address tracking

### 4. **Session Management**
- 30-minute timeout
- Visual countdown timer
- Auto-logout for security
- "Remember Me" option (30 days)

---

## 📚 MODULE GUIDE

### Accessing Modules:

Each module is accessed via the sidebar menu:

#### **Dashboard Home** (`?page=dashboard`)
- Default landing page
- Shows overview statistics
- Recent activity
- Quick actions

#### **Events Management** (`?page=events`)
- Integrates with existing event system
- Add/Edit/Delete events
- Manage categories
- Upload images

*Note: Other modules will be added as we continue development*

---

## 🔧 SECURITY FEATURES

✅ **Password Hashing** (bcrypt)
✅ **Session Timeout** (30 minutes)
✅ **Activity Logging**
✅ **IP Address Tracking**
✅ **CSRF Protection Ready**
✅ **XSS Prevention** (htmlspecialchars used)
✅ **SQL Injection Prevention** (using JSON files)

---

## 💾 DATA STORAGE

### Location:
```
admin/data/
├── events.json
├── faqs.json
├── gallery.json
├── downloads.json
├── settings.json
└── activity_log.json
```

### Backup Strategy:
Simply copy the `admin/data/` folder to backup all content!

### Migration from localStorage:

Your existing events in localStorage can be migrated:

1. Open browser console on main site
2. Run: `console.log(localStorage.getItem('lsuc_events_data'))`
3. Copy the JSON output
4. Save as `admin/data/events.json`

---

## 🎨 CUSTOMIZATION

### Colors:
Edit `admin/assets/admin_styles.css`:
```css
:root {
    --primary-orange: #ff8c00;
    --primary-green: #2e8b57;
    --dark-green: #1e5a3a;
}
```

### Logo:
Edit sidebar logo in `dashboard.php` line ~60

### Session Timeout:
Edit `dashboard.php` line ~19:
```php
if (time() - ($_SESSION['login_time'] ?? 0) > 1800) { // 1800 = 30 min
```

---

## 📱 MOBILE RESPONSIVENESS

The admin panel is fully responsive:
- **Desktop:** Full sidebar visible
- **Tablet:** Collapsible sidebar
- **Mobile:** Hamburger menu

Toggle sidebar with the ☰ button on mobile.

---

## 🐛 TROUBLESHOOTING

### Issue: Can't access admin panel

**Solution:**
1. Ensure XAMPP Apache is running
2. Check URL: `http://localhost/lsuc_website/admin/`
3. Clear browser cache
4. Check file permissions

### Issue: Login fails

**Solution:**
1. Verify username: `admin`
2. Verify password: `admin123`
3. Check cookies are enabled
4. Try incognito/private mode

### Issue: Statistics show 0

**Solution:**
1. This is normal for new installation
2. Add some events/FAQs/gallery images
3. Data files auto-create when content added

### Issue: Session expires quickly

**Solution:**
1. Increase timeout in `dashboard.php`
2. Don't leave idle for >30 minutes
3. Use "Remember Me" option

### Issue: Can't upload files

**Solution:**
1. Check `admin/uploads/` folder exists
2. Ensure folder is writable
3. Check PHP upload limits in `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

---

## 🚀 NEXT STEPS

### Phase 1 (Current):
✅ Login system
✅ Dashboard layout
✅ Statistics display
✅ Activity logging

### Phase 2 (Coming Next):
- Home Page Management module
- FAQs Management module
- Events integration improvements

### Phase 3 (Future):
- Schools & Programs
- Gallery Management
- Downloads Management
- Settings panel

---

## 📞 SUPPORT

For issues or questions:
1. Check this guide first
2. Review error logs in browser console (F12)
3. Check PHP error logs in `xampp/apache/logs/error.log`

---

## ✅ QUICK START CHECKLIST

- [ ] XAMPP running
- [ ] Navigate to `http://localhost/lsuc_website/admin/`
- [ ] Login with `admin` / `admin123`
- [ ] Explore dashboard
- [ ] Check statistics
- [ ] Test navigation menu
- [ ] Logout and login again
- [ ] Change default password

---

## 🎉 SUCCESS!

You now have a professional admin dashboard for managing your LSUC website content!

**Access Points:**
- **Admin Panel:** `http://localhost/lsuc_website/admin/`
- **Main Site:** `http://localhost/lsuc_website/index.html`
- **Logout:** `http://localhost/lsuc_website/admin/logout.php`

Enjoy your WordPress-like CMS experience! 🚀
