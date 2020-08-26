<?php

namespace App\dto;

use Ramsey\Uuid\Rfc4122\UuidV1;
use Ramsey\Uuid\Uuid;

class UuidDto
{
    private $uuid;

    private function __construct()
    {
        $this->uuid = Uuid::uuid1();
    }

    public static function create()
    {
        return new self();
    }

    /**
     * @return UuidV1
     */
    public function getUuid(): UuidV1
    {
        return $this->uuid;
    }
}