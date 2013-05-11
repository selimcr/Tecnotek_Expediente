<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StudentFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('firstname', 'text', array('trim' => true))->
            add('lastname', 'text', array('trim' => true, 'required' => false))->
            add('carne', 'text', array('trim' => true))->
            add('gender', 'choice', array('choices' => array(  '1' => 'Hombre', '2' => 'Mujer'),'required'  => true))->
            add('age', 'integer', array('required' => false))->
            add('address', 'text', array('trim' => true, 'required' => false))->
            add('fatherPhone', 'text', array('trim' => true, 'required' => false))->
            add('motherPhone', 'text', array('trim' => true, 'required' => false))->
            add('pickUp', 'text', array('trim' => true, 'required' => false))->
            add('leaveTime', 'text', array('trim' => true, 'required' => false))->
            add('birthday', 'text', array('trim' => true, 'required' => false))->
            add('admission', 'text', array('trim' => true, 'required' => false))->
            add('identification', 'text', array('trim' => true, 'required' => false))->
            add('observation', 'text', array('trim' => true, 'required' => false))->
            add('route', 'entity', array('class' => 'TecnotekExpedienteBundle:Route', 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentformtype';
    }
}
