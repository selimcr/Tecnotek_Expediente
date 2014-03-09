<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CategoryItemFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('code', 'text', array('trim' => true, 'required' => false))->
            add('name', 'text', array('trim' => true))->
            add('description', 'text', array('required' => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_category_itemformtype';
    }
}
