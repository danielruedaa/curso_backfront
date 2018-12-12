<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12/12/18
 * Time: 10:12 AM
 */

namespace AppBundle\Services;


class Helpers
{
    public $manager;

    public function __construct($manager)
    {

        $this->manager = $manager;

    }

    /**
     * @param $data
     * @return \Symfony\Component\HttpFoundation\Response
     * Cidification any information from db to Json
     */
    public function json($data){
        $normalizers= array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncode());

        $serializer= new \Symfony\Component\Serializer\Serializer($normalizers,$encoders);
        $json = $serializer->serialize($data,'json');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->headers->set('Content-Type','application/json');

        return $response;
    }
}