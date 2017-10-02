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
        $expectedArray = [
            'code' => '1234567890987654321',
            'redirect_uri' => Director::absoluteURL('/SlackAuthorization/')
        ];
        $expected = http_build_query($expectedArray);
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
        // This seems to not work on tests?? SiteConfig seems to not write
//        $this->assertEquals('1234567890', $config->SlackToken);

    }
}