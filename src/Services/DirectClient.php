<?php

namespace Zenmanage\Laravel\Services;

use Zenmanage\Flags\Request\Entities\Context\Context;
use Zenmanage\Flags\Response\Entities\Flag;
use Zenmanage\Laravel\Contracts\Client;

class DirectClient implements Client {

    private $zenmanage;

    public function __construct(\Zenmanage\Zenmanage $zenmanage)
    {
        $this->zenmanage = $zenmanage;
    }

    public function withContext(Context $context): Client
    {
        $this->zenmanage->flags = $this->zenmanage->flags->withContext($context);
        return $this;

    }

    public function withDefault(string $key, string $type, string|bool|float|int $defaultValue): Client
    {
        $this->zenmanage->flags = $this->zenmanage->flags->withDefault($key, $type, $defaultValue);
        return $this;
    }

    public function all(): array
    {
        return $this->zenmanage->flags->all();
    }

    public function get(string $key) : ?Flag
    {
        return $this->zenmanage->flags->single($key);
    }
}
