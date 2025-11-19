# ✅ Railway Deployment Checklist

## 📋 Pre-Deployment Checklist

### File Konfigurasi
- [x] `router.php` - PHP built-in server router
- [x] `.htaccess` - Apache configuration (untuk local)
- [x] `nixpacks.toml` - Nixpacks build config
- [x] `railway.json` - Railway deployment settings
- [x] `.gitignore` - Ignore patterns
- [x] `config/database.php` - Environment variables support
- [x] `uploads/.gitkeep` - Keep uploads folder
- [x] `uploads/profile/.gitkeep` - Keep profile folder

### Database Files
- [x] `database.sql` - Main schema
- [x] `insert_default_data.sql` - Default users & data
- [x] `insert_jadwal.sql` - Jadwal data (jika ada)
- [x] `update_ruangan.sql` - Ruangan updates (jika ada)

### Documentation
- [x] `RAILWAY_DEPLOYMENT.md` - Full deployment guide
- [x] `QUICKSTART_RAILWAY.md` - Quick start guide
- [x] `README.md` - Updated with Railway info

### Testing Files
- [x] `test_db_connection.php` - Database connection test

---

## 🚀 Deployment Steps Checklist

### 1. Git Repository
- [ ] Run `git init` in project folder
- [ ] Run `git add .`
- [ ] Run `git commit -m "Initial commit"`
- [ ] Create GitHub repository
- [ ] Run `git remote add origin <URL>`
- [ ] Run `git push -u origin main`

### 2. Railway Setup
- [ ] Login to [railway.app](https://railway.app)
- [ ] Click **New Project**
- [ ] Click **Provision MySQL**
- [ ] Wait for MySQL to be ready (green status)
- [ ] Click **New** → **GitHub Repo**
- [ ] Select your repository
- [ ] Wait for build to complete

### 3. Link Database
- [ ] Click on PHP application service
- [ ] Go to **Variables** tab
- [ ] Click **Add Reference**
- [ ] Select MySQL database
- [ ] Verify all MYSQL_* variables are added
- [ ] Wait 1-2 minutes for changes to apply

### 4. Generate Domain
- [ ] Go to **Settings** tab
- [ ] Scroll to **Domains** section
- [ ] Click **Generate Domain**
- [ ] Copy the generated URL
- [ ] (Optional) Add custom domain

### 5. Import Database
- [ ] Click MySQL service
- [ ] Go to **Data** tab
- [ ] Click **Query** button
- [ ] Copy contents of `database.sql`
- [ ] Click **Run** to execute
- [ ] Repeat for `insert_default_data.sql`

### 6. Testing
- [ ] Open generated domain URL
- [ ] Visit `https://your-domain.railway.app/test_db_connection.php`
- [ ] Verify database connection is successful
- [ ] Verify all tables exist
- [ ] Test login with admin/password
- [ ] Test login with petugas/password
- [ ] Test register new user
- [ ] Test all main features

### 7. Post-Deployment
- [ ] Change admin password
- [ ] Change petugas password
- [ ] Delete `test_db_connection.php` (security)
- [ ] Delete or comment test files in `.gitignore`
- [ ] Configure Cloudinary for file uploads (production)
- [ ] Setup backup strategy
- [ ] Monitor Railway logs
- [ ] Check Railway usage/billing

---

## 🔒 Security Checklist

- [ ] Admin password changed from default
- [ ] Petugas password changed from default
- [ ] Test files deleted from production
- [ ] Database credentials not exposed
- [ ] File upload validation working
- [ ] SQL injection protection verified
- [ ] XSS protection verified
- [ ] Session security configured

---

## 📊 Monitoring Checklist

- [ ] Check Railway deployment logs
- [ ] Monitor database usage
- [ ] Monitor application errors
- [ ] Setup uptime monitoring (UptimeRobot, etc)
- [ ] Test backup and restore process

---

## 🎯 Optional Enhancements

- [ ] Setup Cloudinary for persistent file storage
- [ ] Configure custom domain with SSL
- [ ] Setup email notifications (SendGrid, Mailgun)
- [ ] Add error tracking (Sentry)
- [ ] Setup cron jobs (via Railway)
- [ ] Configure CDN for static assets
- [ ] Add analytics (Google Analytics)

---

## ❌ Common Issues & Solutions

### Database Connection Failed
- [ ] Check if MySQL service is running
- [ ] Verify environment variables are linked
- [ ] Check Railway logs for errors
- [ ] Wait 2-3 minutes after linking

### 404 Not Found
- [ ] Check build logs for errors
- [ ] Verify `.htaccess` file exists
- [ ] Check DirectoryIndex setting
- [ ] Wait for build to complete

### 500 Internal Server Error
- [ ] Check PHP error logs in Railway
- [ ] Verify all required PHP extensions
- [ ] Check file permissions
- [ ] Verify database schema

### File Upload Issues
- [ ] Remember: Railway uses ephemeral storage
- [ ] Files will be lost on restart
- [ ] Setup external storage (Cloudinary, S3)
- [ ] For testing, use sample images

---

## 📞 Support Resources

- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Railway Status: https://status.railway.app
- PHP Documentation: https://php.net
- MySQL Documentation: https://dev.mysql.com/doc/

---

**Last Updated:** 2025-11-19

**Deployment Status:** 
- [ ] Not Started
- [ ] In Progress
- [ ] Completed
- [ ] Verified

---

Good luck with your deployment! 🚀
