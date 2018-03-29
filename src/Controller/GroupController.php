<?php

namespace App\Controller;

use App\Entity\UserGroup;
use App\Entity\User;
use App\Form\UserGroupType;
use App\Form\UserToGroupType;
use App\Form\Model\UserGroupData;
use App\Form\Model\UserData;
use App\Repository\UserGroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Entity("group", expr="repository.findOneOrRedirectToCanonical(name, 'group_name')")
 */
final class GroupController extends AbstractController {
    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserGroupRepository $group
     * @param int            $page
     * @param Request        $request
     *
     * @return Response
     */
    public function list(UserGroupRepository $group, int $page, Request $request) {
        return $this->render('group/list.html.twig', [
            'page' => $page,
            'groups' => $group->findPaginated($page),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function create(Request $request, EntityManager $em): Response {
        $data = new UserGroupData();

        $form = $this->createForm(UserGroupType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group = $data->toUserGroup();

            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('group/create.html.twig', [
            'form' => $form->createView(),
        ]);
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
