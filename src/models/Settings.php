<?php

namespace KBAMarketing\ContactBuilderIntegration\models;

use craft\base\Model;

class Settings extends Model
{
    public $cbClientName = '';
    public $cbApikey = '';
    public $cbFieldMap = [];
    public $cbEvents = 'sproutForms.saveEntry';

    public function rules()
    {
        return [
            [['cbClientName', 'cbApikey', 'cbFieldMap', 'cbEvents']]
            // ...
        ];
    }
}