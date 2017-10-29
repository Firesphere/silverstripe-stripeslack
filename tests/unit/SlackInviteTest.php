<?php

namespace Firesphere\StripeSlack\Test;

use Firesphere\StripeSlack\Model\SlackInvite;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataList;

/**
 * Class SlackSignupFormTest
 */
class SlackInviteTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/signups.yml';

    public function setUp()
    {
        parent::setUp();
    }

    public function testDeleteDuplicates()
    {
        /** @var SlackInvite $user */
        $user = $this->objFromFixture(SlackInvite::class, 'invite1');
        $user->Invited = true;
        $user->deleteDuplicates();
        /** @var DataList|SlackInvite[] $result */
        $result = SlackInvite::get()->filter(['Email' => $user->Email]);
        $this->assertEquals(1, $result->count());
    }

    public function testHandleResult()
    {
        /** @var SlackInvite $invite */
        $invite = SlackInvite::create();
        $invite->handleResult(['error' => 'not_authed']);
        $this->assertEquals('No valid Slack Token provided, please check your settings', $invite->Message);
        $invite->handleResult(['error' => 'already_invited']);
        $this->assertContains('Invite successful', $invite->Message);
        $this->assertTrue((bool)$invite->Invited);
        $invite->handleResult(['ok' => 1]);
        $this->assertEquals('Invite successful', $invite->Message);
        $this->assertTrue((bool)$invite->Invited);
    }
}
