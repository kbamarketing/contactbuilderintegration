<?php

namespace kbamarketing\contactbuilderintegration\models;

use craft\base\Model;

class Settings extends Model
{
    public $cbClientName = '';
    public $cbApikey = '';
    public $cbFieldMap = [];
    public $cbEvents = 'afterSave';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['cbClientName', 'cbApikey', 'cbEvents'], 'required']
            // ...
        ];
    }
}