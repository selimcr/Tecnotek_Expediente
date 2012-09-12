<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RouteFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('code', 'integer')->
            add('name', 'text', array('trim' => true))->
            add('description', 'text', array('trim' => true, 'required' => false))->
            add('mapUrl', 'text', array('trim' => true, 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_routeformtype';
    }
}
