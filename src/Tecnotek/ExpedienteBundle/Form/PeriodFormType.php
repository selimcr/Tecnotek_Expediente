<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PeriodFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true, 'required' => true))->
            add('year', 'integer', array('required' => true))->
            add('actual', 'checkbox', array(
                'label'     => 'Periodo Actual',
                'required'  => false,
            ));
    }

    public function getName()
    {
        return 'tecnotek_expediente_periodformtype';
    }
}
