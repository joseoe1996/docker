<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use App\Repository\ConexionesRepository;

class NumConexionesTest extends WebTestCase {

    public function testSomething(): void {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $conexionesRepository = static::getContainer()->get(ConexionesRepository::class);

        // retrieve the test user
        $criterio = ['username' => 'jose'];
        $testUser = $userRepository->findBy($criterio)[0];
        $id= $testUser->getId();
        $criteria = ['user' => $id];
        $conexiones = $conexionesRepository->findBy($criteria);

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        // test e.g. the profile page
        $crawler = $client->request('GET', '/inicio/lista_conexion');
        
        $this->assertCount(count($conexiones), $crawler->filter('tr'));
    }

}
