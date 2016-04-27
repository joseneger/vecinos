<?php

namespace JRC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use JRC\UserBundle\Entity\User;
use JRC\UserBundle\Form\UserType;

class UserController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchQuery = $request->get('query');
        
        if(!empty($searchQuery)) //busqueda de elasticSearch
        {
            $finder = $this->container->get('fos_elastica.finder.app.user');
            $users = $finder->createPaginatorAdapter($searchQuery);
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $dql = "SELECT u FROM JRCUserBundle:User u ORDER BY u.id DESC";
            $users = $em->createQuery($dql);
        }
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users, $request->query->getInt('page', 1),
            10
        );
        
        $deleteFormAjax = $this->createCustomForm(':USER_ID', 'DELETE', 'jrc_user_delete');
        
        return $this->render('JRCUserBundle:User:index.html.twig', array('pagination' => $pagination, 'delete_form_ajax' => $deleteFormAjax->createView()));
    }
    
    
    public function addAction()
    {
        $user = new User();
        $form = $this->createCreateForm($user);
        
        return $this->render('JRCUserBundle:User:add.html.twig', array('form' => $form->createView()));
    
    }
    
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
                'action' => $this->generateURL('jrc_user_create'),
                'method' => 'POST'
            ));
            
            return $form;
    }
    
    public function createAction(Request $request)
    {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            $password = $form->get('password')->getData();
            
            $passwordConstraint = new Assert\NotBlank();
            $errorList = $this->get('validator')->validate($password, $passwordConstraint);
            
            if(count($errorList) == 0)
            {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                
                $user->setPassword($encoded);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                $this->addFlash('mensaje', 'El usuario se ha creado correctamente.');
                
                return $this->redirectToRoute('jrc_user_index');
            }
            else{
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }
        }
        
        return $this->render('JRCUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }
    
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('JRCUserBundle:User')->find($id);
        
        if(!$user)
        {
            throw $this->createNotFoundException('User not found.');
        }
        
        $form = $this->createEditForm($user);
        
        return $this->render('JRCUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }
    
    
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
                'action' => $this->generateURL('jrc_user_update', array('id' => $entity->getId())),
                'method' => 'PUT'
            ));
            
        return $form;
    }
    
    
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('JRCUserBundle:User')->find($id);
        
        if(!$user)
        {
            throw $this->createNotFoundException('User not found.');
        }
        
        $form = $this->createEditForm($user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $password = $form->get( 'password' )->getData();
            if(!empty($password))
            {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            }
            else{
                $recoverPass = $this->recoverPass($id);
                $user->setPassword($recoverPass[0]['password']);
            }
            
            if($form->get('role')->getData() =='ROLE_ADMIN')
            {
                $user->setIsActive(1);
            }
            
            $em->flush();
            
            $this->addFlash('mensaje', 'El usuario se ha modificado correctamente.');
            return $this->redirectToRoute('jrc_user_edit', array('id' => $user->getId()));
        }
        return $this->render('JRCUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }
    
    
    private function recoverPass($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT u.password 
            FROM JRCUserBundle:User u
            WHERE u.id = :id'    
        )->setParameter('id', $id);
        
        $currentPass = $query->getResult();
        
        return $currentPass;
    }
    
    
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JRCUserBundle:User');
        
        $user = $repository->find($id);
        
        if(!$user)
        {
            throw $this->createNotFoundException('User not found.');
        }
        
        $deleteForm = $this->createCustomForm($user->getId(), 'DELETE', 'jrc_user_delete');
        
        return $this->render('JRCUserBundle:User:view.html.twig', array('user' => $user, 'delete_form' => $deleteForm->createView()));
    }
    
    
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('JRCUserBundle:User')->find($id);
        
        if(!$user)
        {
            throw $this->createNotFoundException('User not found.');
        }
        
        $allUsers = $em->getRepository('JRCUserBundle:User')->findAll();
        $countUsers = count($allUsers);
        
        // $form = $this->createDeleteForm($user);
        $form = $this->createCustomForm($user->getId(), 'DELETE', 'jrc_user_delete');
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if($request->isXMLHttpRequest())
            {
                $res = $this->deleteUser($user->getRole(), $em, $user);
                
                return new Response(
                    json_encode(array('removed' => $res['removed'], 'message' => $res['message'], 'countUsers' => $countUsers)),
                    200,
                    array('Content-Type' => 'application/json')
                );
                
            }
            // $res contiene array con 'message', 'removed' y 'alert'
            $res = $this->deleteUser($user->getRole(), $em, $user);
            
            $this->addFlash($res['alert'], $res['message']);
            return $this->redirectToRoute('jrc_user_index');
            
        }
    }
    
    private function deleteUser($role, $em, $user)
    {
        if($role == 'ROLE_USER')
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
        }
        
        return array('removed' => $removed, 'message' => $message, 'alert' => $alert);
    }
    
    
    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
}
