<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name = "John Doe")
    {
        return $this->render('TecnotekExpedienteBundle:Default:index.html.twig', array('name' => $name));
    }
}
