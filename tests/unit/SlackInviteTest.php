<?php

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
        $user->updateDuplicates();
        /** @var DataList|SlackInvite[] $result */
        $result = SlackInvite::get()->filter(['Email' => $user->Email]);
        $this->assertEquals(1, $result->count());
    }
}