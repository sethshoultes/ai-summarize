<?php

/**
 * Help and documentation tab template.
 *
 * @var \Caseproof\AiSummarize\Services\Settings $settings The settings service.
 */

declare(strict_types=1);

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

$placeholders = \Caseproof\AiSummarize\Services\Settings::PLACEHOLDERS;
$aiServices   = \Caseproof\AiSummarize\Services\Settings::AI_SERVICES;

?>
<div class="ai-summarize-help">
    <div class="help-section">
        <h3><?php esc_html_e('Getting Started', 'ai-summarize'); ?></h3>
        <p>
            <?php
            esc_html_e(
                'AI Summarize adds branded buttons to your blog posts that allow visitors to quickly summarize content using popular AI services. Here\'s how to get started:',
                'ai-summarize'
            );
            ?>
        </p>
        <ol class="help-steps">
            <li>
                <strong><?php esc_html_e('Configure Global Settings:', 'ai-summarize'); ?></strong>
                <?php esc_html_e('Set up your default prompt template and display options.', 'ai-summarize'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Enable AI Services:', 'ai-summarize'); ?></strong>
                <?php esc_html_e('Choose which AI services you want to make available to visitors.', 'ai-summarize'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Add Blocks to Posts:', 'ai-summarize'); ?></strong>
                <?php esc_html_e('Use the "AI Summarize Buttons" block in your posts and pages.', 'ai-summarize'); ?>
            </li>
            <li>
                <strong><?php esc_html_e('Optional Category Overrides:', 'ai-summarize'); ?></strong>
                <?php esc_html_e('Customize prompts and services for specific categories.', 'ai-summarize'); ?>
            </li>
        </ol>
    </div>

    <div class="help-section">
        <h3><?php esc_html_e('Placeholder Reference', 'ai-summarize'); ?></h3>
        <p>
            <?php
            esc_html_e(
                'Use these placeholders in your prompt templates to include dynamic content:',
                'ai-summarize'
            );
            ?>
        </p>
        <div class="placeholder-reference">
            <?php foreach ($placeholders as $placeholder => $description) : ?>
                <div class="placeholder-item">
                    <code class="placeholder-code"><?php echo esc_html($placeholder); ?></code>
                    <span class="placeholder-description"><?php echo esc_html($description); ?></span>
                    <div class="placeholder-example">
                        <strong><?php esc_html_e('Example:', 'ai-summarize'); ?></strong>
                        <?php
                        switch ($placeholder) {
                            case '[[URL]]':
                                echo esc_html('https://yoursite.com/sample-post/');
                                break;
                            case '[[WEBSITE]]':
                                echo esc_html(get_bloginfo('name') ?: 'Your Site Name');
                                break;
                            case '[[TAGLINE]]':
                                echo esc_html(get_bloginfo('description') ?: 'Your Site Tagline');
                                break;
                            case '[[TITLE]]':
                                esc_html_e('Sample Post Title', 'ai-summarize');
                                break;
                            case '[[EXCERPT]]':
                                esc_html_e('This is a sample excerpt from the post...', 'ai-summarize');
                                break;
                            case '[[CATEGORY]]':
                                esc_html_e('Technology', 'ai-summarize');
                                break;
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="help-section">
        <h3><?php esc_html_e('AI Services Overview', 'ai-summarize'); ?></h3>
        <p>
            <?php
            esc_html_e(
                'This plugin integrates with the following AI services. Visitors need their own accounts with these services to use the summarization feature:',
                'ai-summarize'
            );
            ?>
        </p>
        <div class="services-overview">
            <?php foreach ($aiServices as $serviceKey => $serviceConfig) : ?>
                <div class="service-info">
                    <h4><?php echo esc_html($serviceConfig['name']); ?></h4>
                    <div class="service-details">
                        <?php
                        switch ($serviceKey) {
                            case 'chatgpt':
                                ?>
                                <p><strong><?php esc_html_e('Provider:', 'ai-summarize'); ?></strong> OpenAI</p>
                                <p><strong><?php esc_html_e('Best For:', 'ai-summarize'); ?></strong> <?php esc_html_e('General conversation and analysis', 'ai-summarize'); ?></p>
                                <p><strong><?php esc_html_e('Account Required:', 'ai-summarize'); ?></strong> <?php esc_html_e('Free or paid OpenAI account', 'ai-summarize'); ?></p>
                                <?php
                                break;
                            case 'perplexity':
                                ?>
                                <p><strong><?php esc_html_e('Provider:', 'ai-summarize'); ?></strong> Perplexity AI</p>
                                <p><strong><?php esc_html_e('Best For:', 'ai-summarize'); ?></strong> <?php esc_html_e('Research and fact-checking', 'ai-summarize'); ?></p>
                                <p><strong><?php esc_html_e('Account Required:', 'ai-summarize'); ?></strong> <?php esc_html_e('Free or Pro Perplexity account', 'ai-summarize'); ?></p>
                                <?php
                                break;
                            case 'claude':
                                ?>
                                <p><strong><?php esc_html_e('Provider:', 'ai-summarize'); ?></strong> Anthropic</p>
                                <p><strong><?php esc_html_e('Best For:', 'ai-summarize'); ?></strong> <?php esc_html_e('Thoughtful analysis and writing', 'ai-summarize'); ?></p>
                                <p><strong><?php esc_html_e('Account Required:', 'ai-summarize'); ?></strong> <?php esc_html_e('Free or paid Anthropic account', 'ai-summarize'); ?></p>
                                <?php
                                break;
                            case 'copilot':
                                ?>
                                <p><strong><?php esc_html_e('Provider:', 'ai-summarize'); ?></strong> Microsoft</p>
                                <p><strong><?php esc_html_e('Best For:', 'ai-summarize'); ?></strong> <?php esc_html_e('Productivity and business tasks', 'ai-summarize'); ?></p>
                                <p><strong><?php esc_html_e('Account Required:', 'ai-summarize'); ?></strong> <?php esc_html_e('Microsoft account with Copilot access', 'ai-summarize'); ?></p>
                                <?php
                                break;
                            case 'you':
                                ?>
                                <p><strong><?php esc_html_e('Provider:', 'ai-summarize'); ?></strong> You.com</p>
                                <p><strong><?php esc_html_e('Best For:', 'ai-summarize'); ?></strong> <?php esc_html_e('Private AI search and chat', 'ai-summarize'); ?></p>
                                <p><strong><?php esc_html_e('Account Required:', 'ai-summarize'); ?></strong> <?php esc_html_e('Free or paid You.com account', 'ai-summarize'); ?></p>
                                <?php
                                break;
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="help-section">
        <h3><?php esc_html_e('Sample Prompt Templates', 'ai-summarize'); ?></h3>
        <p><?php esc_html_e('Here are some example prompt templates you can use or adapt:', 'ai-summarize'); ?></p>

        <div class="sample-prompts">
            <div class="sample-prompt">
                <h4><?php esc_html_e('Basic Summary', 'ai-summarize'); ?></h4>
                <code class="prompt-code">Please summarize this blog post from [[WEBSITE]]: [[TITLE]]. You can read the full post at [[URL]].</code>
            </div>

            <div class="sample-prompt">
                <h4><?php esc_html_e('Detailed Analysis', 'ai-summarize'); ?></h4>
                <code class="prompt-code">Please provide a detailed summary and analysis of this article from [[WEBSITE]]: "[[TITLE]]". The post is in the [[CATEGORY]] category. Read the full content at [[URL]] and provide key takeaways.</code>
            </div>

            <div class="sample-prompt">
                <h4><?php esc_html_e('Key Points Focus', 'ai-summarize'); ?></h4>
                <code class="prompt-code">Extract the main points and key insights from this [[CATEGORY]] post: "[[TITLE]]" from [[WEBSITE]]. Full article: [[URL]]</code>
            </div>

            <div class="sample-prompt">
                <h4><?php esc_html_e('Action-Oriented', 'ai-summarize'); ?></h4>
                <code class="prompt-code">Summarize this post and suggest actionable next steps: "[[TITLE]]" from [[WEBSITE]] ([[URL]]). Focus on practical takeaways.</code>
            </div>
        </div>
    </div>

    <div class="help-section">
        <h3><?php esc_html_e('Troubleshooting', 'ai-summarize'); ?></h3>
        <div class="troubleshooting">
            <div class="faq-item">
                <h4><?php esc_html_e('Buttons not showing on posts?', 'ai-summarize'); ?></h4>
                <p><?php esc_html_e('Make sure you\'ve added the "AI Summarize Buttons" block to your posts and have enabled at least one AI service.', 'ai-summarize'); ?></p>
            </div>

            <div class="faq-item">
                <h4><?php esc_html_e('Visitors getting errors when clicking buttons?', 'ai-summarize'); ?></h4>
                <p><?php esc_html_e('Visitors need their own accounts with the AI services. The buttons redirect to external services with pre-filled prompts.', 'ai-summarize'); ?></p>
            </div>

            <div class="faq-item">
                <h4><?php esc_html_e('Placeholders not being replaced?', 'ai-summarize'); ?></h4>
                <p><?php esc_html_e('Make sure you\'re using the exact placeholder format with double brackets, like [[URL]] not [URL].', 'ai-summarize'); ?></p>
            </div>

            <div class="faq-item">
                <h4><?php esc_html_e('Category overrides not working?', 'ai-summarize'); ?></h4>
                <p><?php esc_html_e('Category overrides only apply to posts that have that specific category assigned. Check your post categorization.', 'ai-summarize'); ?></p>
            </div>
        </div>
    </div>

    <div class="help-section">
        <h3><?php esc_html_e('Privacy & Security', 'ai-summarize'); ?></h3>
        <div class="privacy-info">
            <h4><?php esc_html_e('Data Collection', 'ai-summarize'); ?></h4>
            <p><?php esc_html_e('This plugin does not collect any user data. When visitors click AI service buttons, they interact directly with external AI services under their own accounts.', 'ai-summarize'); ?></p>

            <h4><?php esc_html_e('External Services', 'ai-summarize'); ?></h4>
            <p><?php esc_html_e('The AI services are provided by third parties (OpenAI, Anthropic, Microsoft, etc.). Users are subject to those services\' privacy policies and terms of use.', 'ai-summarize'); ?></p>

            <h4><?php esc_html_e('Content Sharing', 'ai-summarize'); ?></h4>
            <p><?php esc_html_e('When visitors use the AI buttons, your post URL and configured prompt text are shared with the selected AI service. No other site data is transmitted.', 'ai-summarize'); ?></p>
        </div>
    </div>
</div>

<style>
.ai-summarize-help {
    max-width: 800px;
}

.help-section {
    margin-bottom: 40px;
    background: #fff;
    padding: 24px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.help-section h3 {
    margin-top: 0;
    color: #1e1e1e;
    border-bottom: 1px solid #ddd;
    padding-bottom: 8px;
}

.help-steps {
    padding-left: 20px;
}

.help-steps li {
    margin-bottom: 12px;
    line-height: 1.6;
}

.placeholder-reference .placeholder-item {
    margin-bottom: 16px;
    padding: 12px;
    background: #f7f7f7;
    border-radius: 4px;
}

.placeholder-code {
    background: #23282d;
    color: #fff;
    padding: 4px 8px;
    border-radius: 3px;
    font-family: monospace;
    margin-right: 12px;
}

.placeholder-description {
    font-weight: 500;
}

.placeholder-example {
    margin-top: 8px;
    font-size: 13px;
    color: #666;
}

.services-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.service-info {
    padding: 16px;
    background: #f9f9f9;
    border-radius: 4px;
}

.service-info h4 {
    margin-top: 0;
    margin-bottom: 8px;
    color: #0073aa;
}

.service-details p {
    margin: 4px 0;
    font-size: 14px;
}

.sample-prompts .sample-prompt {
    margin-bottom: 20px;
    padding: 16px;
    background: #f9f9f9;
    border-radius: 4px;
}

.sample-prompt h4 {
    margin-top: 0;
    margin-bottom: 8px;
}

.prompt-code {
    display: block;
    background: #23282d;
    color: #fff;
    padding: 12px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
}

.faq-item {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
}

.faq-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.faq-item h4 {
    margin-bottom: 8px;
    color: #0073aa;
}

.privacy-info h4 {
    margin-top: 16px;
    margin-bottom: 8px;
    color: #d63638;
}

.privacy-info h4:first-child {
    margin-top: 0;
}
</style>
