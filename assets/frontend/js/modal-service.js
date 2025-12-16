/**
 * Modal Service Handler
 *
 * Shows a modal with the prompt text and copy-to-clipboard functionality
 * for AI services that don't support URL parameters (e.g., Claude).
 */
(function() {
	'use strict';

	// Create modal HTML on page load
	function createModal() {
		if (document.getElementById('ai-summarize-modal')) {
			return; // Already created
		}

		const modal = document.createElement('div');
		modal.id = 'ai-summarize-modal';
		modal.className = 'ai-summarize-modal';
		modal.setAttribute('role', 'dialog');
		modal.setAttribute('aria-modal', 'true');
		modal.setAttribute('aria-labelledby', 'ai-summarize-modal-title');

		modal.innerHTML = `
			<div class="ai-summarize-modal__overlay"></div>
			<div class="ai-summarize-modal__content">
				<div class="ai-summarize-modal__header">
					<h3 id="ai-summarize-modal-title"></h3>
					<button type="button" class="ai-summarize-modal__close" aria-label="Close modal">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="ai-summarize-modal__body">
					<p class="ai-summarize-modal__instructions"></p>
					<div class="ai-summarize-modal__prompt-container">
						<textarea id="ai-summarize-modal-prompt" class="ai-summarize-modal__prompt" readonly></textarea>
						<button type="button" class="ai-summarize-modal__copy" id="ai-summarize-copy-button">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
								<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
							</svg>
							<span>Copy to Clipboard</span>
						</button>
					</div>
				</div>
				<div class="ai-summarize-modal__footer">
					<button type="button" class="ai-summarize-modal__button ai-summarize-modal__button--secondary ai-summarize-modal__cancel">
						Cancel
					</button>
					<a href="#" id="ai-summarize-modal-open-service" target="_blank" rel="noopener noreferrer" class="ai-summarize-modal__button ai-summarize-modal__button--primary">
						Open Service
					</a>
				</div>
			</div>
		`;

		document.body.appendChild(modal);

		// Add event listeners
		const overlay = modal.querySelector('.ai-summarize-modal__overlay');
		const closeButton = modal.querySelector('.ai-summarize-modal__close');
		const cancelButton = modal.querySelector('.ai-summarize-modal__cancel');
		const copyButton = modal.querySelector('.ai-summarize-modal__copy');

		overlay.addEventListener('click', closeModal);
		closeButton.addEventListener('click', closeModal);
		cancelButton.addEventListener('click', closeModal);
		copyButton.addEventListener('click', copyPrompt);

		// Escape key closes modal
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal.classList.contains('ai-summarize-modal--active')) {
				closeModal();
			}
		});
	}

	// Show modal with prompt
	function showModal(prompt, serviceName, serviceUrl) {
		const modal = document.getElementById('ai-summarize-modal');
		const promptTextarea = document.getElementById('ai-summarize-modal-prompt');
		const modalTitle = document.getElementById('ai-summarize-modal-title');
		const instructions = modal.querySelector('.ai-summarize-modal__instructions');
		const openButton = document.getElementById('ai-summarize-modal-open-service');

		if (modal && promptTextarea) {
			// Update modal content
			modalTitle.textContent = `Copy Prompt for ${serviceName}`;
			instructions.textContent = `${serviceName} doesn't support URL parameters. Copy the prompt below and paste it into ${serviceName}:`;
			promptTextarea.value = prompt;
			openButton.href = serviceUrl;
			openButton.textContent = `Open ${serviceName}`;

			// Show modal
			modal.classList.add('ai-summarize-modal--active');
			document.body.classList.add('ai-summarize-modal-open');

			// Focus the textarea
			setTimeout(() => {
				promptTextarea.select();
			}, 100);
		}
	}

	// Close modal
	function closeModal() {
		const modal = document.getElementById('ai-summarize-modal');
		if (modal) {
			modal.classList.remove('ai-summarize-modal--active');
			document.body.classList.remove('ai-summarize-modal-open');
		}
	}

	// Copy prompt to clipboard
	function copyPrompt() {
		const promptTextarea = document.getElementById('ai-summarize-modal-prompt');
		const copyButton = document.getElementById('ai-summarize-copy-button');

		if (promptTextarea) {
			promptTextarea.select();

			// Try modern clipboard API first
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(promptTextarea.value)
					.then(() => {
						showCopyFeedback(copyButton, true);
					})
					.catch(() => {
						// Fallback to execCommand
						fallbackCopy(promptTextarea, copyButton);
					});
			} else {
				// Fallback to execCommand
				fallbackCopy(promptTextarea, copyButton);
			}
		}
	}

	// Fallback copy method
	function fallbackCopy(textarea, button) {
		try {
			document.execCommand('copy');
			showCopyFeedback(button, true);
		} catch (err) {
			showCopyFeedback(button, false);
		}
	}

	// Show copy feedback
	function showCopyFeedback(button, success) {
		const originalHTML = button.innerHTML;

		if (success) {
			button.innerHTML = `
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
				<span>Copied!</span>
			`;
			button.classList.add('ai-summarize-modal__copy--success');
		} else {
			button.innerHTML = '<span>Failed to copy</span>';
		}

		setTimeout(() => {
			button.innerHTML = originalHTML;
			button.classList.remove('ai-summarize-modal__copy--success');
		}, 2000);
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		createModal();

		// Handle clicks on modal service buttons
		document.addEventListener('click', function(e) {
			const button = e.target.closest('.ai-summarize-button--modal');
			if (button) {
				e.preventDefault();
				const prompt = button.getAttribute('data-prompt');
				const serviceName = button.getAttribute('data-service');
				const serviceUrl = button.getAttribute('data-url');
				if (prompt && serviceName && serviceUrl) {
					showModal(prompt, serviceName, serviceUrl);
				}
			}
		});
	}
})();
