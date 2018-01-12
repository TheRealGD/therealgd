<?php

namespace App\DependencyInjection\Compiler;

use App\Form\UserSettingsType;
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
            ->in(__DIR__.'/../../../translations')
            ->in(__DIR__.'/../../../translations/overrides')
            ->depth(0)
            ->name('/^\w+\.\w+\.\w+$/')
            ->sortByName()
            ->files();

        $localeChoices = [];
        $localeBundle = Intl::getLocaleBundle();

        /** @var \SplFileInfo $file */
        foreach ($filenames as $file) {
            $locale = preg_replace('/^\w+\.(\w+)\.\w+$/', '$1', $file->getFilename());
            $name = $localeBundle->getLocaleName($locale, $locale);

            if ($name !== null) {
                $localeChoices[$name] = $locale;
            }
        }

        // sort by language name
        if (function_exists('transliterator_transliterate')) {
            uksort($localeChoices, function ($a, $b) {
                $a = transliterator_transliterate(
                    'NFKD; Latin; Latin/US-ASCII; [:Nonspacing Mark:] Remove; Lower',
                    $a
                );

                $b = transliterator_transliterate(
                    'NFKD; Latin; Latin/US-ASCII; [:Nonspacing Mark:] Remove; Lower',
                    $b
                );

                return strnatcasecmp($a, $b);
            });
        } else {
            ksort($localeChoices);
        }

        $container->getDefinition(UserSettingsType::class)->addMethodCall(
            'setLocaleChoices',
            [$localeChoices]
        );
    }
}
