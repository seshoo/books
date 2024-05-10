<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly string $mainUserLogin,
        private readonly string $mainUserPassword,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'login' => $this->mainUserLogin,
            'plainPassword' => $this->mainUserPassword,
            'roles' => ['ROLE_ADMIN']
        ]);
    }
}
