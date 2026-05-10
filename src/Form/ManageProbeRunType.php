<?php

declare(strict_types=1);

namespace App\Managing\Form;

use App\Managing\Entity\ManageProbeRun;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ManageProbeRunType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('componentKey', TextType::class)
            ->add('probeKey', TextType::class)
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Passed' => 'passed',
                    'Failed' => 'failed',
                    'Skipped' => 'skipped',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ManageProbeRun::class,
        ]);
    }
}
