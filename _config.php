<?php

use Firesphere\StripeSlack\Parsers\StripeSlackShortcodeParser;
use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get()->register('stripeslack', [StripeSlackShortcodeParser::class, 'handle_shortcode']);
