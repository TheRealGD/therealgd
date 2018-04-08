<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use App\Entity\Forum;
use App\Entity\Submission;
use Symfony\Component\HttpFoundation\Request;

final class ReportHelper {
    /**
     * Create a mod thread for a user report.
     */
    public static function createReport(EntityManager $em, Forum $forum, Request $request, $title, $url) {
      // Find nautbot.
      $nautbot = $em->find("App\\Entity\\User", 0);

      if($nautbot != null) {
          $reportComment = new Submission(
              $title,
              $url,
              null,
              $forum,
              $nautbot,
              $request->getClientIp()
          );
          $reportComment->setModThread(true);

          $em->persist($reportComment);
          $em->flush();

          return true;
      } else {
          return false;
      }
    }
}
