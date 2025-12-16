<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize;

use wpdb;
use Caseproof\AiSummarize\Admin\Page;
use Caseproof\AiSummarize\Services\Settings;
use Caseproof\AiSummarize\Services\UrlGenerator;
use Caseproof\AiSummarize\Blocks\AiSummarizeButtons;
use Caseproof\AiSummarize\GroundLevel\Support\Models\Hook;
use Caseproof\AiSummarize\GroundLevel\Package\Bootstrap as BaseBootstrap;
use Caseproof\AiSummarize\GroundLevel\Database\Service as DatabaseService;

class Bootstrap extends BaseBootstrap
{
    /**
     * Initalize the service.
     */
    public function init(): void
    {
        /*
         * Retrieve the plugin's dependency injection container.
         *
         * This container is used to manage the plugin's services, factories, and
         * configuration parameters.
         */
        $container = $this->container();

        /*
         * Define the plugin's database connection service.
         *
         * This is a simple reference to the global $wpdb object. It's necessary
         * to define this so that other services that utilize a database connection
         * will always point to the same database connection.
         */
        $container->addService(
            DatabaseService::DB_CONNECTION,
            static function (): wpdb {
                global $wpdb;
                return $wpdb;
            }
        );

        /*
         * Define the plugin's settings service.
         *
         * This service handles all plugin configuration using WordPress Options API
         * including global prompt templates, AI service configuration, and
         * category-specific overrides.
         */
        $container->addService(
            'settings',
            static function (): Settings {
                return new Settings();
            }
        );

        /*
         * Define the URL generator service.
         *
         * This service generates properly formatted URLs for AI services with
         * encoded prompts for content summarization.
         */
        $container->addService(
            'urlGenerator',
            static function (): UrlGenerator {
                return new UrlGenerator();
            }
        );

        /*
         * Define the AI Summarize Buttons block.
         *
         * This block renders AI service buttons on the frontend for content
         * summarization using the configured settings and enabled services.
         */
        $container->addService(
            'aiSummarizeButtons',
            static function () use ($container): AiSummarizeButtons {
                return new AiSummarizeButtons(
                    $container->get('settings'),
                    $container->get('urlGenerator')
                );
            }
        );

        /*
         * The Admin\Page class renders the plugin's admin page.
         *
         * It has container awareness so we need to set the container on the class
         * so that we ensure that the container is available when class methods
         * require other dependencies from the application.
         *
         * Setting it in this way makes it so that when testing the class we can
         * easily mock the container and inject it into the class but in production
         * the container will be set automatically to the application's container.
         */
        Page::setContainer($container);
        Page::init();
    }

    /**
     * Returns an array of Hooks that should be added by the class.
     *
     * @return array
     */
    protected function configureHooks(): array
    {
        /*
         * The Hook model is an abstraction layer that allows us to define
         * hooks using strict types via a declarative object.
         *
         * Doing so provides better automated validation on the hook's parameters
         * and also allows us to easily mock the hook object when testing.
         *
         * The array also accepts an array of arguments that will be converted
         * into a Hook object if that's preferred.
         */
        return [
            new Hook(
                Hook::TYPE_ACTION,
                'admin_menu',
                [
                    Admin\Page::class,
                    'register',
                ]
            ),
            new Hook(
                Hook::TYPE_ACTION,
                'init',
                function (): void {
                    $block = $this->container()->get('aiSummarizeButtons');
                    $block->register();
                }
            ),
            new Hook(
                Hook::TYPE_ACTION,
                'wp_enqueue_scripts',
                function (): void {
                    // Enqueue frontend block styles.
                    wp_enqueue_style(
                        'ai-summarize-blocks',
                        $this->config()->getBaseUrl() . 'assets/frontend/css/blocks.css',
                        [],
                        $this->config()->getVersion()
                    );

                    // Enqueue modal service styles (for services without URL parameter support).
                    wp_enqueue_style(
                        'ai-summarize-modal-service',
                        $this->config()->getBaseUrl() . 'assets/frontend/css/modal-service.css',
                        [],
                        $this->config()->getVersion()
                    );

                    // Enqueue modal service script.
                    wp_enqueue_script(
                        'ai-summarize-modal-service',
                        $this->config()->getBaseUrl() . 'assets/frontend/js/modal-service.js',
                        [],
                        $this->config()->getVersion(),
                        true
                    );
                }
            ),
        ];
    }
}
