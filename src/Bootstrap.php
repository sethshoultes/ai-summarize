<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize;

use wpdb;
use Caseproof\AiSummarize\Admin\Page;
use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Services\UrlGenerator;
use Caseproof\AiSummarize\Blocks\AiSummarizeButtons;

class Bootstrap {

	/**
	 * @var Container
	 */
	private Container $container;

	/**
	 * @var string
	 */
	private string $mainFile;

	/**
	 * @var array
	 */
	private array $pluginData;

	/**
	 * Constructor.
	 *
	 * @param string $mainFile   Main plugin file path.
	 * @param array  $pluginData Plugin data from get_plugin_data().
	 */
	public function __construct( string $mainFile, array $pluginData ) {
		$this->mainFile   = $mainFile;
		$this->pluginData = $pluginData;
		$this->container  = new Container();

		$this->initContainer();
		$this->init();
		$this->registerHooks();
	}

	/**
	 * Initialize the dependency injection container.
	 */
	private function initContainer(): void {
		// Register plugin configuration.
		$this->container->addParameter( 'MAIN_FILE', $this->mainFile );
		$this->container->addParameter( 'BASE_URL', plugin_dir_url( $this->mainFile ) );
		$this->container->addParameter( 'BASE_PATH', plugin_dir_path( $this->mainFile ) );
		$this->container->addParameter( 'VERSION', $this->pluginData['Version'] ?? '1.0.0' );
		$this->container->addParameter( 'NAME', $this->pluginData['Name'] ?? 'AI Summarize' );

		// Register database connection.
		$this->container->addService(
			'db_connection',
			static function (): wpdb {
				global $wpdb;
				return $wpdb;
			}
		);

		// Register settings service.
		$this->container->addService(
			'settings',
			static function (): Settings {
				return new Settings();
			}
		);

		// Register URL generator service.
		$this->container->addService(
			'urlGenerator',
			static function (): UrlGenerator {
				return new UrlGenerator();
			}
		);

		// Register AI Summarize Buttons block.
		$this->container->addService(
			'aiSummarizeButtons',
			function (): AiSummarizeButtons {
				return new AiSummarizeButtons(
					$this->container->get( 'settings' ),
					$this->container->get( 'urlGenerator' )
				);
			}
		);
	}

	/**
	 * Initialize the plugin.
	 */
	private function init(): void {
		// Set container on admin page.
		Page::setContainer( $this->container );
		Page::init();
	}

	/**
	 * Register WordPress hooks.
	 */
	private function registerHooks(): void {
		// Register admin menu.
		add_action( 'admin_menu', [ Admin\Page::class, 'register' ] );

		// Register block immediately (we're already in the init hook).
		$block = $this->container->get( 'aiSummarizeButtons' );
		$block->register();

		// Enqueue frontend assets.
		add_action(
			'wp_enqueue_scripts',
			function (): void {
				// Enqueue frontend block styles.
				wp_enqueue_style(
					'ai-summarize-blocks',
					$this->container->get( 'BASE_URL' ) . 'assets/frontend/css/blocks.css',
					[],
					$this->container->get( 'VERSION' )
				);

				// Enqueue modal service styles.
				wp_enqueue_style(
					'ai-summarize-modal-service',
					$this->container->get( 'BASE_URL' ) . 'assets/frontend/css/modal-service.css',
					[],
					$this->container->get( 'VERSION' )
				);

				// Enqueue modal service script.
				wp_enqueue_script(
					'ai-summarize-modal-service',
					$this->container->get( 'BASE_URL' ) . 'assets/frontend/js/modal-service.js',
					[],
					$this->container->get( 'VERSION' ),
					true
				);
			}
		);
	}

	/**
	 * Get the container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}
}
