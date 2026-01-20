# WP Connection - WordPress Site Synchronization

A comprehensive WordPress plugin that enables real-time content synchronization between local and remote WordPress installations. Perfect for development workflows, content distribution, and maintaining synchronized sites.

## Overview

WP Site Connector automatically syncs posts, pages, featured images, categories, and tags from a local WordPress site to a remote site in real-time. Any change made on the local site instantly reflects on the remote site.

## Key Features

✅ **Real-time Synchronization** - Changes sync automatically when content is created, updated, or deleted  
✅ **Secure Communication** - API key authentication and HTTPS support  
✅ **Selective Sync** - Choose which content types to synchronize  
✅ **Complete Content** - Syncs posts, pages, featured images, and taxonomies  
✅ **Detailed Logging** - Track every sync event with comprehensive logs  
✅ **Easy Setup** - Simple configuration through WordPress admin interface  
✅ **Connection Testing** - Built-in tool to verify connectivity  
✅ **Bidirectional Support** - Can sync in both directions if needed  

## Quick Start

### Installation

1. Install the plugin on both local and remote WordPress sites
2. Activate on both sites
3. Copy API key from remote site
4. Configure local site with remote URL and API key
5. Enable sync and test connection

For detailed setup instructions, see [INSTALLATION.md](wp-site-connector/INSTALLATION.md)

### Plugin Location

The complete plugin is located in the `/wp-site-connector/` directory. This folder should be placed in your WordPress `/wp-content/plugins/` directory.

## Documentation

- **[Plugin README](wp-site-connector/README.md)** - Complete feature documentation, API reference, and troubleshooting
- **[Installation Guide](wp-site-connector/INSTALLATION.md)** - Step-by-step setup instructions
- **[WordPress Plugin Description](wp-site-connector/readme.txt)** - WordPress.org compatible readme

## Use Cases

### Development to Production
Push content changes from your local development environment to production automatically.

### Content Distribution
Maintain multiple sites with identical content across different domains.

### Staging Workflows
Keep staging and production sites synchronized for testing.

### Content Backup
Create a live mirror of your content on a separate server.

## How It Works

1. **User makes change** on local site (create/update/delete post or page)
2. **Plugin detects change** via WordPress hooks
3. **Content is packaged** with all metadata, featured images, and taxonomies
4. **Sent to remote site** via REST API with secure authentication
5. **Remote site receives** and creates/updates/deletes the corresponding content
6. **Both sites log** the sync event for monitoring

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher  
- Both sites accessible via HTTP/HTTPS
- REST API enabled on both sites

## Repository Structure

```
wp-connection/
├── README.md                          # This file
└── wp-site-connector/                 # Main plugin directory
    ├── wp-site-connector.php          # Plugin entry point
    ├── README.md                      # Plugin documentation
    ├── INSTALLATION.md                # Setup guide
    ├── readme.txt                     # WordPress.org readme
    ├── includes/                      # Plugin classes
    │   ├── class-admin.php            # Admin interface
    │   ├── class-api.php              # REST API endpoints
    │   ├── class-sync.php             # Sync logic
    │   └── class-logger.php           # Logging functionality
    └── assets/                        # Frontend assets
        ├── admin.js                   # Admin JavaScript
        └── admin.css                  # Admin styles
```

## Security

- ✅ API key authentication on all requests
- ✅ HTTPS recommended for all communications
- ✅ Input sanitization and validation
- ✅ Prepared SQL statements
- ✅ WordPress security best practices
- ✅ No credentials stored in plain text

## Support & Contributing

For issues, questions, or contributions:
- **GitHub**: https://github.com/inopik-lt/wp-connection
- **Issues**: Report bugs and request features

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Author

**Inopik**  
https://github.com/inopik-lt

---

## Getting Started

To use this plugin:

1. Navigate to the `wp-site-connector` directory
2. Copy the entire folder to your WordPress `wp-content/plugins/` directory on both sites
3. Follow the [Installation Guide](wp-site-connector/INSTALLATION.md) for configuration
4. Start syncing content!

**Questions?** Check the [Plugin README](wp-site-connector/README.md) for detailed documentation and troubleshooting.
