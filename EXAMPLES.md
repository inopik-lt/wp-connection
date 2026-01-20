# Example Configuration

## Example 1: Development to Production

### Scenario
You have a local development site and want to push content to a production site.

### Configuration

**Local Site (Development):**
- URL: `http://localhost`
- Settings:
  - ✅ Enable Sync: YES
  - Remote Site URL: `https://yoursite.com`
  - Remote Site API Key: `abc123xyz...` (from production)
  - ✅ Sync Posts: YES
  - ✅ Sync Pages: YES

**Remote Site (Production):**
- URL: `https://yoursite.com`
- Settings:
  - ❌ Enable Sync: NO (only receive)
  - Remote Site URL: (empty)
  - Remote Site API Key: (empty)
  - Local API Key: `abc123xyz...` (share with development)

### Workflow
1. Create/edit content on local development site
2. Content automatically syncs to production
3. Production site only receives, doesn't send back

---

## Example 2: Staging to Production

### Scenario
Content is created on a staging site and pushed to production.

### Configuration

**Staging Site:**
- URL: `https://staging.yoursite.com`
- Settings:
  - ✅ Enable Sync: YES
  - Remote Site URL: `https://yoursite.com`
  - Remote Site API Key: (from production)
  - ✅ Sync Posts: YES
  - ✅ Sync Pages: YES

**Production Site:**
- URL: `https://yoursite.com`
- Settings:
  - ❌ Enable Sync: NO
  - Local API Key: (share with staging)

---

## Example 3: Bidirectional Sync

### Scenario
Two sites that need to stay in sync with each other.

### Configuration

**Site A:**
- URL: `https://site-a.com`
- Settings:
  - ✅ Enable Sync: YES
  - Remote Site URL: `https://site-b.com`
  - Remote Site API Key: (from Site B)
  - Local API Key: `key-from-site-a` (share with Site B)

**Site B:**
- URL: `https://site-b.com`
- Settings:
  - ✅ Enable Sync: YES
  - Remote Site URL: `https://site-a.com`
  - Remote Site API Key: (from Site A)
  - Local API Key: `key-from-site-b` (share with Site A)

⚠️ **Warning:** Bidirectional sync requires careful management to avoid conflicts.

---

## Example 4: Multiple Environments

### Scenario
Development → Staging → Production pipeline.

### Configuration

**Development → Staging:**

Development:
- Enable Sync: YES
- Remote: staging.yoursite.com
- Sync Posts: YES
- Sync Pages: YES

Staging:
- Enable Sync: NO (only receive from dev)

**Staging → Production:**

After testing on staging, enable sync:

Staging:
- Enable Sync: YES
- Remote: yoursite.com
- Sync Posts: YES
- Sync Pages: YES

Production:
- Enable Sync: NO (only receive from staging)

---

## Example 5: Content Distribution

### Scenario
One main site pushes content to multiple regional sites.

### Setup
You'll need the plugin on all sites, but configure main site to sync to each regional site separately.

**Option A: Multiple Plugins**
Install multiple sync plugins or use custom code to sync to multiple destinations.

**Option B: Sequential Sync**
1. Sync Main → Region 1
2. Sync Main → Region 2
3. Sync Main → Region 3

⚠️ **Note:** Current plugin supports one remote site. For multiple destinations, you would need to configure each separately or extend the plugin.

---

## Common Settings Combinations

### Only Sync Posts (Not Pages)
```
✅ Sync Posts: YES
❌ Sync Pages: NO
```

### Only Sync Pages (Not Posts)
```
❌ Sync Posts: NO
✅ Sync Pages: YES
```

### Sync Everything
```
✅ Sync Posts: YES
✅ Sync Pages: YES
```

### Receive Only (Don't Send)
```
❌ Enable Sync: NO
(Just have plugin activated to receive via API)
```

---

## Testing Configuration

### Test Setup

**Local Site:**
```
URL: http://localhost:8080
Enable Sync: YES
Remote URL: https://staging.example.com
Remote API Key: test-key-123456789abcdef
Sync Posts: YES
Sync Pages: NO (for testing)
```

**Remote Site:**
```
URL: https://staging.example.com
Enable Sync: NO
Local API Key: test-key-123456789abcdef
```

