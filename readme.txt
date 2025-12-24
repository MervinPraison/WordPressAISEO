=== AI SEO ===
Contributors: MervinPraison
Tags: seo, ai, openai, schema, sitemap
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 5.0.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered SEO optimization for WordPress. Generate meta descriptions, titles, schema markup, and comprehensive SEO analysis using OpenAI.

== Description ==

AI SEO is a powerful AI-powered SEO plugin that helps you optimize your WordPress content using OpenAI's GPT-4o-mini model. Automatically generate SEO-optimized titles and descriptions, schema markup, and get comprehensive content analysis.

= Key Features =

* **AI-Powered Meta Generation** - Generate SEO-optimized titles and descriptions
* **Content Analysis** - 11 SEO metrics with actionable recommendations
* **Schema Markup** - Automatic JSON-LD schema generation
* **Social Media Optimization** - Open Graph and Twitter Card tags
* **XML Sitemap** - Automatic sitemap generation
* **Image SEO** - AI-powered alt text generation
* **Bulk Operations** - Edit multiple posts at once
* **Import/Export** - Migrate from Yoast, Rank Math, AIOSEO
* **REST API** - 60+ endpoints for developers
* **WP-CLI** - 70+ commands for automation

= AI-Powered Features =

* Meta title generation (50-60 characters)
* Meta description generation (155-160 characters)
* Content analysis with 11 SEO metrics
* Image alt text generation
* FAQ generation from content
* Content outline generation
* Smart content rewriter (6 modes)
* Internal linking suggestions
* Content topic suggestions

= Technical SEO =

* Schema markup (Article, BlogPosting, WebPage, FAQ, HowTo)
* Meta tags management
* Canonical URLs
* Robots meta tags
* Open Graph tags
* Twitter Card tags
* XML sitemap with smart caching
* 404 monitoring and redirects

= Developer Features =

* 60+ REST API endpoints
* 70+ WP-CLI commands
* Comprehensive caching system
* Structured logging
* Performance optimized
* Fully documented

= External Services =

This plugin connects to the OpenAI API to provide AI-powered SEO features.

**Service Used:** OpenAI API (https://api.openai.com/)

**Purpose:** Generate SEO titles, meta descriptions, content analysis, and other AI-powered features.

**Data Sent:** When you actively use AI generation features:
* Post content (title and body)
* Focus keyword (if specified)
* User-specified parameters

**When Data is Sent:** Only when you:
* Click "Generate Title" or "Generate Description"
* Run WP-CLI commands with AI generation
* Call REST API endpoints for AI generation

**Privacy & Terms:**
* Privacy Policy: https://openai.com/policies/privacy-policy
* Terms of Use: https://openai.com/policies/terms-of-use
* API Data Usage: https://openai.com/policies/api-data-usage-policies

**User Control:** The plugin only connects to OpenAI when you provide an API key and explicitly use AI generation features. No data is sent without your explicit action.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "AI SEO"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the ZIP file and click "Install Now"
5. Activate the plugin

= Configuration =

1. Navigate to Settings → AI SEO
2. Enter your OpenAI API key (get one at https://platform.openai.com/api-keys)
3. Click "Save Changes"
4. Start optimizing your content!

== Frequently Asked Questions ==

= Do I need an OpenAI API key? =

Yes, you need an OpenAI API key to use the AI-powered features. You can get one at https://platform.openai.com/api-keys. The plugin uses the cost-efficient GPT-4o-mini model.

= How much does it cost to use? =

The plugin itself is free. You only pay for OpenAI API usage:
* GPT-4o-mini: $0.15 per 1M input tokens, $0.60 per 1M output tokens
* Average meta description: ~100 tokens = $0.00006
* Average SEO title: ~50 tokens = $0.00003

= Does it work with other SEO plugins? =

Yes! AI SEO can import metadata from Yoast SEO, Rank Math, and All in One SEO. You can also export your AI SEO data to JSON or CSV.

= Is my data sent to OpenAI? =

Only when you explicitly use AI generation features. The plugin does not automatically send any data. You have full control over when and what data is sent.

= Does it support WP-CLI? =

Yes! AI SEO includes 70+ WP-CLI commands for automation and batch processing. Perfect for large sites and developers.

= Does it have a REST API? =

Yes! AI SEO provides 60+ REST API endpoints for all features. Perfect for headless WordPress, mobile apps, and custom integrations.

= Can I use it with custom post types? =

Yes! AI SEO supports all custom post types. You can enable/disable SEO features for any post type.

= Does it support multilingual sites? =

Yes! AI SEO is compatible with WPML, Polylang, and TranslatePress. It can sync metadata across translations.

= How do I get support? =

* Documentation: https://github.com/MervinPraison/WordPressAISEO
* Issues: https://github.com/MervinPraison/WordPressAISEO/issues
* Website: https://praison.ai

== Screenshots ==

1. SEO Optimization metabox in post editor with real-time scoring
2. AI-powered meta title and description generation
3. Content analysis with 11 SEO metrics
4. Settings page with OpenAI API configuration
5. Bulk editing interface for multiple posts
6. Image SEO dashboard with alt text generation
7. Advanced SEO analysis with 40+ factors
8. Import/Export functionality

== Changelog ==

= 1.0.0 =
* Initial release of AI SEO - AI-Powered SEO Plugin.
* AI-powered meta title and description generation
* Content analysis engine with 11 SEO metrics
* Schema markup generator (Article, BlogPosting, WebPage, FAQ, HowTo)
* Meta tags management and injection
* Social media optimization (Open Graph, Twitter Cards)
* XML sitemap generator with smart caching
* Image SEO and alt text generation
* Advanced SEO analysis (40+ factors)
* Bulk editing interface
* Import/Export functionality (Yoast, Rank Math, AIOSEO)
* Multilingual SEO support (WPML, Polylang, TranslatePress)
* Custom post type support
* Internal linking suggestions
* Content suggestions and topic ideas
* 404 monitor and redirection manager
* Permalink optimization
* Enhanced readability analysis
* AI-powered FAQ generator
* Content outline generator
* Smart content rewriter
* Meta description variations
* Unified reporting system
* Automated testing system
* 60+ REST API endpoints
* 70+ WP-CLI commands
* Comprehensive caching system
* Structured logging and monitoring
* Performance optimizations

== Upgrade Notice ==

= 5.0.0 =
Major update! Complete rewrite with modern architecture, REST API, WP-CLI support, and many new features.

== Privacy Policy ==

AI SEO does not collect or store any personal data on our servers. All data remains on your WordPress installation.

When you use AI-powered features, the plugin sends content to OpenAI's API. Please review OpenAI's privacy policy at https://openai.com/policies/privacy-policy.

Your OpenAI API key is stored encrypted in your WordPress database using AES-256-CBC encryption.

== Support ==

For support, please visit:
* Documentation: https://github.com/MervinPraison/WordPressAISEO
* Issues: https://github.com/MervinPraison/WordPressAISEO/issues
* Website: https://mer.vin
