<?php

namespace Tecnotek\ExpedienteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->
            add('firstname', 'text', array('trim' => true))->
            add('lastname', 'text', array('trim' => true, 'required' => false ))->
            add('birthday', 'text', array('trim' => true, 'required' => false ))->
            add('nationality', 'choice', array('choices' => array(  '01' => 'Costarricense', '02' => 'Panama', '03' => 'USA', '04' => 'Peruano', '09' => 'Chino', '10' => 'Colombiano', '11' => 'Nicaraguense', '99' => 'Otro'),'required'  => false))->
            add('identification', 'text', array('trim' => true, 'required' => false ))->
            add('m_status', 'choice', array('choices' => array(  '01' => 'Soltero', '02' => 'Casado', '03' => 'Divorciado', '04' => 'Viudo', '05' => 'Union Libre'),'required'  => false))->
            add('religion', 'choice', array('choices' => array(  '01' => 'Catolica', '02' => 'Evangelica', '03' => 'Adventista', '04' => 'Mormon', '05' => 'Musulman', '06' => 'Cristiano', '99' => 'Por Definir'),'required'  => false))->
            add('degree', 'choice', array('choices' => array(  '1' => 'Secundaria incompleto', '2' => 'Secundaria completo', '3' => 'Universitario incompleto', '4' => 'Bachiller', '5' => 'Licenciatura', '6' => 'Maestría', '7' => 'Doctorado'),'required'  => false))->
            add('profession', 'text', array('trim' => true, 'required' => false ))->
            add('workplace', 'text', array('trim' => true, 'required' => false ))->
            add('occupation', 'text', array('trim' => true, 'required' => false ))->
            add('sector', 'choice', array('choices' => array(  '1' => 'Publico', '2' => 'Privado'),'required'  => false))->
            add('phonec', 'text', array('trim' => true, 'required' => false ))->
            add('phoneh', 'text', array('trim' => true, 'required' => false ))->
            add('phonew', 'text', array('trim' => true, 'required' => false ))->
            add('adress', 'text', array('trim' => true, 'required' => false ))->
            add('email', 'text', array('trim' => true, 'required' => false ))->
            add('sector', 'integer')->
            add('degree', 'integer')->
            add('restriction', 'text', array('trim' => true, 'required' => false ));

    }

    public function getName()
    {
        return 'tecnotek_expediente_contactformtype';
    }
}
