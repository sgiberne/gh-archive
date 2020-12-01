<?php

namespace App\Domain\Factory\Request;

use App\Domain\DTO\RequestFilterDTO;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestFilterDTOFactory
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function createFromRequest(): RequestFilterDTO
    {
        $filters = $this->requestStack->getMasterRequest()->get('filters', []);

        // Transform createdAt to DateTime
        foreach ($filters as $key => $filter) {
            if ('createdAt' === $key) {
                $filters[$key] = new \DateTime($filter);
            }
        }

        return new RequestFilterDTO(
            $this->requestStack->getMasterRequest()->get('page', 1),
            $this->requestStack->getMasterRequest()->get('limit', 50),
            $this->requestStack->getMasterRequest()->get('sort_by', 'createdAt'),
            $this->requestStack->getMasterRequest()->get('order_by', 'desc'),
            $filters,
        );
    }
}
