<?php

namespace JRC\VecinosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ComunidadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('direccion')
            ->add('mancomunidad', 'checkbox')
            ->add('save', 'submit', array('label' => 'Guardar Cumunidad'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'JRC\VecinosBundle\Entity\Comunidad'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'comunidad';
    }
}
