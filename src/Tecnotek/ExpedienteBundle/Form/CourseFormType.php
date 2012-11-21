<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CourseFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true, 'required' => true))->
            add('type', 'choice', array('choices' =>
                array(  '1' => 'Simple', '2' => 'Reprobatoria'),'required'  => true));
    }

    public function getName()
    {
        return 'tecnotek_expediente_courseformtype';
    }
}
