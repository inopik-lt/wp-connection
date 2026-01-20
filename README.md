# wp-connection

A command-line tool for pulling content from WordPress sites. This tool allows you to sync and pull updates from your online WordPress site to your local environment.

## Features

- Pull posts and pages from any WordPress site via REST API
- Support for WordPress Application Passwords authentication
- Configurable via command-line options or config file
- Export content as JSON files for easy processing

## Installation

1. Clone this repository
2. Install dependencies:

```bash
npm install
```

3. Make the script executable (Linux/Mac):

```bash
chmod +x wp-pull.js
```

## Usage

### Initialize Configuration

Create a configuration file with your WordPress site details:

```bash
node wp-pull.js init
```

This creates a `wp-config.json` file. Edit it with your site details:

```json
{
  "url": "https://your-wordpress-site.com",
  "username": "your-username",
  "password": "your-application-password"
}
```

**Note:** For the password, use a WordPress Application Password (not your regular login password). You can generate one in WordPress under Users > Profile > Application Passwords.

### Pull Content

Pull posts from your WordPress site:

```bash
node wp-pull.js pull
```

Pull pages:

```bash
node wp-pull.js pull --type pages
```

Pull all content (posts and pages):

```bash
node wp-pull.js pull --type all
```

### Command-line Options

You can override config file settings with command-line options:

```bash
node wp-pull.js pull --url https://example.com --username user --password pass --type posts
```

Available options:
- `-u, --url <url>` - WordPress site URL
- `-c, --config <file>` - Configuration file path (default: wp-config.json)
- `--username <username>` - WordPress username
- `--password <password>` - WordPress application password
- `--type <type>` - Content type to pull: posts, pages, or all (default: posts)
- `-o, --output <dir>` - Output directory for pulled content (default: ./wp-content)

## How It Works

The tool uses the WordPress REST API (specifically the `/wp-json/wp/v2/` endpoints) to fetch content from your WordPress site. The content is saved as JSON files in the output directory, organized by content type.

## Authentication

For public content, authentication is optional. For private content or sites with restricted access, you'll need to provide credentials.

To generate an Application Password in WordPress:
1. Go to Users > Profile
2. Scroll to "Application Passwords"
3. Enter a name for your app and click "Add New Application Password"
4. Copy the generated password (you won't see it again!)
5. Use this password in your config file or --password option

## Output Structure

```
wp-content/
├── posts/
│   ├── 1-hello-world.json
│   └── 2-sample-post.json
└── pages/
    └── 3-about-us.json
```

## Examples

Pull posts from a public WordPress site:

```bash
node wp-pull.js pull --url https://wordpress.org/news
```

Pull all content with authentication:

```bash
node wp-pull.js pull --type all --url https://mysite.com --username admin --password "xxxx xxxx xxxx xxxx xxxx xxxx"
```

## Troubleshooting

- **401 Authentication Error**: Check your username and application password
- **404 Not Found**: Verify the WordPress site URL is correct and the REST API is enabled
- **Empty results**: Make sure the site has published posts/pages

## License

MIT
