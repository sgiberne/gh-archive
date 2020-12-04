<?php

namespace App\Domain\DTO;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class RequestFilterDTO
{
    public const ASC_ORDER_BY = 'asc';
    public const DESC_ORDER_BY = 'desc';

    public const ORDER_BY = [
        self::ASC_ORDER_BY,
        self::DESC_ORDER_BY,
    ];

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     */
    public int $page;

    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     */
    public int $limit;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @AppAssert\EventSortBy()
     */
    public string $sortBy;

    /**
     * @AppAssert\EventFilterKeys()
     *
     * @var array<string, string>
     */
    public array $filters;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices=RequestFilterDTO::ORDER_BY)
     */
    public string $orderBy;

    /**
     * RequestFilterDTO constructor.
     *
     * @param array<string, string> $filters
     */
    public function __construct(int $page, int $limit, string $sortBy, string $orderBy, array $filters = [])
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->sortBy = $sortBy;
        $this->orderBy = $orderBy;
        $this->filters = $filters;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
