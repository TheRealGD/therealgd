<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\WikiPage;
use Raddit\AppBundle\Entity\WikiRevision;
use Raddit\AppBundle\Form\Model\WikiData;
use Raddit\AppBundle\Form\WikiType;
use Raddit\AppBundle\Repository\WikiPageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WikiController extends Controller {
    /**
     * Views a wiki page.
     *
     * @param string             $path
     * @param WikiPageRepository $wikiPageRepository
     *
     * @return Response
     */
    public function wikiAction(string $path, WikiPageRepository $wikiPageRepository) {
        $page = $wikiPageRepository->findOneCaseInsensitively($path);

        if (!$page) {
            return $this->render('wiki/404.html.twig', [
                'path' => $path,
            ], new Response('', 404));
        }

        return $this->render('wiki/page.html.twig', [
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
        $data = new WikiData();

        $form = $this->createForm(WikiType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page = $data->toPage($path, $this->getUser());

            $em->persist($page);
            $em->flush();

            return $this->redirectToRoute('wiki', ['path' => $path]);
        }

        return $this->render('wiki/create.html.twig', [
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
        $data = WikiData::createFromPage($page);
        $form = $this->createForm(WikiType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updatePage($page, $this->getUser());

            $em->flush();

            return $this->redirectToRoute('wiki', [
                'path' => $page->getPath(),
            ]);
        }

        return $this->render('wiki/edit.html.twig', [
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
     * @param WikiPage $wikiPage
     * @param int      $page
     *
     * @return Response
     */
    public function historyAction(WikiPage $wikiPage, int $page) {
        return $this->render('wiki/history.html.twig', [
            'page' => $wikiPage,
            'revisions' => $wikiPage->getPaginatedRevisions($page),
        ]);
    }

    /**
     * @param WikiRevision $revision
     *
     * @return Response
     */
    public function revisionAction(WikiRevision $revision) {
        return $this->render('wiki/revision.html.twig', [
            'page' => $revision->getPage(),
            'revision' => $revision,
        ]);
    }

    /**
     * @param int                $page
     * @param WikiPageRepository $wikiPageRepository
     *
     * @return Response
     */
    public function allAction(int $page, WikiPageRepository $wikiPageRepository) {
        $pages = $wikiPageRepository->findAllPages($page);

        return $this->render('wiki/all.html.twig', [
            'pages' => $pages,
        ]);
    }
}
