<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{

    private $userPasswordHasher;

    public function getOrder(): int
    {
        return 1;
    }
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        //création de 10 utilisateurs avec role user
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $defaultUser = new User();
            $defaultUser->setEmail($faker->email);
            $defaultUser->setRoles(['ROLE_USER']);
            $defaultUser->setUsername($faker->username);
            $defaultUser->setPassword($this->userPasswordHasher->hashPassword($defaultUser, 'password'));
            $manager->persist($defaultUser);
            $this->addReference('user_' . $i, $defaultUser);
            $manager->flush();
        }

        //création d'un utilisateur avec role admin
        $adminUser = new User();
        $adminUser->setEmail('admin@todolist.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setUsername('Admin');
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $manager->persist($adminUser);
        $manager->flush();

        //création d'un utilisateur anonyme
        $anonymousUser = new User();
        $anonymousUser->setEmail('anonyme@todolist.com');
        $anonymousUser->setRoles(['ROLE_USER']);
        $anonymousUser->setUsername('Anonyme');
        $anonymousUser->setPassword($this->userPasswordHasher->hashPassword($anonymousUser, 'password'));
        $manager->persist($anonymousUser);
        $this->addReference('user_anonymous', $anonymousUser);
        $manager->flush();

        //création d'un utilisateur avec role user
        $userTest = new User();
        $userTest->setEmail('user@user.fr');
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setUsername('UserTest1');
        $userTest->setPassword($this->userPasswordHasher->hashPassword($userTest, 'password'));
        $manager->persist($userTest);
        $this->addReference('user_test', $userTest);
        $manager->flush();

        $userTestBis = new User();
        $userTestBis->setEmail('userbis@user.fr');
        $userTestBis->setRoles(['ROLE_USER']);
        $userTestBis->setUsername('UserTest2');
        $userTestBis->setPassword($this->userPasswordHasher->hashPassword($userTestBis, 'password'));
        $manager->persist($userTestBis);
        $this->addReference('user_test_bis', $userTestBis);
        $manager->flush();

    }
}
