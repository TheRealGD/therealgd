<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Stylesheet;
use Raddit\AppBundle\Form\StylesheetType;
use Raddit\AppBundle\Repository\StylesheetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StylesheetController extends Controller {
    /**
     * @param StylesheetRepository $stylesheetRepository
     * @param int                  $page
     *
     * @return Response
     */
    public function listAction(StylesheetRepository $stylesheetRepository, int $page) {
        return $this->render('@RadditApp/stylesheet_list.html.twig', [
            'stylesheets' => $stylesheetRepository->findAllPaginated($page),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function createAction(Request $request, EntityManager $em) {
        $stylesheet = new Stylesheet();

        $form = $this->createForm(StylesheetType::class, $stylesheet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stylesheet->setUser($this->getUser());

            $em->persist($stylesheet);
            $em->flush();

            $this->addFlash('success', 'flash.stylesheet_created');

            return $this->redirectToRoute('raddit_app_edit_stylesheet', [
                'id' => $stylesheet->getId(),
            ]);
        }

        return $this->render('@RadditApp/stylesheet_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('edit', stylesheet)")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Stylesheet    $stylesheet
     *
     * @return Response
     */
    public function editAction(Request $request, EntityManager $em, Stylesheet $stylesheet) {
        $form = $this->createForm(StylesheetType::class, $stylesheet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stylesheet->setTimestamp(new \DateTime('@'.time()));

            $em->flush();

            $this->addFlash('success', 'flash.stylesheet_updated');

            return $this->redirectToRoute('raddit_app_edit_stylesheet', [
                'id' => $stylesheet->getId(),
            ]);
        }

        return $this->render('@RadditApp/stylesheet_edit.html.twig', [
            'form' => $form->createView(),
            'stylesheet' => $stylesheet,
        ]);
    }

    /**
     * Deliver the raw stylesheet.
     *
     * @param Request    $request
     * @param Stylesheet $stylesheet
     *
     * @return Response
     */
    public function rawAction(Request $request, Stylesheet $stylesheet) {
        $response = new Response();
        $response->setPublic();
        $response->setLastModified($stylesheet->getTimestamp());

        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setContent($stylesheet->getCss());

        return $response;
    }
}
