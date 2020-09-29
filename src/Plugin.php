<?php
namespace kbamarketing\contactbuilderintegration;

use Craft;

use yii\base\Event;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

class Plugin extends \craft\base\Plugin
{
	public $hasCpSettings = true;
	
    public function init()
    {
        parent::init();
        
        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = \kbamarketing\contactbuilderintegration\fields\DevelopmentsListField::class;
            }
        );
        
        $events = array_filter( explode("\r\n", $this->getSettings()['cbEvents'] ) );
	    
	    foreach( $events as $event ) {
	    
		    Craft::$app->on($event, function(Event $event) {
			    
			    $entry = $event->params['entry'];

                if ( array_key_exists('enquiryType', $entry->getContent()->getAttributes() ) ) {
                    if ($entry->getContent()->getAttributes()['enquiryType'] === "general" ) {
                        return false;
                    }
                }

			    if( $entry->getContent()->getAttribute('contactBuilder') || $entry->getContent()->getAttribute('contactBuilderDevelopmentId') ) {
				    			    
				    $message = Craft::$app->contactBuilderIntegration->add($entry);
				    
				    Craft::info($message, 'ContactBuilderIntegration');
				    
				    Craft::$app->urlManager->setRouteVariables(array('contactBuilderIntegration' => $message));
				    
				}
			    
			});
			
		}

        // Custom initialization code goes here...
    }
    
    protected function createSettingsModel()
    {
        return new models\Settings();
    }
    
    protected function settingsHtml()
    {   
        return \Craft::$app->getView()->renderTemplate(
            'contactbuilderintegration/settings',
            ['settings' => $this->getSettings()]
        );
    }

}