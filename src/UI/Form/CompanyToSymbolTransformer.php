<?php
declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Model\Company;
use App\Domain\Port\CompanyProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CompanyToSymbolTransformer implements DataTransformerInterface
{
    public function __construct(private CompanyProvider $companyProvider)
    {
    }

    /**
     * @param ?Company $value
     * @return string
     */
    public function transform(mixed $value)
    {
        if (null === $value) {
            return '';
        }

        return $value->getSymbol();
    }

    /**
     * @param ?string $value
     * @return ?Company
     */
    public function reverseTransform(mixed $value)
    {
        if (!$value) {
            return null;
        }

        $company = $this->companyProvider->findBySymbol($value);
        if (null === $company) {
            $failure = new TransformationFailedException(sprintf(
                'A company with symbol "%s" does not exist!',
                $value
            ));
            $failure->setInvalidMessage('The given "{{ value }}" value is not a valid company symbol.', [
                '{{ value }}' => $value,
            ]);
            throw $failure;
        }

        return $company;
    }
}
