<?php

declare(strict_types=1);

namespace Caseproof\AiSummarize;

use Caseproof\AiSummarize\GroundLevel\Database\Service as DatabaseService;
use Caseproof\AiSummarize\GroundLevel\Package\PluginConfig;

/**
 * The main application instance.
 *
 * This function returns a singleton instance of the Bootstrap class which
 * acts as the plugin's main entry point.
 *
 * In context of WordPress, this function is called automatically when the
 * plugin is active.
 *
 * If for any reason you need to access the plugin's main entry point, you can
 * call this function to retrieve the instance.
 *
 * @param  string $mainFile The main plugin file.
 * @return Bootstrap
 */
function aiSummarizeApp(string $mainFile): Bootstrap
{
    static $app = null;
    if (is_null($app)) {
        /*
         * The PluginConfig class is used to define the plugin's configuration
         * parameters.
         *
         * Any configurable parameters which services or factories can be
         * defined here.
         *
         * Additionally other custom paramters can be defined here and they
         * can later be pulled from the container using the `get()` method.
         *
         * Plugin information available via get_plugin_data() is automatically
         * loaded into the container, for example, you could retrieve the plugin's
         * version number using `$container->get('VERSION')`.
         */
        $config = new PluginConfig(
            $mainFile,
            [
                DatabaseService::PREFIX => 'ai_summarize',
            ]
        );

        /*
         * The Bootstrap class is the main entry point for the plugin.
         *
         * The Bootstrap class will create the application's dependency injection
         * container and register the plugin's services, factories, and configuration
         * parameters.
         *
         * This sample plugin also uses the Bootstrap file to register an admin page
         * and the EventRecorder class which is responsible for adding WP hooks which,
         * when triggered, will record events to the database.
         */
        $app = new Bootstrap($config);
    }
    return $app;
}
