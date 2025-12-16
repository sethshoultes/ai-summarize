<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Services;

/**
 * Settings service for managing plugin configuration.
 *
 * Handles all plugin settings using WordPress Options API including
 * global prompt templates, AI service configuration, and category overrides.
 */
class Settings
{
    /**
     * Main plugin settings option key.
     */
    public const OPTION_KEY = 'ai_summarize_settings';

    /**
     * Category overrides option key.
     */
    public const CATEGORY_OVERRIDES_KEY = 'ai_summarize_category_overrides';

    /**
     * Plugin version option key.
     */
    public const VERSION_KEY = 'ai_summarize_version';

    /**
     * Available AI services with their configuration.
     *
     * Each service includes:
     * - name: Display name
     * - url_template: URL pattern with %s placeholder for encoded prompt (empty string for modal services)
     * - modal_url: URL to open after copying prompt (for modal services only)
     * - color: Brand color for styling
     * - icon: SVG icon markup
     */
    public const AI_SERVICES = [
        'chatgpt'    => [
            'name'         => 'ChatGPT',
            'url_template' => 'https://chat.openai.com/?q=%s',
            'color'        => '#10A37F',
            'icon'         => '<svg class="ai-summarize-button__icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>',
        ],
        'perplexity' => [
            'name'         => 'Perplexity',
            'url_template' => 'https://perplexity.ai/?q=%s',
            'color'        => '#20B8CD',
            'icon'         => '<svg class="ai-summarize-button__icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm-1 4v4H7l5 6 5-6h-4V6h-2z"/></svg>',
        ],
        'claude'     => [
            'name'         => 'Claude',
            'url_template' => '', // Empty = modal service.
            'modal_url'    => 'https://claude.ai/new',
            'color'        => '#C15F3C',
            'icon'         => '<svg class="ai-summarize-button__icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M14.5 2.5h-5L3 9v6l6.5 6.5h5L21 15V9l-6.5-6.5zM12 18a6 6 0 1 1 0-12 6 6 0 0 1 0 12zm0-2a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>',
        ],
        'copilot'    => [
            'name'         => 'Copilot',
            'url_template' => 'https://copilot.microsoft.com/?q=%s',
            'color'        => '#0078D4',
            'icon'         => '<svg class="ai-summarize-button__icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M23.822 12.696c-.001-1.599-.794-3.146-2.03-4.382-1.236-1.236-2.783-2.029-4.382-2.03h-.002c-1.188 0-2.335.346-3.326.991l-.002-3.775c0-.276-.224-.5-.5-.5s-.5.224-.5.5v3.775c-.991-.645-2.138-.991-3.326-.991h-.002c-1.599.001-3.146.794-4.382 2.03C3.972 9.55 3.179 11.097 3.178 12.696c0 1.188.346 2.335.991 3.326l-3.775.002c-.276 0-.5.224-.5.5s.224.5.5.5l3.775-.002c.645.991.991 2.138.991 3.326 0 1.599.794 3.146 2.03 4.382 1.236 1.236 2.783 2.029 4.382 2.03h.002c1.188 0 2.335-.346 3.326-.991l.002 3.775c0 .276.224.5.5.5s.5-.224.5-.5l-.002-3.775c.991.645 2.138.991 3.326.991h.002c1.599-.001 3.146-.794 4.382-2.03 1.236-1.236 2.029-2.783 2.03-4.382 0-1.188-.346-2.335-.991-3.326l3.775-.002c.276 0 .5-.224.5-.5s-.224-.5-.5-.5l-3.775.002c-.645-.991-.991-2.138-.991-3.326zM12 4.5c1.933 0 3.5 1.567 3.5 3.5s-1.567 3.5-3.5 3.5-3.5-1.567-3.5-3.5 1.567-3.5 3.5-3.5zm-3.5 15.5c0-1.933 1.567-3.5 3.5-3.5s3.5 1.567 3.5 3.5-1.567 3.5-3.5 3.5-3.5-1.567-3.5-3.5z"/></svg>',
        ],
        'you'        => [
            'name'         => 'You.com',
            'url_template' => 'https://you.com/search?q=%s&chatMode=default&tbm=youchat',
            'color'        => '#1E3A8A',
            'icon'         => '<svg class="ai-summarize-button__icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>',
        ],
    ];

