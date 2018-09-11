<?php

namespace yeesoft\auth\parsers;

interface UserAttributesParser
{

    public function getAttributes($attributes);
}
