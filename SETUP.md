# WordPress Site Connection Setup Guide

## Overview

This guide will help you set up WordPress and the WP Site Connector plugin to synchronize content between a local site and a remote site.

## Part 1: WordPress Setup

### Option A: Using Existing WordPress Installations

If you already have WordPress installed on both local and remote servers, skip to Part 2.

### Option B: Setting Up New WordPress Installations

#### For Local Development (Choose One):

**1. Local by Flywheel** (Recommended for beginners)
```bash
# Download from: https://localwp.com/
# Install and create a new WordPress site
# Choose: PHP 7.4+, MySQL 8.0, WordPress latest
```

**2. XAMPP/MAMP**
```bash
# Download from: https://www.apachefriends.org/ (XAMPP) or https://www.mamp.info/ (MAMP)
# Install and start Apache and MySQL
# Download WordPress from https://wordpress.org/download/
# Extract to htdocs/www directory
# Create database and install WordPress
```

**3. Docker** (For developers)
```bash
# Create docker-compose.yml
version: '3.8'
services:
  wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./wordpress:/var/www/html
  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: somewordpress

# Run: docker-compose up -d
# Access: http://localhost:8080
```

**4. WP-CLI** (Command line)
```bash
# Download WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Download WordPress
wp core download

# Create wp-config.php
wp config create --dbname=wordpress --dbuser=root --dbpass=password

# Install WordPress
wp core install --url=http://localhost --title="Local Site" --admin_user=admin --admin_password=password --admin_email=admin@example.com
```

#### For Remote/Production Site:

**Option 1: Managed WordPress Hosting**
- Use services like WP Engine, Kinsta, SiteGround, or Bluehost
- Follow their WordPress installation wizard

**Option 2: VPS/Dedicated Server**
```bash
# Install LAMP/LEMP stack
# Ubuntu example:
sudo apt update
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php

# Download and install WordPress
cd /var/www/html
sudo wget https://wordpress.org/latest.tar.gz
sudo tar -xzvf latest.tar.gz
sudo mv wordpress/* .
sudo rm -rf wordpress latest.tar.gz

# Set permissions
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# Create MySQL database
sudo mysql -u root -p
CREATE DATABASE wordpress;
CREATE USER 'wpuser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON wordpress.* TO 'wpuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Complete WordPress installation via browser
```

## Part 2: Plugin Installation

### Step 1: Install Plugin on Both Sites

#### Method 1: Manual Upload via WordPress Admin

