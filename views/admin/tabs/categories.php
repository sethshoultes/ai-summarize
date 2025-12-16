<?php

/**
 * Category overrides settings tab template.
 *
 * @var \Caseproof\AiSummarize\Services\Settings $settings    The settings service.
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currentOverrides = $settings->getCategoryOverrides();
$categories       = get_categories( [ 'hide_empty' => false ] );
$aiServices       = \Caseproof\AiSummarize\Services\Settings::AI_SERVICES;
$placeholders     = \Caseproof\AiSummarize\Services\Settings::PLACEHOLDERS;

?>
<div class="ai-summarize-categories">
	<p class="description">
		<?php
		esc_html_e(
			'Configure category-specific prompt templates and AI service selections. These settings will override the global defaults for posts in the selected categories.',
			'ai-summarize'
		);
		?>
	</p>

	<div class="category-overrides-container">
		<div class="add-category-override">
			<h3><?php esc_html_e( 'Add Category Override', 'ai-summarize' ); ?></h3>
			<form method="post" action="" class="add-override-form">
				<?php wp_nonce_field( 'ai_summarize_settings', '_ai_summarize_nonce' ); ?>
				<input type="hidden" name="action" value="add_category_override" />

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="category_select"><?php esc_html_e( 'Category', 'ai-summarize' ); ?></label>
						</th>
						<td>
							<select id="category_select" name="category_id" required>
								<option value=""><?php esc_html_e( 'Select a category...', 'ai-summarize' ); ?></option>
								<?php foreach ( $categories as $category ) : ?>
									<?php $categoryKey = 'category_' . $category->term_id; ?>
									<?php if ( ! isset( $currentOverrides[ $categoryKey ] ) ) : ?>
										<option value="<?php echo esc_attr( $category->term_id ); ?>">
											<?php echo esc_html( $category->name ); ?>
											(<?php echo esc_html( $category->count ); ?> posts)
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="category_prompt"><?php esc_html_e( 'Custom Prompt Template', 'ai-summarize' ); ?></label>
						</th>
						<td>
							<textarea
								id="category_prompt"
								name="prompt_template"
								class="large-text"
								rows="4"
								placeholder="<?php esc_attr_e( 'Enter category-specific prompt template...', 'ai-summarize' ); ?>"
							></textarea>
							<p class="description">
								<?php esc_html_e( 'Leave empty to use the global prompt template.', 'ai-summarize' ); ?>
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
										<code class="placeholder-code" data-target="category_prompt"><?php echo esc_html( $placeholder ); ?></code>
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
							<?php esc_html_e( 'Enabled Services', 'ai-summarize' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Select enabled services for this category', 'ai-summarize' ); ?></span>
								</legend>
								<?php foreach ( $aiServices as $serviceKey => $serviceConfig ) : ?>
									<label style="display: inline-block; margin-right: 16px; margin-bottom: 8px;">
										<input
											type="checkbox"
											name="enabled_services[]"
											value="<?php echo esc_attr( $serviceKey ); ?>"
										/>
										<?php echo esc_html( $serviceConfig['name'] ); ?>
									</label>
								<?php endforeach; ?>
								<p class="description">
									<?php esc_html_e( 'Leave unchecked to use global service settings.', 'ai-summarize' ); ?>
								</p>
							</fieldset>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Add Category Override', 'ai-summarize' ), 'secondary' ); ?>
			</form>
		</div>

		<div class="existing-overrides">
			<h3><?php esc_html_e( 'Existing Category Overrides', 'ai-summarize' ); ?></h3>

			<?php if ( empty( $currentOverrides ) ) : ?>
				<div class="no-overrides">
					<p><?php esc_html_e( 'No category overrides configured yet.', 'ai-summarize' ); ?></p>
				</div>
			<?php else : ?>
				<div class="overrides-list">
					<?php foreach ( $currentOverrides as $categoryKey => $override ) : ?>
						<?php
						$categoryId = (int) str_replace( 'category_', '', $categoryKey );
						$category   = get_category( $categoryId );
						if ( ! $category ) {
							continue;
						}
						?>
						<div class="override-item" data-category-id="<?php echo esc_attr( $categoryId ); ?>">
							<div class="override-header">
								<h4 class="category-name">
									<?php echo esc_html( $category->name ); ?>
									<span class="post-count">(<?php echo esc_html( $category->count ); ?> posts)</span>
								</h4>
								<div class="override-actions">
									<button type="button" class="button edit-override">
										<?php esc_html_e( 'Edit', 'ai-summarize' ); ?>
									</button>
									<form method="post" style="display: inline;" class="delete-override-form">
										<?php wp_nonce_field( 'ai_summarize_settings', '_ai_summarize_nonce' ); ?>
										<input type="hidden" name="action" value="delete_category_override" />
										<input type="hidden" name="category_id" value="<?php echo esc_attr( $categoryId ); ?>" />
										<button type="submit" class="button button-link-delete"
												onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this override?', 'ai-summarize' ); ?>')">
											<?php esc_html_e( 'Delete', 'ai-summarize' ); ?>
										</button>
									</form>
								</div>
							</div>

							<div class="override-details">
								<?php if ( ! empty( $override['prompt_template'] ) ) : ?>
									<div class="prompt-preview">
										<strong><?php esc_html_e( 'Custom Prompt:', 'ai-summarize' ); ?></strong>
										<div class="prompt-text"><?php echo esc_html( wp_trim_words( $override['prompt_template'], 20 ) ); ?></div>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $override['enabled_services'] ) ) : ?>
									<div class="services-preview">
										<strong><?php esc_html_e( 'Enabled Services:', 'ai-summarize' ); ?></strong>
										<div class="services-list">
											<?php
											$enabledServiceNames = [];
											foreach ( $override['enabled_services'] as $serviceKey => $enabled ) {
												if ( $enabled && isset( $aiServices[ $serviceKey ] ) ) {
													$enabledServiceNames[] = $aiServices[ $serviceKey ]['name'];
												}
											}
											echo esc_html( implode( ', ', $enabledServiceNames ) );
											?>
										</div>
									</div>
								<?php endif; ?>
							</div>

							<!-- Edit form (hidden by default) -->
							<div class="override-edit-form" style="display: none;">
								<form method="post" class="edit-override-form">
									<?php wp_nonce_field( 'ai_summarize_settings', '_ai_summarize_nonce' ); ?>
									<input type="hidden" name="action" value="update_category_override" />
									<input type="hidden" name="category_id" value="<?php echo esc_attr( $categoryId ); ?>" />

									<table class="form-table" role="presentation">
										<tr>
											<th scope="row">
												<label><?php esc_html_e( 'Custom Prompt Template', 'ai-summarize' ); ?></label>
											</th>
											<td>
												<textarea
													name="prompt_template"
													class="large-text edit-prompt-textarea"
													rows="4"
												><?php echo esc_textarea( $override['prompt_template'] ?? '' ); ?></textarea>
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
															<code class="placeholder-code placeholder-code-edit"><?php echo esc_html( $placeholder ); ?></code>
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
												<?php esc_html_e( 'Enabled Services', 'ai-summarize' ); ?>
											</th>
											<td>
												<?php foreach ( $aiServices as $serviceKey => $serviceConfig ) : ?>
													<?php $isEnabled = ! empty( $override['enabled_services'][ $serviceKey ] ); ?>
													<label style="display: inline-block; margin-right: 16px; margin-bottom: 8px;">
														<input
															type="checkbox"
															name="enabled_services[]"
															value="<?php echo esc_attr( $serviceKey ); ?>"
															<?php checked( $isEnabled, true ); ?>
														/>
														<?php echo esc_html( $serviceConfig['name'] ); ?>
													</label>
												<?php endforeach; ?>
											</td>
										</tr>
									</table>

									<div class="edit-actions">
										<?php submit_button( __( 'Update Override', 'ai-summarize' ), 'primary', 'submit', false ); ?>
										<button type="button" class="button cancel-edit">
											<?php esc_html_e( 'Cancel', 'ai-summarize' ); ?>
										</button>
									</div>
								</form>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<style>
.category-overrides-container {
	margin-top: 20px;
}

.add-category-override {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 30px;
}

.override-item {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 16px;
	margin-bottom: 16px;
}

.override-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.category-name {
	margin: 0;
	font-size: 16px;
}

.post-count {
	font-weight: normal;
	color: #666;
	font-size: 14px;
}

.override-actions {
	display: flex;
	gap: 8px;
}

.override-details {
	margin-bottom: 12px;
}

.prompt-preview, .services-preview {
	margin-bottom: 8px;
}

.prompt-text {
	background: #f7f7f7;
	padding: 8px;
	border-radius: 4px;
	font-style: italic;
	margin-top: 4px;
}

.services-list {
	margin-top: 4px;
}

.edit-actions {
	margin-top: 16px;
}

.no-overrides {
	text-align: center;
	padding: 40px;
	color: #666;
}
</style>

<script>
(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle edit button clicks
		$('.edit-override').on('click', function() {
			var $item = $(this).closest('.override-item');
			var $details = $item.find('.override-details');
			var $editForm = $item.find('.override-edit-form');
			var $actions = $item.find('.override-actions');

			$details.hide();
			$actions.hide();
			$editForm.show();
		});

		// Handle cancel edit
		$('.cancel-edit').on('click', function() {
			var $item = $(this).closest('.override-item');
			var $details = $item.find('.override-details');
			var $editForm = $item.find('.override-edit-form');
			var $actions = $item.find('.override-actions');

			$editForm.hide();
			$details.show();
			$actions.show();
		});

		// Handle placeholder insertion for add form
		$('.add-category-override .placeholder-code').on('click', function() {
			var placeholder = $(this).text();
			var textarea = $('#category_prompt')[0];
			if (!textarea) return;

			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
			var text = textarea.value;

			textarea.value = text.substring(0, start) + placeholder + text.substring(end);
			textarea.setSelectionRange(start + placeholder.length, start + placeholder.length);
			textarea.focus();
		});

		// Handle placeholder insertion for edit forms
		$(document).on('click', '.placeholder-code-edit', function() {
			var placeholder = $(this).text();
			var $editForm = $(this).closest('.override-edit-form');
			var textarea = $editForm.find('.edit-prompt-textarea')[0];
			if (!textarea) return;

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
