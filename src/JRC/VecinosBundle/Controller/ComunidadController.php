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
    
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $comunidad = $em->getRepository('JRCVecinosBundle:Comunidad')->find($id);
        
        if(!$comunidad)
        {
            throw $this->createNotFoundException('Comunidad no encontrada.');
        }
        
        $form = $this->createEditForm($comunidad);
        
        return $this->render('JRCVecinosBundle:Comunidad:edit.html.twig', array('comunidad' => $comunidad, 'form' => $form->createView()));
    }
    
    
    private function createEditForm(Comunidad $entity)
    {
        $form = $this->createForm(new ComunidadType(), $entity, array(
                'action' => $this->generateURL('jrc_comunidad_update', array('id' => $entity->getId())),
                'method' => 'PUT'
            ));
            
        return $form;
    }
    
    
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $comunidad = $em->getRepository('JRCVecinosBundle:Comunidad')->find($id);
        
        if(!$comunidad)
        {
            throw $this->createNotFoundException('Comunidad no encontrada.');
        }
        
        $form = $this->createEditForm($comunidad);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            
            $this->addFlash('mensaje', 'La comunidad se ha modificado correctamente.');
            return $this->redirectToRoute('jrc_comunidad_edit', array('id' => $comunidad->getId()));
        }
        return $this->render('JRCVecinosBundle:Comunidad:edit.html.twig', array('comunidad' => $comunidad, 'form' => $form->createView()));
    }
    
    
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JRCVecinosBundle:Comunidad');
        
        $comunidad = $repository->find($id);
        
        if(!$comunidad)
        {
            throw $this->createNotFoundException('Comunidad no encontrado.');
        }
        
        $deleteForm = $this->createCustomForm($comunidad->getId(), 'DELETE', 'jrc_comunidad_delete');
        
        return $this->render('JRCVecinosBundle:Comunidad:view.html.twig', array('comunidad' => $comunidad, 'delete_form' => $deleteForm->createView()));
    }
    
    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
    
    
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comunidad = $em->getRepository('JRCVecinosBundle:Comunidad')->find($id);
        
        if(!$comunidad)
        {
            throw $this->createNotFoundException('Comunidad no encontrada.');
        }
        
        $comunidades = $em->getRepository('JRCVecinosBundle:Comunidad')->findAll();
        $countComunidades = count($comunidades);
        
        // $form = $this->createDeleteForm($user);
        $form = $this->createCustomForm($comunidad->getId(), 'DELETE', 'jrc_comunidad_delete');
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if($request->isXMLHttpRequest())
            {
                $res = $this->deleteComunidad($em, $comunidad);
                
                return new Response(
                    json_encode(array('removed' => $res['removed'], 'message' => $res['message'], 'countUsers' => $countUsers)),
                    200,
                    array('Content-Type' => 'application/json')
                );
                
            }
            // $res contiene array con 'message', 'removed' y 'alert'
            $res = $this->deleteComunidad($em, $comunidad);
            
            $this->addFlash($res['alert'], $res['message']);
            return $this->redirectToRoute('jrc_comunidad_index');
            
        }
    }
    
    private function deleteComunidad($em, $comunidad)
    {
        /*if($role == 'ROLE_USER')
        {
            $em->remove($user);
            $em->flush();
            
            $message = 'El usuario ha sido eliminado';
            $removed = 1;
            $alert = 'mensaje';
        }
        elseif($role == 'ROLE_ADMIN')
        {
            $message = 'El usuario NO ha sido eliminado';
            $removed = 0;
            $alert = 'error';
        }*/
        $em->remove($comunidad);
        $em->flush();
            
        $message = 'La comunidad ha sido eliminada';
        $removed = 1;
        $alert = 'mensaje';
        
        return array('removed' => $removed, 'message' => $message, 'alert' => $alert);
    }
}