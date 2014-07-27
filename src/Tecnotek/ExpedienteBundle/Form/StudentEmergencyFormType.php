<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StudentEmergencyFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('emergencyout', 'choice', array('choices' => array(  '1' => 'Si', '2' => 'No'),'required'  => false))->
            add('emergencyoutinst', 'choice', array('choices' => array(  '1' => 'Kinder', '2' => 'Escuela', '3' => 'Colegio'),'required'  => false))->
            add('brethren', 'choice', array('choices' => array(  '1' => 'Si', '2' => 'No'),'required'  => false))->
            add('familiars', 'text', array('trim' => true, 'required' => false))->
            add('emergencyinfo', 'text', array('trim' => true, 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentemergencyformtype';
    }
}
