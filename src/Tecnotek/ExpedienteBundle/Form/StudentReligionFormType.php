<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StudentReligionFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('rbautizado', 'text', array('trim' => true, 'required' => false))->
            add('rtomo', 'text', array('trim' => true, 'required' => false))->
            add('rfolio', 'text', array('trim' => true, 'required' => false))->
            add('rasiento', 'text', array('trim' => true, 'required' => false))->
            add('rpromesasfecha', 'text', array('trim' => true, 'required' => false))->
            add('rpromesaslugar', 'text', array('trim' => true, 'required' => false))->
            add('rconfesionfecha', 'text', array('trim' => true, 'required' => false))->
            add('rconfesionlugar', 'text', array('trim' => true, 'required' => false))->
            add('rcomunionfecha', 'text', array('trim' => true, 'required' => false))->
            add('rcomunionlugar', 'text', array('trim' => true, 'required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentformtype';
    }
}
