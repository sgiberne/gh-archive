<?php

namespace App\Tests\Domain\DTO\Command;

use App\Domain\DTO\Command\ImportEventsDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ImportEventsDTOTest extends TestCase
{
    /**
     * @dataProvider provideValidImportEventsDTO
     */
    public function testValidImportEventsDTO(ImportEventsDTO $importEventsDTO): void
    {
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $constraintViolationList = $validator->validate($importEventsDTO);

        $this->assertSame(0, $constraintViolationList->count());
    }

    /**
     * @dataProvider provideInvalidImportEventsDTO
     */
    public function testInvalidImportEventsDTO(ImportEventsDTO $importEventsDTO): void
    {
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $constraintViolationList = $validator->validate($importEventsDTO);

        $this->assertSame(1, $constraintViolationList->count());
    }

    public function provideValidImportEventsDTO(): iterable
    {
        yield [
            new ImportEventsDTO(
                (new \DateTime())->modify('yesterday')->format('Y-m-d H:i:s'),
                0,
                10000
            ),
        ];

        yield [
            new ImportEventsDTO(
                (new \DateTime())->modify('- 10 days')->format('Y-m-d H:i:s'),
                5000,
                5000
            ),
        ];
    }

    public function provideInvalidImportEventsDTO(): iterable
    {
        yield [
            new ImportEventsDTO(
                (new \DateTime())->modify('+ 10 days')->format('Y-m-d H:i:s'),
                0,
                10000
            ),
        ];

        yield [
            new ImportEventsDTO(
                (new \DateTime())->modify('- 10 days')->format('Y-m-d H:i:s'),
                -1,
                10000
            ),
        ];

        yield [
            new ImportEventsDTO(
                (new \DateTime())->modify('- 10 days')->format('Y-m-d H:i:s'),
                10000,
                0
            ),
        ];
    }
}
