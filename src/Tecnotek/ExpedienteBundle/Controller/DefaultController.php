<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name = "John Doe")
    {
        return $this->redirect($this->generateUrl('TecnotekExpedienteBundle_homepage'));
    }

    public function validateUserPassword($newPassword, $confirmPassword, $translator) {
        if($newPassword != $confirmPassword) return $translator->trans("error.passwords.dont.match");
        if( strlen(trim($newPassword)) == 0 ) return $translator->trans("error.password.emtpy");
        return null;
    }

}
