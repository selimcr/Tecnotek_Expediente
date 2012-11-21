<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class GradeFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true, 'required' => true))->
            add('number', 'integer', array('required' => true));
    }

    public function getName()
    {
        return 'tecnotek_expediente_gradeformtype';
    }
}
