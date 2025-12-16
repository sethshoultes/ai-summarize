# AI Summarize WordPress Plugin

A WordPress plugin that adds AI service buttons to blog posts, allowing visitors to quickly summarize content using popular AI services.

## Overview

AI Summarize provides branded action buttons that, when clicked, open the user's preferred AI service with a pre-configured prompt containing the current page URL and customizable context about the website. This approach leverages users' existing AI subscriptions and conversation history while providing a seamless content summarization experience.

**Supported AI Services:**
- ChatGPT (OpenAI)
- Perplexity
- Claude (Anthropic)
- Microsoft Copilot
- You.com

## Features

### Core Functionality
- **WordPress Block Editor Integration** - Add AI summarize buttons via Gutenberg block
- **Live Preview in Editor** - See exactly how buttons will look with real-time updates
- **5 AI Services** - Support for ChatGPT, Perplexity, Claude, Copilot, and You.com
- **Smart Modal System** - Services without URL parameter support (like Claude) show modal with copy-to-clipboard
- **Customizable Prompts** - Global and category-specific prompt templates
- **Dynamic Placeholders** - Insert post data: `[[URL]]`, `[[WEBSITE]]`, `[[TAGLINE]]`, `[[TITLE]]`, `[[EXCERPT]]`, `[[CATEGORY]]`
- **Category Overrides** - Different prompts and services per WordPress category
- **Flexible Display Options** - Multiple layouts (horizontal, vertical, grid), sizes, and label visibility

### Design & Accessibility
- **Brand-Compliant Styling** - Official brand colors and guidelines for each AI service
- **WCAG 2.1 AA Compliant** - Full accessibility with keyboard navigation, screen readers, ARIA labels
- **Responsive Design** - Mobile-friendly layouts with proper touch targets (44px minimum)
- **High Contrast Mode** - Support for `prefers-contrast: high`
- **Reduced Motion** - Respects `prefers-reduced-motion` preference

### Security & Performance
- **Input Sanitization** - All user inputs sanitized (WordPress functions)
- **Output Escaping** - All outputs properly escaped (XSS protection)
- **CSRF Protection** - Nonce verification on all forms
- **Capability Checks** - Proper permission validation
- **Optimized Assets** - Minimal CSS (4.7KB) and JS (3.1KB)
- **Efficient Queries** - No N+1 database issues

### Internationalization
- **Fully Translatable** - All strings use WordPress i18n functions
- **Text Domain:** `ai-summarize`
- **RTL Support** - Right-to-left language compatibility

## Requirements

- **WordPress:** 6.6 or later
- **PHP:** 8.2 or later
- **Browser:** Modern browsers (Chrome, Firefox, Safari, Edge)

### Installation

1. Clone the repository
2. Install dependencies: `composer install --ignore-platform-reqs`
3. Set up WordPress test environment: `composer install:wp`

### Development Commands

```bash
# Run coding standards check
composer cs-check

# Fix coding standards violations
composer cs-fix

# Run tests
composer test

# Install WordPress test environment
composer install:wp
```

### Project Structure

```
ai-summarize.php          # Main plugin file
src/
├── Admin/Page.php        # Admin interface
├── Bootstrap.php         # Plugin bootstrap
└── functions.php         # Main app function
tests/                    # PHPUnit tests
vendor-prefixed/          # Namespaced dependencies
```

## License

GPL-3.0

## Installation

### For Production Use

1. Download the plugin ZIP file
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Click **Activate Plugin**
5. Go to **Settings → AI Summarize** to configure

### For Development

1. Clone the repository into your WordPress plugins directory
2. Run `composer install --ignore-platform-reqs`
3. Activate the plugin from WordPress admin

## Usage

### Basic Setup

1. **Configure Global Settings**
   - Go to **Settings → AI Summarize → General**
   - Set your global prompt template using placeholders
   - Configure display options (button size, layout, labels)

2. **Enable AI Services**
   - Go to the **AI Services** tab
   - Check which services you want to enable
   - All 5 services are enabled by default

3. **Add to Posts**
   - Edit any post or page
   - Add the **AI Summarize Buttons** block
   - Choose to use global settings or customize per-block

### Advanced Configuration

#### Category-Specific Prompts

Configure different prompts for different post categories:

1. Go to **Settings → AI Summarize → Categories**
2. Select a category from the dropdown
3. Set custom prompt template and enabled services
4. Save settings

**Prompt Hierarchy:**
- Category-specific prompt (if configured)
- Global prompt (fallback)

#### Available Placeholders

Use these in your prompt templates:

