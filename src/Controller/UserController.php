<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use App\Entity\User;
use App\Services\jwtAuth;

class UserController extends AbstractController {

    /**
     * @Route("/user", name="user")
     */
    public function index() {
        return new JsonResponse([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request) {


        $json = $request->get('json', null);

        $params = json_decode($json);

        $data = [
            'status' => 'Error',
            'code' => 400,
            'message' => 'El usuario ya existe',
        ];

        if ($json != null) {

            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();

            $validar_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && count($validar_email) == 0 && !empty($name) && !empty($surname) && !empty($password)) {



                $user = new User();

                $pwd = hash('sha256', $password);

                $user->setName($name)->setSurname($surname)->
                        setEmail($email)->setRole('ROLE_USER')->
                        setCreatedAt(new \DateTime('now'))->
                        setPassword($pwd);

                $em = $this->getDoctrine()->getManager();

                $user_repo = $em->getRepository(User::class);

                $user_exist = $user_repo->findBy(['email' => $email]);

                if (count($user_exist) == 0) {

                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'El usuario creado exitoso',
                    ];
                } else {
                    $data = [
                        'status' => 'Error',
                        'code' => 400,
                        'message' => 'El usuario ya existe',
                    ];
                }
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, jwtAuth $jwt_auth) {


        $json = $request->get('json', null);

        $params = json_decode($json);

        $data = [
            'status' => 'Error',
            'code' => 400,
            'message' => 'El usuario no se apodido identificar',
        ];

        if ($json != null) {

            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $gettoken = (!empty($params->gettoken)) ? $params->gettoken : null;

            $validator = Validation::createValidator();

            $validar_email = $validator->validate($email, [
                new Email()
            ]);


            if (!empty($email) && count($validar_email) == 0 && !empty($password)) {

                $pwd = hash('sha256', $password);



                if ($gettoken) {
                    $signup = $jwt_auth->singUp($email, $pwd, $gettoken);
                } else {
                    $signup = $jwt_auth->singUp($email, $pwd);
                }

                return new JsonResponse($signup);
            }
        }
        return new JsonResponse($data);
    }
    
      /**
     * @Route("/edit", name="edit")
     */
    public function edit(Request $request, jwtAuth $jwt_auth) {
        
        
       

        $token = $request->headers->get('Authorization');
        
        $authCheck = $jwt_auth->checkToken($token);

        $data = [
            'status' => 'Error',
            'code' => 400,
            'message' => 'Usuario No Actualizado',
            'token' => $authCheck
        ];


        if ($authCheck) {

            $em = $this->getDoctrine()->getManager();

            $identity = $jwt_auth->checkToken($token, true);

            $use_repo = $this->getDoctrine()->getRepository(User::class);

            $user = $use_repo->findOneBy([
               'id' => $identity->sub
            ]);

            $json = $request->get('json',null);

            $params = json_decode($json);


            //var_dump($params);die;

            if (!empty($json)){
                $name = (!empty($params->name)) ? $params->name : null;
                $surname = (!empty($params->surname)) ? $params->surname : null;
                $email = (!empty($params->email)) ? $params->email : null;


                $validator = Validation::createValidator();

                $validar_email = $validator->validate($email, [
                    new Email()
                ]);

                if (!empty($email) && count($validar_email) == 0 && !empty($name) && !empty($surname)) {

                    $user->setEmail($email)->setName($name)->setSurname($surname);

                    $isset_user = $use_repo->findBy([
                        'email' => $email
                    ]);

                    if (count($isset_user) == 0 || $identity == $email){

                        $em->persist($user);
                        $em->flush();

                        $data = [
                            'status' => 'Success',
                            'code' => 200,
                            'message' => 'Usuario  Actualizado',
                            'user' => $user

                        ];
                    }else{
                        $data = [
                            'status' => 'Error',
                            'code' => 400,
                            'message' => 'No puedes usar ese email ',

                        ];
                    }
                }
            }
        }
        

         
         
          return new JsonResponse($data);
    }
    
    
   

}
