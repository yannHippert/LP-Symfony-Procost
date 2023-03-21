<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Profession;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('profession', EntityType::class, [
                'label' => 'Métier',
                'class' => Profession::class,
                'choice_label' => 'name',
            ])
            ->add('dailySalary', NumberType::class, [
                'label' => 'Coût journalier (en €)'
            ])
            ->add('employmentDate', DateType::class, [
                'label' => "Date d'embauche",
                'widget' => 'single_text',
                'input'  => 'datetime'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class
        ]);
    }
}
