=== Basic WP/Woo SEO Plugin ===
Contributors: torwald45
Donate link: https://github.com/Torwald45/basic-seo-plugin
Tags: seo, meta tags, sitemap, breadcrumbs, open graph, canonical, woocommerce
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight SEO plugin for WordPress and WooCommerce with custom title tags, meta descriptions, XML sitemaps, breadcrumbs, and Open Graph support.

== Description ==

Basic WP/Woo SEO Plugin is a lightweight, performance-focused SEO solution for WordPress and WooCommerce websites. Unlike heavy SEO plugins that can slow down your site, this plugin provides essential SEO features with minimal impact on loading times.

= Key Features =

* **Custom Title Tags & Meta Descriptions** - Add custom SEO titles and descriptions for posts, pages, and WooCommerce products/categories
* **XML Sitemap Generation** - Automatically generated XML sitemaps with pagination support for large sites
* **Breadcrumb Navigation** - Flexible breadcrumbs with shortcode support and schema markup
* **Open Graph Support** - Social media optimization with Facebook and Twitter card support
* **Canonical URLs** - Prevent duplicate content issues with automatic canonical URL generation
* **Admin Columns** - See SEO status at a glance in your WordPress admin
* **Quick Edit Support** - Edit SEO fields directly from post/page lists
* **WooCommerce Integration** - Full support for shop pages and product categories
* **Attachment Redirect** - Automatically redirect attachment pages to prevent duplicate content
* **Multi-language Support** - Available in English, Polish, German, and Spanish

= Why Choose Basic SEO Plugin Torwald45? =

* **Lightweight** - No bloat, fast loading times (unlike RankMath which can add 2-3 seconds to TTFB)
* **Clean Code** - Follows WordPress coding standards
* **No Premium Upsells** - Completely free, no hidden features
* **Developer Friendly** - Well-documented with hooks and filters
* **Privacy Focused** - No external API calls or data collection

= Perfect For =

* Small to medium WordPress sites
* WooCommerce stores
* Performance-conscious developers
* Sites that need basic SEO without the overhead
* Multilingual websites

= WooCommerce Features =

* SEO fields for product categories
* Shop page optimization
* Product meta tags
* Category columns in admin
* Open Graph for products

= Breadcrumbs =

Use the `[basicseo-breadcrumb]` shortcode anywhere in your content, or add `<?php BasicSEO_Torwald45_Breadcrumbs_Shortcode::display_breadcrumbs(); ?>` to your theme files.

Breadcrumb features:
* Automatic hierarchy detection
* WooCommerce support
* Schema.org markup
* Customizable separators
* Responsive design

= Developer Features =

* Clean, object-oriented code
* WordPress coding standards compliant
* Extensive hook system
* No jQuery dependencies
* Minimal database queries
* Proper caching support

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/basic-seo-plugin-torwald45/` directory, or install through WordPress admin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Basic SEO to configure the plugin
4. Enable the modules you want to use
5. Start adding SEO titles and descriptions to your content

= Manual Installation =

1. Download the plugin zip file
2. Extract to `/wp-content/plugins/basic-seo-plugin-torwald45/`
3. Activate in WordPress admin
4. Configure in Settings > Basic SEO

== Frequently Asked Questions ==

= Is this plugin really free? =

Yes! This plugin is completely free with no premium versions or upsells. All features are available to everyone.

= Will this slow down my website? =

No. This plugin is designed for performance. It uses minimal resources and follows WordPress best practices for speed optimization.

= Can I use this with other SEO plugins? =

While technically possible, it's not recommended. SEO plugins can conflict with each other. This plugin is designed to be a complete SEO solution.

= Does it work with WooCommerce? =

Yes! The plugin has extensive WooCommerce integration including support for product categories, shop pages, and product SEO.

= Can I customize the breadcrumbs? =

Yes. The breadcrumbs are highly customizable through shortcode parameters and can be styled with CSS.

= Does it generate XML sitemaps? =

Yes. The plugin automatically generates XML sitemaps for all your content with support for large sites through pagination.

= Is it translation ready? =

Yes. The plugin includes translations for English, Polish, German, and Spanish. More translations are welcome!

= Can I migrate from other SEO plugins? =

The plugin stores SEO data in standard meta fields, making migration easier. However, you may need to manually transfer some settings.

= Does it support custom post types? =

Yes. The plugin supports custom post types through filters and can be extended for your specific needs.

= What about schema markup? =

The plugin includes basic schema markup for breadcrumbs. More schema features may be added in future versions.

== Screenshots ==

1. SEO meta box in post editor - Add custom titles and descriptions
2. Admin columns showing SEO status - Quick overview of your content
3. Settings page - Configure all plugin options
4. Dashboard with SEO analysis - See what needs attention
5. WooCommerce category SEO fields - Optimize your shop
6. Quick edit SEO fields - Fast editing from post lists
7. Breadcrumbs display - Clean navigation for users

== Changelog ==

= 1.0.1 - 2024-05-25 =
* Updated plugin name to "Basic WP/Woo SEO Plugin"
* Updated plugin URI to new repository
* Fixed branding consistency

= 1.0.0 - 2024-05-25 =
* Initial release
* Custom title tags and meta descriptions
* XML sitemap generation with pagination
* Breadcrumb navigation with shortcode
* Open Graph and Twitter Card support
* Canonical URL generation
* WooCommerce integration
* Admin columns and quick edit
* Attachment page redirects
* Multi-language support (EN, PL, DE, ES)
* SEO analysis dashboard
* Performance optimizations

== Upgrade Notice ==

= 1.0.0 =
Initial release of Basic SEO Plugin Torwald45. A lightweight alternative to heavy SEO plugins.

== Technical Requirements ==

* WordPress 6.0 or higher
* PHP 8.1 or higher
* MySQL 5.6 or higher (or equivalent MariaDB)
* WooCommerce 6.0+ (optional, for e-commerce features)

== Support ==

* Documentation: https://github.com/Torwald45/basic-seo/blob/main/README.md
* Issues: https://github.com/Torwald45/basic-seo/issues
* Discussions: https://github.com/Torwald45/basic-seo/discussions

== Contributing ==

This plugin is open source and welcomes contributions:

* GitHub: https://github.com/Torwald45/basic-seo
* Translations: Help translate the plugin into more languages
* Bug reports: Report issues on GitHub
* Feature requests: Suggest improvements

== Privacy ==

This plugin:
* Does not collect or transmit any user data
* Does not make external API calls
* Does not use cookies or tracking
* Stores data only in your WordPress database
* Is GDPR compliant

== Performance ==

The plugin is optimized for performance:
* Minimal database queries
* No jQuery dependencies  
* Efficient caching
* Clean uninstall process
* Small footprint (~500KB)

== Credits ==

* Developed by Torwald45
* Inspired by the need for lightweight SEO solutions
* Thanks to the WordPress community for best practices
* Icons and design elements follow WordPress UI guidelines
