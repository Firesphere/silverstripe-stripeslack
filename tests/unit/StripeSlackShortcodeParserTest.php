<?php


class StripeSlackShortcodeParserTest extends SapphireTest
{
    public function testShortcode()
    {
        $parser = new StripeSlackShortcodeParser();

        $config = SiteConfig::current_site_config();
        $config->SlackToken = '1234567890';
        $result = $parser->stripeslackHandler();

        $this->assertContains('name="Name"', $result);
        $this->assertContains('name="Email"', $result);
    }
}
