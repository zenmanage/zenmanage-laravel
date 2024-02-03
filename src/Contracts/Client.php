<?php

namespace Zenmanage\Laravel\Contracts;

use Zenmanage\Settings\Request\Entities\Context\Context;

interface Client {
    public function all(Context $context = null, array $defaults) : array;
    public function setting(Context $context = null, string $key, string $type, string|bool|float|int $defaultValue) : \Zenmanage\Settings\Response\Entities\Setting;
}
