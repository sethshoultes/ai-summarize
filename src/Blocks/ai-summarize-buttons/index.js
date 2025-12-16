/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style-index.css';
import metadata from './block.json';

// Get AI services from localized data (passed from PHP).
const AI_SERVICES = window.aiSummarizeData?.services || {};

/**
 * Render button preview in the editor
 */
const ButtonPreview = ({ service, showLabel, size }) => {
	const config = AI_SERVICES[service];
	if (!config) return null;

	return (
		<a
			className={`ai-summarize-button ai-summarize-button--${service}`}
			style={{
				backgroundColor: config.color,
				color: '#FFFFFF',
				display: 'inline-flex',
				alignItems: 'center',
				justifyContent: 'center',
				gap: '0.5rem',
				padding: size === 'small' ? '0.5rem 1rem' : size === 'large' ? '1rem 2rem' : '0.75rem 1.5rem',
				borderRadius: '0.5rem',
				fontSize: size === 'small' ? '0.875rem' : size === 'large' ? '1.125rem' : '1rem',
				fontWeight: '600',
				textDecoration: 'none',
				cursor: 'not-allowed',
				minHeight: size === 'small' ? '36px' : size === 'large' ? '52px' : '44px',
				minWidth: '44px',
			}}
		>
			<span
				dangerouslySetInnerHTML={{ __html: config.icon }}
				style={{
					width: size === 'small' ? '1rem' : size === 'large' ? '1.5rem' : '1.25rem',
					height: size === 'small' ? '1rem' : size === 'large' ? '1.5rem' : '1.25rem',
				}}
			/>
			{showLabel && <span>{config.name}</span>}
		</a>
	);
};

/**
 * Get global settings from WordPress
 */
const getGlobalSettings = () => {
	// Try to get from inline script data (if available)
	return window.aiSummarizeGlobalSettings || {
		enabledServices: Object.keys(AI_SERVICES).reduce((acc, key) => {
			acc[key] = true; // Default: all enabled
			return acc;
		}, {}),
		displayOptions: {
			button_size: 'medium',
			layout: 'horizontal',
			show_labels: true,
		},
	};
};

/**
 * Register the AI Summarize Buttons block
 */
registerBlockType(metadata.name, {
	/**
	 * Edit function - renders in the block editor
	 */
	edit: ({ attributes, setAttributes }) => {
		const {
			useGlobalSettings,
			enabledServices,
			buttonSize,
			buttonLayout,
			showLabels,
		} = attributes;

		const blockProps = useBlockProps({
			className: `ai-summarize-preview ai-summarize-preview--${buttonLayout}`,
		});

		// Get global settings for preview
		const globalSettings = getGlobalSettings();
		const globalEnabledServices = Object.keys(globalSettings.enabledServices || {})
			.filter(key => globalSettings.enabledServices[key]);
		const globalButtonSize = globalSettings.displayOptions?.button_size || 'medium';
		const globalLayout = globalSettings.displayOptions?.layout || 'horizontal';
		const globalShowLabels = globalSettings.displayOptions?.show_labels !== false;

		// Determine which settings to use for preview
		const previewServices = useGlobalSettings ? globalEnabledServices : enabledServices;
		const previewSize = useGlobalSettings ? globalButtonSize : buttonSize;
		const previewLayout = useGlobalSettings ? globalLayout : buttonLayout;
		const previewShowLabels = useGlobalSettings ? globalShowLabels : showLabels;

		// Toggle service enabled/disabled
		const toggleService = (service) => {
			const newServices = enabledServices.includes(service)
				? enabledServices.filter((s) => s !== service)
				: [...enabledServices, service];
			setAttributes({ enabledServices: newServices });
		};

		// Sync with global settings when useGlobalSettings changes to true
		const handleUseGlobalSettingsChange = (value) => {
			setAttributes({ useGlobalSettings: value });

			if (value) {
				// Sync block attributes with global settings
				setAttributes({
					enabledServices: globalEnabledServices,
					buttonSize: globalButtonSize,
					buttonLayout: globalLayout,
					showLabels: globalShowLabels,
				});
			}
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Settings', 'ai-summarize')}>
						<ToggleControl
							label={__('Use Global Settings', 'ai-summarize')}
							help={__('Use settings from the admin panel', 'ai-summarize')}
							checked={useGlobalSettings}
							onChange={handleUseGlobalSettingsChange}
						/>
					</PanelBody>

					{!useGlobalSettings && (
						<>
							<PanelBody
								title={__('AI Services', 'ai-summarize')}
								initialOpen={true}
							>
								{Object.entries(AI_SERVICES).map(([key, config]) => (
									<CheckboxControl
										key={key}
										label={config.name}
										checked={enabledServices.includes(key)}
										onChange={() => toggleService(key)}
									/>
								))}
							</PanelBody>

							<PanelBody title={__('Display Options', 'ai-summarize')}>
								<SelectControl
									label={__('Button Size', 'ai-summarize')}
									value={buttonSize}
									options={[
										{ label: __('Small', 'ai-summarize'), value: 'small' },
										{ label: __('Medium', 'ai-summarize'), value: 'medium' },
										{ label: __('Large', 'ai-summarize'), value: 'large' },
									]}
									onChange={(value) =>
										setAttributes({ buttonSize: value })
									}
								/>

								<SelectControl
									label={__('Layout', 'ai-summarize')}
									value={buttonLayout}
									options={[
										{
											label: __('Horizontal', 'ai-summarize'),
											value: 'horizontal',
										},
										{
											label: __('Vertical', 'ai-summarize'),
											value: 'vertical',
										},
										{ label: __('Grid', 'ai-summarize'), value: 'grid' },
									]}
									onChange={(value) =>
										setAttributes({ buttonLayout: value })
									}
								/>

								<ToggleControl
									label={__('Show Labels', 'ai-summarize')}
									checked={showLabels}
									onChange={(value) => setAttributes({ showLabels: value })}
								/>
							</PanelBody>
						</>
					)}
				</InspectorControls>

				<div {...blockProps}>
					<div className="ai-summarize-buttons-preview">
						<div
							className={`ai-summarize-buttons ai-summarize-buttons--size-${previewSize} ai-summarize-buttons--layout-${previewLayout}`}
							style={{
								display: previewLayout === 'grid' ? 'grid' : 'flex',
								flexDirection: previewLayout === 'vertical' ? 'column' : 'row',
								flexWrap: 'wrap',
								gap: '1rem',
								alignItems: 'center',
								justifyContent: 'center',
								gridTemplateColumns: previewLayout === 'grid' ? 'repeat(auto-fit, minmax(150px, 1fr))' : undefined,
							}}
						>
							{previewServices.length > 0 ? (
								// Show button previews
								previewServices.map((service) => (
									<ButtonPreview
										key={service}
										service={service}
										showLabel={previewShowLabels}
										size={previewSize}
									/>
								))
							) : (
								// Show empty state
								<div style={{
									padding: '2rem',
									background: '#f0f0f1',
									borderRadius: '4px',
									textAlign: 'center',
									color: '#666'
								}}>
									<p>{__('No AI services enabled.', 'ai-summarize')}</p>
									<p style={{ fontSize: '0.9em', marginTop: '0.5rem' }}>
										{useGlobalSettings
											? __('Enable services in Settings → AI Summarize', 'ai-summarize')
											: __('Select services in the block settings →', 'ai-summarize')}
									</p>
								</div>
							)}
						</div>
					</div>
				</div>
			</>
		);
	},

	/**
	 * Save function - use dynamic rendering (return null for server-side rendering)
	 */
	save: () => {
		return null; // Dynamic block - rendered server-side
	},
});
