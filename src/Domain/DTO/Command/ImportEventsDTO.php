<?php

namespace App\Domain\DTO\Command;

use App\Domain\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ImportEventsDTO
{
    /**
     * @Assert\Type("string")
     * @Assert\Length(19)
     * @Assert\DateTime()
     * @AppAssert\LessThan("today")
     */
    public string $dateTime;

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\GreaterThanOrEqual(0)
     */
    public int $offset;

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     */
    public int $limit;

    public function __construct(string $dateTime, int $offset, int $limit)
    {
        $this->dateTime = $dateTime;
        $this->offset = $offset;
        $this->limit = $limit;
    }
}
