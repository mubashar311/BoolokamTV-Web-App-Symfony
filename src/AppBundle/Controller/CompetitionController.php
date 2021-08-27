<?php
namespace AppBundle\Controller;
use AppBundle\Form\CompetitionType;
use MediaBundle\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Competition;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CompetitionController extends Controller
{

    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $q = " 1=1 ";
        if ($request->query->has("q") and $request->query->get("q") != "") {
            $q .= " AND  a.name like '%" . $request->query->get("q") . "%'";
        }

        $dql = "SELECT a FROM AppBundle:Competition a  WHERE  " . $q . " ORDER BY a.id desc ";
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $competitions = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        $competitions_count = $em->getRepository('AppBundle:Competition')->count();
        return $this->render('AppBundle:Competition:index.html.twig', array("competitions_count" => $competitions_count, "competitions" => $competitions));
    }

    public function addAction(Request $request)
    {

        $Competition= new Competition();
        $form = $this->createForm(CompetitionType::class,$Competition);
        $em=$this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if( $Competition->getFile()!=null ){
                $media= new Media();
                $media->setFile($Competition->getFile());
                $media->upload($this->container->getParameter('files_directory'));
                $em->persist($media);
                $em->flush();
                $Competition->setMedia($media);

                $em->persist($Competition);
                $em->flush();
                $this->addFlash('success', 'Operation has been done successfully');
                return $this->redirect($this->generateUrl('app_competetion_index'));
            }else{
                $error = new FormError("Required image file");
                $form->get('file')->addError($error);
            }
        }
        return $this->render("AppBundle:Competition:add.html.twig",array("form"=>$form->createView()));
    }

    public function deleteAction($id,Request $request){
        $em=$this->getDoctrine()->getManager();

        $Competition = $em->getRepository("AppBundle:Competition")->find($id);
        if($Competition==null){
            throw new NotFoundHttpException("Page not found");
        }
        $form=$this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->add('Yes', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $media_old = $Competition->getMedia();
            $em->remove($Competition);
            $em->flush();
            if( $media_old!=null ){
                $media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($media_old);
                $em->flush();
            }
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_competetion_index'));
        }
        return $this->render('AppBundle:Competition:delete.html.twig',array("form"=>$form->createView()));
    }
    public function editAction(Request $request,$id)
    {
        $em=$this->getDoctrine()->getManager();
        $Competition=$em->getRepository("AppBundle:Competition")->find($id);
        if ($Competition==null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createForm(CompetitionType::class,$Competition);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if( $Competition->getFile()!=null ){
                $media= new Media();
                $media_old=$Competition->getMedia();
                $media->setFile($Competition->getFile());
                $media->upload($this->container->getParameter('files_directory'));
                $em->persist($media);
                $em->flush();
                $Competition->setMedia($media);
                $media_old->delete($this->container->getParameter('files_directory'));
                $em->remove($media_old);
                $em->flush();
            }
            $em->persist($Competition);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_competetion_index'));

        }
        return $this->render("AppBundle:Competition:edit.html.twig",array("Competition"=>$Competition,"form"=>$form->createView()));
    }


    public function api_competition_by_filtresAction(Request $request,$token, $page=1)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $nombre = 30;
        $em = $this->getDoctrine()->getManager();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $repository = $em->getRepository('AppBundle:Competition');
        $dir = "DESC";
        $query = $repository->createQueryBuilder('p')
            ->where("p.visible = true")
            ->addOrderBy('p.id', $dir)
            ->setFirstResult($nombre * ($page-1))
            ->setMaxResults($nombre)
            ->getQuery();
        $competitions_list = $query->getResult();

        $result = [];
        /**
         *
         * @var Competition $competition
         */
        foreach ($competitions_list as $competition){
            $media = $imagineCacheManager->getBrowserPath("uploads/".$competition->getMedia()->getExtension()."/".$competition->getMedia()->getImage(), 'actor_thumb');
            $result[] = [
                'id' => $competition->getId(),
                'title' => $competition->getTitle(),
                'description' => $competition->getDescription(),
                'startDate' => date_format($competition->getStartDate(),'d/m/Y'),
                'endDate' => date_format($competition->getEndDate(),'d/m/Y'),
                'nbrVotes' => $competition->getNbrVotes(),
                'media' =>  $media
            ];
        }


        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function api_competition_allAction(Request $request,$token, $page=1)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Competition');
        $dir = "DESC";
        $query = $repository->createQueryBuilder('p')
            ->where("p.visible = true")
            ->addOrderBy('p.id', $dir)
            ->getQuery();
        $competitions_list = $query->getResult();
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');
        $result = [];

        /**
         *
         * @var Competition $competition
         */
        foreach ($competitions_list as $competition){

            $topMovies = [];
            $competitionMovies = $competition->getVotesByMovies();
            if (is_array($competitionMovies)){
                uasort($competitionMovies,function ($a,$b){
                    return $b - $a;
                });
                $competitionMovies = array_slice($competitionMovies, 0, 10,true);
            }

            else
                $competitionMovies=[];
            foreach ($competitionMovies as $k => $vote){
                $repositoryMovie = $em->getRepository('AppBundle:Poster');

                $m = $repositoryMovie->findOneBy(['id'=>$k]);
                if ($m)
                    $topMovies[] =[
                      'id' => $m->getId(),
                      'title' => $m->getTitle(),
                      'image' => $imagineCacheManager->getBrowserPath($m->getPoster()->getLink(), 'poster_thumb'),
                      'nbrVotes' => $vote
                    ];
            }

            $media = $imagineCacheManager->getBrowserPath($competition->getMedia()->getLink(), 'actor_thumb');
            $result[] = [
                'id' => $competition->getId(),
                'title' => $competition->getTitle(),
                'description' => $competition->getDescription(),
                'startDate' => date_format($competition->getStartDate(),'d/m/Y'),
                'endDate' => date_format($competition->getEndDate(),'d/m/Y'),
                'nbrVotes' => $competition->getNbrVotes(),
                'media' => $media,
                'topMovies' => $topMovies
            ];

        }


        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    public function api_competition_total_votesAction(Request $request,$token)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Competition');
        $dir = "DESC";
        $query = $repository->createQueryBuilder('p')
            ->select('SUM(p.nbrVotes)')
            ->where("p.visible = true")
            ->addOrderBy('p.id', $dir)
            ->getQuery();
        $competitions_votes = (int)$query->getSingleScalarResult();
        $result = ['totalVotes'=>$competitions_votes];

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function api_competition_detailsAction(Request $request,$token,$id)
    {
        if ($token!=$this->container->getParameter('token_app')) {
            throw new NotFoundHttpException("Page not found");
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Competition');
        if(is_null($id))
            $competition = $repository->findOneBy(['visible'=>true]);
        else
            $competition = $repository->findOneBy(['id'=>$id]);

        $imagineCacheManager = $this->get('liip_imagine.cache.manager');

        $topMovies = [];
        $competitionMovies = $competition->getVotesByMovies();
        if (is_array($competitionMovies)){
            uasort($competitionMovies,function ($a,$b){
                return $b - $a;
            });
        //    $competitionMovies = array_slice($competitionMovies, 0, 100,true);
        }

        else
            $competitionMovies=[];
        foreach ($competitionMovies as $k => $vote){
            $repositoryMovie = $em->getRepository('AppBundle:Poster');

            $m = $repositoryMovie->findOneBy(['id'=>$k]);
            if ($m)
                $topMovies[] =[
                    'id' => $m->getId(),
                    'title' => $m->getTitle(),
                    'image' => $imagineCacheManager->getBrowserPath($m->getPoster()->getLink(), 'poster_thumb'),
                    'nbrVotes' => $vote
                ];
        }

        $media = $imagineCacheManager->getBrowserPath($competition->getMedia()->getLink(), 'actor_thumb');
        $result = [
            'id' => $competition->getId(),
            'title' => $competition->getTitle(),
            'description' => $competition->getDescription(),
            'startDate' => date_format($competition->getStartDate(),'d/m/Y'),
            'endDate' => date_format($competition->getEndDate(),'d/m/Y'),
            'nbrVotes' => $competition->getNbrVotes(),
            'media' => $media,
            'topMovies' => $topMovies
        ];

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
?>