<?php

namespace AppBundle\Controller;

use AppBundle\Form\ThemeSettingsType;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Theme;
use AppBundle\Entity\ThemeRevision;
use AppBundle\Form\Model\ThemeData;
use AppBundle\Form\ThemeCssType;
use AppBundle\Repository\ThemeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThemeController extends AbstractController {
    /**
     * @param ThemeRepository $themeRepository
     * @param int             $page
     *
     * @return Response
     */
    public function list(ThemeRepository $themeRepository, int $page) {
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
    public function create(Request $request, EntityManager $em) {
        $data = new ThemeData($this->getUser());

        $form = $this->createForm(ThemeSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme = $data->toTheme();

            $em->persist($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_created');

            return $this->redirectToRoute('edit_theme_css', [
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
    public function editCss(Request $request, EntityManager $em, Theme $theme) {
        $data = ThemeData::createFromTheme($theme);
        $form = $this->createForm(ThemeCssType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateTheme($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_updated');

            return $this->redirectToRoute('edit_theme_css', [
                'username' => $theme->getAuthor()->getUsername(),
                'name' => $theme->getName(),
            ]);
        }

        return $this->render('theme/edit_css.html.twig', [
            'form' => $form->createView(),
            'theme' => $theme,
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
    public function editSettings(Request $request, EntityManager $em, Theme $theme) {
        $data = ThemeData::createFromTheme($theme);
        $form = $this->createForm(ThemeSettingsType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateTheme($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_updated');

            return $this->redirectToRoute('edit_theme_settings', [
                'username' => $theme->getAuthor()->getUsername(),
                'name' => $theme->getName(),
            ]);
        }

        return $this->render('theme/edit_settings.html.twig', [
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
    public function history(Theme $theme, int $page) {
        return $this->render('theme/history.html.twig', [
            'theme' => $theme,
            'revisions' => $theme->getPaginatedRevisions($page),
        ]);
    }

    public function source(ThemeRevision $revision) {
        return $this->render('theme/source.html.twig', [
            'revision' => $revision,
        ]);
    }

    public function stylesheet(EntityManager $em, Request $request, string $themeId, string $field) {
        $response = new Response();
        $response->setPublic();
        $response->setImmutable();
        $response->setMaxAge(86400 * 365);
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
