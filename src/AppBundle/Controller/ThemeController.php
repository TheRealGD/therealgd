<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Entity\ThemeRevision;
use Raddit\AppBundle\Form\Model\ThemeData;
use Raddit\AppBundle\Form\ThemeType;
use Raddit\AppBundle\Repository\ThemeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThemeController extends Controller {
    /**
     * @param ThemeRepository $themeRepository
     * @param int             $page
     *
     * @return Response
     */
    public function listAction(ThemeRepository $themeRepository, int $page) {
        return $this->render('theme/list.html.twig', [
            'themes' => $themeRepository->findAllPaginated($page),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function createAction(Request $request, EntityManager $em) {
        $data = new ThemeData($this->getUser());

        $form = $this->createForm(ThemeType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme = $data->toTheme();

            $em->persist($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_created');

            return $this->redirectToRoute('edit_theme', [
                'name' => $theme->getName(),
                'username' => $theme->getAuthor()->getUsername(),
            ]);
        }

        return $this->render('theme/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Entity("theme", expr="repository.findOneByUsernameAndName(username, name)")
     * @IsGranted("edit", subject="theme")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Theme         $theme
     *
     * @return Response
     */
    public function editAction(Request $request, EntityManager $em, Theme $theme) {
        $data = ThemeData::createFromTheme($theme);
        $form = $this->createForm(ThemeType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateTheme($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_updated');

            return $this->redirectToRoute('edit_theme', [
                'username' => $theme->getAuthor()->getUsername(),
                'name' => $theme->getName(),
            ]);
        }

        return $this->render('theme/edit.html.twig', [
            'form' => $form->createView(),
            'theme' => $theme,
        ]);
    }

    /**
     * @param Theme $theme
     * @param int   $page
     *
     * @return Response
     */
    public function historyAction(Theme $theme, int $page) {
        return $this->render('theme/history.html.twig', [
            'theme' => $theme,
            'revisions' => $theme->getPaginatedRevisions($page),
        ]);
    }

    public function sourceAction(ThemeRevision $revision) {
        return $this->render('theme/source.html.twig', [
            'revision' => $revision,
        ]);
    }

    /**
     * Deliver a raw stylesheet.
     *
     * @param EntityManager $em
     * @param Request       $request
     * @param string        $themeId
     * @param string        $field
     *
     * @return Response
     */
    public function stylesheetAction(
        EntityManager $em,
        Request $request,
        string $themeId,
        string $field
    ) {
        $response = new Response();
        $response->setPublic();
        $response->setExpires(new \DateTime('@'.time().' +2 weeks'));
        $response->setEtag($themeId);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $theme = $em->find(ThemeRevision::class, $themeId);

        if (!$theme) {
            throw new NotFoundHttpException('No such revision');
        }

        $response->setContent($theme->{'get'.ucfirst($field).'Css'}());

        return $response;
    }
}
