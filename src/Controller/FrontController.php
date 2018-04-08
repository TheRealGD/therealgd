<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ForumRepository;
use App\Repository\SubmissionRepository;
use App\Repository\Submission\SubmissionPager;
use Symfony\Component\HttpFoundation\Request;
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
final class FrontController extends AbstractController {
    /**
     * @var ForumRepository
     */
    private $forums;

    /**
     * @var SubmissionRepository
     */
    private $submissions;

    public function __construct(
        ForumRepository $forums,
        SubmissionRepository $submissions
    ) {
        $this->forums = $forums;
        $this->submissions = $submissions;
    }

    public function front(string $sortBy, Request $request): Response {
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
            return $this->subscribed($sortBy, $request);
        case User::FRONT_FEATURED:
            return $this->featured($sortBy, $request);
        case User::FRONT_ALL:
            return $this->all($sortBy, $request);
        case User::FRONT_MODERATED:
            return $this->moderated($sortBy, $request);
        default:
            throw new \InvalidArgumentException('bad front page selection');
        }
    }

    public function featured(string $sortBy, Request $request): Response {
        $forums = $this->forums->findFeaturedForumNames();

        $submissions = $this->submissions->findSubmissions($sortBy, [
            'forums' => array_keys($this->forums->findFeaturedForumNames()),
        ], $this->submissionPage($sortBy, $request));

        return $this->render('front/featured.html.twig', [
            'forums' => $forums,
            'listing' => 'featured',
            'submissions' => $submissions,
            'sort_by' => $sortBy,
        ]);
    }

    public function subscribed(string $sortBy, Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $forums = $this->forums->findSubscribedForumNames($this->getUser());
        $hasSubscriptions = \count($forums) > 0;

        if (!$hasSubscriptions) {
            $forums = $this->forums->findFeaturedForumNames();
        }

        $submissions = $this->submissions->findSubmissions($sortBy, [
            'forums' => array_keys($forums),
        ], $this->submissionPage($sortBy, $request));

        return $this->render('front/subscribed.html.twig', [
            'forums' => $forums,
            'has_subscriptions' => $hasSubscriptions,
            'listing' => 'subscribed',
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function all(string $sortBy, Request $request): Response {
        $submissions = $this->submissions->findSubmissions($sortBy, [],
            $this->submissionPage($sortBy, $request));

        return $this->render('front/all.html.twig', [
            'listing' => 'all',
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function moderated(string $sortBy, Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $forums = $this->forums->findModeratedForumNames($this->getUser());

        $submissions = $this->submissions->findSubmissions($sortBy, [
            'forums' => array_keys($forums),
        ], $this->submissionPage($sortBy, $request));

        return $this->render('front/moderated.html.twig', [
            'forums' => $forums,
            'listing' => 'moderated',
            'sort_by' => $sortBy,
            'submissions' => $submissions,
        ]);
    }

    public function featuredFeed(string $sortBy, Request $request): Response {
        $forums = $this->forums->findFeaturedForumNames();

        $submissions = $this->submissions->findSubmissions($sortBy, [
            'forums' => array_keys($forums),
        ], $this->submissionPage($sortBy, $request));

        return $this->render('front/featured.xml.twig', [
            'forums' => $forums,
            'submissions' => $submissions,
        ]);
    }
}
