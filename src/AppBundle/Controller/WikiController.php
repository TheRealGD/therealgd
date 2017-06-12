<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\WikiPage;
use Raddit\AppBundle\Entity\WikiRevision;
use Raddit\AppBundle\Form\Model\Wiki;
use Raddit\AppBundle\Form\WikiType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WikiController extends Controller {
    /**
     * Views a wiki page.
     *
     * @param string        $path
     * @param EntityManager $em
     *
     * @return Response
     */
    public function wikiAction(string $path, EntityManager $em) {
        $page = $em->getRepository(WikiPage::class)->findOneCaseInsensitively($path);

        if (!$page) {
            return $this->render('@RadditApp/wiki_404.html.twig', [
                'path' => $path
            ], new Response('', 404));
        }

        return $this->render('@RadditApp/wiki.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * Creates a wiki page.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param string        $path
     * @param EntityManager $em
     *
     * @return Response
     *
     * @todo handle conflicts
     * @todo do something if the page already exists
     */
    public function createAction(Request $request, string $path, EntityManager $em) {
        $model = new Wiki();

        $form = $this->createForm(WikiType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page = new WikiPage();
            $page->setPath($path);

            $revision = new WikiRevision();
            $revision->setPage($page);
            $revision->setUser($this->getUser());
            $revision->setTitle($model->title);
            $revision->setBody($model->body);

            $page->setCurrentRevision($revision);

            $em->persist($page);
            $em->flush();

            return $this->redirectToRoute('raddit_app_wiki', ['path' => $path]);
        }

        return $this->render('@RadditApp/wiki_create.html.twig', [
            'form' => $form->createView(),
            'path' => $path,
        ]);
    }

    /**
     * Edits a wiki page.
     *
     * @ParamConverter("page", options={
     *     "map_method_signature": true,
     *     "repository_method": "findOneCaseInsensitively"
     * })
     *
     * @Security("is_granted('write', page)")
     *
     * @param Request       $request
     * @param WikiPage      $page
     * @param EntityManager $em
     *
     * @return Response
     *
     * @todo handle conflicts
     */
    public function editAction(Request $request, WikiPage $page, EntityManager $em) {
        $model = Wiki::createFromPage($page);
        $form = $this->createForm(WikiType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $revision = new WikiRevision();
            $revision->setPage($page);
            $revision->setUser($this->getUser());
            $revision->setTitle($model->title);
            $revision->setBody($model->body);

            $page->setCurrentRevision($revision);

            $em->flush();

            return $this->redirectToRoute('raddit_app_wiki', [
                'path' => $page->getPath(),
            ]);
        }

        return $this->render('@RadditApp/wiki_edit.html.twig', [
            'form' => $form->createView(),
            'page' => $page,
        ]);
    }

    /**
     * @ParamConverter("wikiPage", options={
     *     "map_method_signature": true,
     *     "repository_method": "findOneCaseInsensitively"
     * })
     *
     * @param WikiPage      $wikiPage
     * @param int           $page
     * @param EntityManager $em
     *
     * @return Response
     */
    public function historyAction(WikiPage $wikiPage, int $page, EntityManager $em) {
        $revisions = $em->getRepository(WikiRevision::class)
            ->findRevisionsForPage($wikiPage, $page);

        return $this->render('@RadditApp/wiki_history.html.twig', [
            'page' => $wikiPage,
            'revisions' => $revisions,
        ]);
    }

    /**
     * @param int           $page
     * @param EntityManager $em
     *
     * @return Response
     */
    public function allAction(int $page, EntityManager $em) {
        $pages = $em->getRepository(WikiPage::class)->findAllPages($page);

        return $this->render('@RadditApp/wiki_all.html.twig', [
            'pages' => $pages,
        ]);
    }
}
