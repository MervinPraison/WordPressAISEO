# AISEO - AI-Powered SEO Plugin for WordPress

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/MervinPraison/WordPressAISEO)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)](LICENSE)

AI-powered SEO optimization for WordPress. Automatically generate meta descriptions, titles, schema markup, and comprehensive SEO analysis using OpenAI's GPT-4o-mini model.

**🤖 AI by Design** - Every feature is built with AI at its core, not as an afterthought.

---

## 🌟 Why AISEO?

### Most Comprehensive AI SEO Plugin
- ✅ **34 features** (more than any competitor)
- ✅ **63+ REST API endpoints** (vs ~10-15 for competitors)
- ✅ **74+ WP-CLI commands** (vs ~15-25 for competitors)
- ✅ **40+ SEO factors analyzed** (vs ~20-30 for competitors)

### Developer-Friendly
- ✅ Complete REST API for headless WordPress
- ✅ Extensive WP-CLI for automation
- ✅ Well-documented codebase (25,000+ lines)

### Security-First
- ✅ AES-256-CBC encryption
- ✅ All WordPress security best practices
- ✅ No vulnerabilities

### Performance-Optimized
- ✅ Smart caching system (24-hour TTL)
- ✅ Async processing via WP-Cron
- ✅ Database query optimization

---

## 🚀 Features

### Core Features

#### 1. **AI-Powered Content Generation**
- **AI Post Creator** - Generate complete blog posts with AI (title, content, SEO metadata)
- **Meta Title Generation** - AI-generated SEO-optimized titles (50-60 characters)
- **Meta Description Generation** - Compelling descriptions (155-160 characters)
- **Multiple Suggestions** - Get 3 AI-generated options to choose from
- **Content Analysis** - AI-powered content quality assessment (11 metrics)
- **Image SEO & Alt Text** - Automatic AI-powered alt text generation
- **Advanced SEO Analysis** - Comprehensive 40+ factor analysis
- **Smart Content Rewriter** - 6 rewrite modes (improve, simplify, expand, shorten, professional, casual)
- **Meta Description Variations** - Generate 5+ variations with scoring and A/B testing support

#### 2. **SEO Analysis & Optimization**
- **Content Analysis Engine** - 11 SEO metrics with actionable recommendations
- **Enhanced Readability Analysis** - 6 readability metrics (Flesch, Gunning Fog, SMOG, etc.)
- **Advanced SEO Analysis** - 40+ factors analyzed across content, technical, and UX
- **Unified Reporting System** - Comprehensive reports combining all analyzers
- **Permalink Optimization** - Remove stop words, SEO-friendly slug suggestions
- **Internal Linking Suggestions** - AI-powered related content suggestions, orphan page detection

#### 3. **Technical SEO**
- **Schema Markup Generator** - JSON-LD for Article, BlogPosting, WebPage, FAQ, HowTo
- **Meta Tags Management** - Automatic injection of SEO meta tags in `<head>`
- **Social Media Optimization** - Open Graph and Twitter Card tags
- **XML Sitemap Generator** - Automatic **sitemap.xml** (also available at wp-sitemap.xml) with smart caching
- **Canonical URLs** - Prevent duplicate content issues
- **Robots Meta Tags** - Control indexing and following

#### 4. **Content Management**
- **Bulk Editing Interface** - Edit metadata for multiple posts at once
- **Import/Export Functionality** - Import from Yoast, Rank Math, AIOSEO; Export to JSON/CSV
- **Multilingual SEO Support** - WPML, Polylang, TranslatePress compatibility
- **Custom Post Type Support** - Extend SEO to any custom post type
- **404 Monitor & Redirection Manager** - Monitor errors, AI-powered redirect suggestions

#### 5. **AI-Powered Tools**
- **AI-Powered FAQ Generator** - Auto-generate FAQs from content with schema markup
- **Content Outline Generator** - AI-powered content outlines with structured headings
- **Content Suggestions** - AI topic ideas, optimization tips, trending topics
- **Automated Testing System** - Comprehensive test suite for all endpoints and commands

