<?php


/**
 * Class SlackAuthController
 *
 */
class SlackAuthController extends Controller
{

    public function index(SS_HTTPRequest $request)
    {
        // Code param
        $code = $request->getVar('code');
        $config = SiteConfig::current_site_config();
        // Build the URL
        $baseURL = $config->SlackURL;
        $baseURL = (substr($baseURL, -1) === '/') ? substr($baseURL, 0, -1) : $baseURL;
        $url = 'api/oauth.access?';
        $params = [
            'client_id'     => $config->SlackClientID,
            'client_secret' => $config->SlackClientSecret,
            'code'          => $code,
            'redirect_uri'  => Director::absoluteURL('/SlackAuthorization/'),
        ];
        $query = http_build_query($params);
        // Setup and request the code
        $service = RestfulService::create($baseURL, 'GET', null, 0);
        $response = $service->request($url . $query);
        // Convert the JSON to use in our config (hidden from user view)
        $result = Convert::json2array($response->getBody());
        $config->SlackToken = $result['access_token'];
        $config->write();
        // A successful write should go back to the admin
        $this->redirect('/admin/settings#Root_Slack');
    }
}
