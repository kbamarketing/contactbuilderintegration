<?php

namespace kbamarketing\contactbuilderintegration\services;

use SimpleXMLElement;
use craft\base\Component;

class Service extends Component
{

    protected $settings = [];

    public function add( BaseElementModel $entry )
    {

        $clientName = $this->getSetting('cbClientName');
        $apiKey = $this->getSetting('cbApikey');

        if( $clientName && $apiKey ) {

            $ch = curl_init();

            $contact = $this->mapFields( $this->getPayloadFields( $entry ) );

            $data = array(
                'password' => $apiKey,
                'action' => 'add',
                'contact' => $contact
            );

            $xml = new SimpleXMLElement('<request />');

            $this->array_to_xml($data, $xml);

            $xml = $xml->asXML();

            ContactBuilderIntegrationPlugin::log('Submitted XML: ' . $xml, LogLevel::Warning);

            curl_setopt($ch, CURLOPT_URL,"https://$clientName.contact-builder.co.uk/api/add.asp");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Cache-Control: no-cache",
                "Content-Type: application/xml"
            ));
            curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

            $response = curl_exec($ch);

            $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT ); // request headers

            curl_close ($ch);

            return $this->getMessage($response, $data, $xml, $headerSent);

        }

    }

    private function array_to_xml( $data = array(), SimpleXMLElement &$xml )
    {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    public function getExplodedSetting( $setting )
    {
        return explode( "\r\n", str_ireplace( array("\r", "\n"), "\r\n", $this->getSetting( $setting ) ) );
    }

    private function mapFields( $fields = array() )
    {

        $map = array_combine(array_map(function($row) {

            return $row['key'];

        }, $this->getSetting( 'cbFieldMap' )), array_map(function($row) {

            return $row['value'];

        }, $this->getSetting( 'cbFieldMap' )));

        $mappedFields = array();

        foreach( array_keys( $fields ) as $field ) {

            $key = ! empty( $map[ $field ] ) ? $map[ $field ] : false;

            if( $key ) {

                $keyParts = explode( ':', $key );

                $count = count($keyParts);

                $mapPart = array();

                foreach($keyParts as $i => $keyPart) {

                    if( $i === 0 && isset( $mappedFields[$keyPart] ) ) {

                        $mapPart[$keyPart] = $mappedFields[$keyPart];

                        $mapArray = $mapPart[$keyPart];

                    } else if( $count > $i+1 && ! isset( $mappedFields[$keyPart] ) ) {

                        $mapPart[$keyPart] = array();

                        $mapArray = $mapPart[$keyPart];

                    } else if( ! empty( $fields[$field] ) ) {

                        if( $count > 1 ) {

                            $mapArray[$keyPart] = $fields[$field];

                        } else {

                            $mapPart[$keyPart] = $fields[$field];

                        }

                    }

                }

                if( $count > 1 ) {

                    $keyPart = reset($keyParts);

                    $mapPart[$keyPart] = $mapArray;

                }

                $mappedFields = array_merge($mappedFields, $mapPart);

            }

        }

        $mappedFields = array_replace(array_intersect_key(array_flip($map), $mappedFields), $mappedFields);

        uasort($mappedFields, function($a, $b) {

            return is_array($b) && ! is_array($a) ? -1 : ( is_array($a) && ! is_array($b) ? 1 : 0 );

        });

        return $mappedFields;

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
     * generates message from Contact Builder response
     *
     */
    private function getMessage($response, $data, $xml, $headerSent) {

        ContactBuilderIntegrationPlugin::log('CB Response: ' . $response, LogLevel::Warning);

        if( ! empty( $response['status']['statuscode'] ) ) {

            switch( $response['status']['statuscode'] ) {

                case 1 :

                    return 'Contact Builder: Contact #' . $response['contact']['contactid'] . ' ' . strtolower( $response['status']['statusdesc'] );

                    break;

                default :

                    return 'Contact Builder: Error: Status(' . $response['status']['statuscode'] . ') - ' . strtolower( $response['status']['statusdesc'] ) . ' (Data Array:) ' . print_r($data, true) . ' (XML:) ' . $xml . ' (Header:) ' . $headerSent;

                    break;

            }

        }

    }

    /**
     * Returns an array of key/value pairs to send along in payload forwarding requests
     *
     * @return array
     */
    private function getPayloadFields( BaseElementModel $entry )
    {
        $fields = array();
        $ignore = array(
            'id',
            'slug',
            'title',
            'handle',
            'locale',
            'element',
            'elementId',
        );
        $content = $entry->getContent()->getAttributes();
        foreach ($content as $field => $value)
        {
            if (!in_array($field, $ignore))
            {
                $fields[$field] = $value;
            }
        }

        return $fields;
    }

}