<?php

namespace App\Form;

use App\Entity\Project;
use App\Form\Data\WorkTimeData;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkTimeDataType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('project', EntityType::class, [
                'label' => 'Projet concerné',
                'class' => Project::class,
                'choice_label' => 'name',
                'query_builder' => function (ProjectRepository $projectRepository) {
                    return $projectRepository->createQueryBuilder('p')
                        ->where('p.deliveredAt IS NULL')
                        ->orderBy('p.name', 'ASC');
                },
            ])
            ->add('daysSpent', NumberType::class, [
                'label' => 'Nombre de jours'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkTimeData::class
        ]);
    }
}