    /**
     * Available placeholders for prompt templates.
     */
    public const PLACEHOLDERS = [
        '[[URL]]'      => 'Current post permalink',
        '[[WEBSITE]]'  => 'Site name',
        '[[TAGLINE]]'  => 'Site tagline',
        '[[TITLE]]'    => 'Post title',
        '[[EXCERPT]]'  => 'Post excerpt',
        '[[CATEGORY]]' => 'Primary category name',
    ];

    /**
     * Default plugin settings.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            'version'          => '1.0.0',
            'global_prompt'    => 'Please summarize this blog post from [[WEBSITE]]: [[TITLE]]. ' .
                                  'You can read the full post at [[URL]].',
            'enabled_services' => [
                'chatgpt'    => true,
                'perplexity' => true,
                'claude'     => true,
                'copilot'    => true,
                'you'        => true,
            ],
            'display_options'  => [
                'button_size' => 'medium',
                'show_labels' => true,
                'layout'      => 'horizontal',
            ],
        ];
    }

    /**
     * Get all plugin settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        $settings = get_option(self::OPTION_KEY, []);
        return wp_parse_args($settings, self::getDefaults());
    }

    /**
     * Update plugin settings.
     *
     * @param  array $settings The settings to save.
     * @return boolean True on successful update, false on failure.
     */
    public function updateSettings(array $settings): bool
    {
        $sanitized = $this->sanitizeSettings($settings);
        return update_option(self::OPTION_KEY, $sanitized);
    }

    /**
     * Get a specific setting value.
     *
     * @param  string $key     The setting key.
     * @param  mixed  $default Default value if setting doesn't exist.
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update a specific setting.
     *
     * @param  string $key   The setting key.
     * @param  mixed  $value The setting value.
     * @return boolean
     */
    public function updateSetting(string $key, $value): bool
    {
        $settings       = $this->getSettings();
        $settings[$key] = $value;
        return $this->updateSettings($settings);
    }

    /**
     * Get category-specific prompt overrides.
     *
     * @return array
     */
    public function getCategoryOverrides(): array
    {
        return get_option(self::CATEGORY_OVERRIDES_KEY, []);
    }

    /**
     * Update category-specific prompt overrides.
     *
     * @param  array $overrides The category overrides.
     * @return boolean
     */
    public function updateCategoryOverrides(array $overrides): bool
    {
        $sanitized = $this->sanitizeCategoryOverrides($overrides);
        return update_option(self::CATEGORY_OVERRIDES_KEY, $sanitized);
    }

    /**
     * Get prompt template for a specific category.
     *
     * @param  \WP_Term|null $category The category term.
     * @return string
     */
    public function getPromptForCategory(?\WP_Term $category = null): string
    {
        if ($category === null) {
            return $this->getSetting('global_prompt', self::getDefaults()['global_prompt']);
        }

        $overrides   = $this->getCategoryOverrides();
        $categoryKey = 'category_' . $category->term_id;

        if (isset($overrides[$categoryKey]['prompt_template'])) {
            return $overrides[$categoryKey]['prompt_template'];
        }

        return $this->getSetting('global_prompt', self::getDefaults()['global_prompt']);
    }

    /**
     * Get enabled services for a specific category.
     *
     * @param  \WP_Term|null $category The category term.
     * @return array
     */
    public function getEnabledServicesForCategory(?\WP_Term $category = null): array
    {
        if ($category === null) {
            return $this->getSetting('enabled_services', self::getDefaults()['enabled_services']);
        }

        $overrides   = $this->getCategoryOverrides();
        $categoryKey = 'category_' . $category->term_id;

        if (! empty($overrides[$categoryKey]['enabled_services'])) {
            return $overrides[$categoryKey]['enabled_services'];
        }

        return $this->getSetting('enabled_services', self::getDefaults()['enabled_services']);
    }

