<?php

namespace JRC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
