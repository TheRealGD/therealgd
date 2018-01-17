<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use App\Entity\User;
use App\Form\IpBanType;
use App\Form\BanUserType;
use App\Form\Model\IpBanData;
use App\Form\Model\UserBanData;
use App\Form\UnbanUserType;
use App\Repository\IpBanRepository;
use App\Repository\UserBanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_ADMIN")
 */
final class BanController extends AbstractController {
    public function userBans(UserBanRepository $repository, int $page) {
        return $this->render('ban/user_bans.html.twig', [
            'bans' => $repository->findActiveBans($page),
        ]);
    }

    public function banUser(User $user, UserRepository $repository, EntityManager $em, Request $request) {
        $data = new UserBanData($repository->findIpsUsedByUser($user));

        $form = $this->createForm(BanUserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ban = $data->toUserBan($user, $this->getUser(), true);

            if ($form->get('ban_ip')->getData()) {
                foreach ($data->toIpBans($user, $this->getUser()) as $ipBan) {
                    $em->persist($ipBan);
                }
            }

            $em->persist($ban);
            $em->flush();

            return $this->redirectToRoute('user_bans');
        }

        return $this->render('ban/ban_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    public function unbanUser(User $user, EntityManager $em, Request $request) {
        $data = new UserBanData();

        $form = $this->createForm(UnbanUserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $unban = $data->toUserBan($user, $this->getUser(), false);

            $em->persist($unban);

            if ($form->get('unban_ips')->getData()) {
                foreach ($user->getIpBans() as $ipBan) {
                    $em->remove($ipBan);
                }
            }

            $em->flush();

            $this->addFlash('success', 'flash.user_was_unbanned');

            return $this->redirectToRoute('user_bans');
        }

        return $this->render('ban/unban_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    public function ipBans(int $page, IpBanRepository $repository) {
        return $this->render('ban/ip_bans.html.twig', [
            'bans' => $repository->findAllPaginated($page),
        ]);
    }

    public function banIp(Request $request, EntityManager $em) {
        $data = new IpBanData();

        if ($request->query->has('ip')) {
            $data->ip = $request->query->get('ip');
        }

        if ($request->query->has('user_id')) {
            $data->user = $em->find(User::class, $request->query->getInt('user_id'));
        }

        $form = $this->createForm(IpBanType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ban = $data->toIpBan($this->getUser());

            $em->persist($ban);
            $em->flush();

            $this->addFlash('success', 'flash.ban_added');

            return $this->redirectToRoute('ip_bans');
        }

        return $this->render('ban/ban_ip.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function unbanIps(Request $request, IpBanRepository $repository, EntityManager $em) {
        $this->validateCsrf('unban_ips', $request->request->get('token'));

        $ids = array_filter((array) $request->request->get('ban'), function ($id) {
            return is_int(+$id);
        });

        foreach ($repository->findBy(['id' => $ids]) as $ban) {
            $em->remove($ban);
        }

        $em->flush();

        return $this->redirectToRoute('ip_bans');
    }
}
