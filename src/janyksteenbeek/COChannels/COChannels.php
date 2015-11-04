<?php

namespace janyksteenbeek\COChannels;

class COChannels
{
    private $curl;
    private $contentOwner;
    private $cookieFile;
    private $sessionKey;

    /**
     * @param $username
     * @param $password
     * @param $contentowner
     */
    public function __construct($username, $password, $contentowner)
    {
        $this->curl = curl_init();

        $this->contentOwner = $contentowner;

        $this->cookieFile = tempnam(sys_get_temp_dir(), 'COChannels'.uniqid());
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 120);

        $this->logIn($username, $password, $contentowner);
    }

    /**
     * Execute HTTP query.
     *
     * @param $url
     * @param null $postfields
     *
     * @return mixed
     */
    private function call($url, $postfields = null)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        if (!empty($postfields)) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->arrayToQuery($postfields));
        }

        return curl_exec($this->curl);
    }

    /**
     * Convert an array to a HTTP query string.
     *
     * @param $array
     *
     * @return string
     */
    private function arrayToQuery($array)
    {
        $post_string = '';
        foreach ($array as $key => $value) {
            $post_string .= $key.'='.urlencode($value).'&';
        }

        return substr($post_string, 0, -1);
    }

    /**
     * Find all forms in string.
     *
     * @param $data
     *
     * @return bool
     */
    private function getForms($data)
    {
        if (preg_match('/(<form.*?id=.?gaia_loginform.*?<\/form>)/is', $data, $matches)) {
            $inputs = $this->getInputs($matches[1]);

            return $inputs;
        } else {
            return false;
        }
    }

    /**
     * Get all input fields of form.
     *
     * @param $form
     *
     * @return array
     */
    private function getInputs($form)
    {
        $inputs = [];

        $elements = preg_match_all('/(<input[^>]+>)/is', $form, $matches);

        if ($elements > 0) {
            for ($i = 0; $i < $elements; $i++) {
                $el = preg_replace('/\s{2,}/', ' ', $matches[1][$i]);

                if (preg_match('/name=(?:["\'])?([^"\'\s]*)/i', $el, $name)) {
                    $name = $name[1];
                    $value = '';

                    if (preg_match('/value=(?:["\'])?([^"\'\s]*)/i', $el, $value)) {
                        $value = $value[1];
                    }

                    $inputs[$name] = $value;
                }
            }
        }

        return $inputs;
    }

    /**
     * Log in to the content owner.
     *
     * @param $username
     * @param $password
     * @param $contentowner
     *
     * @return bool
     */
    private function logIn($username, $password, $contentowner)
    {
        $data = $this->call('https://accounts.google.com/ServiceLogin?hl=en&service=alerts&continue=http://www.google.com/alerts/manage');
        $formFields = $this->getForms($data);

        $formFields['Email'] = $username;
        $formFields['Passwd'] = $password;
        unset($formFields['PersistentCookie']);
        $result = $this->call('https://accounts.google.com/ServiceLoginAuth', $formFields);

        if (!strpos($result, 'window.history.replaceState(null, document.title,')) {
            return false;
        }
        $channelpage = $this->call('https://www.youtube.com/my_channels?o='.$contentowner);

        $document = new \DOMDocument();
        $document->loadHTML($channelpage);
        $inputs = $document->getElementsByTagName('input');

        foreach ($inputs as $input) {
            if ($input->getAttribute('name') == 'session_token') {
                $this->sessionKey = $input->getAttribute('value');

                return true;
            }
        }

        if (empty($this->sessionKey)) {
            return false;
        }
    }

    /**
     * Add (a) channel(s) to the content owner.
     *
     * @param $channels
     * @param bool $canViewRevenue
     * @param bool $webClaiming
     *
     * @return bool|mixed
     */
    public function addChannel($channels, $canViewRevenue = true, $webClaiming = true)
    {
        if (is_array($channels)) {
            $channels = implode(',', $channels);
        }

        if (!$result = $this->call('https://www.youtube.com/my_channels_ajax', [
            'usernames' => $channels,
            'can_view_revenue' => intval($canViewRevenue),
            'web_claiming' => intval($webClaiming),
            'session_token' => $this->sessionKey,
            'action_add' => 1,
            'so' => 'tcld',
            'sq' => '',
            'o' => $this->contentOwner,
        ])) {
            return false;
        } else {
            return json_decode($result, true);
        }
    }

    /**
     * Remove (a) channel(s) from the content owner
     * Note: You have to specify a channel ID without UC.
     *
     * @param $channels
     *
     * @return bool|mixed
     */
    public function removeChannel($channels)
    {
        if (is_array($channels)) {
            $channels = implode(',', $channels);
        }

        if (!$result = $this->call('https://www.youtube.com/my_channels_ajax?action_remove=1', [
            'channels' => $channels,
            'session_token' => $this->sessionKey,
            'so' => 'tcld',
            'sq' => '',
            'si' => 0,
            'o' => $this->contentOwner,
        ])) {
            return false;
        } else {
            return json_decode($result, true);
        }
    }
}
