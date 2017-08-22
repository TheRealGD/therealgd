<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Form\Model\ThemeData;
use Raddit\AppBundle\Form\ThemeType;
use Raddit\AppBundle\Repository\ThemeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        return $this->render('@RadditApp/theme_list.html.twig', [
            'themes' => $themeRepository->findAllPaginated($page),
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
        $data = new ThemeData($this->getUser());

        $form = $this->createForm(ThemeType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme = $data->toTheme();

            $em->persist($theme);
            $em->flush();

            $this->addFlash('success', 'flash.theme_created');

            return $this->redirectToRoute('raddit_app_edit_theme', [
                'id' => $theme->getId(),
            ]);
        }

        return $this->render('@RadditApp/theme_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('edit', theme)")
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

            return $this->redirectToRoute('raddit_app_edit_theme', [
                'id' => $theme->getId(),
            ]);
        }

        return $this->render('@RadditApp/theme_edit.html.twig', [
            'form' => $form->createView(),
            'theme' => $theme,
        ]);
    }

    /**
     * Deliver a raw stylesheet.
     *
     * @ParamConverter("unixTime", options={"format": "U"})
     * @ParamConverter("theme", options={"mapping": {"unixTime": "lastModified"}})
     *
     * @param Request   $request
     * @param Theme     $theme
     * @param string    $field
     * @param \DateTime $unixTime
     *
     * @return Response
     */
    public function stylesheetAction(
        Request $request,
        Theme $theme,
        string $field,
        /* @noinspection PhpUnusedParameterInspection */ \DateTime $unixTime
    ) {
        $response = new Response();
        $response->setPublic();
        $response->setLastModified($theme->getLastModified());
        $response->setExpires(new \DateTime('@'.time().' +2 weeks'));

        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setContent($theme->{'get'.ucfirst($field).'Css'}());

        return $response;
    }
}
