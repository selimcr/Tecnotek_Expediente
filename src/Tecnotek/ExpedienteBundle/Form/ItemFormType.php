<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ItemFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('code', 'text', array('trim' => true, 'required' => false))->
            add('name', 'text', array('trim' => true))->
            add('description', 'text', array('required' => false))->
            add('status', 'choice', array('choices' => array(  '1' => 'En inventario', '2' => 'En prestamo'),'required'  => true))->
            add('user', 'entity', array('class' => 'TecnotekExpedienteBundle:User', 'required' => false))->
            add('category', 'entity', array('class' => 'TecnotekExpedienteBundle:CategoryItem'));
    }

    public function getName()
    {
        return 'tecnotek_expediente_itemformtype';
    }
}
