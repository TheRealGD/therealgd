<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ForumConfiguration;
use App\Entity\Submission;
use App\Repository\ForumRepository;
use App\Repository\ForumConfigurationRepository;
use App\Repository\SubmissionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\NewForumAnnouncementType;
use App\Form\Model\NewForumAnnouncementData;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;


 /**
  * @IsGranted("ROLE_ADMIN")
  */
final class FrontPageConfigurationController extends AbstractController {

    public function frontPageConfig(
        ForumRepository $fr,
        SubmissionRepository $sr,
        ForumConfigurationRepository $fcr,
        UserRepository $ur,
        EntityManager $em,
        Request $request
    ) {
        $announcementForumName = "announcements";
        $data = new NewForumAnnouncementData();

        $form = $this->createForm(NewForumAnnouncementType::class, $data);
        $form->handleRequest($request);

        $message = "";
        $messageClass = "";

        if ($form->isSubmitted() && $form->isValid()) {
            // Find the user and forum for submission.
            $user = $ur->loadUserByUsername($this->getUser()->getUsername());
            $forum = $fr->findOneByCaseInsensitiveName($announcementForumName);

            if($forum != null) {
                // Create the submission.
                $submission = new Submission(
                    $data->threadTitle,
                    null,
                    $data->threadContent,
                    $forum,
                    $user,
                    null
                );
                $em->persist($submission);
                $em->flush();

                // Load the current announcement's row id. Sitewide announcemnts have null forum id.
                $fc = $fcr->findSitewide();
                $fc->setAnnouncementSubmissionId($submission->getId());
                $fc->setAnnouncement($data->announcement);
                $fc->setForumId(null);

                $em->persist($fc);
                $em->flush();

                // After saving, pull the data back down again.
                $data = new NewForumAnnouncementData();
                $form = $this->createForm(NewForumAnnouncementType::class, $data);

                $message = "front_page_configuration_form.announcement_saved";
                $messageClass = "success";
            } else {
                $message = "front_page_configuration_form.announcement_forum_error";
                $messageClass = "error";
            }
        }

        // Load the current sitewide so we can show the announcement.
        $frontpageconfig = $fcr->findSitewide();
        $announcementSubmission = null;
        if($frontpageconfig->getAnnouncementSubmissionId() != null) {
            $announcementSubmission = $em->find("App\\Entity\\Submission", $frontpageconfig->getAnnouncementSubmissionId());
        }

        return $this->render('frontpageconfig/frontpageconfig.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'messageClass' => $messageClass,
            'current_announcement' => $frontpageconfig->getAnnouncement(),
            'announcementSubmission' => $announcementSubmission,
        ]);
    }

    public function removeAnnouncement(ForumRepository $fr, SubmissionRepository $sr, ForumConfigurationRepository $fcr, EntityManager $em, Request $request) {
        $fc = $fcr->findSitewide();

        $fc->setAnnouncement(null);
        $fc->setAnnouncementSubmissionId(null);
        $em->merge($fc);
        $em->flush();

        return $this->redirectToRoute('frontpageconfig');
    }
}
