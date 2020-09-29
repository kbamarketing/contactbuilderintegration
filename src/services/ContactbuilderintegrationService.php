<?php
/**
 * contactbuilderintegration plugin for Craft CMS 3.x
 *
 * A plugin to integrate with contact builder
 *
 * @link      https://weareaduro.com
 * @copyright Copyright (c) 2020 Aduro
 */

namespace KBAMarketing\ContactBuilderIntegration\services;

use KBAMarketing\ContactBuilderIntegration\Contactbuilderintegration;

use Craft;
use craft\base\Component;

/**
 * ContactbuilderintegrationService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Aduro
 * @package   Contactbuilderintegration
 * @since     1.0.0
 */
class ContactbuilderintegrationService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Contactbuilderintegration::$plugin->contactbuilderintegrationService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }
}
