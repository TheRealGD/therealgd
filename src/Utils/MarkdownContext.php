<?php

namespace App\Utils;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MarkdownContext {
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Retrieve a set of options based on request context.
     *
     * @param array $options Additional options (can override context)
     *
     * @return array
     */
    public function getContextAwareOptions(array $options = []): array {
        $resolved = MarkdownConverter::resolveOptions($options);

        if (!isset($options['base_path'])) {
            $request = $this->requestStack->getCurrentRequest();

            if ($request) {
                $resolved['base_path'] = $request->getBasePath();
            }
        }

        if (!isset($options['open_external_links_in_new_tab'])) {
            $token = $this->tokenStorage->getToken();
            $user = $token ? $token->getUser() : null;

            if ($user instanceof User) {
                $resolved['open_external_links_in_new_tab'] = $user->openExternalLinksInNewTab();
            }
        }

        return $resolved;
    }
}
