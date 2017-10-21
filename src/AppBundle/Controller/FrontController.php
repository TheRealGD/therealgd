<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Repository\ForumRepository;
use Raddit\AppBundle\Repository\SubmissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions that list submissions across many forums.
 *
 * Notes:
 *
 * - Using the {@link Controller::forward()} method kills performance, so we
 *   call the action methods manually instead.
 *
 * - Security annotations aren't used, since we need to call the action methods
 *   manually.
 *
 * - The subscribed listing is special in that it will show featured forums when
 *   there are no subscriptions. This is because new users won't have any
 *   subscriptions, but 'subscribed' is still the default listing for logged-in
 *   users. To avoid showing them a blank page, we show them the featured forums
 *   instead.
 */
final class FrontController extends Controller {
    public function front(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $listing = User::FRONT_FEATURED;
        } elseif ($user->getFrontPage() === 'default') {
            $listing = User::FRONT_SUBSCRIBED;
        } else {
            $listing = $user->getFrontPage();
        }

        switch ($listing) {
        case User::FRONT_SUBSCRIBED:
            return $this->subscribed($fr, $sr, $sortBy, $page);
        case User::FRONT_FEATURED:
            return $this->featured($fr, $sr, $sortBy, $page);
        case User::FRONT_ALL:
            return $this->all($sr, $sortBy, $page);
        case User::FRONT_MODERATED:
            return $this->moderated($fr, $sr, $sortBy, $page);
        default:
            throw new \InvalidArgumentException('bad front page selection');
        }
    }

    public function featured(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        $forums = $fr->findFeaturedForumNames();
        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('front/featured.html.twig', [
            'forums' => $forums,
            'listing' => 'featured',
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    public function subscribed(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $forums = $fr->findSubscribedForumNames($this->getUser());
        $hasSubscriptions = count($forums) > 0;

        if (!$hasSubscriptions) {
            $forums = $fr->findFeaturedForumNames();
        }

        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('front/subscribed.html.twig', [
            'forums' => $forums,
            'has_subscriptions' => $hasSubscriptions,
            'listing' => 'subscribed',
            'sort_by' => $sortBy,
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
    public function all(SubmissionRepository $sr, string $sortBy, int $page) {
        $submissions = $sr->findAllSubmissions($sortBy, $page);

        return $this->render('front/all.html.twig', [
            'listing' => 'all',
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function moderated(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $forums = $fr->findModeratedForumNames($this->getUser());
        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('front/moderated.html.twig', [
            'forums' => $forums,
            'listing' => 'moderated',
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function featuredFeed(ForumRepository $fr, SubmissionRepository $sr, string $sortBy, int $page = 1) {
        $forums = $fr->findFeaturedForumNames();
        $submissions = $sr->findFrontPageSubmissions($forums, $sortBy, $page);

        return $this->render('front/featured.xml.twig', [
            'forums' => $forums,
            'submissions' => $submissions,
        ]);
    }
}
