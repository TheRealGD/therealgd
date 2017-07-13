<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineSelectableAdapter;
use Pagerfanta\Pagerfanta;
use Raddit\AppBundle\Entity\Stylesheet;
use Raddit\AppBundle\Form\StylesheetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StylesheetController extends Controller {
    /**
     * @param EntityManager $em
     * @param int           $page
     *
     * @return Response
     */
    public function listAction(EntityManager $em, int $page) {
        $repository = $em->getRepository(Stylesheet::class);
        $criteria = Criteria::create()->orderBy(['timestamp' => 'DESC']);

        $stylesheets = new Pagerfanta(new DoctrineSelectableAdapter($repository, $criteria));
        $stylesheets->setMaxPerPage(25);
        $stylesheets->setCurrentPage($page);

        return $this->render('@RadditApp/stylesheet_list.html.twig', [
            'stylesheets' => $stylesheets,
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

            $this->addFlash('success', 'stylesheets.created_notice');

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

            $this->addFlash('success', 'stylesheets.edited_notice');

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
