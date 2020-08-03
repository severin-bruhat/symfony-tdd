<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControlllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testDisplayLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains("h1", "Sign in");
        $this->assertSelectorNotExists('.alert.alert-danger');
    }


    public function testLoginWithBadCredentials()
    {
        
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'john@doe.com',
            'password' => 'fakepassword',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfulLogin()
    {
        $this->loadFixtureFiles([__DIR__ . '/users.yml']);
        self::ensureKernelShutdown();

        $client = static::createClient();
        
        // $crawler = $client->request('GET', '/login');
        // $form = $crawler->selectButton('Sign in')->form([
        //     'email' => 'john@doe.com',
        //     'password' => '000000',
        // ]);
        // $client->submit($form);

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $client->request('POST', '/login', [
                'email' => 'john@doe.com',
                'password' => '000000',
                '_csrf_token' => $csrfToken,
            ]);

        $this->assertResponseRedirects('/auth');
    }
   
}
