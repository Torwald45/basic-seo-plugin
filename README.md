# Basic WP/Woo SEO Plugin

Lightweight SEO plugin for WordPress and WooCommerce with custom title tags, meta descriptions, XML sitemaps, breadcrumbs, and Open Graph support.

## Features

- **Custom Title Tags & Meta Descriptions** - Add custom SEO titles and descriptions for posts, pages, and WooCommerce products/categories
- **XML Sitemap Generation** - Automatically generated XML sitemaps with pagination support for large sites
- **Breadcrumb Navigation** - Flexible breadcrumbs with shortcode support and schema markup
- **Open Graph Support** - Social media optimization with Facebook and Twitter card support
- **Canonical URLs** - Prevent duplicate content issues with automatic canonical URL generation
- **WooCommerce Integration** - Full support for shop pages and product categories
- **Performance Focused** - Lightweight (~500KB), no bloat, fast loading times

## Admin Interface

The plugin provides a clean, organized admin interface with two main sections:

### Dashboard
- **Module Information** - Clear descriptions of how each SEO feature works
- **Content Analysis** - Shows posts missing SEO title, description, or featured image
- **Quick Links** - Direct access to sitemap and documentation

### Help
- **Configuration Settings** - Meta description limits, breadcrumb options, sitemap settings
- **Open Graph Settings** - Default images, Facebook App ID, Twitter card configuration

## Module Descriptions

All SEO modules work automatically without requiring activation:

- **Meta Tags** - Custom title and description fields in post/page editor
- **XML Sitemap** - Available at `/sitemap.xml` with automatic generation
- **Breadcrumbs** - Use `[breadcrumbs]` shortcode or theme integration
- **Open Graph** - Automatic social media tags using featured images
- **Canonical URLs** - Automatic rel=canonical tags on all pages
- **Redirect Attachments** - Prevents duplicate content from attachment pages

## Requirements

- WordPress 6.0+
- PHP 8.1+
- WooCommerce 6.0+ (optional, for e-commerce features)

## Installation

1. Upload plugin files to `/wp-content/plugins/basic-seo-plugin-torwald45/`
2. Activate the plugin through WordPress admin
3. Go to Settings > Basic SEO to configure

## Usage

### Adding SEO Meta Data
1. Edit any post, page, or product
2. Scroll to "Basic SEO" meta box below editor
3. Add custom title and description
4. Set featured image for social sharing

### Using Breadcrumbs
**Shortcode:** `[breadcrumbs]`
**PHP function:** `<?php echo do_shortcode('[breadcrumbs]'); ?>`

### Sitemap Access
Your XML sitemap is automatically available at: `https://yoursite.com/sitemap.xml`

## Changelog

### Version 1.0.4
- Redesigned admin Dashboard with module descriptions
- Improved content analysis showing missing SEO data
- Reorganized settings into Help section
- Enhanced Posts Missing analyzer (checks title, description, featured image)
- Removed confusing module activation checkboxes
- Streamlined Quick Links section

### Version 1.0.3
- Fixed emoji support in meta descriptions
- Improved Unicode character handling

### Version 1.0.2
- Fixed CSS styling issues in admin Dashboard
- Improved admin interface consistency

### Version 1.0.1
- Initial release
- Core SEO functionality
- WooCommerce integration

## License

GPL
