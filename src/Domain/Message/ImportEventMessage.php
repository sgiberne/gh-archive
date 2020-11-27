<?php

namespace App\Domain\Message;

class ImportEventMessage
{
    public string $dateTime;
    public int $offset;
    public int $limit;
    public bool $lastOne = false;

    public function __construct(string $dateTime, int $offset, int $limit, bool $lastOne = false)
    {
        $this->dateTime = $dateTime;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->lastOne = $lastOne;
    }
}
