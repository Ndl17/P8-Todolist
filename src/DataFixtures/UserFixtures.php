<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Faker\Factory;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{

    private $userPasswordHasher;

    public function getOrder()
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
        $defaultUser= new User();
        $defaultUser->setEmail($faker->email);
        $defaultUser->setRoles(['ROLE_USER']);
        $defaultUser->setUsername($faker->username);
        $defaultUser->setPassword($this->userPasswordHasher->hashPassword($defaultUser, 'password'));
        $manager->persist($defaultUser);
        $this->addReference('user_' . $i, $defaultUser);
        $manager->flush();
        }
        
        //création d'un utilisateur avec role admin
        $adminUser= new User();
        $adminUser->setEmail('admin@todolist.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setUsername('Admin');
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $manager->persist($adminUser);
        $manager->flush();

    }
}
