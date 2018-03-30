<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ForumConfiguration;
use App\Repository\ForumRepository;
use App\Repository\ForumConfigurationRepository;
use App\Repository\SubmissionRepository;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\ForumConfigurationType;
use App\Form\Model\ForumConfigurationData;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;


 /**
  * @IsGranted("ROLE_ADMIN")
  */
final class FrontPageConfigurationController extends AbstractController {
    public function frontPageConfig(ForumRepository $fr, SubmissionRepository $sr, ForumConfigurationRepository $fcr, EntityManager $em, Request $request) {
        $data = new ForumConfigurationData($fcr->findSitewide());

        $form = $this->createForm(ForumConfigurationType::class, $data);
        $form->handleRequest($request);

        $message = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $fc = $data->toForumConfiguration();

            // For sitewide settings, we need to force this to null.
            $fc->setForumId(null);

            if($fc->getId() == null || trim($fc->getId()) == "")
                $em->persist($fc);
            else
                $em->merge($fc);

            $em->flush();

            // After saving, pull the data back down again.
            $data = new ForumConfigurationData($fcr->findSitewide());
            $form = $this->createForm(ForumConfigurationType::class, $data);

            $message = "front_page_configuration_form.message_saved";
        }

        return $this->render('frontpageconfig/frontpageconfig.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }
}
