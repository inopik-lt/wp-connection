# Quick Start Guide - WP Site Connector

Get your WordPress sites syncing in 5 minutes!

## Prerequisites

- Two WordPress sites (local and remote) with admin access
- Both sites running WordPress 5.0+ and PHP 7.2+

## 5-Minute Setup

### Step 1: Install Plugin (2 minutes)

**On Both Sites:**

1. Download/copy the `wp-site-connector` folder
2. Upload to `wp-content/plugins/` directory
3. Go to WordPress Admin → Plugins
4. Click "Activate" on "WP Site Connector"

### Step 2: Get API Key (1 minute)

**On Remote Site:**

1. Go to Settings → Site Connector
2. Copy the "Local API Key"
3. Save it somewhere

### Step 3: Configure Local Site (1 minute)

**On Local Site:**

1. Go to Settings → Site Connector
2. Check "Enable Sync"
3. Enter Remote Site URL: `https://your-remote-site.com`
4. Paste the Remote Site API Key
5. Check "Sync Posts" and "Sync Pages"
6. Click "Save Changes"

### Step 4: Test (1 minute)

**On Local Site:**

1. Click "Test Connection" button
2. Should see: "Connection successful!" ✓
3. Create a test post
4. Check if it appears on remote site ✓

## Done! 🎉

Your sites are now connected and syncing automatically!

## What Happens Now?

- ✅ Create a post → Automatically syncs to remote
- ✅ Update a post → Updates sync to remote  
- ✅ Delete a post → Deletes from remote
- ✅ Featured images → Sync automatically
- ✅ Categories & tags → Sync automatically

## Quick Troubleshooting

### Connection Test Fails?

1. Verify remote URL is correct (no trailing slash)
2. Check API key has no extra spaces
3. Ensure remote site has plugin activated
4. Try with http:// if https:// fails

### Post Not Syncing?

1. Check "Enable Sync" is checked
2. Verify content type (Posts/Pages) is enabled
3. Look at Sync Logs for error messages
4. Try clicking "Test Connection" again

## Need More Help?

- Detailed Setup: See [SETUP.md](SETUP.md)
- Full Documentation: See [wp-site-connector/README.md](wp-site-connector/README.md)
- Installation Guide: See [wp-site-connector/INSTALLATION.md](wp-site-connector/INSTALLATION.md)

## Next Steps

1. Test editing and deleting posts
2. Try syncing pages
3. Monitor sync logs for any issues
4. Set up HTTPS for security (recommended)

---

**Questions?** Check the troubleshooting section or create an issue on GitHub.

**Working?** Great! Now you can develop locally and push to production automatically! 🚀
