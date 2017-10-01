<?php

/**
 * Class SlackSignupFormTest
 */
class SlackSignupFormTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/signups.yml';

    public function setUp()
    {
        parent::setUp();
    }

    public function testUpdateDuplicates()
    {
        /** @var SlackSignupForm $form */
        $form = Injector::inst()->get('SlackSignupForm');
        /** @var SlackInvite $user */
        $user = $this->objFromFixture(SlackInvite::class, 'invite1');
        $form->updateDuplicates($user);
        /** @var DataList|SlackInvite[] $result */
        $result = SlackInvite::get()->filter(['Email' => $user->Email]);
        foreach ($result as $invite) {
            $this->assertTrue($invite->Invited);
        }
    }
}