<?php

namespace JRC\VecinosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JRC\VecinosBundle\Entity\Comunidad;
use JRC\VecinosBundle\Form\ComunidadType;

class ComunidadController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchQuery = $request->get('query');
        
        if(!empty($searchQuery)) //busqueda de elasticSearch
        {
            $finder = $this->container->get('fos_elastica.finder.app.comunidad');
            $comunidades = $finder->createPaginatorAdapter($searchQuery);
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $dql = "SELECT c FROM JRCVecinosBundle:Comunidad c ORDER BY c.id DESC";
            $comunidades = $em->createQuery($dql);
        }
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $comunidades, $request->query->getInt('page', 1),
            3
        );
        
        /*$deleteFormAjax = $this->createCustomForm(':USER_ID', 'DELETE', 'jrc_user_delete');
        */
        return $this->render('JRCVecinosBundle:Comunidad:index.html.twig', array('pagination' => $pagination));
    }
    
    
    public function addAction()
    {
        $comunidad = new Comunidad();
        $form = $this->createCreateForm($comunidad);
        
        return $this->render('JRCVecinosBundle:Comunidad:add.html.twig', array('form' => $form->createView()));
    
    }
    
    private function createCreateForm(Comunidad $entity)
    {
        $form = $this->createForm(new ComunidadType(), $entity, array(
                'action' => $this->generateURL('jrc_comunidad_create'),
                'method' => 'POST'
            ));
            
            return $form;
    }
    
    public function createAction(Request $request)
    {
        $comunidad = new Comunidad();
        $form = $this->createCreateForm($comunidad);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
                $em = $this->getDoctrine()->getManager();
                $em->persist($comunidad);
                $em->flush();
                
                $this->addFlash('mensaje', 'La comunidad se ha creado correctamente.');
                
                return $this->redirectToRoute('jrc_comunidad_index');
        }
        
        return $this->render('JRCVecinosBundle:Comunidad:add.html.twig', array('form' => $form->createView()));
    }
}