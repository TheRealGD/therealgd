<?php


namespace App\Entity;

use App\Entity\Report;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="report_entry")
 */
class ReportEntry {
    /**
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     *
     * @var int
     */
    private $id;

    /**
     * Many report entries have one report.
     * @ORM\ManyToOne(targetEntity="Report", inversedBy="reportEntries")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * Many reports have one user.
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $body;

    /**
     *
     */
    public function __construct() {
        $this->report = null;
        $this->user = null;
    }

    public function setReport(Report $report) {
        $this->report = $report;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    public function setBody($body) {
      $this->body = $body;
    }
}
