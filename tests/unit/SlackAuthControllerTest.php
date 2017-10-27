<?php

/**
 * Test various items on the SlackAuthController
 *
 * Class SlackAuthControllerTest
 */
class SlackAuthControllerTest extends SapphireTest
{
    /**
     * @var SlackAuthController
     */
    protected $controller;

    public function setUp()
    {
        $this->controller = Injector::inst()->get('SlackAuthController');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetQuery()
    {
        $config = SiteConfig::current_site_config();
        $result = $this->controller->getQuery($config, '1234567890987654321');
        $expectedArray = [
            'client_id'     => $config->SlackClientID,
            'client_secret' => $config->SlackClientSecret,
            'code'          => '1234567890987654321',
            'redirect_uri'  => Director::absoluteURL('/SlackAuthorization/'),
        ];
        $expected = http_build_query($expectedArray);
        $this->assertEquals($expected, $result);
    }

    public function testSaveToken()
    {
        $response = new RestfulService_Response('{"access_token":"12345678chdiyp67"}');

        $config = SiteConfig::current_site_config();

        $this->controller->saveToken($response, $config);
        $config = SiteConfig::current_site_config();
        // This seems to not work on tests?? SiteConfig seems to not write
        $this->assertEquals('12345678chdiyp67', $config->SlackToken);
    }

    public function testGetConfig()
    {
        $controller = SlackAuthController::create();
        $config = SiteConfig::current_site_config();
        $config->SlackURL = 'https://team.slack.com';
        $config->write();
        $result = $controller->getConfig(new SS_HTTPRequest('GET', 'https://team.slack.com/api/something', ['code' => '1234567890']));
        $this->assertEquals('1234567890', $result[0]);
        $this->assertInstanceOf('SiteConfig', $result[1]);
        $this->assertEquals('api/oauth.access?', $result[3]);
    }
}
