<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/12/18
 * Time: 10:51 AM
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Task;

class TaskController  extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * this method allow you create new task and edit task
     * when use 2nd parameter in method newAction
     */
    public function newAction(Request $request, $id = null){
       //check token 'security'
        $helpers = $this->get(Helpers::class);

        $jwt_auth= $this->get(JwtAuth::class);
        $token= $request->get('authorization',null);
        $authChechk= $jwt_auth->checkToken($token);

        if($authChechk){
            $identity = $jwt_auth->checkToken($token,true);
            $json = $request->get('json',null);

            if($json != null){
                //create task
                $params=json_decode($json);
                $createdAT = new \DateTime('now');
                $updateAT = new \DateTime('now');

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title:null;
                $description = (isset($params->description)) ? $params->description:null;
                $status = (isset($params->status)) ? $params->status:null;

                if ($user_id != null && $title != null){

                    $em = $this->getDoctrine()->getManager();
                    //selec user
                    $user = $em->getRepository('BackendBundle:User')
                        ->findOneBy(array('id'=>$user_id));
                    //var_dump($user->getId());
                    //die();
                    if($id == null){
                        $task = new Task();
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);
                        $task->setCreatedAt($createdAT);
                        $task->setUpdatedAt($updateAT);

                        $em->persist($task);
                        $em->flush();

                        $data= array(
                            'status'=>'success',
                            'code'=> 200,
                            "msg"=>'Task ok',
                            "data"=>$task
                        );

                    }else{
                        // search task to edit
                        $task = $em->getRepository('BackendBundle:Task')
                            ->findOneBy(array('id'=>$id));
                        // check user
                        if(isset($identity->sub) && $identity->sub == $task->getUser()->getId()){


                            $task->setTitle($title);
                            $task->setDescription($description);
                            $task->setStatus($status);
                            $task->setUpdatedAt($updateAT);

                            $em->persist($task);
                            $em->flush();

                            $data= array(
                                'status'=>'edit success',
                                'code'=> 200,
                                "msg"=>'Task  update',
                                "data"=>$task
                            );

                        }else{

                            $data= array(
                                'status'=>'error',
                                'code'=> 400,
                                "msg"=>'Task not update, you not owner'
                            );
                        }
                    }


                }


            }else{
                $data= array(
                    'status'=>'error',
                    'code'=> 400,
                    "msg"=>'Task not create, params failed'
                );

            }
        }else{
            $data= array(
                'status'=>'error',
                'code'=> 400,
                "msg"=>'Task fail'
            );

        }
        return $helpers->json($data);
    }

}