<?php
declare(strict_types=1);

namespace App\Tests\UI\Form;

use App\Domain\Model\Company;
use App\Domain\Port\CompanyProvider;
use App\UI\Dto\HistoryRequestDto;
use App\UI\Form\CompanyToSymbolTransformer;
use App\UI\Form\HistoryRequestType;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class HistoryRequestTypeTest extends TypeTestCase
{
    private CompanyProvider|MockObject $companyProvider;

    protected function setUp(): void
    {
        Carbon::setTestNow('2020-02-02');
        $this->companyProvider = $this->createMock(CompanyProvider::class);
        $this->companyProvider
            ->method('findBySymbol')
            ->willReturnMap([
                ['TEST', new Company('TEST', 'Test')],
                ['TEST2', null],
            ]);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function getExtensions()
    {
        $type = new HistoryRequestType(
            new CompanyToSymbolTransformer($this->companyProvider),
        );
        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([$type], []),
        ];
    }

    public function testSubmitValid()
    {
        $formData = $this->generateFormData();
        $expectedRequest = new HistoryRequestDto(
            company: new Company('TEST', 'Test'),
            start: (new \DateTime('2020-01-01 midnight')),
            end: (new \DateTime('2020-01-10 midnight')),
            email: 'test@test.ts',
        );

        $form = $this->factory->create(HistoryRequestType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expectedRequest, $form->getData());
    }

    public function testSubmitValidWithoutEmail()
    {
        $formData = $this->generateFormData(['email' => '']);
        $expectedRequest = new HistoryRequestDto(
            company: new Company('TEST', 'Test'),
            start: (new \DateTime('2020-01-01 midnight')),
            end: (new \DateTime('2020-01-10 midnight')),
        );

        $form = $this->factory->create(HistoryRequestType::class, null, ['include_email' => false]);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expectedRequest, $form->getData());
    }

    public function testSubmitValidCurrentDate()
    {
        $formData = $this->generateFormData([
            'start' => '2020-02-02',
            'end' => '2020-02-02',
        ]);
        $expectedRequest = new HistoryRequestDto(
            company: new Company('TEST', 'Test'),
            start: (new \DateTime('2020-02-02 midnight')),
            end: (new \DateTime('2020-02-02 midnight')),
            email: 'test@test.ts',
        );

        $form = $this->factory->create(HistoryRequestType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expectedRequest, $form->getData());
    }

    /**
     * @dataProvider submitInvalidData
     */
    public function testSubmitInvalid(array $formData, array $expectedErrors)
    {
        $form = $this->factory->create(HistoryRequestType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());
        self::assertFormErrors($form, $expectedErrors);
    }

    public function submitInvalidData()
    {
        yield 'Blank company symbol' => [
            'formData' => $this->generateFormData([
                'company' => '',
            ]),
            'expectedErrors' => [
                'company' => 'This value should not be blank.',
            ]
        ];
        yield 'Invalid company symbol' => [
            'formData' => $this->generateFormData([
                'company' => 'TEST2',
            ]),
            'expectedErrors' => [
                'company' => 'The given "TEST2" value is not a valid company symbol.',
            ]
        ];

        yield 'Blank email' => [
            'formData' => $this->generateFormData([
                'email' => '',
            ]),
            'expectedErrors' => [
                'email' => 'This value should not be blank.',
            ]
        ];
        yield 'Invalid email' => [
            'formData' => $this->generateFormData([
                'email' => 'test',
            ]),
            'expectedErrors' => [
                'email' => 'This value is not a valid email address.',
            ]
        ];

        yield 'Blank start' => [
            'formData' => $this->generateFormData([
                'start' => '',
            ]),
            'expectedErrors' => [
                'start' => 'This value should not be blank.',
            ]
        ];
        yield 'Blank end' => [
            'formData' => $this->generateFormData([
                'end' => '',
            ]),
            'expectedErrors' => [
                'end' => 'This value should not be blank.',
            ]
        ];
        yield 'Dates in future' => [
            'formData' => $this->generateFormData([
                'start' => '2020-02-03',
                'end' => '2020-02-04',
            ]),
            'expectedErrors' => [
                'start' => 'Start date should be less or equal than current date.',
                'end' => 'End date should be less or equal than current date.',
            ]
        ];
        yield 'End before start' => [
            'formData' => $this->generateFormData([
                'start' => '2020-01-03',
                'end' => '2020-01-01',
            ]),
            'expectedErrors' => [
                'end' => 'End date should be greater or equal than Start date.',
            ]
        ];
    }

    public static function assertFormErrors(FormInterface $form, $expectedErrors)
    {
        $errors = $form->getErrors(true);
        $actualErrors = [];
        foreach ($errors as $error) {
            $actualErrors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        self::assertEquals($actualErrors, $expectedErrors);
    }

    private function generateFormData(array $replace = [])
    {
        return array_merge([
            'company' => 'TEST',
            'start' => '2020-01-01',
            'end' => '2020-01-10',
            'email' => 'test@test.ts',
        ], $replace);
    }
}