### Test Steps:
1. Click "Test Connection" → Should show success
2. Create test post on local
3. Verify it appears on remote
4. Edit test post on local
5. Verify changes on remote
6. Delete test post on local
7. Verify deletion on remote

---

## Security Best Practices

### Recommended Settings

**Production Site:**
```
- Use HTTPS (required)
- Strong API key (auto-generated is fine)
- Firewall rules to limit IP access (optional)
- Regular security updates
- Monitor sync logs daily
```

**Development Site:**
```
- Keep API keys out of version control
- Use environment variables for sensitive data
- Restrict admin access
- Regular backups
```

### API Key Management

**DO:**
- ✅ Keep API keys secure
- ✅ Use strong, random keys
- ✅ Store in secure password manager
- ✅ Regenerate if compromised

**DON'T:**
- ❌ Share API keys publicly
- ❌ Commit keys to Git
- ❌ Use same key for multiple sites
- ❌ Use predictable keys

---

## Troubleshooting Examples

### Example Issue: Posts Not Syncing

**Symptoms:**
- Created post on local
- Not appearing on remote
- No errors in logs

**Check:**
1. "Enable Sync" is checked? ✓
2. "Sync Posts" is enabled? ✓
3. Remote URL correct? ✓
4. API key correct? ✓
5. Test connection passes? ✓

**Solution:**
- Check sync logs for specific error
- Verify remote site is accessible
- Try regenerating API key

### Example Issue: Connection Test Fails

**Error:** "Connection failed: cURL error 28"

**Cause:** Timeout reaching remote server

**Solutions:**
1. Check remote site is online
2. Verify URL is correct
3. Check firewall settings
4. Increase timeout in code if needed

### Example Issue: Images Not Syncing

**Symptoms:**
- Post syncs correctly
- Featured image missing on remote

**Causes:**
1. `allow_url_fopen` disabled on remote
2. Image too large
3. Remote site can't access local URLs

**Solutions:**
1. Enable `allow_url_fopen` in PHP
2. Reduce image size
3. Make local images publicly accessible

---

## Advanced Examples

### Custom Post Types (Future Extension)

```php
// Add to functions.php or custom plugin
add_filter( 'wp_site_connector_post_types', function( $types ) {
    $types[] = 'product';  // WooCommerce products
    $types[] = 'portfolio'; // Custom post type
    return $types;
} );
```

### Custom Sync Logic (Future Extension)

```php
// Modify data before sending
add_filter( 'wp_site_connector_before_send', function( $data, $post_id ) {
    // Add custom field
    $data['data']['custom_field'] = get_post_meta( $post_id, 'custom_field', true );
    return $data;
}, 10, 2 );
```

---

## Configuration Checklist

Use this checklist for each setup:

**Pre-Setup:**
- [ ] WordPress installed on both sites
- [ ] PHP 7.2+ on both sites
- [ ] Admin access to both sites
- [ ] HTTPS enabled (production)

**Installation:**
- [ ] Plugin uploaded to both sites
- [ ] Plugin activated on both sites
- [ ] Can access Settings → Site Connector

**Configuration:**
- [ ] Remote site API key copied
- [ ] Local site enabled sync
- [ ] Remote URL entered (no trailing slash)
- [ ] Remote API key pasted
- [ ] Content types selected
- [ ] Settings saved

**Testing:**
- [ ] Connection test passes
- [ ] Created test post
- [ ] Post appears on remote
- [ ] Edited post syncs
- [ ] Deleted post removes from remote
- [ ] Sync logs show success

**Production:**
- [ ] HTTPS enabled
- [ ] Backups configured
- [ ] Monitoring enabled
- [ ] Documentation updated

---

## Need Custom Configuration?

If you need help with a specific setup:

1. Review the main [README.md](wp-site-connector/README.md)
2. Check [SETUP.md](SETUP.md) for detailed instructions
3. Review troubleshooting section
4. Create an issue on GitHub with your specific scenario

---

## Quick Reference

**Enable Sending:**
- Enable Sync: ✅ YES
- Remote URL: Set
- Remote API Key: Set

**Enable Receiving:**
- Plugin activated
- Local API Key: Share

**Disable Temporarily:**
- Enable Sync: ❌ NO

**Test Connection:**
- Settings → Site Connector → Test Connection

**View Logs:**
- Settings → Site Connector → Sync Logs

**Regenerate API Key:**
- Settings → Site Connector → Regenerate button
