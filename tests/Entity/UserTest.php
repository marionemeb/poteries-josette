<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testGetRolesKeepsCustomRolesAndDeduplicates(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], array_values($user->getRoles()));
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('josette@poterie-josette.com');

        $this->assertSame('josette@poterie-josette.com', $user->getUserIdentifier());
    }

    public function testToStringReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('josette@poterie-josette.com');

        $this->assertSame('josette@poterie-josette.com', (string) $user);
    }
}
