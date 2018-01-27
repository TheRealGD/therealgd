<?php

namespace App\DependencyInjection\Compiler;

use App\Twig\AppExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Process\Process;

/**
 * Compiler pass that makes the current version number of the software available
 * to templates.
 */
final class VersionPass implements CompilerPassInterface {
    const COMPOSER_JSON = __DIR__.'/../../../composer.json';

    public function process(ContainerBuilder $container) {
        if (!$container->has(AppExtension::class)) {
            return;
        }

        $branchName = $this->getGitBranchName();
        $branchAlias = $this->getComposerBranchAlias($branchName);
        $tagName = $this->getGitTagName();

        $extensionDefinition = $container->getDefinition(AppExtension::class);
        $extensionDefinition->setArgument('$branch', $branchAlias ?? $branchName);
        $extensionDefinition->setArgument('$version', $tagName);
    }

    private function getGitBranchName(): ?string {
        return $this->getVersionFromCommand('git rev-parse --abbrev-ref HEAD');
    }

    private function getGitTagName(): ?string {
        return $this->getVersionFromCommand('git describe --tags');
    }

    private function getComposerBranchAlias(?string $branch): ?string {
        if ($branch !== null) {
            $content = json_decode(file_get_contents(self::COMPOSER_JSON));
            $key = preg_match('/^\d+(\.\d+){1,2}$/', $branch) ? $branch : "dev-$branch";

            if (isset($content->extra->{'branch-alias'}->{$key})) {
                return $content->extra->{'branch-alias'}->{$key};
            }
        }

        return null;
    }

    private function getVersionFromCommand(string $commandLine): ?string {
        $process = new Process($commandLine);
        $process->run();

        $output = trim($process->getOutput());

        if ($process->isSuccessful() && strlen($output) > 0) {
            return preg_split('/\r?\n/', $output, 2)[0];
        }

        return null;
    }
}
