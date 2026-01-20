# WP Site Connector

A WordPress plugin that enables real-time synchronization between a local WordPress site and a remote WordPress site. Any changes made to posts and pages on the local site are automatically reflected on the remote site.

## Features

- **Real-time Sync**: Automatically synchronizes posts and pages when created, updated, or deleted
- **Bidirectional Support**: Can be installed on both local and remote sites
- **Secure API**: Uses API keys for secure authentication
- **REST API Endpoints**: Exposes endpoints for receiving synchronized content
- **Selective Sync**: Choose which content types to synchronize (posts, pages)
- **Sync Logging**: Track all synchronization events with detailed logs
- **Featured Images**: Synchronizes featured images along with content
- **Taxonomies**: Syncs categories and tags
- **Admin Interface**: Easy-to-use settings page for configuration
- **Connection Testing**: Built-in connection test to verify setup

## Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload the `wp-site-connector` folder to the `/wp-content/plugins/` directory on both sites
3. Activate the plugin through the 'Plugins' menu in WordPress on both sites

### Method 2: Direct Upload

1. Download the plugin as a ZIP file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Activate the plugin
5. Repeat on the remote site

## Configuration

### On the Remote Site (Receiver)

1. Go to **Settings → Site Connector**
2. Copy the **Local API Key** - you'll need this for the local site
3. Keep "Enable Sync" unchecked if you only want to receive updates (not send them back)
4. Save settings

### On the Local Site (Sender)

1. Go to **Settings → Site Connector**
2. Check **Enable Sync** to activate synchronization
3. Enter the **Remote Site URL** (e.g., `https://your-remote-site.com`)
4. Paste the **Remote Site API Key** that you copied from the remote site
5. Choose which content types to sync:
   - **Sync Posts**: Synchronize blog posts
   - **Sync Pages**: Synchronize pages
6. Click **Save Changes**
7. Use the **Test Connection** button to verify the setup

## How It Works

### Local to Remote Sync Flow

1. User creates/updates/deletes a post or page on the local site
2. Plugin detects the change via WordPress hooks
3. Post data is packaged and sent to the remote site's REST API endpoint
4. Remote site receives the data, authenticates using API key
5. Remote site creates/updates/deletes the corresponding content
6. Both sites log the sync event

### What Gets Synchronized

- Post/Page title
- Post/Page content
- Post/Page excerpt
- Post/Page status (publish, draft, etc.)
- Featured image
- Categories and tags
- Custom taxonomies

### What Doesn't Get Synchronized

- Comments
- Custom fields (can be extended)
- Custom post types (can be extended)
- User information
- Plugin settings
- Theme settings

## API Endpoints

The plugin exposes the following REST API endpoints:

### Test Connection
```
POST /wp-json/wp-site-connector/v1/test
Headers: X-API-Key: {your-api-key}
```

### Receive Content
```
POST /wp-json/wp-site-connector/v1/receive
Headers: X-API-Key: {your-api-key}
Content-Type: application/json

Body:
{
  "object_type": "post",
  "remote_id": 123,
  "data": {
    "title": "Post Title",
    "content": "Post content...",
    "excerpt": "Post excerpt...",
    "status": "publish",
    "featured_image_url": "https://...",
    "terms": {
      "category": ["Category1", "Category2"],
      "post_tag": ["Tag1", "Tag2"]
    }
  }
}
```

### Delete Content
```
POST /wp-json/wp-site-connector/v1/delete
Headers: X-API-Key: {your-api-key}
Content-Type: application/json

Body:
{
  "object_type": "post",
  "remote_id": 123
}
```

## Usage Examples

### Basic Setup (One-way sync)

**Scenario**: You have a local development site and want to push changes to a production site.

1. Install plugin on both sites
2. On production site: Copy the API key
3. On local site: Enable sync, add production URL and API key
4. Create a new post on local site
5. Post automatically appears on production site

