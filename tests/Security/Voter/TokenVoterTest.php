<?php

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\TokenVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @group time-sensitive
 */
class TokenVoterTest extends TestCase {
    private $accessDecisionManager;

    protected function setUp() {
        $this->accessDecisionManager = new class() implements AccessDecisionManagerInterface {
            public function decide(TokenInterface $token, array $attributes, $object = null) {
                return !array_diff($attributes, array_map(function (Role $role) {
                    return $role->getRole();
                }, $token->getRoles()));
            }
        };
    }

    /**
     * @param $roles
     * @param $userCreated
     * @param $interval
     * @param $expected
     *
     * @dataProvider createForumProvider
     */
    public function testVotesCorrectlyOnCreateForumAttribute($roles, $userCreated, $interval, $expected) {
        $user = $this->createMock(User::class);

        $user->expects($this->atMost(1))
            ->method('getCreated')
            ->willReturn((new \DateTime('@'.time()))->modify($userCreated));

        $voter = new TokenVoter($this->accessDecisionManager, $interval);
        $token = $this->getToken($roles, $userCreated);

        $this->assertSame($expected, $voter->vote($token, null, ['create_forum']));
    }

    private function getToken($roles, $createdAt) {
        for ($i = count($roles); $i--;) {
            $roles[$i] = new Role($roles[$i]);
        }

        $user = $this->createMock(User::class);

        $user->method('getCreated')
            ->willReturn((new \DateTime('@'.time()))->modify($createdAt));

        /* @var TokenInterface|MockObject $token */
        $token = $this->createMock(TokenInterface::class);

        $token->method('getRoles')->willReturn($roles);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    public function createForumProvider() {
        yield [['ROLE_ADMIN', 'ROLE_USER'], '1 year ago', '1 day', VoterInterface::ACCESS_GRANTED];
        yield [['ROLE_ADMIN', 'ROLE_USER'], '1 second ago', '1 day', VoterInterface::ACCESS_GRANTED];
        yield [['ROLE_ADMIN', 'ROLE_USER'], '1 second ago', null, VoterInterface::ACCESS_GRANTED];
        yield [['ROLE_USER'], '1 year ago', '1 day', VoterInterface::ACCESS_GRANTED];
        yield [['ROLE_USER'], '1 second ago', '1 day', VoterInterface::ACCESS_DENIED];
        yield [['ROLE_USER'], '1 second ago', null, VoterInterface::ACCESS_GRANTED];
    }
}
