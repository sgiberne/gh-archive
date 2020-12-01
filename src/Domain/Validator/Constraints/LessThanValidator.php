<?php

namespace App\Domain\Validator\Constraints;

use Symfony\Component\Validator\Constraints\LessThanValidator as SymfonyLessThanValidator;

class LessThanValidator extends SymfonyLessThanValidator
{
    /**
     * {@inheritdoc}
     */
    protected function compareValues($value1, $value2): bool
    {
        if ('today' === $value2) {
            $value2 = (new \DateTime())->format('Y-m-d 00:00:00');
        }

        return null === $value2 || $value1 < $value2;
    }
}
