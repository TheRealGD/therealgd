<?php

namespace App\Controller;

use App\Entity\WikiPage;
use App\Entity\WikiRevision;
use App\Form\Model\WikiData;
use App\Form\WikiType;
use App\Repository\WikiPageRepository;
use App\Repository\WikiRevisionRepository;
use App\Utils\Differ;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WikiController extends AbstractController {
    /**
     * Views a wiki page.
     *
     * @param string             $path
     * @param WikiPageRepository $wikiPageRepository
     *
     * @return Response
     */
    public function wiki(string $path, WikiPageRepository $wikiPageRepository) {
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
     * @IsGranted("ROLE_USER")
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
    public function create(Request $request, string $path, EntityManager $em) {
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
     * @Entity("page", expr="repository.findOneCaseInsensitively(path)")
     * @IsGranted("write", subject="page")
     *
     * @param Request       $request
     * @param WikiPage      $page
     * @param EntityManager $em
     *
     * @return Response
     *
     * @todo handle conflicts
     */
    public function edit(Request $request, WikiPage $page, EntityManager $em) {
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
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request       $request
     * @param WikiPage      $page
     * @param bool          $lock
     * @param EntityManager $em
     *
     * @return Response
     */
    public function lock(Request $request, WikiPage $page, bool $lock, EntityManager $em) {
        $this->validateCsrf('wiki_lock', $request->request->get('token'));

        $page->setLocked($lock);

        $em->flush();

        $this->addFlash('success', 'flash.page_'.($lock ? 'locked' : 'unlocked'));

        if ($request->headers->has('Referer')) {
            return $this->redirect($request->headers->get('Referer'));
        }

        return $this->redirectToRoute('wiki', ['path' => $page->getPath()]);
    }

    /**
     * @Entity("wikiPage", expr="repository.findOneCaseInsensitively(path)")
     *
     * @param WikiPage $wikiPage
     * @param int      $page
     *
     * @return Response
     */
    public function history(WikiPage $wikiPage, int $page) {
        return $this->render('wiki/history.html.twig', [
            'page' => $wikiPage,
            'revisions' => $wikiPage->getPaginatedRevisions($page),
        ]);
    }

    public function diff(Request $request, WikiRevisionRepository $repository) {
        /* @var WikiRevision $from
         * @var WikiRevision $to */
        [$from, $to] = array_map(function ($q) use ($request, $repository) {
            $id = $request->query->get($q);

            if (!Uuid::isValid($id)) {
                throw $this->createNotFoundException();
            }

            $revision = $repository->find($id);

            if (!$revision) {
                throw $this->createNotFoundException();
            }

            return $revision;
        }, ['from', 'to']);

        return $this->render('wiki/diff.html.twig', [
            'diff' => Differ::diff($from->getBody(), $to->getBody()),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * @param WikiRevision $revision
     *
     * @return Response
     */
    public function revision(WikiRevision $revision) {
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
    public function all(int $page, WikiPageRepository $wikiPageRepository) {
        $pages = $wikiPageRepository->findAllPages($page);

        return $this->render('wiki/all.html.twig', [
            'pages' => $pages,
        ]);
    }

    public function recentChanges(WikiRevisionRepository $repository, int $page) {
        return $this->render('wiki/recent.html.twig', [
            'revisions' => $repository->findRecent($page),
        ]);
    }
}
