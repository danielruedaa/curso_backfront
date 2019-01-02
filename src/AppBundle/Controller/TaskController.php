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

    /**
     * @param Request $request
     * @return mixed
     * list task
     */
    public function tasksAction(Request $request){

        //check token 'security'
        $helpers = $this->get(Helpers::class);

        $jwt_auth= $this->get(JwtAuth::class);
        $token= $request->get('authorization',null);
        $authChechk= $jwt_auth->checkToken($token);

        if($authChechk) {
            $identity = $jwt_auth->checkToken($token, true);
            $json = $request->get('json', null);

            $em = $this->getDoctrine()->getManager();
            // query sql
            $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = {$identity->sub} ORDER BY t.id DESC";
            /*
            $query= $em->createQueryBuilder();
            $query->select('t');
            $query->from('BackendBundle:Task','t');
            $query->orderBy('t.id','ASC');
            */
            //$rDb[]=$query->getResult();
            $query = $em->createQuery($dql);

            // give query to paginator

            $page = $request->query->getInt('page',1);
            $paginator= $this->get('knp_paginator');
            $items_per_page = 10;

            $pagination= $paginator->paginate($query,$page,$items_per_page);

            $total_items_count= $pagination->getTotalItemCount();



            $data=array(
                'status'=>'success',
                'code'=> 200,
                'total'=> $total_items_count,
                'page_actual'=> $page,
                'items_per_page'=>$items_per_page,
                'total_pages'=> ceil($total_items_count/$items_per_page),
                'data' => $pagination

            );
            /**
             * falta poner 'data'=>$paginator que tiene la info de las tareas
             * no se por que no funciona. cuando funciona lista la hora muchas veces
             *
             *
             */

        }else{
            $data=array(
                'status'=>'error',
                'code'=> 400,
                'msg'=> 'Authorization not valid'
            );

        }

        return $helpers->json($data);

    }

    /**
     * @param Request $request
     * @param null $id
     * @return mixed
     * return task detail
     */
    public function taskAction(Request $request, $id = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                'id' => $id
            ));

            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
                $data = array(
                    'status' => 'success',
                    'code'   => 200,
                    'data'	 => $task
                );
            }else{
                $data = array(
                    'status' => 'error',
                    'code'   => 404,
                    'msg'	 => 'Task not found'
                );
            }

        }else{
            $data = array(
                'status' => 'error',
                'code'   => 400,
                'msg'	 => 'Authorization not valid'
            );
        }

        return $helpers->json($data);
    }

    /**
     * @param Request $request
     * @param null $search
     * @return mixed
     * filter whe you use the search
     */
    public function searchAction(Request $request, $search = null){
        $helpers= $this->get(Helpers::class);
        $jwt_auth= $this->get(JwtAuth::class);

        $token = $request->get('authorization',null);
        $authCheck= $jwt_auth->checkToken($token,true);

        if($authCheck){
            $identity= $jwt_auth->checkToken($token,true);

            $em= $this->getDoctrine()->getManager();

            // filtro

            $filter = $request->get('filter',null);


            if(empty($filter)){
                $filter=null;
            }elseif ($filter == 1){
                $filter = 'new';
            }elseif ($filter == 2){
                $filter ='todo';
            }else{
                $filter='finished';
            }


            // orden

            $order = $request->get('order', null);
            if(empty($order) || $order==1){
                $order = 'DESC';

            }else{
                $order= 'ASC';
            }


            // busqueda


            if($search != null){
                $dql = "SELECT t FROM BackendBundle:Task t "
                    ."WHERE t.user = $identity->sub AND "
                    ."(t.title LIKE :search OR t.description LIKE :search)";



            }else{
                $dql = "SELECT t FROM BackendBundle:Task t "
                    ."WHERE t.user = $identity->sub";

            }

            // set filter
            if($filter != null){
                $dql .= "AND t.status = :filter";
            }

            // set order
            $dql.= " ORDER BY t.id $order";

            // create query
            $query = $em->createQuery($dql);

            // sset parameter filter
            if($filter != null){
                $query->setParameter('filter',"filter");
            }


            //set search

            if(!empty($search)){
                $query->setParameter('search',"%$search%");
            }

            $task = $query->getResult();

            $data=array(
                'status'=>'succes',
                'code'=> 200,
                'data'=> $task
            );



        }else{
            $data=array(
                'status'=>'error',
                'code'=> 404,
                'msg'=> 'task not found'
            );
        }


        return $helpers->json($data);

    }

    /**
     * @param Request $request
     * @param null $id
     * @return mixed
     * remove some task
     */
    public function removeAction(Request $request, $id = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                'id' => $id
            ));

            if($task && is_object($task) && $identity->sub == $task->getUser()->getId()){
                // Borrar objeto y borrar registro de la tabla bbdd
                $em->remove($task);
                $em->flush();

                $data = array(
                    'status' => 'success',
                    'code'   => 200,
                    'data'	 => $task
                );
            }else{
                 $data = array(
                    'status' => 'error',
                    'code'   => 404,
                    'msg'	 => 'Task not found'
                );
            }

        }else{
            $data = array(
                'status' => 'error',
                'code'   => 400,
                'msg'	 => 'Authorization not valid'
            );
        }

        return $helpers->json($data);
    }
}