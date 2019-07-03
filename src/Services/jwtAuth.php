<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class jwtAuth extends AbstractController {

    public $key = 'jhannkamezaurielmeza1811';

    public function singUp($email, $password, $gettoken = null) {

        $em = $this->getDoctrine()->getManager();


        $user = $em->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);


        $sinup = false;

        if (is_object($user)) {
            $sinup = true;
        }

        if ($sinup) {

            $token = [
                'sub' => $user->getId(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60),
            ];



            $jwt = JWT::encode($token, $this->key, 'HS256');

            if (!empty($gettoken)) {
                $data = $jwt;
            } else {
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);


                $data = $decoded;
            }
        } else {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Login Incorrecto',
            ];
        }
        return $data;
    }

    public function checkToken($jwt, $identity= false) {

        $auth = false;

        
         try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $exc) {
           $auth = false;
        }catch (\DomainException $exc) {
           $auth = false;
        }
        
        

        if (isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($identity != false){
            return $decoded;
        }else{
            return $auth;
        }

    }

}
