<?php

namespace App\Controller;

use App\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\User;
use App\Services\jwtAuth;
use Knp\Component\Pager\PaginatorInterface;

class VideoController extends AbstractController
{



    public function createVideo(Request $request,jwtAuth $jwtAuth,$id=null){

        $data = [
            'status' => 'Error',
            'code' => 400,
            'message' => 'El video no ha podido crearse',
        ];

        $token = $request->headers->get('Authorization', null);

        $autCheck = $jwtAuth->checkToken($token);

        if ($autCheck){

            $json = $request->get('json',null);
            $params = json_decode($json);

            $identity = $jwtAuth->checkToken($token, true);

            if (!empty($json)){

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $url = (!empty($params->url)) ? $params->url : null;


                if (!empty($user_id) && !empty($title) && !empty($description) && !empty($url)) {

                    $em = $this->getDoctrine()->getManager();

                    $user = $em->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);


                    if ($id == null) {



                        $video = new Video();
                    $video->setUser($user)->
                    setTitle($title)->
                    setDescription($description)->
                    setUrl($url)->
                    setStatus('normal')->
                    setCreatedAt(new \DateTime('now'))->
                    setUpdatedAt(new \DateTime('now'));

                    $em->persist($video);
                    $em->flush();

                    $data = [
                        'status' => 'Succes',
                        'code' => 200,
                        'message' => 'El video sea aguardado',
                        'video' => $video
                    ];
                }else{
                        $em = $this->getDoctrine()->getManager();

                        $video = $em->getRepository(Video::class)->findOneBy([
                            'id' => $id,
                            'user' => $identity->sub
                        ]);

                        if ($video && is_object($video)){

                            $video -> setTitle($title)->
                            setDescription($description)->
                            setUrl($url)->


                            setUpdatedAt(new \DateTime('now'));

                            $em->persist($video);
                            $em->flush();

                            $data = [
                                'status' => 'Succes',
                                'code' => 200,
                                'message' => 'El video sea actualizado',
                                'video' => $video
                            ];
                        }
                    }
                }

            }
        }




        return new JsonResponse($data);
    }

    /**
     * @Route("/listar/videos", name="listar_video", methods={"GET"})
     */
    public function videos(Request $request,jwtAuth $jwtAuth,PaginatorInterface $paginator){



        $token = $request->headers->get('Authorization', null);

        $autCheck = $jwtAuth->checkToken($token);

        if ($autCheck){
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

           $dql = "SELECT v FROM App\Entity\Video v WHERE v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $em->createQuery($dql);


            $page = $request->query->getInt('page',1);
            $items_per_page = 5;

            $pagination = $paginator->paginate($query,$page,$items_per_page);
            $total = $pagination->getTotalItemCount();




            $data = [
                'status' => 'Success',
                'code' => 200,
                'message' => 'Correcto',
                'total' => $total,
                'actual' => $page,
                'items_per_page' => $items_per_page,
                'total_pages'=> ceil($total / $items_per_page),
                'videos' => $pagination,
                'user_id' => $identity

            ];
        }else{
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'No se pueden listar los videos en este momento',
            ];
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/details/videos/{id}", name="details_video", methods={"GET"})
     */
    public function  detail(Request $request,jwtAuth $jwtAuth,$id = null)
    {


        $data = [
            'status' => 'Error',
            'code' => 404,
            'message' => 'Video no encontrado',

        ];

        $token = $request->headers->get('Authorization', null);

        $autCheck = $jwtAuth->checkToken($token);

        if ($autCheck) {

            $identity = $jwtAuth->checkToken($token, true);

            $video = $this->getDoctrine()->getManager()->getRepository(Video::class)->findOneBy([
                'id' => $id
            ]);

            if ($video && is_object($video) && $identity->sub == $video->getUser()->getId()){

                $data = [
                    'status' => 'Succes',
                    'code' => 200,
                    'video' => $video,

                ];
            }




        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/remove/videos/{id}", name="remove_video", methods={"DELETE"})
     */
    public function remove(Request $request,jwtAuth $jwtAuth,$id = null){


        $data = [
            'status' => 'Error',
            'code' => 404,
            'message' => 'Video no encontrado',

        ];

        $token = $request->headers->get('Authorization', null);

        $autCheck = $jwtAuth->checkToken($token);

        if ($autCheck){

            $identity = $jwtAuth->checkToken($token, true);

            $video = $this->getDoctrine()->getManager()->getRepository(Video::class)->findOneBy([
                'id' => $id
            ]);

            if ($video && is_object($video) && $identity->sub == $video->getUser()->getId()){

                $em = $this->getDoctrine()->getManager();

                $em->remove($video);
                $em->flush();

                $data = [
                    'status' => 'Succes',
                    'code' => 200,
                    'video' => $video,

                ];
            }




        }

        return new JsonResponse($data);
    }
}
