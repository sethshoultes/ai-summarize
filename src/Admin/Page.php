<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize\Admin;

use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\GroundLevel\Container\Concerns\HasStaticContainer;
use Caseproof\AiSummarize\GroundLevel\Container\Contracts\StaticContainerAwareness;

/**
 * This class registers and renders the AI Summarize admin settings page.
 *
 * The admin page provides settings management for AI service configuration,
 * prompt templates, and category-specific overrides.
 */
class Page implements StaticContainerAwareness
{
    use HasStaticContainer;

    /**
     * The capability required to view the page.
     */
    public const CAPABILITY = 'manage_options';

    /**
     * The page slug.
     */
    public const SLUG = 'ai-summarize';

    /**
     * Retrieves the page title.
     *
     * @return string
     */
    public static function pageTitle(): string
    {
        return esc_html__('AI Summarize Settings', 'ai-summarize');
    }

    /**
     * Registers the page.
     *
     * @return string The resulting page's hook suffix.
     */
    public static function register(): string
    {
        return add_menu_page(
            self::pageTitle(),
            esc_html__('AI Summarize', 'ai-summarize'),
            self::CAPABILITY,
            self::SLUG,
            [
                Page::class,
                'render',
            ],
            'dashicons-admin-comments',
            100
        );
    }

    /**
     * Initialize the admin page settings.
     */
    public static function init(): void
    {
        add_action('admin_init', [self::class, 'registerSettings']);
        add_action('admin_post_add_category_override', [self::class, 'handleAddCategoryOverride']);
        add_action('admin_post_update_category_override', [self::class, 'handleUpdateCategoryOverride']);
        add_action('admin_post_delete_category_override', [self::class, 'handleDeleteCategoryOverride']);
    }

    /**
     * Register WordPress settings.
     */
    public static function registerSettings(): void
    {
        register_setting(
            'ai_summarize_settings_group',
            Settings::OPTION_KEY,
            [
                'sanitize_callback' => [self::class, 'sanitizeSettings'],
                'default'           => Settings::getDefaults(),
            ]
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param  array $input Raw input data.
     * @return array Sanitized data.
     */
    public static function sanitizeSettings(array $input): array
    {
        $settings = self::getContainer()->get('settings');
        $current  = $settings->getSettings();

        // Merge with current settings to preserve any missing fields.
        $merged = array_merge($current, $input);

        // Use the existing Settings service from the container to handle sanitization.
        return $settings->sanitizeSettings($merged);
    }

    /**
     * Get the current active tab.
     *
     * @return string
     */
    public static function getCurrentTab(): string
    {
        return sanitize_key($_GET['tab'] ?? 'general');
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        // Handle any form submissions first.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handleFormSubmissions();
        }

        $settings  = self::getContainer()->get('settings');
        $pageTitle = self::pageTitle();
        $activeTab = self::getCurrentTab();

        include self::getContainer()->get('BASE_PATH') . 'views/admin/settings.php';
    }

    /**
     * Handle form submissions for category overrides.
     */
    private static function handleFormSubmissions(): void
    {
        if (!isset($_POST['_ai_summarize_nonce']) || !wp_verify_nonce($_POST['_ai_summarize_nonce'], 'ai_summarize_settings')) {
            wp_die(esc_html__('Security check failed.', 'ai-summarize'));
        }

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-summarize'));
        }

        $action = sanitize_key($_POST['action'] ?? '');

        switch ($action) {
            case 'add_category_override':
                self::handleAddCategoryOverride();
                break;
            case 'update_category_override':
                self::handleUpdateCategoryOverride();
                break;
            case 'delete_category_override':
                self::handleDeleteCategoryOverride();
                break;
        }
    }

    /**
     * Handle adding a new category override.
     */
    public static function handleAddCategoryOverride(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        if (!$categoryId || !get_category($categoryId)) {
            add_settings_error('ai_summarize_settings', 'invalid_category', __('Invalid category selected.', 'ai-summarize'));
            return;
        }

        $settings         = self::getContainer()->get('settings');
        $currentOverrides = $settings->getCategoryOverrides();
        $categoryKey      = 'category_' . $categoryId;

        if (isset($currentOverrides[$categoryKey])) {
            add_settings_error('ai_summarize_settings', 'category_exists', __('Override already exists for this category.', 'ai-summarize'));
            return;
        }

        $override = [];

        // Add prompt template if provided.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        if (!empty($_POST['prompt_template'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
            $override['prompt_template'] = wp_kses_post($_POST['prompt_template']);
        }

        // Add enabled services if provided.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        if (!empty($_POST['enabled_services']) && is_array($_POST['enabled_services'])) {
            $override['enabled_services'] = [];
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
            foreach ($_POST['enabled_services'] as $service) {
                $service = sanitize_key($service);
                if (isset(Settings::AI_SERVICES[$service])) {
                    $override['enabled_services'][$service] = true;
                }
            }
        }

        $currentOverrides[$categoryKey] = $override;
        $settings->updateCategoryOverrides($currentOverrides);

        $category = get_category($categoryId);
        add_settings_error(
            'ai_summarize_settings',
            'override_added',
            // Translators: %s: Category name.
            sprintf(__('Category override added for "%s".', 'ai-summarize'), $category->name),
            'success'
        );
    }

    /**
     * Handle updating an existing category override.
     */
    public static function handleUpdateCategoryOverride(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        if (!$categoryId || !get_category($categoryId)) {
            add_settings_error('ai_summarize_settings', 'invalid_category', __('Invalid category selected.', 'ai-summarize'));
            return;
        }

        $settings         = self::getContainer()->get('settings');
        $currentOverrides = $settings->getCategoryOverrides();
        $categoryKey      = 'category_' . $categoryId;

        $override = [];

        // Update prompt template.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        if (!empty($_POST['prompt_template'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
            $override['prompt_template'] = wp_kses_post($_POST['prompt_template']);
        }

        // Update enabled services.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        if (!empty($_POST['enabled_services']) && is_array($_POST['enabled_services'])) {
            $override['enabled_services'] = [];
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
            foreach ($_POST['enabled_services'] as $service) {
                $service = sanitize_key($service);
                if (isset(Settings::AI_SERVICES[$service])) {
                    $override['enabled_services'][$service] = true;
                }
            }
        }

        $currentOverrides[$categoryKey] = $override;
        $settings->updateCategoryOverrides($currentOverrides);

        $category = get_category($categoryId);
        add_settings_error(
            'ai_summarize_settings',
            'override_updated',
            // Translators: %s: Category name.
            sprintf(__('Category override updated for "%s".', 'ai-summarize'), $category->name),
            'success'
        );
    }

    /**
     * Handle deleting a category override.
     */
    public static function handleDeleteCategoryOverride(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in handleFormSubmissions().
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        if (!$categoryId) {
            add_settings_error('ai_summarize_settings', 'invalid_category', __('Invalid category selected.', 'ai-summarize'));
            return;
        }

        $settings         = self::getContainer()->get('settings');
        $currentOverrides = $settings->getCategoryOverrides();
        $categoryKey      = 'category_' . $categoryId;

        if (isset($currentOverrides[$categoryKey])) {
            unset($currentOverrides[$categoryKey]);
            $settings->updateCategoryOverrides($currentOverrides);

            $category = get_category($categoryId);
            $message  = $category
                // Translators: %s: Category name.
                ? sprintf(__('Category override deleted for "%s".', 'ai-summarize'), $category->name)
                : __('Category override deleted.', 'ai-summarize');

            add_settings_error('ai_summarize_settings', 'override_deleted', $message, 'success');
        }
    }
}
