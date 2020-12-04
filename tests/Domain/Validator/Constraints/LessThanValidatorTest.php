<?php

namespace App\Tests\Domain\Validator\Constraints;

use App\Domain\Validator\Constraints\LessThan;
use App\Domain\Validator\Constraints\LessThanValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class LessThanValidatorTest extends ConstraintValidatorTestCase
{
    public function createValidator(): LessThanValidator
    {
        return new LessThanValidator();
    }

    /**
     * @dataProvider validDataProvider
     *
     * @var int|string|\DateTime
     */
    public function testValidate($valueToValidate, array $options): void
    {
        $this->validator->validate($valueToValidate, new LessThan($options));

        $this->assertNoViolation();
    }

    /**
     * @dataProvider noValidDataProvider
     *
     * @var int|string|\DateTime
     */
    public function testNoValidate($valueToValidate, array $options): void
    {
        $this->validator->validate($valueToValidate, new LessThan($options));

        $this->assertSame(1, $violationsCount = \count($this->context->getViolations()), sprintf('1 violation expected. Got %u.', $violationsCount));
    }

    public function validDataProvider(): iterable
    {
        yield [1, ['value' => 10]];
        yield [(new \DateTime())->modify('yesterday')->format('Y-m-d H:i:s'), ['value' => 'today']];
        yield [(new \DateTime())->modify('yesterday'), ['value' => new \DateTime()]];
    }

    public function noValidDataProvider(): iterable
    {
        yield [12, ['value' => 10]];
        yield [(new \DateTime())->modify('tomorrow')->format('Y-m-d H:i:s'), ['value' => 'today']];
        yield [(new \DateTime())->modify('tomorrow'), ['value' => new \DateTime()]];
    }
}
