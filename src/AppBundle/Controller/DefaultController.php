<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Services\Helpers;
use Symfony\Component\Validator\Constraints as Assert;

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
     *test method whit services to become anything to json
     */
    public function pruebasAction()
    {
        $em=$this->getDoctrine()->getManager();
        $userRepo= $em->getRepository('BackendBundle:User');
        $user= $userRepo->findAll();

        $helpers = $this->get(Helpers::class);
        return $helpers->json(array(
                                    'status' => 'succes',
                                    'users'=>$user,
        ));
    }

    /**
     * method to login user whit token
     */
    public function loginAction(Request $request){


        $helpers =$this->get(Helpers::class);

        //Recibir json por post

        $json =$request->get('json', null);
        $data=array(
                    'status' => 'error',
                    'data'   =>'send json via post'
        );
        if($json != null){
            // login
            // json to array php
            $params = json_decode($json);
            $email=(isset($params->email)) ? $params->email: null;//si existe la propidedad
            $password=(isset($params->password)) ? $params->password : null;//si existe la propidedad
            //validacion con symfony
            $emailConstrain = new Assert\Email();
            $emailConstrain->message="This email is not valid";
            $validate_email = $this->get("validator")->validate($email,$emailConstrain);


                if(count($validate_email)== 0 && $password != null){
                    $data=array(
                        'status' => 'succes',
                        'data'   =>'login'
                    );

                }else{
                     $data=array(
                        'status' => 'succes',
                        'data'   =>'email failed'
                     );
                }

        }else{
            //error
            $data=array(
                'status' => 'succes',
                'data'   =>'email not valid'
            );

        }

        return $helpers->json($data);
    }
}
