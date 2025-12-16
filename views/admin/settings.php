<?php

/**
 * AI Summarize admin settings page template.
 *
 * @var string                                    $pageTitle The page title.
 * @var \Caseproof\AiSummarize\Services\Settings $settings  The settings service.
 * @var string                                    $activeTab The currently active tab.
 */

declare(strict_types=1);

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap ai-summarize-admin">
    <h1><?php echo esc_html($pageTitle); ?></h1>

    <?php settings_errors('ai_summarize_settings'); ?>

    <div class="ai-summarize-tabs-wrapper">
        <nav class="nav-tab-wrapper">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-summarize&tab=general')); ?>"
               class="nav-tab <?php echo ($activeTab === 'general') ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Global Settings', 'ai-summarize'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-summarize&tab=categories')); ?>"
               class="nav-tab <?php echo ($activeTab === 'categories') ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Category Overrides', 'ai-summarize'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-summarize&tab=services')); ?>"
               class="nav-tab <?php echo ($activeTab === 'services') ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('AI Services', 'ai-summarize'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-summarize&tab=help')); ?>"
               class="nav-tab <?php echo ($activeTab === 'help') ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Help', 'ai-summarize'); ?>
            </a>
        </nav>

        <div class="ai-summarize-tab-content">
            <?php
            switch ($activeTab) {
                case 'categories':
                    include __DIR__ . '/tabs/categories.php';
                    break;
                case 'services':
                    include __DIR__ . '/tabs/services.php';
                    break;
                case 'help':
                    include __DIR__ . '/tabs/help.php';
                    break;
                case 'general':
                default:
                    include __DIR__ . '/tabs/general.php';
                    break;
            }
            ?>
        </div>
    </div>
</div>
