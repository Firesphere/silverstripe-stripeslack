<?php

namespace Firesphere\StripeSlack\Controller;

use GuzzleHttp\Client;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

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
     * @param HTTPRequest $request
     * @throws ValidationException
     */
    public function index(HTTPRequest $request)
    {
        list($code, $config, $baseURL, $url) = $this->getConfig($request);

        $query = $this->getQuery($config, $code);

        // Setup and request the code
        $service = new Client(['base_uri' => $baseURL]);
        $response = $service->request('GET', $url . $query);

        $this->saveToken($response, $config);

        // A successful write should go back to the admin
        $this->redirect('/admin/settings#Root_Slack');
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function getConfig(HTTPRequest $request)
    {
        // Code param
        $code = $request->getVar('code');
        $config = SiteConfig::current_site_config();
        // Build the URL
        $baseURL = $config->SlackURL;
        $baseURL = (substr($baseURL, -1) === '/') ? $baseURL : $baseURL . '/';
        $url = 'api/oauth.access?';

        return [$code, $config, $baseURL, $url];
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
     * @param HTTPResponse $response
     * @param SiteConfig $config
     * @throws ValidationException
     */
    public function saveToken($response, $config)
    {
        // Convert the JSON to use in our config (hidden from user view)
        $result = Convert::json2array($response->getBody());

        $config->SlackToken = $result['access_token'];
        $config->write();
    }
}
