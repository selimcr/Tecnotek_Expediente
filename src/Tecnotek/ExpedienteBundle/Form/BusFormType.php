<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BusFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true))->
            add('licensePlate', 'text', array('trim' => true))->
            add('color', 'text', array('trim' => true))->
            add('driver', 'text', array('trim' => true))->
            add('telephone', 'text', array('trim' => true))->
            add('capacity', 'integer')->
            add('riteve', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))->
            add('ins', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))->
            add('permission', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_busformtype';
    }
}
