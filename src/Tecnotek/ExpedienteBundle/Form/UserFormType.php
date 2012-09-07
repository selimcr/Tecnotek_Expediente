<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('firstname', 'text', array('trim' => true))->
            add('lastname', 'text', array('trim' => true))->
            add('username', 'text', array('trim' => true))->
            add('email', 'email')->
            add('active', 'checkbox', array(
                'label'     => 'Active?',
                'required'  => false,
                ))->
            add('password', 'repeated', array (
            'type'            => 'password',
            'first_name'      => "Password",
            'second_name'     => "Re-enter Password",
            'invalid_message' => "Los passwords no coinciden!"
        ));
    }

    public function getName()
    {
        return 'tecnotek_expediente_userformtype';
    }
}
