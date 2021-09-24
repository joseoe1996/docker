<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistroTest extends WebTestCase {

    public function testSomething(): void {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        // select the button
        $buttonCrawlerNode = $crawler->selectButton('boton');

        // retrieve the Form object for the form belonging to this button
        $form = $buttonCrawlerNode->form();

        // submit the Form object
        $client->submit($form, [
            'registration_form[username]' => 'jose',
            'registration_form[plainPassword]' => 'aa',
        ]);

        $response = $client->getResponse();
        $res = array();
        preg_match('/(<li>).+(<\/li>)/', $response, $res);

        $this->assertResponseRedirects(null, null, substr($res[0], 4, -5));
        $this->assertResponseIsSuccessful();
    }

}
