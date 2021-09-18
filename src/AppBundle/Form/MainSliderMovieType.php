<?php
namespace AppBundle\Form;

use AppBundle\Entity\MainSliderMovie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MainSliderMovieType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('poster');
        $builder->add('save',SubmitType::class, array("label" => "save"));
    }
    public function getName() {
        return 'MainSliderMovie';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MainSliderMovie::class
        ]);
    }
}
?>