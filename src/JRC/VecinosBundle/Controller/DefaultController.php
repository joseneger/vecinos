<?php

namespace JRC\VecinosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('JRCVecinosBundle:Default:index.html.twig', array('name' => $name));
    }
}
