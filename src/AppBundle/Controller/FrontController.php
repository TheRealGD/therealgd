<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Repository\ForumRepository;
use Raddit\AppBundle\Repository\SubmissionRepository;
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
     * @param ForumRepository      $fr
     * @param SubmissionRepository $sr
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function frontAction(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        if ($this->isGranted('ROLE_USER')) {
            $forums = $fr->findSubscribedForumNames($this->getUser());
            $hasSubscriptions = count($forums) > 0;
        }

        if (empty($forums)) {
            $forums = $fr->findFeaturedForumNames();
        }

        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/front.html.twig', [
            'sort_by' => $sortBy,
            'forums' => $forums,
            'has_subscriptions' => $hasSubscriptions ?? false,
            'submissions' => $submissions,
        ]);
    }

    /**
     * @param SubmissionRepository $sr
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function allAction(SubmissionRepository $sr, string $sortBy, int $page) {
        $submissions = $sr->findAllSubmissions($sortBy, $page);

        return $this->render('@RadditApp/all.html.twig', [
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    /**
     * Show featured forums.
     *
     * @param ForumRepository      $fr
     * @param SubmissionRepository $sr
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function featuredAction(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('raddit_app_front');
        }

        $forums = $fr->findFeaturedForumNames();
        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

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
     * @param ForumRepository      $fr
     * @param SubmissionRepository $sr
     * @param string               $sortBy
     * @param int                  $page
     *
     * @return Response
     */
    public function moderatedAction(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        /** @var User $user */
        $user = $this->getUser();

        $forums = $fr->findModeratedForumNames($user);
        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('@RadditApp/moderated.html.twig', [
            'forums' => $forums,
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }
}
