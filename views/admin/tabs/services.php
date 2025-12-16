<?php

/**
 * AI Services settings tab template.
 *
 * @var \Caseproof\AiSummarize\Services\Settings $settings    The settings service.
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currentSettings = $settings->getSettings();
$aiServices      = \Caseproof\AiSummarize\Services\Settings::AI_SERVICES;

?>
<form method="post" action="options.php" class="ai-summarize-form">
	<?php settings_fields( 'ai_summarize_settings_group' ); ?>
	<?php wp_nonce_field( 'ai_summarize_settings', '_ai_summarize_nonce' ); ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Enabled AI Services', 'ai-summarize' ); ?>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php esc_html_e( 'Select which AI services to enable', 'ai-summarize' ); ?></span>
					</legend>

					<div class="ai-services-grid">
						<?php foreach ( $aiServices as $serviceKey => $serviceConfig ) : ?>
							<?php $isEnabled = ! empty( $currentSettings['enabled_services'][ $serviceKey ] ); ?>
							<div class="ai-service-item <?php echo $isEnabled ? 'enabled' : 'disabled'; ?>">
								<label for="service_<?php echo esc_attr( $serviceKey ); ?>" class="service-label">
									<input
										type="checkbox"
										id="service_<?php echo esc_attr( $serviceKey ); ?>"
										name="ai_summarize_settings[enabled_services][<?php echo esc_attr( $serviceKey ); ?>]"
										value="1"
										<?php checked( $isEnabled, true ); ?>
										class="service-checkbox"
									/>
									<div class="service-info">
										<strong class="service-name"><?php echo esc_html( $serviceConfig['name'] ); ?></strong>
										<div class="service-description">
											<?php
											switch ( $serviceKey ) {
												case 'chatgpt':
													esc_html_e( 'OpenAI ChatGPT - Conversational AI assistant', 'ai-summarize' );
													break;
												case 'perplexity':
													esc_html_e( 'Perplexity - AI-powered search and research', 'ai-summarize' );
													break;
												case 'claude':
													esc_html_e( 'Anthropic Claude - Constitutional AI assistant', 'ai-summarize' );
													break;
												case 'copilot':
													esc_html_e( 'Microsoft Copilot - AI productivity assistant', 'ai-summarize' );
													break;
												case 'you':
													esc_html_e( 'You.com - Private AI search and chat', 'ai-summarize' );
													break;
												default:
													esc_html_e( 'AI service for content summarization', 'ai-summarize' );
											}
											?>
										</div>
									</div>
								</label>
							</div>
						<?php endforeach; ?>
					</div>

					<p class="description">
						<?php
						esc_html_e(
							'Select which AI services you want to make available to your site visitors. Disabled services will not show buttons on your posts.',
							'ai-summarize'
						);
						?>
					</p>
				</fieldset>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Service Information', 'ai-summarize' ); ?>
			</th>
			<td>
				<div class="ai-service-info-panel">
					<h4><?php esc_html_e( 'How It Works', 'ai-summarize' ); ?></h4>
					<p>
						<?php
						esc_html_e(
							'When visitors click an AI service button, they will be taken to that service with your custom prompt and the current page information pre-filled. This leverages their existing AI subscriptions and conversation history.',
							'ai-summarize'
						);
						?>
					</p>

					<h4><?php esc_html_e( 'Requirements', 'ai-summarize' ); ?></h4>
					<ul class="ai-service-requirements">
						<li><?php esc_html_e( 'Visitors need accounts with their chosen AI services', 'ai-summarize' ); ?></li>
						<li><?php esc_html_e( 'Services must be accessible in the visitor\'s region', 'ai-summarize' ); ?></li>
						<li><?php esc_html_e( 'Some services may have usage limits or require subscriptions', 'ai-summarize' ); ?></li>
					</ul>

					<h4><?php esc_html_e( 'Privacy & Data', 'ai-summarize' ); ?></h4>
					<p>
						<?php
						esc_html_e(
							'No user data is collected by this plugin. When visitors use AI services, they are interacting directly with those services under their own accounts and privacy policies.',
							'ai-summarize'
						);
						?>
					</p>
				</div>
			</td>
		</tr>
	</table>

	<?php submit_button( __( 'Save AI Service Settings', 'ai-summarize' ) ); ?>
</form>

<style>
.ai-services-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 16px;
	margin-bottom: 16px;
}

.ai-service-item {
	border: 1px solid #ddd;
	border-radius: 4px;
	padding: 16px;
	transition: all 0.2s ease;
}

.ai-service-item.enabled {
	border-color: #0073aa;
	background-color: #f7f7f7;
}

.ai-service-item:hover {
	border-color: #0073aa;
}

.service-label {
	display: block;
	cursor: pointer;
	margin: 0;
}

.service-checkbox {
	margin-right: 8px;
}

.service-name {
	display: block;
	font-size: 14px;
	margin-bottom: 4px;
}

.service-description {
	font-size: 12px;
	color: #666;
	line-height: 1.4;
}

.ai-service-info-panel {
	background: #f9f9f9;
	border: 1px solid #e5e5e5;
	border-radius: 4px;
	padding: 16px;
}

.ai-service-info-panel h4 {
	margin-top: 0;
	margin-bottom: 8px;
	color: #23282d;
}

.ai-service-requirements {
	margin: 8px 0;
	padding-left: 20px;
}

.ai-service-requirements li {
	margin-bottom: 4px;
}
</style>