#### 6. **Developer Features**
- **REST API** - 60+ endpoints for all features
- **WP-CLI Commands** - 70+ commands for automation and batch processing
- **Caching System** - Transient cache with 24-hour TTL, Redis/Memcached compatible
- **Logging & Monitoring** - Structured logging with JSON format
- **Performance Optimization** - Async processing, conditional loading, database optimization

---

## 📊 Implementation Status

**Version 1.0.0 - Feature Complete!**

✅ **33 Features Implemented** (27 core + 6 bonus features)

| Category | Status | Features |
|----------|--------|----------|
| **Core Features (1-7)** | ✅ 7/7 | AI Meta Generation, Content Analysis, Schema Markup, Social Media, XML Sitemap, Permalink Optimization, Internal Linking |
| **Phase 1 (8-11)** | ✅ 4/4 | Image SEO, Advanced Analysis (40+ factors), Bulk Editing, Import/Export |
| **Phase 2 (12-14)** | ⏳ 0/3 | Google Search Console, Google Analytics 4, Rank Tracking (requires paid APIs) |
| **Phase 3 (15-16)** | ✅ 2/2 | 404 Monitor & Redirects, Internal Linking |
| **Phase 4 (17-21)** | ✅ 2/5 | Content Suggestions, Multilingual Support (Competitor/Keyword/Backlink require paid APIs) |
| **Bonus Features** | ✅ 6/6 | Enhanced Readability, FAQ Generator, Content Outline, Smart Rewriter, Meta Variations, Unified Reporting |

**API Coverage:**
- ✅ 60+ REST API endpoints
- ✅ 70+ WP-CLI commands
- ✅ 6 database tables
- ✅ Full documentation

**See [IMPLEMENTATION-STATUS.md](IMPLEMENTATION-STATUS.md) for detailed feature breakdown.**

---

