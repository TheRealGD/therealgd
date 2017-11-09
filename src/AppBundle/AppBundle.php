<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\LocalePass;
use AppBundle\DependencyInjection\Compiler\VersionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new LocalePass());
        $container->addCompilerPass(new VersionPass());
    }
}
