<?php

namespace BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        echo "backend";
        return $this->render('BackendBundle:Default:index.html.twig');
    }
}
