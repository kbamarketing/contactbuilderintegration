<?php
namespace kbamarketing\contactbuilderintegration;

use Craft;

use yii\base\Event;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use barrelstrength\sproutforms\elements\Entry;
use kbamarketing\contactbuilderintegration\services\Service;

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
        
        $this->setComponents([
	        'service' => Service::class,
	    ]);
        
        $events = array_filter( explode("\r\n", $this->getSettings()['cbEvents'] ) );
	    
	    foreach( $events as $event ) {
		    
		    Event::on(Entry::class, $event, function(Event $event) {
			    
			    if (Craft::$app->request->isSiteRequest)
		        {
		            // The Form Entry Element is available via the $event->sender attribute     
		            $entry = $event->sender;
		        }
		        
		        if (Craft::$app->request->isCpRequest)
		        {
		            $entry = $event->sender;
		        }

                if ( array_key_exists('enquiryType', $entry->getAttributes() ) ) {
                    if ($entry->getAttributes()['enquiryType']->value === "general" ) {
                        return false;
                    }
                }

			    if( $entry->getAttributes()['contactBuilder'] || $entry->getAttributes()['contactBuilderDevelopmentId'] ) {
				    			    
				    $message = $this->service->add($entry);
				    
				    Craft::info($message, 'ContactBuilderIntegration');
				    
				    Craft::$app->getUrlManager()->setRouteParams(array('contactBuilderIntegration' => $message));
				    
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
