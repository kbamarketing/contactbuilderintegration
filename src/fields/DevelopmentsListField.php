<?php
/**
 * contactbuilderintegration plugin for Craft CMS 3.x
 *
 * A plugin to integrate with contact builder
 *
 * @link      https://weareaduro.com
 * @copyright Copyright (c) 2020 Aduro
 */

namespace kbamarketing\contactbuilderintegration\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * ContactbuilderintegrationField Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Aduro
 * @package   Contactbuilderintegration
 * @since     1.0.0
 */
class DevelopmentsListField extends Field
{

	protected $settings = [];

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return 'Developments List';
    }
    
    /**
     * Gets Developments
     *
     */
    private function getDevelopments() {
	    
	    $developments = array(
		    array(
			    'value' => 0,
			    'label' => 'None'
		    )
	    );
	    
	    if( $clientName = $this->getSetting('cbClientName') ) {
	    
	    	$xml = json_decode( json_encode( simplexml_load_string( file_get_contents( "https://$clientName.contact-builder.co.uk/api/developments.asp" ), "SimpleXMLElement", LIBXML_NOCDATA ) ), true );
	    	
	    	$divisions = is_array($xml['division']) ? $xml['division'] : [$xml['division']];
	    	
	    	
	    	foreach($divisions as $division) {
	    	
		    	$regions = empty( $division['region']['id'] ) ? $division['region'] : array( $division['region'] );
		    	
		    	foreach($regions as $region) {
			    	
			    	$developments[]= [
				    	'optgroup' => $region['name']
			    	];
			    	
			    	if(isset($region['development'])) { 
				    	
				    	if(isset($region['development']['id'])) {
					    	
					    	$developments[]= [
						    	'value' => $region['development']['id'],
						    	'label' => $region['development']['name']
					    	];
					    	
				    	} else {
				    	
					    	foreach($region['development'] as $development) {
						    	
						    	$developments[]= [
							    	'value' => $development['id'],
							    	'label' => $development['name']
						    	];
						    	
						    }
						    
						}
				    	
			    	}
			    	
		    	}
		    	
		    }
	    	
	    }
	    
	    return $developments;
	    
    }
    
    /**
     * Gets a plugin setting
     *
     * @param $name String Setting name
     * @return mixed Setting value
     * @author André Elvan
     */
    public function getSetting( $name = '' )
    {
        if ($this->settings == null) {
            $this->settings = Craft::$app->plugins->getPlugin('contactbuilderintegration')->getSettings();
        }

        return $this->settings[$name];
    }

    /**
     * Returns the field’s input HTML.
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="'.$name.'">'.$value.'</textarea>';
     * ```
     *
     * For more complex inputs, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template located at
     * craft/plugins/myplugin/templates/_fieldinput.html, passing the $name and $value variables to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *     'name'  => $name,
     *     'value' => $value
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your input, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getInputHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] provides a couple handy methods that can help you deal with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getInputHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getInputHtml($value, $element)
     * {
     *     // Come up with an ID value based on $name
     *     $id = Craft::$app->getView()->formatInputId($name);
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'name'         => $name,
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'value'        => $value
     *     ]);
     * }
     * ```
     *
     * And the _fieldinput.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="{{ name }}">{{ value }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
     * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'contactbuilderintegration/developmentslist',
            [
                'options' => $this->getDevelopments(),
				'name'  => $this->handle,
				'value' => $value
            ]
        );
    }
}