**On Local Site:**
1. Log in to WordPress admin (http://localhost/wp-admin or your local URL)
2. Go to **Plugins → Add New → Upload Plugin**
3. If you have a zip file:
   - Click "Choose File" and select `wp-site-connector.zip`
   - Click "Install Now"
4. If using plugin folder:
   - Copy `wp-site-connector` folder to `wp-content/plugins/`
5. Click **Activate Plugin**

**On Remote Site:**
1. Repeat the same process
2. Or use FTP/SFTP to upload the plugin folder

#### Method 2: Direct File Copy

**On Local Site:**
```bash
# Navigate to WordPress directory
cd /path/to/wordpress

# Copy plugin folder
cp -r /path/to/wp-connection/wp-site-connector wp-content/plugins/

# Set proper permissions
chmod -R 755 wp-content/plugins/wp-site-connector
```

**On Remote Site:**
```bash
# Using SCP (from local machine)
scp -r wp-site-connector user@remote-server:/path/to/wordpress/wp-content/plugins/

# Or using SFTP
sftp user@remote-server
put -r wp-site-connector /path/to/wordpress/wp-content/plugins/
```

#### Method 3: Using WP-CLI

**On Both Sites:**
```bash
# Navigate to WordPress directory
cd /path/to/wordpress

# Copy plugin (if not already there)
cp -r /path/to/wp-site-connector wp-content/plugins/

# Activate plugin
wp plugin activate wp-site-connector
```

### Step 2: Configure Remote Site (Receiver)

1. **Access WordPress Admin**
   ```
   https://your-remote-site.com/wp-admin
   ```

2. **Navigate to Plugin Settings**
   - Go to **Settings → Site Connector**

3. **Copy API Key**
   - Find the "Local API Key" field
   - Copy the entire key (should be 32 characters)
   - Save this somewhere secure (you'll need it for the local site)

4. **Optional Settings**
   - Keep "Enable Sync" unchecked if this is only a receiver
   - Leave other fields empty if not syncing back

5. **Click "Save Changes"**

### Step 3: Configure Local Site (Sender)

1. **Access WordPress Admin**
   ```
   http://localhost/wp-admin (or your local URL)
   ```

2. **Navigate to Plugin Settings**
   - Go to **Settings → Site Connector**

3. **Enable Synchronization**
   - Check the **"Enable Sync"** checkbox

4. **Enter Remote Site URL**
   - In "Remote Site URL" field, enter your remote site URL
   - Example: `https://your-remote-site.com`
   - **Important**: No trailing slash!

5. **Enter Remote API Key**
   - Paste the API key you copied from the remote site

6. **Choose Content Types**
   - Check **"Sync Posts"** to sync blog posts
   - Check **"Sync Pages"** to sync pages
   - Or uncheck to disable specific types

7. **Click "Save Changes"**

### Step 4: Test Connection

1. **On Local Site Settings Page**
   - Scroll down to "Test Connection" section
   - Click **"Test Connection"** button

2. **Verify Success**
   - You should see: "Connection successful!"
   - If you see an error, troubleshoot using the guide below

### Step 5: Test Content Sync

1. **Create a Test Post**
   ```
   Local Site → Posts → Add New
   Title: "Test Sync Post"
   Content: "This is a test post to verify synchronization."
   Add a featured image
   Add categories and tags
   Click "Publish"
   ```

2. **Verify on Remote Site**
   ```
   Remote Site → Posts → All Posts
   Look for "Test Sync Post"
   Open the post to verify:
   - Content is correct
   - Featured image is present
   - Categories and tags are synced
   ```

3. **Check Sync Logs**
   ```
   Local Site → Settings → Site Connector
   Scroll to "Sync Logs" section
   Verify: status = "success"
   ```

## Part 3: Verification Checklist

### Pre-Flight Checks
- [ ] WordPress 5.0+ installed on both sites
- [ ] PHP 7.2+ on both sites
- [ ] Both sites accessible via HTTP/HTTPS
- [ ] Admin access to both sites
- [ ] Plugin uploaded and activated on both sites

### Configuration Checks
- [ ] Remote site API key copied
- [ ] Local site has "Enable Sync" checked
- [ ] Remote URL entered correctly (no trailing slash)
- [ ] Remote API key pasted correctly
- [ ] Content types (Posts/Pages) selected
- [ ] Settings saved on both sites

### Connection Checks
- [ ] Test connection shows "Connection successful!"
- [ ] No firewall blocking requests
- [ ] SSL certificates valid (if using HTTPS)
- [ ] REST API accessible on both sites

### Sync Checks
- [ ] Test post created on local site
- [ ] Test post appears on remote site
- [ ] Content matches exactly
- [ ] Featured image transferred
- [ ] Categories/tags transferred
- [ ] Sync logs show success status

## Part 4: Common Setup Issues

### Issue 1: "Connection failed"

**Possible Causes:**
- Remote site not accessible
- Firewall blocking requests
- Wrong URL

**Solutions:**
```bash
# Test remote site accessibility
curl -I https://your-remote-site.com

# Test REST API endpoint
curl -X POST https://your-remote-site.com/wp-json/wp-site-connector/v1/test \
  -H "X-API-Key: YOUR_API_KEY"

# Should return: {"success":true,"message":"Connection successful!"}
```

### Issue 2: "Authentication failed"

**Possible Causes:**
- Wrong API key
- Extra spaces in API key
- API key not saved

**Solutions:**
1. Regenerate API key on remote site
2. Copy and paste carefully (no spaces)
3. Save settings on local site
4. Test connection again

### Issue 3: REST API not accessible

**Possible Causes:**
- REST API disabled
- Permalink structure not set
- .htaccess issues

**Solutions:**
```bash
# Check if REST API works
curl https://your-site.com/wp-json/

# Should return JSON data

# Fix permalinks
# WordPress Admin → Settings → Permalinks
# Choose "Post name" or any option except "Plain"
# Click "Save Changes"

# Regenerate .htaccess
# Check if wp-content is writable
chmod 644 .htaccess
```

### Issue 4: Plugin not appearing

**Possible Causes:**
- Files not uploaded correctly
- Wrong directory structure
- PHP errors

**Solutions:**
```bash
# Check plugin directory structure
ls -la wp-content/plugins/wp-site-connector/

# Should show:
# wp-site-connector.php
# includes/
# assets/
# README.md
# etc.

# Check PHP errors
# Enable debugging in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

# Check debug.log
tail -f wp-content/debug.log
```

### Issue 5: Featured images not syncing

**Possible Causes:**
- `allow_url_fopen` disabled
- Images not publicly accessible
- File size too large

**Solutions:**
```bash
# Check PHP settings
php -i | grep allow_url_fopen

# Should show: allow_url_fopen => On

# Increase upload limits in php.ini
upload_max_filesize = 64M
post_max_size = 64M

# Restart web server
sudo systemctl restart apache2  # or nginx
```

## Part 5: Advanced Configuration

### Enabling HTTPS (Recommended)

**Using Let's Encrypt (Free SSL):**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d your-remote-site.com

# Auto-renewal is set up automatically
```

### Setting Up Cron Jobs for Log Cleanup

**On both sites:**
```php
// Add to wp-config.php
define('DISABLE_WP_CRON', false);

// Or set up system cron
*/30 * * * * cd /path/to/wordpress && wp cron event run --due-now
```

### Database Optimization

**Clean old logs:**
```bash
# Using WP-CLI
wp db query "DELETE FROM wp_site_connector_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"

# Or via phpMyAdmin
# Run SQL query to delete old logs
```

## Part 6: Testing Different Scenarios

### Test 1: Create Post
```
1. Local Site → Posts → Add New
2. Create post with content, featured image, categories
3. Publish
4. Verify on remote site
5. Check sync logs
```

### Test 2: Update Post
```
1. Local Site → Edit existing synced post
2. Change title, content, or image
3. Update
4. Verify changes on remote site
5. Check sync logs
```

### Test 3: Delete Post
```
1. Local Site → Trash a synced post
2. Verify post removed from remote site
3. Check sync logs
```

### Test 4: Create Page
```
1. Ensure "Sync Pages" is enabled
2. Local Site → Pages → Add New
3. Create and publish page
4. Verify on remote site
```

### Test 5: Categories and Tags
```
1. Create post with multiple categories and tags
2. Verify all taxonomies sync correctly
3. Check if new categories/tags are created on remote
```

## Part 7: Monitoring and Maintenance

### Regular Checks

**Daily:**
- Check sync logs for errors
- Verify recent posts synced correctly

**Weekly:**
- Review failed sync attempts
- Clean up old logs if needed

**Monthly:**
- Update WordPress core and plugins
- Test connection and sync
- Review and optimize database

### Log Monitoring

**Via WordPress Admin:**
```
Settings → Site Connector → Sync Logs
Filter by: status = "failed"
Review and resolve issues
```

**Via Database:**
```sql
SELECT * FROM wp_site_connector_logs 
WHERE status = 'failed' 
ORDER BY created_at DESC 
LIMIT 20;
```

## Part 8: Next Steps

Once setup is complete:

1. **Test thoroughly** with different content types
2. **Monitor sync logs** for the first few days
3. **Document your setup** for future reference
4. **Set up backups** on both sites
5. **Configure monitoring/alerts** for sync failures
6. **Train users** on the sync behavior

## Need Help?

- Check the main [README.md](README.md) for features and API
- Review [INSTALLATION.md](INSTALLATION.md) for detailed setup
- Check [WordPress documentation](https://wordpress.org/support/)
- Visit: https://github.com/inopik-lt/wp-connection

## Security Notes

⚠️ **Important Security Practices:**

1. Always use HTTPS for production
2. Keep API keys secure and private
3. Regularly update WordPress and plugins
4. Use strong admin passwords
5. Enable WordPress security plugins
6. Monitor sync logs for suspicious activity
7. Limit admin access to trusted users
8. Regular backups of both sites

## Success!

If all tests pass, your WordPress sites are now connected and syncing! 🎉

Any changes made on the local site will automatically appear on the remote site.
