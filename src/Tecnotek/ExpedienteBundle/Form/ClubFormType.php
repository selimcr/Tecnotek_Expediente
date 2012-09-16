<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ClubFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true))->
            add('coordinator', 'text', array('trim' => true, 'required' => false))->
            add('day', 'choice', array('choices' =>
                array(  '1' => 'Lunes',
                        '2' => 'Martes',
                        '3' => 'Miércoles',
                        '4' => 'Jueves',
                        '5' => 'Viernes',
                        '6' => 'Sábado',
                        '7' => 'Domingo',),'required'  => false))->
            add('timeI', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))->
            add('timeO', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_clubformtype';
    }
}