- `[[URL]]` - Current post permalink
- `[[WEBSITE]]` - Site name from WordPress settings
- `[[TAGLINE]]` - Site tagline
- `[[TITLE]]` - Post title
- `[[EXCERPT]]` - Post excerpt
- `[[CATEGORY]]` - Primary category name

**Example Prompt:**
```
Please summarize this [[CATEGORY]] article from [[WEBSITE]]: "[[TITLE]]"

You can read the full article here: [[URL]]
```

#### Block Customization

Each block can override global settings:

1. Add **AI Summarize Buttons** block to post
2. **Live Preview**: Block shows real-time preview of buttons in the editor
3. In block sidebar, toggle **Use Global Settings** OFF to customize
4. Choose which services to display
5. Set button size, layout, and label visibility
6. Preview updates instantly as you make changes

**Preview Features:**
- See exactly how buttons will appear on the published post
- Real-time updates for all settings (size, layout, labels, services)
- Global settings mode shows actual admin panel configuration
- Custom settings mode shows your selected configuration
- Non-clickable in editor (prevents accidental navigation)

### Display Options

**Button Sizes:**
- Small (14px, 36px min height)
- Medium (16px, 44px min height) - Default
- Large (18px, 52px min height)

**Layouts:**
- Horizontal (default)
- Vertical
- Grid (auto-fit columns)

**Other Options:**
- Show/hide service labels
- Block alignment (left, center, right, wide, full)

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Check coding standards
composer cs-check

# Fix coding standards violations
composer cs-fix

# Generate coverage report (requires Xdebug)
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tmp/coverage
```

**Test Coverage:** 64 tests, 227 assertions, **92.93% line coverage**

### Coverage Breakdown

| Component | Line Coverage | Status |
|-----------|--------------|---------|
| UrlGenerator | 100% (11/11) | ✅ Perfect |
| Settings | 98.99% (98/99) | ✅ Exceptional |
| AiSummarizeButtons | 97.94% (95/97) | ✅ Exceptional |
| Bootstrap | 97.30% (72/74) | ✅ Exceptional |
| Admin/Page | 85.71% (102/119) | ✅ Very Good |
| **Overall** | **92.93% (381/410)** | ✅ Exceptional |

**Coverage Report:** View detailed HTML report at `tmp/coverage/index.html`

### Manual Testing Checklist

- [ ] Install and activate plugin
- [ ] Configure global settings
- [ ] Enable/disable services
- [ ] Add block to post
- [ ] Test all layout options
- [ ] Test category overrides
- [ ] Click each AI service button
- [ ] Verify URLs open correctly with prompt
- [ ] Test responsive design on mobile
- [ ] Test keyboard navigation
- [ ] Test with screen reader

## Troubleshooting

### Buttons Not Appearing

1. Check that at least one service is enabled in **AI Services** tab
2. Verify the block is added to the post
3. If using category overrides, ensure services are enabled for that category
4. Check that the post is published (buttons don't show in drafts)

### Prompts Not Working

1. Verify placeholder syntax is correct: `[[PLACEHOLDER]]`
2. Check that post has required data (title, URL, category)
3. Test with the default prompt first

### Styling Issues

1. Check for theme CSS conflicts
2. Verify `assets/frontend/css/blocks.css` is loading
3. Clear browser cache
4. Check browser console for errors

## Development

This plugin is built using WordPress coding standards.

### Project Structure

```
ai-summarize.php               # Main plugin file
src/
├── Admin/
│   └── Page.php              # Admin interface
├── Blocks/
│   └── AiSummarizeButtons.php # Block registration & rendering
├── Services/
│   ├── Settings.php          # Settings management
│   └── UrlGenerator.php      # AI service URL generation
├── Bootstrap.php             # Plugin bootstrap
└── functions.php             # Helper functions
views/admin/
└── tabs/                     # Admin tab templates
tests/                        # PHPUnit tests
assets/frontend/css/          # Frontend styles
build/blocks/                 # Built block assets
```

## Changelog

### 0.1.0 (October 2024)
- Initial release
- Support for 5 AI services (ChatGPT, Perplexity, Claude, Copilot, You.com)
- WordPress block editor integration with server-side rendering
- **Live preview in block editor** - see buttons with real-time updates
- **Smart modal system** - Services without URL parameters (Claude) use modal with copy-to-clipboard
- Global and category-specific prompt templates
- 6 dynamic placeholders for prompt customization
- WCAG 2.1 AA accessibility compliant
- Full internationalization support (i18n/RTL)
- **92.93% test coverage** with 64 tests and 227 assertions
- Zero security vulnerabilities
- Optimized performance (4.7KB CSS, 4.1KB + 4KB modal JS)
- Comprehensive admin interface with 4 tabs
- Multiple layout options (horizontal, vertical, grid)
- Responsive design with mobile support
