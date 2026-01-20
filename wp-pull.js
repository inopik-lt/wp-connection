#!/usr/bin/env node

const { program } = require('commander');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

// Configure the CLI
program
  .name('wp-pull')
  .description('Pull updates from a WordPress site')
  .version('1.0.0');

// Pull command
program
  .command('pull')
  .description('Pull posts and updates from the remote WordPress site')
  .option('-u, --url <url>', 'WordPress site URL')
  .option('-c, --config <file>', 'Configuration file path', 'wp-config.json')
  .option('--username <username>', 'WordPress username')
  .option('--password <password>', 'WordPress application password')
  .option('--type <type>', 'Content type to pull (posts, pages, all)', 'posts')
  .option('-o, --output <dir>', 'Output directory for pulled content', './wp-content')
  .action(async (options) => {
    try {
      // Load configuration
      let config = {};
      if (fs.existsSync(options.config)) {
        config = JSON.parse(fs.readFileSync(options.config, 'utf8'));
      }

      // Merge CLI options with config file
      let siteUrl = options.url || config.url;
      const username = options.username || config.username;
      const password = options.password || config.password;
      const contentType = options.type;
      const outputDir = options.output;

      if (!siteUrl) {
        console.error('Error: WordPress site URL is required. Use --url or add it to wp-config.json');
        process.exit(1);
      }

      // Normalize siteUrl by removing trailing slashes
      siteUrl = siteUrl.replace(/\/+$/, '');

      console.log(`Pulling ${contentType} from ${siteUrl}...`);

      // Create output directory if it doesn't exist
      if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
      }

      // Prepare authentication headers
      const headers = {};
      if (username && password) {
        const auth = Buffer.from(`${username}:${password}`).toString('base64');
        headers['Authorization'] = `Basic ${auth}`;
      }

      // Determine which endpoints to pull from
      const endpoints = [];
      if (contentType === 'posts' || contentType === 'all') {
        endpoints.push('posts');
      }
      if (contentType === 'pages' || contentType === 'all') {
        endpoints.push('pages');
      }

      // Pull content from each endpoint
      for (const endpoint of endpoints) {
        const apiUrl = `${siteUrl}/wp-json/wp/v2/${endpoint}`;
        console.log(`Fetching ${endpoint} from ${apiUrl}...`);

        try {
          // Fetch all items with pagination
          let allItems = [];
          let page = 1;
          let hasMore = true;

          while (hasMore) {
            const response = await axios.get(apiUrl, { 
              headers,
              params: {
                per_page: 100, // Maximum allowed by WordPress
                page: page
              }
            });
            const items = response.data;
            
            if (items.length === 0) {
              hasMore = false;
            } else {
              allItems = allItems.concat(items);
              page++;
              
              // Check if there are more pages
              const totalPages = response.headers['x-wp-totalpages'];
              if (totalPages && page > parseInt(totalPages)) {
                hasMore = false;
              }
            }
          }

          console.log(`Found ${allItems.length} ${endpoint}`);

          // Save each item to a file
          const typeDir = path.join(outputDir, endpoint);
          if (!fs.existsSync(typeDir)) {
            fs.mkdirSync(typeDir, { recursive: true });
          }

          // Use async file operations
          const promises = allItems.map(async (item) => {
            // Create a safe filename with fallback for missing slug
            const slug = item.slug || `item-${item.id}`;
            const filename = `${item.id}-${slug}.json`;
            const filepath = path.join(typeDir, filename);
            
            await fs.promises.writeFile(filepath, JSON.stringify(item, null, 2));
            console.log(`  Saved: ${filename}`);
          });

          await Promise.all(promises);
        } catch (error) {
          if (error.response) {
            console.error(`  Error fetching ${endpoint}: ${error.response.status} ${error.response.statusText}`);
            if (error.response.status === 401) {
              console.error('  Authentication failed. Check your username and password.');
            }
          } else {
            console.error(`  Error fetching ${endpoint}:`, error.message);
          }
        }
      }

      console.log('\nPull completed!');
    } catch (error) {
      console.error('Error:', error.message);
      process.exit(1);
    }
  });

// Config command - create a sample config file
program
  .command('init')
  .description('Create a sample configuration file')
  .option('-o, --output <file>', 'Output file path', 'wp-config.json')
  .action((options) => {
    const config = {
      url: 'https://example.com',
      username: 'your-username',
      password: 'your-application-password',
      note: 'For password, use WordPress Application Password (not your regular password)'
    };

    fs.writeFileSync(options.output, JSON.stringify(config, null, 2));
    console.log(`Configuration file created: ${options.output}`);
    console.log('Please edit the file with your WordPress site details.');
  });

program.parse();
