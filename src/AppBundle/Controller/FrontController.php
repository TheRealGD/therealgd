<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions that list submissions across many forums.
 */
final class FrontController extends Controller {
    /**
     * View submissions on the front page.
     *
     * @param EntityManager $em
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function frontAction(EntityManager $em, string $sortBy, int $page) {
        $forumRepo = $em->getRepository(Forum::class);
        $submissionRepo = $em->getRepository(Submission::class);

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
     * @param EntityManager $em
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function allAction(EntityManager $em, string $sortBy, int $page) {
        $submissions = $em->getRepository(Submission::class)
            ->findAllSubmissions($sortBy, $page);

        return $this->render('@RadditApp/all.html.twig', [
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    /**
     * Show featured forums.
     *
     * @param EntityManager $em
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function featuredAction(EntityManager $em, string $sortBy, int $page) {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('raddit_app_front');
        }

        $forums = $em->getRepository(Forum::class)->findFeaturedForumNames();

        $submissions = $em->getRepository(Submission::class)
            ->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/featured.html.twig', [
            'forums' => $forums,
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    /**
     * Show from forums the user moderates.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param EntityManager $em
     * @param string        $sortBy
     * @param int           $page
     *
     * @return Response
     */
    public function moderatedAction(EntityManager $em, string $sortBy, int $page) {
        /** @var User $user */
        $user = $this->getUser();

        $forums = $em->getRepository(Forum::class)->findModeratedForumNames($user);

        $submissions = $em->getRepository(Submission::class)
            ->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/moderated.html.twig', [
            'forums' => $forums,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }
}
