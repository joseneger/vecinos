<?php

namespace JRC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 
use JRC\UserBundle\Entity\Task;
use JRC\UserBundle\Form\TaskType;


class TaskController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT t FROM JRCUserBundle:Task t ORDER BY t.id DESC";
        $tasks = $em->createQuery($dql);
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $tasks,
            $request->query->getInt('page', 1),
            3
        );
        
        return $this->render('JRCUserBundle:Task:index.html.twig', array('pagination' => $pagination));
    }
    
    public function addAction()
    {
        $task = new Task();
        $form = $this->createCreateForm($task);
        
        return $this->render('JRCUserBundle:Task:add.html.twig', array ('form' => $form->createView()));
    }
    
    private function createCreateForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateURL('jrc_task_create'),
            'method' => 'POST'
        ));
        
        return $form;
    }
    
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createCreateForm($task);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            $task->setStatus(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
            
            $this->addFlash('mensaje', 'Se ha creado la tarea.');
            return $this->redirectToRoute('jrc_task_index');
        }
        else{
            $this->addFlash('mensaje', 'La tarea no se ha podido crear, el formulario no es valido.');
        }
        
        return $this->render('JRCUserBundle:Task:add.html.twig', array('form' => createView()));
    }
}
