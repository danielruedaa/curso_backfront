<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 14/12/18
 * Time: 03:29 PM
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * Create new user this method check if exits the user and check the password
     */
    public function newAction(Request $request){
        //change something to json
        $helpers = $this->get(Helpers::class);

        $json = $request->get('json',null);

        //json to object
        $params = json_decode($json );
        $data = array(
            'status'=>'error',
            'code'=> 400,
            'msg'=>'User not create !! something wrong was happend'
        );

        if($json != null){
          $createdAt = new \Datetime("now");
          $role = 'user';

          $email =(isset($params->email))? $params->email : null;
          $name =(isset($params->name))? $params->name : null;
          $surname =(isset($params->surname))? $params->surname : null;
          $password =(isset($params->password))? $params->password : null;

          $emailConstraint = new Assert\Email();
          $emailConstraint->message = "this email is not valid";
          $validate_email = $this->get('validator')->validate($email,$emailConstraint);

          if($email != null && count($validate_email)== 0 && $password != null && $name != null && $surname != null){
              //Instanciamos to insert new user
              $user = new User();
              $user->setCreatedAt($createdAt);
              $user->setRole($role);
              $user->setEmail($email);
              $user->setName($name);
              $user->setSurname($surname);

              // encriptation password
              $pwd = hash('sha256',$password);
              $user->setPassword($pwd);
              $em= $this->getDoctrine()->getManager();
              $isset_user = $em->getRepository('BackendBundle:User')
                  ->findBy(array("email"=>$email));
              if(count($isset_user)== 0){
                  //save data
                  $em->persist($user);
                  $em->flush();
                  $data = array(
                      'status'=>'succes',
                      'code'=> 200,
                      'msg'=>'New user create !!',
                      'user'=> $user
                  );
              }else{
                  $data = array(
                      'status'=>'error',
                      'code'=> 400,
                      'msg'=>'User not create , duplicate !!'
                  );
              }
          }
        }

        return $helpers->json($data);
    }

    /**
     * @param Request $request
     * @return mixed
     * Edit user
     */
    public function editAction(Request $request){
        //change something to json
        $helpers = $this->get(Helpers::class);

        //autentication user
        $token= $request->get('authorization',null);

        $jwt_auth= $this->get(JwtAuth::class);
        $authChechk= $jwt_auth->checkToken($token);


        if($authChechk){

            $em= $this->getDoctrine()->getManager();
            //get data user identify
            $identity = $jwt_auth->checkToken($token,true);
            //get data user from db
            $user = $em->getRepository('BackendBundle:User')
                ->findOneBy(array(
                    'id'=>$identity->sub
                ));

            // pick data up
            $json = $request->get('json',null);

            //json to object
            $params = json_decode($json );
            // default error
            $data = array(
                'status'=>'error',
                'code'=> 400,
                'msg'=>'User not create !! something wrong was happend'
            );

            if($json != null){


                //$createdAt = new \Datetime("now");
                $role = 'user';

                $email =(isset($params->email))? $params->email : null;
                $name =(isset($params->name))? $params->name : null;
                $surname =(isset($params->surname))? $params->surname : null;
                $password =(isset($params->password))? $params->password : null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "this email is not valid";
                $validate_email = $this->get('validator')->validate($email,$emailConstraint);

                if($email != null && count($validate_email)== 0 && $name != null && $surname != null){
                    //update user
                //    $user->setCreatedAt($createdAt);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);
                    // encriptation password
                    if($password != null){
                        $pwd = hash('sha256',$password);
                        $user->setPassword($pwd);
                    }


                    $isset_user = $em->getRepository('BackendBundle:User')
                        ->findBy(array("email"=>$email));
                    if(count($isset_user)== 0 || $identity->email == $email){
                        $em->persist($user);
                        $em->flush();
                        $data = array(
                            'status'=>'succes',
                            'code'=> 200,
                            'msg'=>'Update user !!',
                            'user'=> $user
                        );
                    }else{
                        $data = array(
                            'status'=>'error',
                            'code'=> 400,
                            'msg'=>'User not update , duplicate !!'
                        );
                    }
                }
            }

        }else{
            $data = array(
                'status'=>'error',
                'code'=> 400,
                'msg'=>'Authorization not valid !!'
            );
        }

        return $helpers->json($data);
    }

}