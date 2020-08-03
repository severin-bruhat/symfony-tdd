<?php

namespace App\Tests\Controller;

use App\Tests\NeedLogin;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PageControlllerTest extends WebTestCase
{
    use FixturesTrait;
    use NeedLogin;

    public function testHelloPage()
    {
        $client = static::createClient();
        $client->request('GET', '/hello');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
    
    public function testH1HelloPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/hello');
        $this->assertSelectorTextContains("h1", "Welcome");
    }

    public function testMailSendsEmail()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('GET', '/mail');
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
    }

    public function testAuthPageIsRestricted()
    {
        //$this->markTestSkipped('must be revisited.');
        $client = static::createClient();
        $client->request('GET', '/auth');
        $this->assertResponseRedirects('/login');
    }

    public function testRedirectToLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/auth');
        $this->assertResponseRedirects('/login');
    }

    public function testLetAuthenticatedUserAccessAuth()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__ . '/users.yml']);
        $this->login($client, $users['user_user']);
        $client->request('GET', '/auth');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAdminRequiresAdminRole()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__ . '/users.yml']);
        $this->login($client, $users['user_user']);
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminRequiresAdminRoleWithSufficientRole()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__ . '/users.yml']);
        $this->login($client, $users['user_admin']);
        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
