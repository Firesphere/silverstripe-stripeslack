<?php


class StripeSlackShortcodeParserTest extends SapphireTest
{

    public function testShortcode()
    {
        $parser = new StripeSlackShortcodeParser();

        $result = $parser->stripeslackHandler();

        $this->assertContains('name="Name"', $result);
        $this->assertContains('name="Email"', $result);
    }
}