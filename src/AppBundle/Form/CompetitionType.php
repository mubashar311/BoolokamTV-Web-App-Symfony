<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('inAppNotice')
            ->add('description')
            ->add('startDate', DateType::class,
                [
                    'label' => false,
                    'required' => true,
                    'mapped' => true,
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'form-control datepicker'
                    ]
                ])
            ->add('endDate', DateType::class,
                [
                    'label' => false,
                    'required' => true,
                    'mapped' => true,
                    'widget' => 'single_text',
                    'attr' => [
                        'class' => 'form-control datepicker'
                    ]
                ])
            ->add('visible')
            ->add('file')
        ;
        $builder->add('save', SubmitType::class,array("label"=>"SAVE"));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Competition'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_competition';
    }


}
