<?php

/**
 * Test various items on the SlackAuthController
 *
 * Class SlackAuthControllerTest
 */
class SlackAuthControllerTest extends SapphireTest
{

    public function testGetQuery()
    {
        $config = SiteConfig::current_site_config();
        /** @var SlackAuthController $controller */
        $controller = Injector::inst()->get(SlackAuthController::class);
        $result = $controller->getQuery($config, '1234567890987654321');

        $expected = 'client_id=' . $config->SlackClientID . '&client_secret=' . $config->SlackClientSecret .
            'code&=1234567890987654321&redirect_uri=' .Director::absoluteURL('/SlackAuthorization/');
        $this->assertEquals($expected, $result);
    }

    public function testSaveToken()
    {
        /** @var SlackAuthController $controller */
        $controller = Injector::inst()->get(SlackAuthController::class);
        $response = new RestfulService_Response('{access_token:1234567890}');

        $config = SiteConfig::current_site_config();
        $controller->saveToken($response, $config);
        $config = SiteConfig::current_site_config();
        $this->assertEquals('1234567890', $config->SlackToken);

    }
}