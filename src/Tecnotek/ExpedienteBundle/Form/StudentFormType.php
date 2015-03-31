<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class StudentFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('firstname', 'text', array('trim' => true))->
            add('lastname', 'text', array('trim' => true, 'required' => false))->
            add('carne', 'text', array('trim' => true))->
            add('gender', 'choice', array('choices' => array(  '1' => 'Hombre', '2' => 'Mujer'),'required'  => true))->
            add('laterality', 'choice', array('choices' => array(  '1' => 'Diestro', '2' => 'Zurdo'),'required'  => true))->
            add('age', 'integer', array('required' => false))->
            add('address', 'text', array('trim' => true, 'required' => false))->
            add('religion', 'choice', array('choices' => array(  '01' => 'Catolica', '02' => 'Evangelica', '03' => 'Adventista', '04' => 'Mormon', '05' => 'Musulman', '06' => 'Cristiano',  '07' => 'Judio',  '08' => 'Bautista',  '09' => 'Episcopal', '99' => 'Por Definir'),'required'  => false))->
            add('nacionality', 'choice', array('choices' => array(  '01' => 'Costarricense', '02' => 'Panama', '03' => 'USA', '04' => 'Peruano', '09' => 'Chino', '10' => 'Colombiano', '11' => 'Nicaraguense', '12' => 'Canadiense', '13' => 'Argentino', '14' => 'Cubano', '99' => 'Otro'),'required'  => false))->
            add('fatherPhone', 'text', array('trim' => true, 'required' => false))->
            add('motherPhone', 'text', array('trim' => true, 'required' => false))->
            add('pickUp', 'text', array('trim' => true, 'required' => false))->
            add('leaveTime', 'text', array('trim' => true, 'required' => false))->
            add('birthday', 'text', array('trim' => true, 'required' => false))->
            add('admission', 'text', array('trim' => true, 'required' => false))->
            add('identification', 'text', array('trim' => true, 'required' => false))->
            add('observation', 'text', array('trim' => true, 'required' => false))->
            add('route', 'entity', array('class' => 'TecnotekExpedienteBundle:Route', 'required' => false))->
            add('routeIn', 'entity', array('class' => 'TecnotekExpedienteBundle:Route', 'required' => false))->
            add('routeType', 'choice', array('choices' => array(  '1' => 'Completo', '2' => 'Medio'),'required'  => false));
    }

    public function getName()
    {
        return 'tecnotek_expediente_studentformtype';
    }
}
