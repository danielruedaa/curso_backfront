<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12/12/18
 * Time: 12:43 PM
 */

namespace AppBundle\Services;

use Firebase\JWT\JWT;


class JwtAuth
{
    public $manager;
    public $key;

    /**
     * JwtAuth constructor.
     * @param $manager
     * construccion para el servicio
     */
    public function __construct($manager)
    {
        $this->manager=$manager;
        $this->key='secretkey159263';
    }

    /**
     * @param $email
     * @param $password
     * @return string
     */
    public function singup($email,$password,$getHash = null){

        $user= $this->manager->getRepository('BackendBundle:User')
            ->findOneBy(array(
                "email"=>$email,
                "password"=>$password)
            );
        $sigup=false;
        if(is_object($user)){
            $sigup=true;
        }

        if($sigup== true){
            // Generar un token JWT
            $token = array(
                "sub"=>$user->getId(),
                "email"=>$user->getEmail(),
                "name"=>$user->getName(),
                "surname"=>$user->getSurname(),
                "iat"=>time(),
                "exp"=>time() + ( 7* 24 * 60 *60)

            );
            // encode token , key ,algoritmo encoder
            $jwt=JWT::encode($token,$this->key,'HS256');
            $decode = JWT::decode($jwt,$this->key,array('HS256'));

            if($getHash == null){
                $data=$jwt;
            }else{
                $data = $decode;
            }

            $data=$jwt;
            /*
             * TEST
            $data= array(
                "status"=> "succes",
                "user"=>$user

            );
            */
        }else{
            $data= array(
                "status"=> "error",
                "data"=>"login failed!!"
            );
        }
        return $data;

    }

    /**
     * @param $jwt
     * @param $getIdentity
     * @return bool|object
     * Check token to login user
     */
    public function checkToken($jwt,$getIdentity =false)
    {
        //$decode= '';
        $auth=false;
        try{
            // get object with method and encripted
            $decode = JWT::decode($jwt,$this->key,array('HS256'));

        }catch (\UnexpectedValueException $e){
            $auth=false;
        }catch (\DomainException $e){
            $auth=false;
        }
        //check method and some data
        if(isset($decode) && is_object($decode ) && isset($decode->sub)){
            $auth=true;
        }else{
            $auth=false;
        }
        //return false or true and complete validation
        if($getIdentity==false){
            return $auth;
        }else{
            return $decode;
        }
    }
}