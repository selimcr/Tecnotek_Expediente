<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StudentExtraPointsFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('studentYear')->
            add('course', 'entity', array('class' => 'TecnotekExpedienteBundle:Course', 'required' => false))->
            add('typePoints', 'choice', array('choices' => array(  '1' => 'Puntos extras', '2' => 'Puntos de traslado'),'required'  => true))->
            add('points', 'integer', array('required' => true))
        ;
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentExtraPointsformtype';
    }
}
