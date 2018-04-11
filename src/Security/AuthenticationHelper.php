<?php

namespace App\Security;

use App\Entity\User;
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
     * @var RememberMeServicesInterface
     */
    private $rememberMeServices;

    /**
     * @var string
     */
    private $secret;

    public function __construct(
        FirewallMap $firewallMap,
        TokenStorageInterface $tokenStorage,
        RememberMeServicesInterface $rememberMeServices,
        string $secret
    ) {
        $this->firewallMap = $firewallMap;
        $this->tokenStorage = $tokenStorage;
        $this->rememberMeServices = $rememberMeServices;
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

        $response = $response ?: new Response();

        $this->rememberMeServices->loginSuccess($request, $response, $token);

        return $response;
    }
}
