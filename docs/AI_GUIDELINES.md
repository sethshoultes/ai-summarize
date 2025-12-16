# AI Development Guidelines for AI Summarize Plugin

## Project Overview

**Plugin:** AI Summarize WordPress Plugin
**Purpose:** Add AI service buttons to blog posts for quick content summarization
**Framework:** Ground Level Framework + WordPress
**Target:** WordPress 6.6+, PHP 8.2+, modern development standards

## Ground Level Framework Usage

### ✅ Used Packages
- **ground-level-package** - Core plugin bootstrapping and container management
- **ground-level-container** - Dependency injection container
- **ground-level-database** - Database abstraction and connection management
- **ground-level-support** - Utility classes and hooks management
- **ground-level-query-builder** - SQL query building (dependency)

### ❌ Avoided Packages
- **ground-level-events** - Not needed for this plugin's scope
- **ground-level-resque** - No background job processing required
- **ground-level-mothership** - No licensing/addon management needed

### Container Usage Patterns
```php
// DO: Register services in Bootstrap::init()
$container->addService(
    DatabaseService::DB_CONNECTION,
    static function (): wpdb {
        global $wpdb;
        return $wpdb;
    }
);

// DO: Use container for admin classes
Page::setContainer($container);

// DON'T: Access container directly in business logic
```

## Composer and Dependency Management

### Commands to Use
```bash
composer install --ignore-platform-reqs  # Initial setup
composer update --ignore-platform-reqs   # Update dependencies
composer cs-check                        # Coding standards check
composer cs-fix                          # Auto-fix coding standards
composer test                            # Run PHPUnit tests
composer install:wp                      # Setup WordPress test environment
```

### Strauss Configuration
- **Purpose:** Namespace Ground Level dependencies to avoid conflicts
- **Packages to prefix:** All `caseproof/ground-level-*` packages
- **Text domain replacement:** `ai-summarize`
- **Build runs automatically:** On post-install and post-update hooks

### Dependencies Philosophy
- **Runtime dependencies:** Only WordPress core functionality
- **Dev dependencies:** All Ground Level, testing, and tooling packages
- **Platform requirements:** PHP 8.2+ only, ignore platform reqs for dev work

## Coding Standards

### Standards Compliance
- **Primary:** Caseproof-WP standard (includes WordPress + company standards)
- **Base:** PSR-12 + WordPress Coding Standards
- **Tools:** PHPCS with company rulesets

### Code Quality Requirements
- **Zero violations:** `composer cs-check` must pass with no errors/warnings
- **Auto-fixing:** Use `composer cs-fix` for automated corrections
- **Modern PHP:** Leverage PHP 8.2+ features (readonly properties, enums, etc.)
- **strict_types:** Always include `declare(strict_types=1);` after opening PHP tag

### Variable Naming Conventions
**CRITICAL:** Always use camelCase for all variable names (not snake_case)

```php
// ✅ DO: Use camelCase for variables
$postId = get_the_ID();
$enabledServices = $this->settings->getEnabledServices();
$primaryCategory = $categories[0];
$encodedPrompt = rawurlencode($prompt);
$ariaLabel = sprintf('Label: %s', $value);

// ❌ DON'T: Use snake_case for variables
$post_id = get_the_ID();
$enabled_services = $this->settings->getEnabledServices();
$primary_category = $categories[0];
$encoded_prompt = rawurlencode($prompt);
$aria_label = sprintf('Label: %s', $value);
```

**Exception:** WordPress core objects like `$post`, `\WP_Term` have properties in snake_case (e.g., `$term->term_id`). These are acceptable as they're part of WordPress core API.

### Inline Comments
- All inline comments must end with proper punctuation (`.`, `!`, or `?`)
- Comments should be clear and descriptive

```php
// ✅ DO: End comments with punctuation.
// Get the primary category for this post.
$primaryCategory = $categories[0];

// ❌ DON'T: Omit punctuation
// Get the primary category for this post
$primaryCategory = $categories[0];
```

### File Organization
```
src/
├── Admin/           # Admin interface classes
├── Blocks/          # WordPress block implementations
├── Services/        # Business logic services
└── Models/          # Data models if needed (future)
assets/
├── frontend/        # Frontend CSS/JS
│   └── css/
├── blocks/          # Block editor assets (future)
└── images/          # Brand assets and images
tests/
├── Admin/           # Admin tests
├── Blocks/          # Block tests
└── Services/        # Service tests
```

