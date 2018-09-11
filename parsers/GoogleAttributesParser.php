<?php

namespace yeesoft\auth\parsers;

use yii\helpers\ArrayHelper;

class GoogleAttributesParser implements UserAttributesParser
{

    public function getAttributes($attributes)
    {
        $result = [];

        $result['source'] = 'google';
        $result['source_id'] = ArrayHelper::getValue($attributes, 'id');
        $result['email'] = ArrayHelper::getValue($attributes, 'emails.0.value');
        $result['first_name'] = ArrayHelper::getValue($attributes, 'name.givenName');
        $result['last_name'] = ArrayHelper::getValue($attributes, 'name.familyName');

        return $result;
    }

}
