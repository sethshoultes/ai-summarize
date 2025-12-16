<?php

/**
 * General settings tab template.
 *
 * @var \Caseproof\AiSummarize\Services\Settings $settings    The settings service.
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currentSettings = $settings->getSettings();
$placeholders    = \Caseproof\AiSummarize\Services\Settings::PLACEHOLDERS;

?>
<form method="post" action="options.php" class="ai-summarize-form">
	<?php settings_fields( 'ai_summarize_settings_group' ); ?>
	<?php wp_nonce_field( 'ai_summarize_settings', '_ai_summarize_nonce' ); ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="global_prompt"><?php esc_html_e( 'Global Prompt Template', 'ai-summarize' ); ?></label>
			</th>
			<td>
				<textarea
					id="global_prompt"
					name="ai_summarize_settings[global_prompt]"
					class="large-text"
					rows="6"
					placeholder="<?php esc_attr_e( 'Enter your default prompt template...', 'ai-summarize' ); ?>"
				><?php echo esc_textarea( $currentSettings['global_prompt'] ); ?></textarea>
				<p class="description">
					<?php
					esc_html_e(
						'This template will be used for all posts unless overridden by category-specific settings.',
						'ai-summarize'
					);
					?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Available Placeholders', 'ai-summarize' ); ?>
			</th>
			<td>
				<div class="ai-summarize-placeholders">
					<?php foreach ( $placeholders as $placeholder => $description ) : ?>
						<div class="placeholder-item">
							<code class="placeholder-code"><?php echo esc_html( $placeholder ); ?></code>
							<span class="placeholder-description"><?php echo esc_html( $description ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="description">
					<?php esc_html_e( 'Click any placeholder to insert it into your prompt template.', 'ai-summarize' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Display Options', 'ai-summarize' ); ?>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php esc_html_e( 'Display Options', 'ai-summarize' ); ?></span>
					</legend>

					<p>
						<label for="button_size"><?php esc_html_e( 'Button Size:', 'ai-summarize' ); ?></label>
						<select id="button_size" name="ai_summarize_settings[display_options][button_size]">
							<option value="small" <?php selected( $currentSettings['display_options']['button_size'], 'small' ); ?>>
								<?php esc_html_e( 'Small', 'ai-summarize' ); ?>
							</option>
							<option value="medium" <?php selected( $currentSettings['display_options']['button_size'], 'medium' ); ?>>
								<?php esc_html_e( 'Medium', 'ai-summarize' ); ?>
							</option>
							<option value="large" <?php selected( $currentSettings['display_options']['button_size'], 'large' ); ?>>
								<?php esc_html_e( 'Large', 'ai-summarize' ); ?>
							</option>
						</select>
					</p>

					<p>
						<label for="layout"><?php esc_html_e( 'Button Layout:', 'ai-summarize' ); ?></label>
						<select id="layout" name="ai_summarize_settings[display_options][layout]">
							<option value="horizontal" <?php selected( $currentSettings['display_options']['layout'], 'horizontal' ); ?>>
								<?php esc_html_e( 'Horizontal', 'ai-summarize' ); ?>
							</option>
							<option value="vertical" <?php selected( $currentSettings['display_options']['layout'], 'vertical' ); ?>>
								<?php esc_html_e( 'Vertical', 'ai-summarize' ); ?>
							</option>
							<option value="grid" <?php selected( $currentSettings['display_options']['layout'], 'grid' ); ?>>
								<?php esc_html_e( 'Grid', 'ai-summarize' ); ?>
							</option>
						</select>
					</p>

					<p>
						<label for="show_labels">
							<input
								type="checkbox"
								id="show_labels"
								name="ai_summarize_settings[display_options][show_labels]"
								value="1"
								<?php checked( $currentSettings['display_options']['show_labels'], true ); ?>
							/>
							<?php esc_html_e( 'Show service labels on buttons', 'ai-summarize' ); ?>
						</label>
					</p>
				</fieldset>
			</td>
		</tr>
	</table>

	<?php submit_button( __( 'Save Global Settings', 'ai-summarize' ) ); ?>
</form>

<script>
(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle placeholder insertion
		$('.placeholder-code').on('click', function() {
			var placeholder = $(this).text();
			var textarea = $('#global_prompt')[0];
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			var text = textarea.value;

			textarea.value = text.substring(0, start) + placeholder + text.substring(end);
			textarea.setSelectionRange(start + placeholder.length, start + placeholder.length);
			textarea.focus();
		});

		// Style placeholder items for better alignment
		$('.ai-summarize-placeholders').css({
			'display': 'grid',
			'grid-template-columns': 'auto 1fr',
			'gap': '8px 16px',
			'align-items': 'center',
			'margin-bottom': '16px'
		});

		$('.placeholder-item').css({
			'display': 'contents'
		});

		$('.placeholder-code').css({
			'cursor': 'pointer',
			'background': '#f0f0f1',
			'padding': '4px 8px',
			'border-radius': '3px',
			'font-family': 'Consolas, Monaco, monospace',
			'font-size': '13px',
			'white-space': 'nowrap',
			'justify-self': 'start'
		}).hover(
			function() { $(this).css('background', '#dcdcde'); },
			function() { $(this).css('background', '#f0f0f1'); }
		);

		$('.placeholder-description').css({
			'color': '#646970',
			'font-size': '13px',
			'line-height': '1.4'
		});
	});
})(jQuery);
</script>