### Bidirectional Setup

**Scenario**: You have two sites that need to stay in sync.

1. Install plugin on both sites
2. On Site A: Copy API key, enable sync, add Site B URL and API key
3. On Site B: Copy API key, enable sync, add Site A URL and API key
4. Changes on either site will sync to the other
5. ⚠️ **Warning**: Be careful with bidirectional sync to avoid conflicts

## Troubleshooting

### Connection Test Fails

**Problem**: Test connection returns an error

**Solutions**:
- Verify the remote URL is correct (no trailing slash)
- Check that the API key is correct
- Ensure the remote site has the plugin installed and activated
- Check if the remote site is accessible (not behind a firewall)
- Verify SSL certificates are valid on both sites

### Content Not Syncing

**Problem**: Changes aren't appearing on the remote site

**Solutions**:
- Ensure "Enable Sync" is checked
- Check that the content type (posts/pages) is enabled for sync
- Review the Sync Logs for error messages
- Verify both sites can communicate (test connection)
- Check PHP error logs on both sites

### Duplicate Content

**Problem**: Content appears multiple times on remote site

**Solutions**:
- This can happen if posts are manually copied before enabling sync
- Remote posts are tracked by a `_wp_site_connector_remote_id` meta field
- If needed, manually delete duplicates on the remote site

### API Key Issues

**Problem**: Authentication errors

**Solutions**:
- Regenerate the API key on the receiving site
- Update the sending site with the new API key
- Ensure there are no extra spaces in the API key
- Check that the API key field isn't being cached

## Security Considerations

- **API Keys**: Keep your API keys secure and don't share them publicly
- **HTTPS**: Always use HTTPS for both sites to encrypt data in transit
- **Authentication**: All API requests require valid API keys
- **Permissions**: Only users with `manage_options` capability can configure the plugin
- **Input Sanitization**: All incoming data is sanitized and validated
- **SQL Injection**: Uses prepared statements for all database queries

## Performance

- **Async Processing**: Syncs happen in real-time but don't block page loads
- **Timeout**: API requests timeout after 30 seconds
- **Logging**: Logs are automatically cleaned up after 30 days
- **Resource Usage**: Minimal impact on site performance

## Developer Information

### Hooks and Filters

The plugin provides hooks for developers to extend functionality:

```php
// Modify data before sending
apply_filters( 'wp_site_connector_before_send', $data, $post_id );

// Modify data after receiving
apply_filters( 'wp_site_connector_after_receive', $data, $local_id );

// Add custom post types
add_filter( 'wp_site_connector_post_types', function( $types ) {
    $types[] = 'custom_post_type';
    return $types;
} );
```

### Database Schema

The plugin creates a `wp_site_connector_logs` table:

```sql
CREATE TABLE wp_site_connector_logs (
    id bigint(20) AUTO_INCREMENT PRIMARY KEY,
    event_type varchar(50) NOT NULL,
    object_type varchar(50) NOT NULL,
    object_id bigint(20) NOT NULL,
    remote_url varchar(255) NOT NULL,
    status varchar(20) NOT NULL,
    message text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP
);
```

### Post Meta

The plugin stores the remote post ID in post meta:
- Meta key: `_wp_site_connector_remote_id`
- Meta value: Remote post ID (integer)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Both sites must be accessible via HTTP/HTTPS
- REST API must be enabled on both sites

## Limitations

- Does not sync comments
- Does not sync users
- Does not sync custom post types by default (can be extended)
- Maximum post size limited by PHP `max_post_size` and `upload_max_filesize`
- Featured images are re-downloaded (not streamed)

## Changelog

### Version 1.0.0
- Initial release
- Posts and pages synchronization
- REST API endpoints
- Admin settings interface
- Sync logging
- Connection testing
- Featured image support
- Taxonomy support

## Support

For issues, questions, or contributions, please visit:
https://github.com/inopik-lt/wp-connection

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Inopik
