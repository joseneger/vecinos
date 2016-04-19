<?php

namespace JRC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JRC\UserBundle\Entity\User;
use JRC\UserBundle\Form\UserType;

class UserController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $users = $em->getRepository('JRCUserBundle:User')->findAll();
        
        /*$res = 'Lista de usuarios: <br /><br />';
        
        foreach($users as $user)
        {
            $res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
        }
        
        return new Response($res);
        */
        
        return $this->render('JRCUserBundle:User:index.html.twig', array('users' => $users));
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
            
            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $password);
            
            $user->setPassword($encoded);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('jrc_user_index');
        }
        
        return $this->render('JRCUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }
    
    
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JRCUserBundle:User');
        
       $user = $repository->find($id);
       //$user = $repository->findOneById($id);
        
        /*return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
        */
        return $this->render('JRCUserBundle:User:view.html.twig', array('users' => $user));
    }
    
}
