<?php

namespace Firesphere\StripeSlack\Test;

use Firesphere\StripeSlack\Parsers\StripeSlackShortcodeParser;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Parsers\ShortcodeParser;

class StripeSlackShortcodeParserTest extends SapphireTest
{
    protected $parser;

    public function testTitle()
    {
        $result = StripeSlackShortcodeParser::get_shortcodes();
        $this->assertEquals(['stripeslack'], $result);
    }

    public function testShortcode()
    {
        $config = SiteConfig::current_site_config();
        $config->SlackToken = '1234567890';
        $config->write();
        // This is a PITA, but works
        Config::forClass(RequestHandler::class)->set('url_segment', '/');
        $request = new HTTPRequest('GET', '/');
        $request->setSession(new Session([]));
        Controller::curr()->setRequest($request);

        $result = StripeSlackShortcodeParser::handle_shortcode(
            [],
            '[stripeslack]',
            new ShortcodeParser(),
            'stripeslack'
        );

        $this->assertContains('name="Name"', $result);
        $this->assertContains('name="Email"', $result);
    }
}
