<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('firstname', 'text', array('trim' => true))->
            add('lastname', 'text', array('trim' => true))->
            add('identification', 'text', array('trim' => true))->
        add('phonec', 'text', array('trim' => true))->
        add('phonew', 'text', array('trim' => true))->
        add('phoneh', 'text', array('trim' => true))->
        add('workplace', 'text', array('trim' => true))->
        add('email', 'text', array('trim' => true))->
        add('adress', 'text', array('trim' => true))->
        add('restriction', 'text', array('trim' => true));

    }

    public function getName()
    {
        return 'tecnotek_expediente_contactformtype';
    }
}
