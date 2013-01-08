<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EntryFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('name', 'text', array('trim' => true))->
            add('code', 'text', array('trim' => true))->
            add('maxValue', 'integer')->
            add('percentage', 'integer')->
            add('sortOrder', 'integer')->
            add('parent');
        ;
    }

    public function getName()
    {
        return 'tecnotek_expediente_entryformtype';
    }
}
