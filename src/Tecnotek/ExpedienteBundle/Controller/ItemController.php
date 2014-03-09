<?php

namespace Tecnotek\ExpedienteBundle\Controller;

use Tecnotek\ExpedienteBundle\Entity\Absence;
use Tecnotek\ExpedienteBundle\Entity\Contact;
use Tecnotek\ExpedienteBundle\Entity\StudentExtraTest;
use Tecnotek\ExpedienteBundle\Entity\Club as Club;
use Tecnotek\ExpedienteBundle\Entity\Item;
use Tecnotek\ExpedienteBundle\Entity\CategoryItem;
use Tecnotek\ExpedienteBundle\Entity\Relative as Relative;
use Tecnotek\ExpedienteBundle\Entity\Student;
use Tecnotek\ExpedienteBundle\Entity\StudentPenalty;
use Tecnotek\ExpedienteBundle\Entity\StudentToRoute;
use Tecnotek\ExpedienteBundle\Entity\Ticket;
use Tecnotek\ExpedienteBundle\Form\ContactFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    /* Metodos para CRUD de Items */
    public function itemListAction($rowsPerPage = 30)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $text = $this->get('request')->query->get('text');
        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE i.name like '%$text%'";
        }

        $dql = "SELECT i FROM TecnotekExpedienteBundle:Item i" . $sqlText;
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');


        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(i) FROM TecnotekExpedienteBundle:Item i" . $sqlText;
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3, 'text' => $text
        ));
    }

    public function itemCreateAction()
    {
        $entity = new Item();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ItemFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function itemShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Item")->find($id);

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/show.html.twig', array('entity' => $entity,
            'menuIndex' => 3));
    }

    public function itemSaveAction(){
        $entity  = new Item();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ItemFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_item', array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/new.html.twig', array(
                'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
            ));
        }
    }

    public function itemDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Item")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_item'));
    }

    public function itemEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Item")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ItemFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function itemUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:Item")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\ItemFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_item_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:Item/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_item'));
        }

    }
    /* Final de los metodos para CRUD de items*/


    /* Metodos para CRUD de category_items */
    public function category_itemListAction($rowsPerPage = 30)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $text = $this->get('request')->query->get('text');
        $sqlText = "";
        if(isset($text) && $text != "") {
            $sqlText = " WHERE ci.name like '%$text%'";
        }

        $dql = "SELECT ci FROM TecnotekExpedienteBundle:CategoryItem ci" . $sqlText;
        $query = $em->createQuery($dql);

        $param = $this->get('request')->query->get('rowsPerPage');


        if(isset($param) && $param != "")
            $rowsPerPage = $param;

        $dql2 = "SELECT count(ci) FROM TecnotekExpedienteBundle:CategoryItem ci" . $sqlText;
        $page = $this->getPaginationPage($dql2, $this->get('request')->query->get('page', 1), $rowsPerPage);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page/*page number*/,
            $rowsPerPage/*limit per page*/
        );

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/list.html.twig', array(
            'pagination' => $pagination, 'rowsPerPage' => $rowsPerPage, 'menuIndex' => 3, 'text' => $text
        ));
    }

    public function category_itemCreateAction()
    {
        $entity = new CategoryItem();
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CategoryItemFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/new.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function category_itemShowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:CategoryItem")->find($id);

        return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/show.html.twig', array('entity' => $entity,
            'menuIndex' => 3));
    }

    public function category_itemSaveAction(){
        $entity  = new CategoryItem();
        $request = $this->getRequest();
        $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CategoryItemFormType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('_expediente_sysadmin_category_item', array('id' => $entity->getId())));
        } else {
            return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/new.html.twig', array(
                'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
            ));
        }
    }

    public function category_itemDeleteAction($id){
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:Item")->find( $id );
        if ( isset($entity) ) {
            $em->remove($entity);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_expediente_sysadmin_item'));
    }

    public function category_itemEditAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository("TecnotekExpedienteBundle:CategoryItem")->find($id);
        $form   = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CategoryItemFormType(), $entity);
        return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/edit.html.twig', array('entity' => $entity,
            'form'   => $form->createView(), 'menuIndex' => 3));
    }

    public function category_itemUpdateAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        $entity = $em->getRepository("TecnotekExpedienteBundle:CategoryItem")->find( $request->get('id'));

        if ( isset($entity) ) {
            $request = $this->getRequest();
            $form    = $this->createForm(new \Tecnotek\ExpedienteBundle\Form\CategoryItemFormType(), $entity);
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('_expediente_sysadmin_category_item_show_simple') . "/" . $entity->getId());
            } else {
                return $this->render('TecnotekExpedienteBundle:SuperAdmin:CategoryItem/edit.html.twig', array(
                    'entity' => $entity, 'form'   => $form->createView(), 'menuIndex' => 3
                ));
            }
        } else {
            return $this->redirect($this->generateUrl('_expediente_sysadmin_category_item'));
        }

    }
    /* Final de los metodos para CRUD de category_items*/

    public function getPaginationPage($dql, $currentPage, $rowsPerPage){
        if(isset($currentPage) == false || $currentPage <= 1){
            return 1;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery($dql);
            $total = $query->getSingleScalarResult();
            //Check if current page has Results
            if( $total > (($currentPage - 1) * $rowsPerPage)){//the page has results
                return $currentPage;
            } else {
                $x = intval($total / $rowsPerPage);
                if($x == 0){
                    return 1;
                } else {
                    if( $total % ($x * $rowsPerPage) > 0){
                        return $x+1;
                    } else {
                        return $x;
                    }
                }
            }
        }
    }
}
