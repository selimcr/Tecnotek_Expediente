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
            add('lastname', 'text', array('trim' => true))->
            add('carne', 'text', array('trim' => true))->
            add('address', 'text', array('trim' => true))->
            add('fatherPhone', 'text', array('trim' => true))->
            add('motherPhone', 'text', array('trim' => true))->
            add('pickUp', 'text', array('trim' => true))->
            add('leaveTime', 'text', array('trim' => true))->
            add('route');
        ;
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentformtype';
    }
}
