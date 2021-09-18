<?php

namespace AppBundle\Controller;


use AppBundle\Entity\MainSliderMovie;
use AppBundle\Entity\TrendingMovie;
use AppBundle\Form\MainSliderMovieType;
use AppBundle\Form\TrendingMovieType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class TrendingMovieController extends Controller {
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $slides = $em->getRepository("AppBundle:TrendingMovie")->findBy(array(), array("position" => "asc"));
        return $this->render("AppBundle:TrendingMovie:index.html.twig", array("slides" => $slides));
    }
    public function api_allAction() {
        $em = $this->getDoctrine()->getManager();
        $slides = $em->getRepository("AppBundle:TrendingMovie")->findBy(array(), array("position" => "asc"));

        $posters_list = [];
        foreach ($slides as $slide)
            $posters_list[] = $slide->getPoster();

        return $this->render('AppBundle:Movie:api_all.html.php', array("posters_list" => $posters_list));
    }
    public function upAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $slide = $em->getRepository("AppBundle:TrendingMovie")->find($id);
        if ($slide == null) {
            throw new NotFoundHttpException("Page not found");
        }
        if ($slide->getPosition() > 1) {
            $p = $slide->getPosition();
            $slides = $em->getRepository('AppBundle:TrendingMovie')->findAll();
            foreach ($slides as $key => $value) {
                if ($value->getPosition() == $p - 1) {
                    $value->setPosition($p);
                }
            }
            $slide->setPosition($slide->getPosition() - 1);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('app_trending_movie_index'));
    }
    public function downAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $slide = $em->getRepository("AppBundle:TrendingMovie")->find($id);
        if ($slide == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $max = 0;
        $slides = $em->getRepository('AppBundle:TrendingMovie')->findBy(array(), array("position" => "asc"));
        foreach ($slides as $key => $value) {
            $max = $value->getPosition();
        }
        if ($slide->getPosition() < $max) {
            $p = $slide->getPosition();
            foreach ($slides as $key => $value) {
                if ($value->getPosition() == $p + 1) {
                    $value->setPosition($p);
                }
            }
            $slide->setPosition($slide->getPosition() + 1);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('app_trending_movie_index'));
    }
    public function deleteAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();

        $slide = $em->getRepository("AppBundle:TrendingMovie")->find($id);
        if ($slide == null) {
            throw new NotFoundHttpException("Page not found");
        }
        $form = $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->add('Yes', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($slide);
            $em->flush();

            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_trending_movie_index'));
        }
        return $this->render('AppBundle:TrendingMovie:delete.html.twig', array("form" => $form->createView()));
    }
    public function addAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $slide = new TrendingMovie();
        $form = $this->createForm(TrendingMovieType::class, $slide);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $max = 0;
            $slides = $em->getRepository('AppBundle:TrendingMovie')->findBy(array(), array("position" => "asc"));
            foreach ($slides as $key => $value) {
                if ($value->getPosition() > $max) {
                    $max = $value->getPosition();
                }
            }
            $slide->setPosition($max + 1);
            $em->persist($slide);
            $em->flush();
            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_trending_movie_index'));
        }
        return $this->render('AppBundle:TrendingMovie:add.html.twig', array("form" => $form->createView()));
    }
    public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $slide = $em->getRepository("AppBundle:TrendingMovie")->find($id);
        if ($slide == null) {
            throw new NotFoundHttpException("Page not found");
        }

        $form = $this->createForm(TrendingMovieType::class, $slide);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($slide);
            $em->flush();

            $this->addFlash('success', 'Operation has been done successfully');
            return $this->redirect($this->generateUrl('app_trending_movie_index'));

        }
        return $this->render("AppBundle:TrendingMovie:add.html.twig", array("form" => $form->createView()));
    }
}