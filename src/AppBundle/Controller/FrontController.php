<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions that list submissions across many forums.
 */
final class FrontController extends Controller {
    /**
     * View submissions on the front page.
     *
     * @param ObjectManager $om
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function frontAction(ObjectManager $om, string $sortBy, int $page) {
        $forumRepo = $om->getRepository(Forum::class);
        $submissionRepo = $om->getRepository(Submission::class);

        if ($this->isGranted('ROLE_USER')) {
            $forums = $forumRepo->findSubscribedForumNames($this->getUser());
            $hasSubscriptions = count($forums) > 0;
        }

        if (empty($forums)) {
            $forums = $forumRepo->findFeaturedForumNames();
        }

        $submissions = $submissionRepo->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/front.html.twig', [
            'sort_by' => $sortBy,
            'forums' => $forums,
            'has_subscriptions' => $hasSubscriptions ?? false,
            'submissions' => $submissions,
        ]);
    }

    /**
     * @param ObjectManager $om
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function allAction(ObjectManager $om, string $sortBy, int $page) {
        $submissions = $om->getRepository(Submission::class)
            ->findAllSubmissions($sortBy, $page);

        return $this->render('@RadditApp/all.html.twig', [
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    /**
     * Show featured forums.
     *
     * @param ObjectManager $om
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function featuredAction(ObjectManager $om, string $sortBy, int $page) {
        $forums = $om->getRepository(Forum::class)->findFeaturedForumNames();

        $submissions = $om->getRepository(Submission::class)
            ->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/featured.html.twig', [
            'forums' => $forums,
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }
}
