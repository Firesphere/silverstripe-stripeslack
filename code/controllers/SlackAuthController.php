<?php


/**
 * Class SlackAuthController
 *
 */
class SlackAuthController extends Controller
{

    /**
     * It seems like a lot is going on here
     * But in reality, it's mainly just configuration stuff
     *
     * @param SS_HTTPRequest $request
     */
    public function index(SS_HTTPRequest $request)
    {
        list($code, $config, $baseURL, $url) = $this->getConfig($request);

        $query = $this->getQuery($config, $code);

        // Setup and request the code
        // @todo rewrite to Guzzle for SS4
        $service = RestfulService::create($baseURL, 'GET', null, 0);
        $response = $service->request($url . $query);

        $this->saveToken($response, $config);

        // A successful write should go back to the admin
        $this->redirect('/admin/settings#Root_Slack');
    }

    /**
     * @param $config
     * @param $code
     * @return string
     */
    public function getQuery($config, $code)
    {
        $params = [
            'client_id'     => $config->SlackClientID,
            'client_secret' => $config->SlackClientSecret,
            'code'          => $code,
            'redirect_uri'  => Director::absoluteURL('/SlackAuthorization/'),
        ];

        return http_build_query($params);
    }

    /**
     * @param RestfulService_Response $response
     * @param SiteConfig $config
     * @throws \ValidationException
     */
    public function saveToken($response, $config)
    {
        // Convert the JSON to use in our config (hidden from user view)
        $result = Convert::json2array($response->getBody());

        $config->SlackToken = $result['access_token'];
        $config->write();
    }

    /**
     * @param SS_HTTPRequest $request
     * @return array
     */
    public function getConfig(SS_HTTPRequest $request)
    {
        // Code param
        $code = $request->getVar('code');
        $config = SiteConfig::current_site_config();
        // Build the URL
        $baseURL = $config->SlackURL;
        $baseURL = (substr($baseURL, -1) === '/') ? $baseURL : $baseURL . '/';
        $url = 'api/oauth.access?';

        return array($code, $config, $baseURL, $url);
    }
}
