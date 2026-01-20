# Installation and Setup Guide

## Quick Start Guide

This guide will help you set up WP Site Connector to sync content between a local WordPress site and a remote WordPress site.

### Prerequisites

- Two WordPress installations (local and remote)
- Admin access to both sites
- Both sites accessible via HTTP/HTTPS
- PHP 7.2 or higher on both sites
- WordPress 5.0 or higher on both sites

## Step-by-Step Setup

### Step 1: Install the Plugin

#### On Both Sites (Local and Remote):

1. Download the `wp-site-connector` plugin folder
2. Upload to `/wp-content/plugins/` directory
3. Navigate to **Plugins** in WordPress admin
4. Find "WP Site Connector" and click **Activate**

### Step 2: Configure the Remote Site (Receiver)

The remote site will receive content changes from the local site.

1. Log in to the remote WordPress admin
2. Go to **Settings → Site Connector**
3. You'll see the **Local API Key** field with an auto-generated key
4. **Copy this API key** (you'll need it for the local site)
5. Keep **Enable Sync** unchecked if you only want this site to receive updates
6. Click **Save Changes**

### Step 3: Configure the Local Site (Sender)

The local site will send content changes to the remote site.

1. Log in to the local WordPress admin
2. Go to **Settings → Site Connector**
3. Check the **Enable Sync** checkbox
4. In the **Remote Site URL** field, enter your remote site's URL
   - Example: `https://yoursite.com`
   - **Important**: Do not include trailing slash
   - Must include `https://` or `http://`
5. In the **Remote Site API Key** field, paste the API key you copied from the remote site
6. Enable the content types you want to sync:
   - Check **Sync Posts** to synchronize blog posts
   - Check **Sync Pages** to synchronize pages
7. Click **Save Changes**

### Step 4: Test the Connection

1. On the local site, scroll down to the **Test Connection** section
2. Click the **Test Connection** button
3. You should see a success message: "Connection successful!"
4. If you see an error:
   - Verify the remote URL is correct
   - Check the API key is correct
   - Ensure the remote site has the plugin activated
   - Check both sites are accessible

### Step 5: Test the Sync

1. On your local site, create a new post or page
2. Add some content, a featured image, and categories/tags
3. Publish the post
4. Go to your remote site and check if the post appears
5. Go to **Settings → Site Connector** on the local site
6. Check the **Sync Logs** section for the sync status

## Configuration Options

### Enable Sync
When checked, the plugin will automatically sync content to the remote site.

### Remote Site URL
The full URL of the remote WordPress site without trailing slash.
- ✅ Correct: `https://example.com`
- ❌ Incorrect: `https://example.com/`

### Remote Site API Key
The API key from the remote site. This authenticates your requests.

### Local API Key
Your site's API key for receiving updates. Share this with sites that want to send content to you.

### Sync Posts
Enable to synchronize blog posts.

### Sync Pages
Enable to synchronize pages.

## Bidirectional Sync (Optional)

If you want both sites to sync changes to each other:

1. Complete the setup above (local → remote)
2. On the remote site, enable sync and configure it to point to the local site
3. Use the local site's API key on the remote site

**Warning**: Be careful with bidirectional sync to avoid sync conflicts if the same content is edited on both sites simultaneously.

## Verifying the Setup

### Check 1: API Keys Match
- Remote site's local API key = Local site's remote API key ✓

### Check 2: Connection Test Passes
- Test connection button returns success ✓

### Check 3: Sync Logs Show Success
- Create test post on local site
- Check sync logs for "success" status ✓

### Check 4: Content Appears on Remote
- Test post visible on remote site ✓

## Common Setup Issues

### Issue: "Connection failed"
**Solution**: 
- Verify remote URL is accessible
- Check remote site has plugin activated
- Ensure no firewall blocking requests
- Try with `http://` if `https://` fails (then fix SSL)

### Issue: "Authentication failed"
**Solution**:
- Verify API key is copied correctly
- No extra spaces in API key field
- Regenerate API key if needed

### Issue: "Post created but images missing"
**Solution**:
- Check remote site can download images from local site
- Verify image URLs are accessible publicly
- Check PHP `allow_url_fopen` is enabled

### Issue: "Sync logs empty"
**Solution**:
- Check "Enable Sync" is checked
- Verify content type (posts/pages) is enabled
- Test with a fresh post/page creation

## Testing Checklist

- [ ] Plugin activated on both sites
- [ ] API keys configured correctly
- [ ] Connection test passes
- [ ] Created test post on local site
- [ ] Test post appears on remote site
- [ ] Featured image synced correctly
- [ ] Categories/tags synced correctly
- [ ] Sync logs show success
- [ ] Updated post syncs changes
- [ ] Deleted post removes from remote

## Next Steps

Once setup is complete:

1. Monitor the sync logs for any errors
2. Test editing and deleting content
3. Configure any additional content types if needed
4. Set up regular backups on both sites
5. Consider setting up monitoring/alerts for sync failures

## Security Recommendations

1. Always use HTTPS for both sites
2. Keep API keys secure and private
3. Regularly update WordPress and the plugin
4. Use strong passwords for admin accounts
5. Consider IP whitelisting if possible
6. Monitor sync logs for suspicious activity

## Need Help?

If you encounter issues:

1. Check the Sync Logs for error messages
2. Review the Troubleshooting section in README.md
3. Check PHP error logs on both sites
4. Visit: https://github.com/inopik-lt/wp-connection

## Advanced Configuration

For developers who want to extend the plugin:

- See README.md for hooks and filters
- Review the source code for customization points
- Refer to the API documentation for endpoint details
