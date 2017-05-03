<?php

namespace Raddit\AppBundle;

use Raddit\AppBundle\DependencyInjection\Compiler\VersionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RadditAppBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new VersionPass());
    }
}
