<?php


/**
 * Class SlackStatusController
 *
 */
class SlackStatusController extends Controller
{
    private static $allowed_actions = [
        'usercount',
        'badge'
    ];

    /**
     * @return int
     * @throws ValidationException
     */
    public function usercount()
    {
        /** @var SiteConfig $config */
        $config = SiteConfig::current_site_config();
        // Break if there is a configuration error
        if (!$config->SlackURL || !$config->SlackToken || !$config->SlackChannel) {
            return '';
        }
        $params = $this->getRequestParams($config);

        return $this->getStatus($config, $params);
    }

    /**
     * @return SS_HTTPResponse
     * @throws ValidationException
     */
    public function badge()
    {
        $config = SiteConfig::current_site_config();
        $params = $this->getRequestParams($config);
        $count = $this->getStatus($config, $params);
        list($width, $pos) = $this->getSVGSettings($count);

        $body = $this->renderWith('SVGTemplate', ['Count' => $count, 'Width' => $width, 'Pos' => $pos]);
        $response = new SS_HTTPResponse($body);
        $response->addHeader('Content-Type', 'image/svg+xml');

        return $response;
    }


    protected function getRequestParams($config)
    {
        return [
            'token'   => $config->SlackToken,
            'type'    => 'post',
            'channel' => $config->SlackChannel,
            'scope'   => 'identify,read,post,client',
        ];
    }

    /**
     * @param SiteConfig $config
     * @param array $params
     * @return int
     * @throws ValidationException
     */
    public function getStatus($config, $params = [])
    {
        /** @var SlackUserCount $count */
        $count = SlackUserCount::get()->first();
        // To limit the amount of API requests, only update the count
        // once every 3 hours
        if ($count) {
            $dateTime = SS_Datetime::create();
            $dateTime->setValue($count->LastEdited);
            $diff = explode(' ', $dateTime->TimeDiffIn('hours'));
            if ($diff[0] < 3) {
                return $count->UserCount;
            }
        } else {
            $count = SlackUserCount::create();
        }

        return $this->getSlackCount($config, $params, $count);
    }

    /**
     * @param SiteConfig $config
     * @param array $params
     * @param SlackUserCount $count
     * @return int
     * @throws \ValidationException
     */
    protected function getSlackCount($config, $params, $count)
    {
        list($url, $service) = $this->getRestfulService($config);

        $response = $service->request($url, 'POST', $params);
        $result = Convert::json2array($response->getBody());

        return $this->validateResponse($count, $result);
    }

    /**
     * @param $count
     * @return array
     */
    public function getSVGSettings($count)
    {
        if ($count < 100) {
            $width = 25;
            $pos = 60;
        } elseif ($count < 1000) {
            $width = 35;
            $pos = 65;
        } else {
            $width = 45;
            $pos = 70;
        }

        return [$width, $pos];
    }

    /**
     * @param $config
     * @return array
     */
    public function getRestfulService($config)
    {
        $now = time();
        $baseURL = $config->SlackURL;
        $baseURL = (substr($baseURL, -1) === '/') ? $baseURL : $baseURL . '/';
        $url = 'api/channels.info?t=' . $now;

        /** @var RestfulService $service with an _uncached_ response */
        $service = RestfulService::create($baseURL, 0);

        return array($url, $service);
    }

    /**
     * @param SlackUserCount $count
     * @param array $result
     * @return int
     */
    public function validateResponse($count, $result)
    {
        if (isset($result['ok']) && $result['ok']) {
            $userCount = count($result['channel']['members']);
            $count->UserCount = $userCount;
            $count->write();

            return $userCount;
        }

        return 0;
    }
}