    /**
     * Process placeholder replacements in a prompt template.
     *
     * @param  string $template The prompt template.
     * @param  array  $context  Additional context data.
     * @return string
     */
    public function processPlaceholders(string $template, array $context = []): string
    {
        $replacements = [
            '[[URL]]'      => $context['url'] ?? get_permalink(),
            '[[WEBSITE]]'  => $context['website'] ?? get_bloginfo('name'),
            '[[TAGLINE]]'  => $context['tagline'] ?? get_bloginfo('description'),
            '[[TITLE]]'    => $context['title'] ?? get_the_title(),
            '[[EXCERPT]]'  => $context['excerpt'] ?? get_the_excerpt(),
            '[[CATEGORY]]' => $context['category'] ?? $this->getPrimaryCategoryName(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get the primary category name for the current post.
     *
     * @return string
     */
    private function getPrimaryCategoryName(): string
    {
        $categories = get_the_category();
        if (!empty($categories)) {
            return $categories[0]->name;
        }
        return '';
    }

    /**
     * Sanitize plugin settings.
     *
     * @param  array $settings The settings to sanitize.
     * @return array
     */
    public function sanitizeSettings(array $settings): array
    {
        $sanitized = [];
        $defaults  = self::getDefaults();

        // Sanitize version.
        $sanitized['version'] = sanitize_text_field($settings['version'] ?? $defaults['version']);

        // Sanitize global prompt.
        $sanitized['global_prompt'] = wp_kses_post($settings['global_prompt'] ?? $defaults['global_prompt']);

        // Sanitize enabled services.
        $sanitized['enabled_services'] = [];
        if (isset($settings['enabled_services']) && is_array($settings['enabled_services'])) {
            foreach (self::AI_SERVICES as $service => $label) {
                $sanitized['enabled_services'][$service] = !empty($settings['enabled_services'][$service]);
            }
        } else {
            $sanitized['enabled_services'] = $defaults['enabled_services'];
        }

        // Sanitize display options.
        $sanitized['display_options'] = [
            'button_size' => in_array($settings['display_options']['button_size'] ?? '', ['small', 'medium', 'large'], true)
                ? $settings['display_options']['button_size']
                : $defaults['display_options']['button_size'],
            'show_labels' => !empty($settings['display_options']['show_labels'] ?? $defaults['display_options']['show_labels']),
            'layout'      => in_array($settings['display_options']['layout'] ?? '', ['horizontal', 'vertical', 'grid'], true)
                ? $settings['display_options']['layout']
                : $defaults['display_options']['layout'],
        ];

        return $sanitized;
    }

    /**
     * Sanitize category overrides.
     *
     * @param  array $overrides The overrides to sanitize.
     * @return array
     */
    private function sanitizeCategoryOverrides(array $overrides): array
    {
        $sanitized = [];

        foreach ($overrides as $categoryKey => $override) {
            if (!is_array($override) || !str_starts_with($categoryKey, 'category_')) {
                continue;
            }

            $sanitized[$categoryKey] = [];

            // Sanitize prompt template.
            if (isset($override['prompt_template'])) {
                $sanitized[$categoryKey]['prompt_template'] = wp_kses_post($override['prompt_template']);
            }

            // Sanitize enabled services.
            if (isset($override['enabled_services']) && is_array($override['enabled_services'])) {
                $sanitized[$categoryKey]['enabled_services'] = [];
                foreach (self::AI_SERVICES as $service => $label) {
                    // Handle both associative arrays (from Admin Page) and simple arrays (from tests/forms).
                    $isEnabled = false;
                    if (isset($override['enabled_services'][$service])) {
                        // Associative array format: ['chatgpt' => true, 'claude' => true].
                        $isEnabled = !empty($override['enabled_services'][$service]);
                    } elseif (in_array($service, $override['enabled_services'], true)) {
                        // Simple array format: ['chatgpt', 'claude'].
                        $isEnabled = true;
                    }

                    if ($isEnabled) {
                        $sanitized[$categoryKey]['enabled_services'][$service] = true;
                    }
                }
            }
        }

        return $sanitized;
    }

    /**
     * Reset settings to defaults.
     *
     * @return boolean
     */
    public function resetToDefaults(): bool
    {
        $defaults = self::getDefaults();
        return $this->updateSettings($defaults);
    }

    /**
     * Delete all plugin settings.
     *
     * @return boolean
     */
    public function deleteAllSettings(): bool
    {
        $deleted1 = delete_option(self::OPTION_KEY);
        $deleted2 = delete_option(self::CATEGORY_OVERRIDES_KEY);
        $deleted3 = delete_option(self::VERSION_KEY);

        return $deleted1 && $deleted2 && $deleted3;
    }
}
