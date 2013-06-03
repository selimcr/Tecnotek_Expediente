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
            add('lastname', 'text', array('trim' => true, 'required' => false ))->
            add('identification', 'text', array('trim' => true, 'required' => false ))->
        add('phonec', 'text', array('trim' => true, 'required' => false ))->
        add('phonew', 'text', array('trim' => true, 'required' => false ))->
        add('phoneh', 'text', array('trim' => true, 'required' => false ))->
        add('workplace', 'text', array('trim' => true, 'required' => false ))->
        add('email', 'text', array('trim' => true, 'required' => false ))->
        add('adress', 'text', array('trim' => true, 'required' => false ))->
        add('restriction', 'text', array('trim' => true, 'required' => false ));

    }

    public function getName()
    {
        return 'tecnotek_expediente_contactformtype';
    }
}