## WordPress Integration Patterns

### Plugin Structure
- **Main file:** `ai-summarize.php` with headers and bootstrap call
- **Bootstrap:** `src/Bootstrap.php` extends Ground Level BaseBootstrap
- **App function:** `src/functions.php` contains `aiSummarizeApp()`
- **Namespace:** `Caseproof\AiSummarize`
- **Text domain:** `ai-summarize`

### Hook Management
```php
// DO: Use Ground Level Hook objects
return [
    new Hook(
        Hook::TYPE_ACTION,
        'admin_menu',
        [Admin\Page::class, 'register']
    ),
];

// DON'T: Use raw WordPress hooks in business logic
```

### WordPress Block Development
- **Registration:** Use `register_block_type()` with proper attributes
- **Frontend:** Server-side rendering preferred for performance and simplicity
- **Render callback:** Use class method with dependency injection via container
- **Styling:** Enqueue styles in `wp_enqueue_scripts` hook, not inline
- **Accessibility:** WCAG 2.1 AA compliance required (ARIA labels, keyboard nav, color contrast)
- **Block cleanup:** Unregister blocks in tests to avoid "already registered" errors

```php
// ✅ DO: Server-side rendering with dependency injection
class AiSummarizeButtons
{
    public function __construct(Settings $settings, UrlGenerator $urlGenerator)
    {
        $this->settings     = $settings;
        $this->urlGenerator = $urlGenerator;
    }

    public function register(): void
    {
        register_block_type(
            'ai-summarize/buttons',
            [
                'api_version'     => 3,
                'render_callback' => [$this, 'render'],
                'attributes'      => [
                    'align' => [
                        'type'    => 'string',
                        'default' => 'center',
                    ],
                ],
            ]
        );
    }

    public function render(array $attributes, string $content, $block): string
    {
        // Render logic here using injected services
    }
}

// Register in Bootstrap hooks
new Hook(
    Hook::TYPE_ACTION,
    'init',
    function (): void {
        $block = $this->container()->get('aiSummarizeButtons');
        $block->register();
    }
),
```

## PHPUnit Testing Framework

### Test Environment Setup
- **Database:** MariaDB/MySQL required for WordPress integration
- **WordPress:** Full WordPress test environment in `tmp/` directory
- **Bootstrap:** Custom bootstrap loads plugin and WordPress test library
- **Base class:** Extend `GroundLevel\Testing\WPTestCase`

### Testing Patterns
```php
// DO: Extend the Ground Level test case
class MyFeatureTest extends TestCase
{
    // Test with WordPress loaded and database available
}

// DO: Use WordPress test functions
$this->factory()->post->create();
$this->assertWPError($result);

// DON'T: Test without WordPress environment for integration tests
```

### Test Organization
- **Unit tests:** Test individual classes/methods in isolation
- **Integration tests:** Test WordPress integration points
- **Coverage target:** 80% minimum
- **Test data:** Use WordPress factories for realistic data
- **Block testing:** Unregister blocks before testing to prevent "already registered" errors

```php
// ✅ DO: Clean up WordPress state in block tests
public function testBlockRegistration(): void
{
    $registry = \WP_Block_Type_Registry::get_instance();

    // Unregister first if already registered.
    if ($registry->is_registered('ai-summarize/buttons')) {
        $registry->unregister('ai-summarize/buttons');
    }

    $this->block->register();
    $this->assertTrue($registry->is_registered('ai-summarize/buttons'));
}

// ✅ DO: Use WordPress test helpers for post context
public function testRenderWithEnabledServices(): void
{
    $postId = $this->factory()->post->create([
        'post_title'   => 'Test Post',
        'post_content' => 'Test content',
    ]);

    global $post;
    $post = get_post($postId);
    setup_postdata($post);

    // Test logic here

    wp_reset_postdata(); // Always clean up
}
```

## AI Service Integration Guidelines

### URL Generation Patterns
```php
// DO: Centralized URL generation
class AiServiceUrlGenerator
{
    private const SERVICES = [
        'chatgpt' => 'https://chat.openai.com/?q=%s',
        'perplexity' => 'https://perplexity.ai/?q=%s',
        // ...
    ];
}

// DON'T: Hardcode URLs in multiple places
```

