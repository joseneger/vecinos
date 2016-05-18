<?php

namespace JRC\VecinosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class VecinosController extends Controller
{
    public function indexAction($name)
    {
        return new Response('Bienvenido al módulo de Vecinos');
    }
}
