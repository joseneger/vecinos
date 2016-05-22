<?php

namespace JRC\VecinosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use JRC\VecinosBundle\Entity\Usuario;
use JRC\VecinosBundle\Form\UsuarioType;

class UsuarioController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchQuery = $request->get('query');
        
        if(!empty($searchQuery)) //busqueda de elasticSearch
        {
            $finder = $this->container->get('fos_elastica.finder.app.usuario');
            $usuarios = $finder->createPaginatorAdapter($searchQuery);
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $dql = "SELECT u FROM JRCVecinosBundle:Usuario u ORDER BY u.id DESC";
            $usuarios = $em->createQuery($dql);
        }
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usuarios, $request->query->getInt('page', 1),
            3
        );
        
        /*$deleteFormAjax = $this->createCustomForm(':USER_ID', 'DELETE', 'jrc_user_delete');
        */
        return $this->render('JRCVecinosBundle:Usuario:index.html.twig', array('pagination' => $pagination));
    }
    
    
    public function addAction()
    {
        $usuario = new Usuario();
        $form = $this->createCreateForm($usuario);
        
        return $this->render('JRCVecinosBundle:Usuario:add.html.twig', array('form' => $form->createView()));
    
    }
    
    private function createCreateForm(Usuario $entity)
    {
        $form = $this->createForm(new UsuarioType(), $entity, array(
                'action' => $this->generateURL('jrc_usuario_create'),
                'method' => 'POST'
            ));
            
            return $form;
    }
    
    public function createAction(Request $request)
    {
        $usuario = new Usuario();
        $form = $this->createCreateForm($usuario);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            $password = $form->get('password')->getData();
            
            $passwordConstraint = new Assert\NotBlank();
            $errorList = $this->get('validator')->validate($password, $passwordConstraint);
            
            if(count($errorList) == 0)
            {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($usuario, $password);
                
                $usuario->setPassword($encoded);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($usuario);
                $em->flush();
                
                $this->addFlash('mensaje', 'El usuario se ha creado correctamente.');
                
                return $this->redirectToRoute('jrc_usuario_index');
            }
            else{
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }
        }
        
        return $this->render('JRCVecinosBundle:Usuario:add.html.twig', array('form' => $form->createView()));
    }
    
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('JRCVecinosBundle:Usuario')->find($id);
        
        if(!$usuario)
        {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }
        
        $form = $this->createEditForm($usuario);
        
        return $this->render('JRCVecinosBundle:Usuario:edit.html.twig', array('usuario' => $usuario, 'form' => $form->createView()));
    }
    
    
    private function createEditForm(Usuario $entity)
    {
        $form = $this->createForm(new UsuarioType(), $entity, array(
                'action' => $this->generateURL('jrc_usuario_update', array('id' => $entity->getId())),
                'method' => 'PUT'
            ));
            
        return $form;
    }
    
    
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('JRCVecinosBundle:Usuario')->find($id);
        
        if(!$usuario)
        {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }
        
        $form = $this->createEditForm($usuario);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $password = $form->get( 'password' )->getData();
            if(!empty($password))
            {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($usuario, $password);
                $usuario->setPassword($encoded);
            }
            else{
                $recoverPass = $this->recoverPass($id);
                $usuario->setPassword($recoverPass[0]['password']);
            }
            
            $em->flush();
            
            $this->addFlash('mensaje', 'El usuario se ha modificado correctamente.');
            return $this->redirectToRoute('jrc_usuario_edit', array('id' => $usuario->getId()));
        }
        return $this->render('JRCVecinosBundle:Usuario:edit.html.twig', array('usuario' => $usuario, 'form' => $form->createView()));
    }
    
    
    private function recoverPass($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT u.password 
            FROM JRCVecinosBundle:Usuario u
            WHERE u.id = :id'    
        )->setParameter('id', $id);
        
        $currentPass = $query->getResult();
        
        return $currentPass;
    }
    
    
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JRCVecinosBundle:Usuario');
        
        $usuario = $repository->find($id);
        
        if(!$usuario)
        {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }
        
        $deleteForm = $this->createCustomForm($usuario->getId(), 'DELETE', 'jrc_usuario_delete');
        
        return $this->render('JRCVecinosBundle:Usuario:view.html.twig', array('usuario' => $usuario, 'delete_form' => $deleteForm->createView()));
    }
    
    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
    
}