### Prompt Template System
- **Placeholders:** Use `[[URL]]`, `[[WEBSITE]]`, `[[TAGLINE]]` format
- **Sanitization:** All user input must be escaped/sanitized
- **Validation:** Validate URLs against service whitelist
- **Encoding:** Proper URL encoding for special characters

### Brand Compliance
- **Asset storage:** Store logos/assets in organized directory structure
- **Color codes:** Document hex codes for each service
- **Usage guidelines:** Follow each service's brand requirements
- **Updates:** Plan for brand guideline changes over time

**Brand Colors (from BRAND_RESEARCH.md):**
- ChatGPT: `#10A37F` (green)
- Perplexity: `#20B8CD` (turquoise)
- Claude: `#C15F3C` (rust orange)
- Copilot: `#0078D4` (Microsoft blue)
- You.com: `#1E3A8A` (deep blue)

## Security Considerations

### Input Sanitization
```php
// DO: Sanitize all user inputs
$prompt = sanitize_text_field($_POST['prompt']);
$setting = wp_kses($input, ['strong' => [], 'em' => []]);

// DO: Use nonces for admin forms
wp_verify_nonce($_POST['nonce'], 'ai_summarize_settings');
```

### Vulnerability Prevention
- **Prompt injection:** Validate and sanitize prompt templates
- **XSS:** Escape all output using WordPress functions
- **CSRF:** Use nonces for all admin actions
- **Open redirects:** Validate AI service URLs against whitelist

## Development Workflow

### Weekly Focus Areas
- **Week 2:** Admin settings interface and database schema
- **Week 3:** WordPress block development and frontend rendering
- **Week 4:** AI service integration and URL generation
- **Week 5:** Security hardening and comprehensive testing
- **Week 6:** Performance optimization and launch preparation

### Code Review Checklist
- [ ] Follows Ground Level patterns
- [ ] Passes `composer cs-check` with zero violations
- [ ] Has appropriate test coverage
- [ ] Follows WordPress security practices
- [ ] Uses proper error handling
- [ ] Includes inline documentation

### Git Workflow
- **Branch:** Work on feature branches, merge to main
- **Commits:** Small, focused commits with clear messages
- **Testing:** All tests must pass before merge
- **Standards:** All code must pass coding standards check

## Common Patterns and Decisions

### Settings Storage
- **Method:** WordPress Options API via Ground Level database service
- **Structure:** Single option array vs individual options (decide based on complexity)
- **Caching:** Leverage WordPress object cache when appropriate

### Admin Interface
- **Framework:** WordPress admin pages + Ground Level admin classes
- **Forms:** Standard WordPress form patterns with nonces
- **Validation:** Server-side validation with user-friendly error messages
- **Help text:** Comprehensive help text for all settings

### Performance Considerations
- **Asset loading:** Load CSS/JS only where needed
- **Caching:** Cache expensive operations appropriately
- **Database queries:** Minimize queries, use efficient patterns
- **Frontend impact:** <50ms additional page load time target

## DO's and DON'Ts Summary

### ✅ DO
- Use Ground Level framework patterns consistently
- Follow company coding standards religiously (camelCase variables, strict_types, etc.)
- Write comprehensive tests with WordPress environment
- Sanitize and validate all user inputs
- Use dependency injection container properly
- Document complex business logic with PHPDoc comments
- Keep security considerations in mind always
- End all inline comments with proper punctuation
- Clean up WordPress state in tests (unregister blocks, reset postdata)
- Use `rawurlencode()` for URL parameters to preserve special characters
- Include `declare(strict_types=1);` in all PHP files
- Run `composer cs-fix` before committing to auto-fix violations

### ❌ DON'T
- Use snake_case for variable names (use camelCase instead)
- Hardcode configuration values
- Skip coding standards checks
- Write tests without WordPress integration
- Use deprecated WordPress functions
- Access global variables directly (use container)
- Commit code that doesn't pass tests
- Implement functionality outside planned scope
- Forget to cleanup WordPress state in tests (leads to "already registered" errors)
- Use inline styles/scripts (enqueue them properly instead)

---

**Remember:** This plugin will be deployed on company websites and potentially released open source. Code quality, security, and maintainability are paramount.

**Note:** Week-specific implementation notes and lessons learned are documented in their respective `WEEK_X_TODO.md` files.
