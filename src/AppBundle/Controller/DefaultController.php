<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     *
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     *
     */
    public function pruebasAction()
    {
        $em=$this->getDoctrine()->getManager();
        $userRepo= $em->getRepository('BackendBundle:User');
        $user= $userRepo->findAll();

        return new JsonResponse(array(
                                        'status' => 'succes',
                                        'users'=>$user[0],
                                        ));

    }
}
