<?php

namespace Raddit\AppBundle\Twig;

/**
 * Twig extension which makes certain parameters available as template
 * functions.
 */
final class AppExtension extends \Twig_Extension {
    /**
     * @var string
     */
    private $siteName;

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('raddit_app_site_name', [$this, 'getSiteName']),
        ];
    }

    /**
     * @return string
     */
    public function getSiteName() {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     */
    public function setSiteName($siteName) {
        $this->siteName = $siteName;
    }
}
