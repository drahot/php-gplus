<?php

namespace Gplus;

use Goutte\Client as GoutteClient;

/**
 * Gplus Access Client
 * @author drahot
 */
class Client
{
    /**
     * HTTP USER AGENT
     * @var string
     */
    const HTTP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/23.0.1271.101 Safari/535.7';

    private $client;
    private $urls = array(
        "login"         => "https://accounts.google.com/login",
        "loginAuth"     => "https://accounts.google.com/ServiceLoginAuth",
        "plus"          => "https://plus.google.com/",
        "posts"         => "https://plus.google.com/u/0/%s/posts",
        "page"          => "https://plus.google.com/b/%s/_/%s/%s",
        "user"          => "https://plus.google.com/_/%s/%s",
        "comment"       => "https://plus.google.com/_/stream/getactivity/",
        "pagenotify"    => "https://plus.google.com/b/%s/_/notifications/getnotificationsdata",
        "usernotify"    => "https://plus.google.com/_/notifications/getnotificationsdata",
        "activity"      => "https://plus.google.com/_/stream/getactivities/",
        "search"        => "https://plus.google.com/_/s/query?_reqid=%s",
        "upload1"       => "https://plus.google.com/_/upload/photos/resumable?authuser=0",
    );

    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        $this->client = new GoutteClient(
            array('HTTP_USER_AGENT' => self::HTTP_USER_AGENT)
        );
    }

    /**
     * Google Plus Login
     * @param string $mailAddress 
     * @param string $password 
     * @return void
     */
    public function login($mailAddress, $password)
    {
        $crawler = $this->request('GET', $this->urls['login']);
        $dsh = $crawler->filter('#dsh')->attr('value');
        $GALX = $crawler->filter('input[name=GALX]')->attr('value');
        $params = array(
            'dsh'               => $dsh,
            'GALX'              => $GALX,
            'pstMsg'            => '1',
            'dnConn'            => 'https://accounts.youtube.com',
            'timeStmp'          => '',
            'secTok'            => '',
            'Email'             => '',
            'Passwd'            => '',
            'Email'             => $mailAddress,
            'Passwd'            => $password,
            'signIn'            => 'Sign in',
            'PersistentCookie'  => 'yes',
            'rmShown'           => '1',
            '_utf8'             => '&#9731;',
        );
        $this->request('POST', $this->urls['loginAuth'], $params);
        if ($this->client->getRequest()->getUri() === $this->urls['loginAuth']) {
            throw new Exception("Invalid Login!");
        }
    }

    /**
     * Get User Data
     * @return array
     */
    public function getUserData()
    {
        $this->request('GET', $this->urls['plus']);
        if ($this->client->getRequest()->getUri() !== $this->urls['plus']) {
            throw new Exception("Google Plus cannot Access!");
        }
        $content = $this->client->getResponse()->getContent();
        $sendId = null;
        if (preg_match("/\"(AObGSA.*:[0-9]*)\"/", $content, $matches)) {
            $sendId = $matches[1];
        }
        $userId = null;
        if (preg_match("/key: '2',.+data:[ ]\[.([0-9]*)/", $content, $matches)) {
            $userId = $matches[1];
        }
        if (is_null($sendId) || is_null($userId)) {
            throw new Exception("Invalid sendId Or UserId!");
        }
        return array($sendId, $userId);
    }

    /**
     * Get Last Post Data
     * @param string $userId 
     * @return array
     */
    public function getLastPostData($userId)
    {
        $url = sprintf($this->urls['posts'], $userId);
        $this->request('GET', $url);
        $content = $this->client->getResponse()->getContent();
        $postId = '';
        if (preg_match('/\"(.*)\",\"\",\"s:updates:esshare\"/', $content, $matches)) {
            $postId = $matches[1];
        }
        $commentCount = 0;
        if (preg_match('/\"\d+-\d+-\d+\",([0-9]+),/', $content, $matches)) {
            $commentCount = intval($matches[1]);
        }
        return array(
            'postId'        => $postId,
            'commentCount'  => $commentCount,
        );
    }

    /**
     * Request
     * @param string $method 
     * @param string $uri 
     * @param array $parameters 
     * @return string
     */
    public function request($method, $uri, array $parameters = array())
    {
        return $this->client->request($method, $uri, $parameters);
    }

    /**
     * Get Notify Count
     * @param GPlus $gplus 
     * @return int
     */
    public function getNotifyCount(GPlus $gplus)
    {
        $params = array(
            'poll'  => 'false',
            'pid'   => '119',
        );
        $content = $this->getGplusData($gplus, 'n', 'guc', $params);
        $jsonData = JSONHelper::decode($content);
        if ($jsonData) {
            foreach ($jsonData as $data) {
                if ($data[0] === "on.uc") {
                    return $data[1];
                }
            }
        }
        return 0;
    }

    /**
     * Get Gplus Data
     * @param GPlus $gplus 
     * @param string $type 
     * @param string $function 
     * @param string array $params 
     * @param bool $useSlash 
     * @param array $postData 
     * @return string
     */
    public function getGplusData(
        GPlus $gplus, $type, $function, array $params, $useSlash = false, array $postData = array()
    ){
        $slash = $useSlash ? '/' : '';
        $url = $this->getFunctionUrl($gplus, $type, $function.$slash, $gplus->usePage(), $params);
        if (count($postData) > 0) {
            $this->request('POST', $url, $postData);
        } else {
            $this->request('GET', $url);
        }
        return $this->client->getResponse()->getContent();
    }

    /**
     * Get Function Url
     * @param GPlus $gplus 
     * @param string $type 
     * @param string $functionUrl 
     * @param boool $isPage 
     * @param array $params 
     * @return string
     */
    protected function getFunctionUrl(GPlus $gplus, $type, $functionUrl, $isPage, array $params)
    {
        if ($isPage) {
            return sprintf(
                $this->urls["page"], 
                $gplus->getUserId(), 
                $type,
                $functionUrl,
                http_build_query($params),
                $this->getRequestId()
            );
        } else {
            return sprintf(
                $this->urls["user"], 
                $type,
                $functionUrl,
                http_build_query($params),
                $this->getRequestId()
            );
        }
    }

    /**
     * Get Request Id
     * @return string
     */
    public function getRequestId()
    {
        $result = '';
        foreach (range(1, 7) as $i) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    /**
     * Comment JSON Data 
     * @param string $postId 
     * @return array
     */
    public function getCommentData($postId)
    {
        $params = array(
            'updateId' => $postId,
            '_reqid' => $this->getRequestId(),
        );
        $this->request('GET', $this->urls['comment'], $params);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::decode($content);
    }

    /**
     * Get Notify Page Data
     * @param string $userId 
     * @return array
     */
    public function getNotifyPageData($userId)
    {
        $url = sprintf($this->urls['pagenotify'], $userId);
        $this->request('GET', $url);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::decode($content);
   }
    
    /**
     * Get Notify User Data
     * @return array
     */   
    public function getNotifyUserData()
    {
        $this->request('GET', $this->urls['pagenotify']);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::decode($content);
    }

    /**
     * Get Activity Data
     * @param string $userId 
     * @param int $limit 
     * @return array
     */
    public function getActivityData($userId, $limit = 20)
    {
        return $this->getActivityNodeData(null, $userId, $limit);
    }

    /**
     * Get Activity Node Data
     * @param string $node 
     * @param string $userId 
     * @param int $limit 
     * @return array
     */
    public function getActivityNodeData($node, $userId, $limit = 20)
    {
        $sp = sprintf(
            '[1,2,"%s",null,null,%d,null,"social.google.com",[]]',
            $userId,
            $limit
        );
        $params = array(
            "sp"        => $sp,
            "hl"        => "ja",
            "_reqid"    => $this->getRequestId(),
            "rt"        => "j",
        );
        if ($node) {
            $params["ct"] = $node;
        }
        return $this->requestActivity($params);
    }

    /**
     * Get Hot Data
     * @param int $limit 
     * @return array
     */
    public function getHotData($limit = 20)
    {
        return $this->getHotNodeData(null, $limit);
    }

    /**
     * Get Hot Node Data
     * @param string $node 
     * @param int $limit 
     * @return array
     */
    public function getHotNodeData($node, $limit = 20)
    {
        $sp = sprintf(
            '[16,2,null,null,null,%s,null,"social.google.com",[],null,null,null,null,null,null,[]]',
            $limit
        );
        $params = array(
            "sp"        => $sp,
            "hl"        => "ja",
            "_reqid"    => $this->getRequestId(),
            "rt"        => "j",
        );
        if ($node) {
            $params["ct"] = $node;
        }
        return $this->requestActivity($params);
    }

    /**
     * Get Stream Data
     * @param int $limit 
     * @return array
     */
    public function getStreamData($limit = 20)
    {
        return $this->getStreamNodeData(null, $limit);
    }

    /**
     * Get Stream Node Data
     * @param string $node 
     * @param int $limit 
     * @return array
     */
    public function getStreamNodeData($node, $limit = 20)
    {
        $sp = sprintf(
            '[1,2,null,null,null,%s,null,"social.google.com",[],null,null,null,null,null,null,[]]',
            $limit
        );
        $params = array(
            "sp"        => $sp,
            "hl"        => "ja",
            "_reqid"    => $this->getRequestId(),
            "rt"        => "j",
        );
        if ($node) {
            $params["ct"] = $node;
        }
        return $this->requestActivity($params);
    }

    /**
     * Get Search Data
     * @param string $sendId 
     * @param string $query 
     * @param string $node 
     * @param int $mode 
     * @param int $range 
     * @param int $type 
     * @return array
     */
    public function getSearchData($sendId, $query, $node, $mode, $range, $type)
    {   
        $data = array(
            array($query, $mode, $type, array($range)),
            "null",
            ($node) ? array($node) : array(),
        );
        $json = JSONHelper::encode($data);
        $params = array(
            "srchrp"    => $json,
            "at"        => $sendId,
        );
        $url = sprintf($this->urls['search'], $this->getRequestId());
        $this->request('POST', $url, $params);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::decode($content);
    }

    /**
     * Upload Image
     * TODO Test
     * @param array $uploadData 
     * @param array $uploadImage 
     * @return void
     */
    public function uploadImage($uploadData, $uploadImage)
    {
        $this->client->request('POST', $this->urls['upload1'], array(), array(), array(), $uploadData);
        $content = $this->client->getResponse()->getContent();
        $resultData = JSONHelper::decode($content);
        $uploadUrl = $resultData["sessionStatus"]["externalFieldTransfers"][0]["formPostInfo"]["url"];
        $this->client->setHeader("Content-Type", "application/octet-stream");
        $this->client->setHeader("X-HTTP-Method-Override", "PUT");
        $this->client->request('POST', $uploadUrl, array(), array($uploadImage));
    }

    /**
     * Request Activity Url
     * @param array $params 
     * @return array
     */
    protected function requestActivity(array $params)
    {
        $this->request('POST', $this->urls['activity'], $params);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::decode($content);
    }

}