## 📦 Installation

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- OpenAI API key ([Get one here](https://platform.openai.com/api-keys))

### Quick Install

1. **Download the plugin**
   ```bash
   cd wp-content/plugins
   git clone https://github.com/MervinPraison/WordPressAISEO.git
   ```

2. **Activate the plugin**
   ```bash
   wp plugin activate aiseo
   ```

3. **Configure API key**
   
   **Option A:** Via WordPress Admin
   - Go to Settings → AISEO
   - Enter your OpenAI API key
   - Click Save
   
   **Option B:** Using `.env` file (recommended for development)
   ```bash
   cd wp-content/plugins/aiseo
   echo "OPENAI_API_KEY=your-api-key-here" > .env
   ```
   
   **Option C:** Using WP-CLI
   ```bash
   wp eval 'AISEO_Helpers::save_api_key("your-api-key-here");'
   ```

---

## 🎯 Quick Start

### Generate SEO Metadata

**Via Admin Interface:**
1. Edit any post or page
2. Scroll to "AISEO - SEO Optimization" metabox
3. Click "Generate Title" or "Generate Description"
4. Review AI-generated suggestions
5. Click "Use This" to apply

**Via WP-CLI:**
```bash
# Generate metadata for a single post
wp aiseo generate --id=123 --meta=title,description

# Generate for all posts
wp aiseo generate --all --post-type=post
```

**Via REST API:**
```bash
# Generate SEO title
curl -X POST https://yoursite.test/wp-json/aiseo/v1/generate/title \
  -H "Content-Type: application/json" \
  -d '{"content": "Your post content", "keyword": "wordpress seo"}'
```

### Analyze SEO

**Via WP-CLI:**
```bash
# Analyze single post
wp aiseo analyze --id=123

# Analyze all posts and export to CSV
wp aiseo analyze --all --format=csv > seo-report.csv
```

**Via REST API:**
```bash
# Analyze post SEO (11 metrics)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'
```

---

## 🔌 REST API Endpoints

All endpoints are tested and working! Perfect for browser testing, mobile apps, JavaScript integrations, and automation.

### Core Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/status` | GET | Plugin status and configuration |
| `/wp-json/aiseo/v1/validate-key` | GET | Validate OpenAI API key |
| `/wp-json/aiseo/v1/generate/title` | POST | Generate SEO title |
| `/wp-json/aiseo/v1/generate/description` | POST | Generate meta description |
| `/wp-json/aiseo/v1/generate/post/{id}` | POST | Generate metadata for post |

### Analysis Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/analyze` | POST | Comprehensive SEO analysis (11 metrics) |
| `/wp-json/aiseo/v1/analyze/advanced/{id}` | GET | Advanced SEO analysis (40+ factors) |
| `/wp-json/aiseo/v1/readability/analyze/{post_id}` | GET | Enhanced readability analysis |
| `/wp-json/aiseo/v1/report/unified/{id}` | GET | Get unified SEO report |
| `/wp-json/aiseo/v1/report/history/{id}` | GET | Get historical reports |

### Schema & Meta Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/schema/{id}` | GET | Get schema markup (JSON-LD) |
| `/wp-json/aiseo/v1/meta-tags/{id}` | GET | Get all meta tags for a post |
| `/wp-json/aiseo/v1/social-tags/{id}` | GET | Get social media tags (OG & Twitter) |
| `/wp-json/aiseo/v1/sitemap/stats` | GET | Get sitemap statistics |

### Image SEO Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/image/generate-alt/{id}` | POST | Generate alt text for image |
| `/wp-json/aiseo/v1/image/missing-alt` | GET | Get images missing alt text |
| `/wp-json/aiseo/v1/image/seo-score/{id}` | GET | Get image SEO score |

### Bulk Operations Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/bulk/posts` | GET | Get posts for bulk editing |
| `/wp-json/aiseo/v1/bulk/update` | POST | Bulk update multiple posts |
| `/wp-json/aiseo/v1/bulk/generate` | POST | Bulk generate metadata with AI |
| `/wp-json/aiseo/v1/bulk/preview` | POST | Preview bulk changes |

### Import/Export Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/export/json` | GET | Export metadata to JSON |
| `/wp-json/aiseo/v1/export/csv` | GET | Export metadata to CSV |
| `/wp-json/aiseo/v1/import/json` | POST | Import from JSON |
| `/wp-json/aiseo/v1/import/yoast` | POST | Import from Yoast SEO |
| `/wp-json/aiseo/v1/import/rankmath` | POST | Import from Rank Math |
| `/wp-json/aiseo/v1/import/aioseo` | POST | Import from AIOSEO |

### Content & Linking Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/internal-linking/suggestions/{post_id}` | GET | Get internal linking suggestions |
| `/wp-json/aiseo/v1/internal-linking/orphans` | GET | Detect orphan pages |
| `/wp-json/aiseo/v1/content/topics` | POST | Get AI topic suggestions |
| `/wp-json/aiseo/v1/content/optimize/{post_id}` | GET | Get optimization tips for post |
| `/wp-json/aiseo/v1/content/trending` | GET | Get trending topics |
| `/wp-json/aiseo/v1/content/brief` | POST | Generate content brief |

### Redirects & 404 Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/404/errors` | GET | Get 404 errors log |
| `/wp-json/aiseo/v1/404/suggest` | POST | AI-powered redirect suggestions |
| `/wp-json/aiseo/v1/redirects/create` | POST | Create redirect |
| `/wp-json/aiseo/v1/redirects/list` | GET | Get all redirects |
| `/wp-json/aiseo/v1/redirects/delete/{id}` | DELETE | Delete redirect |
| `/wp-json/aiseo/v1/redirects/import` | POST | Bulk import redirects from CSV |
| `/wp-json/aiseo/v1/redirects/export` | GET | Export redirects to CSV |

### AI Tools Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/post/create` | POST | Create AI-generated post |
| `/wp-json/aiseo/v1/post/bulk-create` | POST | Bulk create AI-generated posts |
| `/wp-json/aiseo/v1/post/stats` | GET | Get post creator statistics |
| `/wp-json/aiseo/v1/faq/generate/{post_id}` | POST | Generate FAQs from content |
| `/wp-json/aiseo/v1/faq/get/{post_id}` | GET | Get saved FAQs |
| `/wp-json/aiseo/v1/outline/generate` | POST | Generate content outline |
| `/wp-json/aiseo/v1/rewrite/content` | POST | Rewrite content with AI |
| `/wp-json/aiseo/v1/meta/variations/{post_id}` | POST | Generate meta variations |

### Multilingual & CPT Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/multilingual/plugin` | GET | Get active multilingual plugin |
| `/wp-json/aiseo/v1/multilingual/languages` | GET | Get available languages |
| `/wp-json/aiseo/v1/multilingual/translations/{id}` | GET | Get post translations |
| `/wp-json/aiseo/v1/multilingual/sync/{id}` | POST | Sync metadata across translations |
| `/wp-json/aiseo/v1/cpt/list` | GET | List all custom post types |
| `/wp-json/aiseo/v1/cpt/supported` | GET | Get supported post types |
| `/wp-json/aiseo/v1/cpt/enable` | POST | Enable SEO for post type |

**Full API documentation:** See [REST API Examples](#rest-api-examples) below.

---

## 💻 WP-CLI Commands

Comprehensive command-line interface for automation and batch processing.

### Core Commands

| Command | Description |
|---------|-------------|
| `wp aiseo generate` | Generate SEO metadata (by slug, ID, or --all) |
| `wp aiseo analyze` | Analyze SEO scores with multiple output formats |
| `wp aiseo meta` | Get/update/delete specific metadata fields |
| `wp aiseo cache` | Clear AISEO caches |
| `wp aiseo export` | Export metadata to JSON |

### Image SEO Commands

| Command | Description |
|---------|-------------|
| `wp aiseo image generate-alt` | Generate alt text for image |
| `wp aiseo image bulk-generate` | Bulk generate alt text |
| `wp aiseo image detect-missing` | Find images without alt text |
| `wp aiseo image analyze` | Analyze image SEO for post |

### Analysis Commands

| Command | Description |
|---------|-------------|
| `wp aiseo advanced analyze` | Advanced SEO analysis (40+ factors) |
| `wp aiseo advanced bulk` | Bulk advanced analysis |
| `wp aiseo readability analyze <post-id>` | Enhanced readability analysis |
| `wp aiseo readability bulk` | Bulk readability analysis |
| `wp aiseo report unified <post-id>` | Generate unified SEO report |
| `wp aiseo report history <post-id>` | Get historical reports |

### Bulk Operations Commands

| Command | Description |
|---------|-------------|
| `wp aiseo bulk list` | List posts for bulk editing |
| `wp aiseo bulk update` | Bulk update metadata |
| `wp aiseo bulk generate` | Bulk generate metadata with AI |
| `wp aiseo bulk preview` | Preview bulk changes |

### Import/Export Commands

| Command | Description |
|---------|-------------|
| `wp aiseo export json` | Export to JSON |
| `wp aiseo export csv` | Export to CSV |
| `wp aiseo import json` | Import from JSON file |
| `wp aiseo import yoast` | Import from Yoast SEO |
| `wp aiseo import rankmath` | Import from Rank Math |
| `wp aiseo import aioseo` | Import from AIOSEO |

### Content & Linking Commands

| Command | Description |
|---------|-------------|
| `wp aiseo internal-linking suggestions <post-id>` | Get internal linking suggestions |
| `wp aiseo internal-linking orphans` | Detect orphan pages |
| `wp aiseo content topics` | Get AI-powered topic suggestions |
| `wp aiseo content optimize <post-id>` | Get optimization tips for post |
| `wp aiseo content trending <niche>` | Get trending topics |

### Redirects & 404 Commands

| Command | Description |
|---------|-------------|
| `wp aiseo 404 errors` | Get 404 errors log |
| `wp aiseo 404 suggest <url>` | Get AI-powered redirect suggestions |
| `wp aiseo redirects create <source> <target>` | Create redirect |
| `wp aiseo redirects list` | List all redirects |
| `wp aiseo redirects import <file>` | Import redirects from CSV |
| `wp aiseo redirects export` | Export redirects to CSV |

### AI Tools Commands

| Command | Description |
|---------|-------------|
| `wp aiseo post create` | Create AI-generated post |
| `wp aiseo post bulk-create <file>` | Bulk create posts from CSV/JSON |
| `wp aiseo post list` | List AI-generated posts |
| `wp aiseo post stats` | Get post creator statistics |
| `wp aiseo faq generate <post-id>` | Generate FAQs from content |
| `wp aiseo outline generate <topic>` | Generate content outline |
| `wp aiseo rewrite content <post-id>` | Rewrite post content |
| `wp aiseo meta variations <post-id>` | Generate meta variations |
| `wp aiseo permalink optimize <post-id>` | Optimize permalink |

### Multilingual & CPT Commands

| Command | Description |
|---------|-------------|
| `wp aiseo multilingual plugin` | Get active multilingual plugin |
| `wp aiseo multilingual sync` | Sync metadata across translations |
| `wp aiseo cpt list` | List all custom post types |
| `wp aiseo cpt enable` | Enable SEO for post type |
| `wp aiseo cpt bulk-generate` | Bulk generate metadata |

**Full CLI documentation:** See [WP-CLI Examples](#wp-cli-examples) below.

---

## 🧪 Testing

### Quick Test Commands

```bash
# Test plugin status
curl https://yoursite.test/wp-json/aiseo/v1/status

# Test SEO analysis
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'

# Test WP-CLI
wp aiseo analyze --id=123
```

### Complete Testing Workflow

```bash
# 1. Create test post
POST_ID=$(wp post create --post_title="Test SEO Post" \
  --post_content="Lorem ipsum dolor sit amet..." \
  --post_status=publish --porcelain)

# 2. Set focus keyword
wp aiseo meta update $POST_ID focus_keyword "wordpress seo"

# 3. Generate all metadata
wp aiseo generate --id=$POST_ID --meta=all

# 4. Analyze content
wp aiseo analyze --id=$POST_ID --format=json

# 5. Verify via REST API
curl https://yoursite.test/wp-json/aiseo/v1/meta-tags/$POST_ID
```

For comprehensive testing documentation, see [tests/TESTING.md](tests/TESTING.md).

---

## 🔧 Configuration

### Plugin Options

| Option | Default | Description |
|--------|---------|-------------|
| `aiseo_api_model` | `gpt-4o-mini` | OpenAI model to use |
| `aiseo_api_timeout` | `45` | API timeout in seconds |
| `aiseo_rate_limit_per_minute` | `10` | Requests per minute |
| `aiseo_rate_limit_per_hour` | `60` | Requests per hour |

### Update Configuration

```bash
# Change API model
wp option update aiseo_api_model "gpt-4o"

# Adjust rate limits
wp option update aiseo_rate_limit_per_minute 20
```

---

## 🔒 Security Features

- **AES-256-CBC Encryption** - API keys encrypted in database
- **Nonce Verification** - All AJAX requests protected
- **Capability Checks** - Proper WordPress permission handling
- **Input Sanitization** - XSS and SQL injection prevention
- **Output Escaping** - All output properly escaped

---

## ⚡ Performance

- **Caching System** - Transient cache with 24-hour TTL
- **Object Cache Support** - Redis/Memcached compatible
- **Conditional Loading** - Scripts only on relevant pages
- **Database Optimization** - Indexed queries and batch updates
- **Async Processing** - WP-Cron for background tasks

---

## 📊 OpenAI API Integration

- **GPT-4o-mini Model** - Cost-efficient AI model ($0.15/1M input tokens)
- **Rate Limiting** - 10 requests/minute, 60 requests/hour
- **Circuit Breaker** - Automatic failure handling
- **Retry Logic** - Exponential backoff (3 retries)
- **Token Tracking** - Monitor usage and costs
- **Error Handling** - Graceful degradation on API failures

### Cost Estimation
- Average meta description: ~100 tokens = $0.00006
- Average SEO title: ~50 tokens = $0.00003

---

## 🐛 Debugging

### Enable Debug Mode

Add to `wp-config.php`:
```php
define('AISEO_DEBUG', true);
```

### View Logs

```bash
# View recent logs
wp db query "SELECT * FROM wp_aiseo_logs ORDER BY timestamp DESC LIMIT 50"

# View errors only
wp db query "SELECT * FROM wp_aiseo_logs WHERE level='ERROR' ORDER BY timestamp DESC LIMIT 20"
```

---

## 📚 Documentation

- [Architecture Documentation](ARCHITECTURE.md) - Detailed technical architecture
- [Testing Guide](tests/TESTING.md) - Comprehensive testing workflows, REST API & WP-CLI commands
- [Nonce Fix Summary](NONCE-FIX-SUMMARY.md) - AJAX nonce issue resolution
- [WordPress.org Checklist](WORDPRESS-ORG-CHECKLIST.md) - Publication checklist

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 🔧 Development Workflow

**AISEO follows a strict test-driven development workflow to ensure quality and reliability.**

### Feature Development Process

For **every new feature**, follow this exact workflow:

```
1. Create Core Class → 2. Build REST API → 3. Build WP-CLI → 4. Test Both → 5. Update Docs
```

#### Step-by-Step:

**1. Create Core Class**
```php
// Example: includes/class-aiseo-feature.php
class AISEO_Feature {
    public function process($data) {
        // Core logic here
    }
}
```

**2. Build REST API Endpoint**
```php
// Add to includes/class-aiseo-rest.php
register_rest_route('aiseo/v1', '/feature/process', [
    'methods' => 'POST',
    'callback' => [$this, 'process_feature'],
    'permission_callback' => function() {
        return current_user_can('edit_posts');
    }
]);
```

**3. Build WP-CLI Command**
```php
// Create: includes/cli/class-aiseo-feature-cli.php
class AISEO_Feature_CLI {
    public function process($args, $assoc_args) {
        WP_CLI::success('Feature processed!');
    }
}
WP_CLI::add_command('aiseo feature', 'AISEO_Feature_CLI');
```

**4. Test Both (CRITICAL)**
```bash
# Test REST API
curl -k "https://wordpress.test/wp-json/aiseo/v1/feature/process" \
  -X POST -H "Content-Type: application/json" \
  -d '{"data": "test"}'

# Test WP-CLI
wp aiseo feature process --data=test

# Both must work before proceeding!
```

**5. Update Documentation**
- Add feature to README.md (this file)
- Add REST endpoint to API section
- Add WP-CLI command to CLI section
- Add usage examples
- Update ARCHITECTURE.md with technical details

### Development Guidelines

#### AI-First Approach
- ✅ **Always** use AI for content generation, analysis, and optimization
- ✅ **Focus** on features that don't require paid third-party services (except OpenAI)
- ✅ **Prioritize** free, AI-powered features over paid integrations
- ❌ **Avoid** features requiring paid APIs (SEMrush, Ahrefs, etc.) in core version

#### Code Quality Standards
```bash
# Generate code index for context
/opt/homebrew/bin/ctags -R --languages=PHP --output-format=json > api-index.json

# Follow WordPress Coding Standards
phpcs --standard=WordPress includes/

# Test before committing
./tests/test-all-endpoints.sh
```

#### Testing Requirements
- ✅ **REST API**: Test with curl, browser, or Postman
- ✅ **WP-CLI**: Test all commands with various parameters
- ✅ **Both**: Must work simultaneously (no conflicts)
- ✅ **Documentation**: Update before marking feature complete

### Example: Complete Feature Implementation

```bash
# 1. Create core class
vim includes/class-aiseo-example.php

# 2. Add REST API endpoint
vim includes/class-aiseo-rest.php

# 3. Create WP-CLI command
vim includes/cli/class-aiseo-example-cli.php

# 4. Test REST API
curl -k "https://wordpress.test/wp-json/aiseo/v1/example/test"

# 5. Test WP-CLI
wp aiseo example test

# 6. Update docs
vim README.md
vim ARCHITECTURE.md

# 7. Commit
git add .
git commit -m "Add Example feature with REST API and WP-CLI"
```

### Quality Checklist

Before marking any feature as "complete":

- [ ] Core class created and functional
- [ ] REST API endpoint registered and tested
- [ ] WP-CLI command registered and tested
- [ ] Both REST and CLI work without errors
- [ ] README.md updated with examples
- [ ] ARCHITECTURE.md updated with technical details
- [ ] Code follows WordPress standards
- [ ] Security checks implemented (nonces, capabilities)
- [ ] Error handling implemented
- [ ] Success/failure messages clear

---

## 📝 License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- OpenAI for the GPT-4o-mini API
- WordPress community for excellent documentation
- Yoast SEO and SEOPress for inspiration

---

## 📧 Support

- Documentation: This README and linked docs
- Issues: [GitHub Issues](https://github.com/MervinPraison/WordPressAISEO/issues)
- Website: [https://praison.ai](https://praison.ai)

---

## 🌟 External Services

This plugin connects to the OpenAI API to provide AI-powered SEO features.

**Service Used:** OpenAI API (https://api.openai.com/)

**Purpose:** The plugin sends post content and keywords to OpenAI to generate SEO titles, meta descriptions, content analysis, and other AI-powered features.

**Data Sent:** When a user actively uses AI generation features, the following data is transmitted:
- Post content (title and body)
- Focus keyword (if specified)
- User-specified parameters (e.g., tone, length)

**When Data is Sent:** Data is sent to OpenAI only when:
- A user actively clicks "Generate Title" or "Generate Description"
- A user runs WP-CLI commands with AI generation
- A user calls REST API endpoints for AI generation

**Privacy & Terms:**
- Privacy Policy: https://openai.com/policies/privacy-policy
- Terms of Use: https://openai.com/policies/terms-of-use
- API Data Usage: https://openai.com/policies/api-data-usage-policies

**User Control:** The plugin only connects to OpenAI when you provide an API key and explicitly use AI generation features. No data is sent without your explicit action.

---

## 🔐 API Authentication

### Security Notice

As of version 1.0.0, all write operations (POST, PUT, DELETE) require authentication to protect your site from unauthorized access.

**Protected Endpoints:**
- All POST/PUT/DELETE operations (generate, update, import, etc.)
- Bulk operations
- Import/export operations
- Settings modifications

**Public Endpoints (Read-Only):**
- `/status` - Plugin status
- `/schema/{id}` - Schema markup
- `/meta-tags/{id}` - Meta tags
- `/analyze/advanced/{id}` - SEO analysis
- And other GET endpoints that only retrieve data

### Authentication Methods

#### Method 1: WordPress Application Passwords (Recommended)

1. **Create Application Password:**
   - Go to WordPress Admin → Users → Your Profile
   - Scroll to "Application Passwords"
   - Enter a name (e.g., "AISEO API")
   - Click "Add New Application Password"
   - Copy the generated password

2. **Use with cURL:**
```bash
# Using Basic Auth with Application Password
curl -X POST https://yoursite.test/wp-json/aiseo/v1/generate/title \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your post content here",
    "keyword": "wordpress seo"
  }'
```

#### Method 2: Cookie Authentication (Browser/JavaScript)

For browser-based requests, WordPress automatically handles authentication via cookies:

```javascript
// JavaScript example (must be on same domain)
fetch('/wp-json/aiseo/v1/generate/title', {
  method: 'POST',
  credentials: 'same-origin', // Include cookies
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce // WordPress nonce
  },
  body: JSON.stringify({
    content: 'Your post content',
    keyword: 'wordpress seo'
  })
});
```

#### Method 3: OAuth (Advanced)

For third-party applications, use WordPress OAuth plugins like:
- WP OAuth Server
- OAuth2 Provider

### WP-CLI Authentication

**WP-CLI commands automatically use WordPress authentication** and are not affected by API restrictions. All commands work as normal:

```bash
# WP-CLI commands work without additional authentication
wp aiseo generate --id=123 --meta=title,description
wp aiseo analyze --id=123
wp aiseo bulk generate 123,456,789
```

**Why?** WP-CLI runs within the WordPress environment and has full access to all WordPress functions and capabilities.

---

## REST API Examples

### Generate SEO Title (Authenticated)
```bash
# With Application Password
curl -X POST https://yoursite.test/wp-json/aiseo/v1/generate/title \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your post content here",
    "keyword": "wordpress seo"
  }'
```

### Analyze Post SEO (Authenticated)
```bash
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'
```

### Get Schema Markup (Public - No Auth Required)
```bash
# Read-only endpoints don't require authentication
curl https://yoursite.test/wp-json/aiseo/v1/schema/123
```

### Bulk Operations (Authenticated)
```bash
# Get posts for bulk editing (requires auth)
curl "https://yoursite.test/wp-json/aiseo/v1/bulk/posts?post_type=post&limit=10" \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx"

# Bulk update posts (requires auth)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/update \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"updates": [{"post_id": 123, "focus_keyword": "wordpress seo"}]}'
```

### Import/Export (Authenticated)
```bash
# Export to JSON (requires auth)
curl "https://yoursite.test/wp-json/aiseo/v1/export/json?post_type=post" \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  > export.json

# Import from Yoast SEO (requires auth)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/yoast \
  --user "username:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": false}'
```

---

## WP-CLI Examples

### Generate Metadata
```bash
# Single post
wp aiseo generate --id=123 --meta=title,description

# All posts
wp aiseo generate --all --post-type=post

# Dry run (preview without saving)
wp aiseo generate --id=123 --dry-run
```

### Analyze SEO
```bash
# Single post
wp aiseo analyze --id=123

# All posts with JSON output
wp aiseo analyze --all --format=json

# Export to CSV
wp aiseo analyze --all --format=csv > seo-scores.csv
```

### Image SEO
```bash
# Generate alt text for single image
wp aiseo image generate-alt 456

# Bulk generate alt text
wp aiseo image bulk-generate --missing-only --limit=50

# Detect images without alt text
wp aiseo image detect-missing --format=table
```

### Advanced Analysis
```bash
# Analyze post with 40+ SEO factors
wp aiseo advanced analyze 123

# Bulk analyze posts with scores below 70%
wp aiseo advanced bulk --min-score=70 --limit=20

# Export to CSV
wp aiseo advanced bulk --min-score=80 --format=csv > seo-report.csv
```

### Bulk Operations
```bash
# List posts for bulk editing
wp aiseo bulk list --limit=20 --format=table

# Bulk update focus keyword
wp aiseo bulk update 123,456,789 --focus-keyword="wordpress seo"

# Bulk generate metadata
wp aiseo bulk generate 123,456,789 --meta-types=title,description
```

### Import/Export
```bash
# Export to JSON
wp aiseo export json --output=aiseo-export.json

# Export to CSV
wp aiseo export csv --output=aiseo-export.csv

# Import from JSON
wp aiseo import json aiseo-export.json

# Import from Yoast SEO
wp aiseo import yoast --post-type=post
```

---

## 📦 Publishing to WordPress.org

This repository contains both `aiseo.php` and `seo-wordpress.php` (same content, different plugin names):
- **aiseo.php** - local plugin header
- **seo-wordpress.php** - Praison AI SEO, the file shipped for the WordPress.org slug `seo-wordpress`

The plugin SVN checkout is `/Users/praison/seo-wordpress-svn` (`https://plugins.svn.wordpress.org/seo-wordpress`). WordPress.org only sees SVN, and only a finished release should be committed there. Update trunk, then copy trunk to a tag with `svn cp`. The Stable tag in `readme.txt` must match that tag.

```bash
# From this repository. Version is read from seo-wordpress.php.
# Uses the cached WordPress.org SVN login for mervinpraison.
./publish.sh 5.0.10

# Preview the trunk sync without committing:
./publish.sh 5.0.10 --dry-run
```

`publish.sh` syncs this directory to `trunk/`, removes `aiseo.php` and development files, commits trunk, then tags `tags/<version>`.

### Git remote

```bash
origin -> github.com:MervinPraison/WordPressAISEO.git (main)
```

```bash
git push origin main
```

---

**Made with ❤️ by [PraisonAI](https://praison.ai)**
