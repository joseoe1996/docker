<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class InicioLogginTest extends WebTestCase {

    public function testSomething(): void {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $criterio=['username'=>'jose'];
        $testUser = $userRepository->findBy($criterio)[0];

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        // test e.g. the profile page
        $client->request('GET', '/inicio');
        //$this->assertResponseIsSuccessful();
        $this->assertResponseRedirects();
    }

}
