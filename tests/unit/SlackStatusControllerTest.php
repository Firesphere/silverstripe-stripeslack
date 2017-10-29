<?php

namespace Firesphere\StripeSlack\Test;

use Firesphere\StripeSlack\Controller\SlackStatusController;
use Firesphere\StripeSlack\Model\SlackUserCount;
use GuzzleHttp\Client;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;
use SilverStripe\SiteConfig\SiteConfig;

class SlackStatusControllerTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/count.yml';


    public function setUp()
    {
        $config = SiteConfig::current_site_config();
        $config->SlackURL = 'https://team.slack.com';
        $config->SlackToken = '1234567890';
        $config->SlackChannel = 'EAR9887';
        $config->write();
        parent::setUp();
    }

    public function tearDown()
    {
        /** @var DataList|SlackUserCount[] $counts */
        $counts = SlackUserCount::get();
        foreach ($counts as $count) {
            $count->delete();
        }
        parent::tearDown();
    }

    public function testInvalidConfig()
    {
        $controller = SlackStatusController::create(new HTTPRequest('GET', '/SlackStatus/usercount'));
        $config = SiteConfig::current_site_config();
        $config->SlackURL = '';
        $config->write();
        $this->assertEquals('', $controller->usercount());
    }

    public function additionProvider()
    {
        return [
            [[25, 60], 20],
            [[35, 65], 200],
            [[45, 70], 2000],
        ];
    }

    /**
     * @dataProvider additionProvider
     * @param $expected
     * @param $amount
     */
    public function testGetSVGSettings($expected, $amount)
    {
        $controller = SlackStatusController::create(new HTTPRequest('GET', '/SlackStatus/usercount'));
        $result = $controller->getSVGSettings($amount);
        $this->assertEquals($expected, $result);
    }

    public function testCachedStatus()
    {
        $controller = SlackStatusController::create(new HTTPRequest('GET', '/SlackStatus/usercount'));
        $this->assertEquals(10, $controller->getStatus(SiteConfig::current_site_config()));
    }

    /**
     * @dataProvider additionProvider
     * @param $expected
     * @param $amount
     */
    public function testSVG($expected, $amount)
    {
        $controller = SlackStatusController::create(new HTTPRequest('GET', '/SlackStatus/badge'));

        $counter = SlackUserCount::get()->first();
        $counter->UserCount = $amount;
        $counter->write();

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"115\" height=\"20\">
    <rect rx=\"3\" width=\"70\" height=\"20\" fill=\"#555\"></rect>
    <rect rx=\"3\" x=\"47\" width=\"$expected[0]\" height=\"20\" fill=\"#005b94\"></rect>
    <path d=\"M47 0h4v20h-4z\" fill=\"#005b94\"></path>
    <g text-anchor=\"middle\" font-family=\"Verdana\" font-size=\"11\">
        <text fill=\"#010101\" fill-opacity=\".3\" x=\"24\" y=\"15\">slack</text>
        <text fill=\"#fff\" x=\"24\" y=\"14\">slack</text>
        <text fill=\"#010101\" fill-opacity=\".3\" x=\"$expected[1]\" y=\"15\">$amount</text>
        <text fill=\"#fff\" x=\"$expected[1]\" y=\"14\">$amount</text>
    </g>
</svg>";
        $this->assertEquals($svg, $controller->badge()->getBody());
        $this->assertEquals('image/svg+xml', $controller->badge()->getHeader('Content-Type'));
    }

    public function testGetRestfulService()
    {
        /** @var SlackStatusController $controller */
        $controller = SlackStatusController::create();
        $result = $controller->getClient(SiteConfig::current_site_config());
        $this->assertInstanceOf(Client::class, $result);
    }

    public function testValidateResponse()
    {
        /** @var SlackUserCount $count */
        $count = SlackUserCount::get()->first();
        if (!$count) {
            $count = SlackUserCount::create();
        }
        $controller = SlackStatusController::create();
        $expected = [
            'ok'      => true,
            'channel' => [
                'members' => ['1', '4', '2']
            ]
        ];

        $result = $controller->validateResponse($count, $expected);
        $this->assertEquals(count($expected['channel']['members']), $result);
        $this->assertEquals(3, $result);
        $result = $controller->validateResponse($count, ['ok' => false]);
        $this->assertEquals(0, $result);
    }
}
