<?php

namespace AppBundle\Utils;

use Psr\Container\ContainerInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class AuthenticationHelper {
    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ContainerInterface
     *
     * @todo Don't.
     */
    private $container;

    /**
     * @var string
     */
    private $secret;

    public function __construct(
        FirewallMap $firewallMap,
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $secret
    ) {
        $this->firewallMap = $firewallMap;
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->secret = $secret;
    }

    /**
     * Programmatically set a user as logged in.
     *
     * @param User          $user
     * @param Request       $request
     * @param Response|null $response
     *
     * @return Response provided response, or a new one if none was provided
     */
    public function login(User $user, Request $request, Response $response = null): Response {
        $name = $this->firewallMap->getFirewallConfig($request)->getName();

        $token = new RememberMeToken($user, $name, $this->secret);

        /* @var RememberMeServicesInterface $rememberMeServices */
        $rememberMeServices = $this->container->get(
            'security.authentication.rememberme.services.simplehash.'.$name
        );

        $response = $response ?: new Response();

        $rememberMeServices->loginSuccess($request, $response, $token);

        return $response;
    }
}
