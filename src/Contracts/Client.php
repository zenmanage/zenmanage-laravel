<?php

namespace Zenmanage\Laravel\Contracts;

use Zenmanage\Flags\Request\Entities\Context\Context;
use Zenmanage\Flags\Response\Entities\Flag;

interface Client {

    public function withContext(Context $context): Client;
    public function withDefault(string $key, string $type, string|bool|float|int $defaultValue): Client;
    public function connect(): Client;
    public function all(): array;
    public function get(string $key) : ?Flag;
}
