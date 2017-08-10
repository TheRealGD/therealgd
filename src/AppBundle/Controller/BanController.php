<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\Ban;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\BanType;
use Raddit\AppBundle\Repository\BanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BanController extends Controller {
    /**
     * What banned users see.
     *
     * @return Response
     */
    public function landingPageAction() {
        return $this->render('@RadditApp/banned.html.twig');
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param int           $page
     * @param BanRepository $banRepository
     *
     * @return Response
     */
    public function listAction(int $page, BanRepository $banRepository) {
        return $this->render('@RadditApp/ban_list.html.twig', [
            'bans' => $banRepository->findAllPaginated($page),
        ]);
    }

    /**
     * Form for adding new bans.
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function addAction(Request $request, EntityManager $em) {
        $ban = new Ban();

        $ban->setIp($request->query->filter('ip', null, FILTER_VALIDATE_IP));

        // TODO: use a DTO instead
        if ($request->query->has('user_id')) {
            $id = $request->query->getInt('user_id');
            $user = $em->find(User::class, $id);
            $ban->setUser($user);
        }

        $form = $this->createForm(BanType::class, $ban);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ban->setBannedBy($this->getUser());

            $this->addFlash('success', 'flash.ban_added');

            $em->persist($ban);
            $em->flush();

            return $this->redirectToRoute('raddit_app_bans');
        }

        return $this->render('@RadditApp/ban_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Redirect to the ban form.
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param EntityManager $em
     * @param string        $entityClass
     * @param string        $id
     *
     * @return Response
     */
    public function redirectAction(EntityManager $em, $entityClass, $id) {
        $entity = $em->find($entityClass, $id);

        return $this->redirectToRoute('raddit_app_add_ban', [
            'ip' => $entity->getIp(),
            'user_id' => $entity->getUser()->getId(),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request       $request
     * @param EntityManager $em
     *
     * @return Response
     */
    public function removeAction(Request $request, EntityManager $em) {
        if (!$this->isCsrfTokenValid('remove_bans', $request->request->get('token'))) {
            throw $this->createAccessDeniedException();
        }

        $banIds = (array) $request->request->get('ban');
        $banIds = array_filter($banIds, 'is_numeric');

        foreach ($banIds as $banId) {
            $ban = $em->find(Ban::class, $banId);

            if ($ban) {
                $em->remove($ban);
            }
        }

        $em->flush();

        return $this->redirectToRoute('raddit_app_bans');
    }
}
