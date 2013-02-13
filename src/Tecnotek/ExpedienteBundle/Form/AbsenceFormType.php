<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AbsenceFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('studentYear')->
            add('date', 'date', array('widget' => 'single_text', 'format' => 'dd/MM/yyyy', 'required' => false))->
            add('justify', 'checkbox', array(
            'label'     => 'Justificada?',
            'required'  => false,
            ))->
            add('comments', 'text', array('trim' => true, 'required' => false))
            ;
    }

    public function getName()
    {
        return 'tecnotek_expediente_absenceformtype';
    }
}
