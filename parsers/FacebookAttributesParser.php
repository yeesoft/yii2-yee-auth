<?php

namespace yeesoft\auth\parsers;

use yii\helpers\ArrayHelper;

class FacebookAttributesParser implements UserAttributesParser
{

    public function getAttributes($attributes)
    {
        $result = [];

        $result['source'] = 'facebook';
        $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
        $result['email'] = ArrayHelper::getValue($attributes, 'email');

        return $result;
    }

}
