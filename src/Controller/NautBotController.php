<?php

namespace App\Controller;

use App\Entity\UserGroup;
use App\Entity\User;
use App\Entity\Forum;
use App\Form\UserGroupType;
use App\Form\RateLimitType;
use App\Form\Model\UserGroupData;
use App\Form\Model\RateLimitData;
use App\Repository\UserGroupRepository;
use App\Repository\UserRepository;
use App\Repository\RateLimitRepository;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Entity("forum", expr="repository.findOneOrRedirectToCanonical(forum_name, 'forum_name')")
 * @Entity("group", expr="repository.findOneOrRedirectToCanonical(group_name, 'group_name')")
 */
final class NautBotController extends AbstractController {
    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param RateLimitRepository $rlr
     * @param int            $page
     * @param Request        $request
     *
     * @return Response
     */
    public function info(RateLimitRepository $rlr, Forum $forum, int $page, Request $request) {
        return $this->render('nautbot/info.html.twig', [
            'page' => $page,
            'forum' => $forum,
            'rates' => $rlr->findPaginated($forum, $page),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Forum         $forum
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function addLimit(RateLimitRepository $rlr, Forum $forum, UserGroup $group = null, Request $request, EntityManager $em): Response {
        $data = new RateLimitData();
        $edit = false;
        $data->setForum($forum);
        $data->setGroup($group);
        $existing = $rlr->getRateLimit($group, $forum);
        if ($existing !== false) {
            $data->setRate($existing->getRate());
            $edit = true;
        }

        $form = $this->createForm(RateLimitType::class, $data, array('group' => $group, 'rates' => $rlr->getRatesGroups($forum)));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rate = $rlr->getRateLimit($data->getGroup(), $data->getForum());
            if ($rate !== false) {
                $rate->setRate($data->getRate());
            } else {
                $rate = $data->toRateLimit();
            }

            $em->persist($rate);
            $em->flush();

            return $this->redirectToRoute('nautbot', ['forum_name' => $forum->getName()]);
        }

        return $this->render('nautbot/rate_limit.html.twig', [
            'form' => $form->createView(),
            'editing' => $edit
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Forum         $forum
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function removeLimit(RateLimitRepository $rlr, Forum $forum, UserGroup $group = null, Request $request, EntityManager $em): Response {
        $this->validateCsrf('remove_rate_limit', $request->request->get('token'));
        $rate = $rlr->getRateLimit($group, $forum);
        $em->remove($rate);
        $em->flush();
        $this->addFlash('success', 'flash.rate_limit_removed');
        return $this->redirectToRoute('nautbot', ['forum_name' => $forum->getName()]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserGroup     $userGroup
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function edit(UserGroup $userGroup, Request $request, EntityManager $em): Response {
        $data = new UserGroupData($userGroup);

        $form = $this->createForm(UserGroupType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUserGroup($userGroup);

            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('group/edit.html.twig', [
            'group' => $userGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserRepository $ur
     * @param UserGroup     $userGroup
     * @param int           $page
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function group(UserRepository $ur, UserGroup $userGroup, int $page, Request $request, EntityManager $em): Response {
        $users = $ur->getPaginatedUsersInGroup($userGroup, $page);
        return $this->render('group/group.html.twig', [
            'group' => $userGroup,
            'page' => $page,
            'users' => $users
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserGroupRepository $ugm
     * @param User          $user
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function addToGroup(UserGroupRepository $ugm, User $user, Request $request, EntityManager $em): Response {
        $data = UserData::fromUser($user);

        $form = $this->createForm(UserToGroupType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUser($user);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('group/add.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
