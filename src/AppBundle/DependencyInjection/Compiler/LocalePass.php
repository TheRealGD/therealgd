<?php

namespace Raddit\AppBundle\DependencyInjection\Compiler;

use Raddit\AppBundle\Form\UserSettingsType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Intl\Intl;

/**
 * Compiler pass that does things with locales.
 *
 * 1. Looks for available locales automatically.
 * 2. Sorts these alphabetically.
 * 3. Makes them available as options.
 */
class LocalePass implements CompilerPassInterface {
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) {
        if (!$container->hasDefinition(UserSettingsType::class)) {
            return;
        }

        $filenames = Finder::create()
            ->in(__DIR__.'/../../Resources/translations')
            ->name('/^\w+\.\w+\.yml$/')
            ->sortByName()
            ->files();

        $localeChoices = [];
        $localeBundle = Intl::getLocaleBundle();

        /** @var \SplFileInfo $file */
        foreach ($filenames as $file) {
            $locale = preg_replace('/^\w+\.(\w+)\.yml$/', '$1', $file->getFilename());
            $name = $localeBundle->getLocaleName($locale, $locale);

            if ($name !== null) {
                $localeChoices[$name] = $locale;
            }
        }

        // sort by language name
        if (function_exists('iconv')) {
            uksort($localeChoices, function ($a, $b) {
                $a = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $a);
                $b = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $b);

                return strnatcasecmp($a, $b);
            });
        }

        $container->getDefinition(UserSettingsType::class)->addMethodCall(
            'setLocaleChoices',
            [$localeChoices]
        );
    }
}
