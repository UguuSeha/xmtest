<?php
declare(strict_types=1);

namespace App\UI\Form;

use App\UI\Dto\HistoryRequestDto;
use Carbon\Carbon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HistoryRequestType extends AbstractType
{
    public function __construct(
        private CompanyToSymbolTransformer $companyToSymbolTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('start', DateType::class, [
                'constraints' => [new Assert\NotBlank()],
                'widget' => 'single_text',
            ])
            ->add('end', DateType::class, [
                'constraints' => [new Assert\NotBlank()],
                'widget' => 'single_text',
            ]);

        $builder->get('company')->addModelTransformer($this->companyToSymbolTransformer);

        if ($options['include_email']) {
            $builder->add('email', EmailType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Email()]
            ]);
        }

        $builder->add('showHistory', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HistoryRequestDto::class,
            'constraints' => [new Assert\Callback([$this, 'validateDates'])],
            'include_email' => true,
            'allow_extra_fields' => true,
        ]);
    }

    public function validateDates(HistoryRequestDto $dto, ExecutionContextInterface $context, $payload)
    {
        $now = Carbon::now();
        if ($dto->start > $now) {
            $context
                ->buildViolation('Start date should be less or equal than current date.')
                ->atPath('start')
                ->addViolation();
        }
        if ($dto->end > $now) {
            $context
                ->buildViolation('End date should be less or equal than current date.')
                ->atPath('end')
                ->addViolation();
        }
        if ($dto->start > $dto->end) {
            $context
                ->buildViolation('End date should be greater or equal than Start date.')
                ->atPath('end')
                ->addViolation();
        }
    }
}
