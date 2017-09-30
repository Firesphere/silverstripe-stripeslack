# StripeSlack

Inviting users to your public Slack channel is pretty hard to automate.

This module intents to make it at least a bit easier.

Although the initial steps seem overwhelming, I've tried to write it out as detailed as possible.
Once you've done it once, the next time will be a breeze.

## Why StripeSlack

Unlike other services that are require a third party to have full administrative access to your Slack Workspace,
StripeSlack is under your full control. You don't need to trust a third party to be trustworthy and not operate simply
to collect email addresses.

Although the submissions are stored in the database, this is on your own server. The reason for storing is so you can
re-send invites if something went wrong.

## Disclaimer

Unless you add a checkbox asking the user to also sign up for your newsletter, this module is explicitly not intended
to add this functionality and therefore, please do not abuse the storage of email addresses for this purpose.

Be a decent human being.

## Prerequisites

Somebody in your team needs to be an admin on your Slack usergroup, or have contact with an admin, to have it set up.

## Caveats

It looks pretty hard to get it all set up with Slack. You'll need to jump through quite some hoops. Please follow the instructions closely.
When done, you'll find it wasn't actually that hard after all, just a few steps that seem somewhat confusing at first.

## Installation

- Option 1, with Composer:

`composer require firesphere/stripeslack`

- Option 2, download:

Download the zip from GitHub

Finally, run `https://yourdomain.com/dev/build?flush=all`

## Setting up Slack

### Note:
- To do this, you need to be an admin on the slack channel.
    - The administrator taking the steps will be the person who will be sending out the invites.
- Don't be taken aback by the amount of steps. I've tried to be as detailed as possible.

1. Go to the [Slack API site](https://api.slack.com/web).
2. Scroll down to the **Authentication** heading and click on *Register your application*.
3. You'll be presented with a Modal where you can give the app a name and a dropdown to select the group you want the application to work for.
4. After creation, you'll be presented with the **Basic Information** screen.
5. Click on *Add features and functionality*.
6. Click on *Permissions*.
7. Scroll down to *Redirect URLs*.
8. Enter the URL from where the application will operate, e.g. `https://www.silverstripe.org`. Preferably it's an `https` site.
9. Click on the *Save URLs* button.
10. Scroll further down to **Scopes**.
11. Under *Select Permission Scopes*, select *Administer the workspace*.
12. Click on *Save changes*.
13. Scroll back up to the top and click the button *Install App to Workspace*.
14. On the left, click on *Basic Information* to go back to the basic information.
15. You are now done setting up Slack.

## Setting up StripeSlack

After setting up Slack, a few more steps are needed.

1. In a new tab or window, open your CMS and go to *Settings*.
2. Select the *Slack* tab.
3. Enter the url of your Slack Workspace (e.g. `https://silverstripe-users.slack.com`).
4. Select the default channel you want your users to be invited for (Usually General).
    1. You can get the ID by right clicking on your channel and select "copy link". 
    2. Open the copied link in a browser and copy the part after "messages/".
    3. Paste this code in to *ID of your channel* field.
5. Go back to your Slack application on the *Basic Information* and scroll down to see the **App Credentials**.
6. Copy the *Client ID*.
7. Paste the Client ID in the CMS in the Client ID field.
8. Back to the Slack Application, click on *show* under *Client Secret*.
9. Copy the now visible *Client Secret*.
10. Paste the Client Secret in the CMS *Client Secret* field. (Note that it's pasted as a password, don't worry, it's stored correctly).
11. Select a page on your website to redirect the user to, when the invitation has been sent successfully and one for when it was unsuccessful.
12. Save the site settings
13. You will now be presented with a link to click, to Authorize your application.
14. Click the link to be redirected to Slack, which will ask you for Administrator Privileges for this app.
    1. The privileges are fully under your control, and the app is installed locally
    2. No third party will get administrative access.
    3. This repository does _not_ collect any data from you.
15. After accepting, you will be redirected back to the CMS. Your application is now ready to go.

## Adding the form to your website

On your place of choice to add the form, simply include `$SlackForm` and the form should appear.

Textual changes can be made in a translation file in your base project. See `stripeslack\lang\en.yml` for an example.

### Styling and design of the form

The form relies completely on the provided `FormField` templates. So styling is all up to you.

## Todo

- [ ] Add tests
- [ ] Make invites sent to a certain channel
- [ ] Re-try sending invites via the CMS
- [ ] Move away from `RestfulService` (Removed in SS4) to using `Guzzle`
- [ ] SilverStripe 4 compatible version

# Cow?

```

               /( ,,,,, )\
              _\,;;;;;;;,/_
           .-"; ;;;;;;;;; ;"-.
           '.__/`_ / \ _`\__.'
              | (')| |(') |
              | .--' '--. |
              |/ o     o \|
              |           |
             / \ _..=.._ / \
            /:. '._____.'   \
           ;::'    / \      .;
           |     _|_ _|_   ::|
         .-|     '==o=='    '|-.
        /  |  . /       \    |  \
        |  | ::|         |   | .|
        |  (  ')         (.  )::|
        |: |   |;  U U  ;|:: | `|
        |' |   | \ U U / |'  |  |
        ##V|   |_/`"""`\_|   |V##
           ##V##         ##V##
```

# License

Copyright 2017 Firesphere

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.