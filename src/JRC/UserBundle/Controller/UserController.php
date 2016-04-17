<?php

namespace JRC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $users = $em->getRepository('JRCUserBundle:User')->findAll();
        
        $res = 'Lista de usuarios: <br /><br />';
        
        foreach($users as $user)
        {
            $res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
        }
        
        return new Response($res);
    }
    
    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JRCUserBundle:User');
        
       $user = $repository->find($id);
       //$user = $repository->findOneById($id);
        
        return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
    }
    
}
