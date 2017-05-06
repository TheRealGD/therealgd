<?php

namespace Raddit\AppBundle\Controller;

use Raddit\AppBundle\Entity\Ban;
use Raddit\AppBundle\Form\BanType;
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
     * @return Response
     */
    public function listAction() {
        $bans = $this->getDoctrine()->getRepository(Ban::class)->findAll();

        return $this->render('@RadditApp/ban_list.html.twig', [
            'bans' => $bans,
        ]);
    }

    /**
     * Form for adding new bans.
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addAction(Request $request) {
        $ban = new Ban();

        $form = $this->createForm(BanType::class, $ban);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ban->setBannedBy($this->getUser());

            $this->addFlash('success', 'ban_add.banned_notice');

            $this->getDoctrine()->getManager()->persist($ban);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('raddit_app_bans');
        }

        return $this->render('@RadditApp/ban_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeAction(Request $request) {
        if (!$this->isCsrfTokenValid('remove_bans', $request->request->get('token'))) {
            throw $this->createAccessDeniedException();
        }

        $banIds = $request->request->get('ban');

        if (!is_array($banIds)) {
            $banIds = [$banIds];
        }

        $banIds = array_filter($banIds, function ($a) {
            return is_numeric($a);
        });

        $em = $this->getDoctrine()->getManager();
        $banRepository = $this->getDoctrine()->getRepository(Ban::class);

        foreach ($banIds as $banId) {
            $ban = $banRepository->find($banId);

            if ($ban) {
                $em->remove($ban);
            }
        }

        $em->flush();

        return $this->redirectToRoute('raddit_app_bans');
    }
}
