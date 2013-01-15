<?php

namespace Gplus;

use Goutte\Client as GoutteClient;

/**
 * 
 * @author drahot
 */
class Client
{
    const HTTP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/23.0.1271.101 Safari/535.7';

    private $client;
    private $urls = array(
        "login"         => "https://accounts.google.com/login",
        "loginAuth"     => "https://accounts.google.com/ServiceLoginAuth",
        "plus"          => "https://plus.google.com/",
        "posts"         => "https://plus.google.com/u/0/%s/posts"
        "page"          => "https://plus.google.com/b/%s/_/%s/%s",
        "user"          => "https://plus.google.com/_/%s/%s",
        "comment"       => "https://plus.google.com/_/stream/getactivity/",
        "pagenotify"    => "https://plus.google.com/b/%s/_/notifications/getnotificationsdata",
        "usernotify"    => "https://plus.google.com/_/notifications/getnotificationsdata",
        "activity"      => "https://plus.google.com/_/stream/getactivities/",
    );

    /**
     * Description
     * @return void
     */
    public function __construct()
    {
        $this->client = new GoutteClient(
            array('HTTP_USER_AGENT' => self::HTTP_USER_AGENT)
        );
    }

    /**
     * Description
     * @param type $mailAddress 
     * @param type $password 
     * @return type
     */
    public function doLogin($mailAddress, $password)
    {
        $crawler = $this->doRequest('GET', $this->urls['login']);
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
        $this->doRequest('POST', $this->urls['loginAuth'], $params);
        if ($this->client->getRequest()->getUri() === $this->urls['loginAuth']) {
            throw new Exception("Invalid Login!");
        }
    }

    /**
     * Description
     * @return array
     */
    public function doPlus()
    {
        $this->doRequest('GET', $this->urls['plus']);
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
     * Description
     * @param string $userId 
     * @return array
     */
    public function getLastPostData($userId)
    {
        $url = sprintf($this->urls['posts'], $userId);
        $this->doRequest('GET', $url);
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
     * Description
     * @param type $method 
     * @param type $uri 
     * @param type array $parameters 
     * @return type
     */
    public function doRequest($method, $uri, array $parameters = array())
    {
        return $this->client->request($method, $uri, $parameters);
    }
    /**
     * Description
     * @param type GPlus $gplus 
     * @return type
     */
    public function getNotifyCount(GPlus $gplus)
    {
        $params = array(
            'poll'  => 'false',
            'pid'   => '119',
        );
        $content = $this->getGplusData($gplus, 'n', 'guc', $params);
        $jsonData = JSONHelper::load($content);
        if ($jsonData) {
            foreach ($jsonData as $data) {
                if ($data[0] === "on.uc") {
                    return $data[1];
                }
            }
        }
        return 0
    }

    /**
     * Description
     * @param type GPlus $gplus 
     * @param type $type 
     * @param type $function 
     * @param type array $params 
     * @param type $useSlash 
     * @param type array $postData 
     * @return type
     */
    public function getGplusData(
        GPlus $gplus, $type, $function, array $params, $useSlash = false, array $postData = array()
    ){
        $slash = $useSlash ? '/' : '';
        $url = $this->getFunctionUrl($gplus, $type, $functionUrl.$slash, $isPage, $params);

        if (count($postData) > 0) {
            $this->doRequest('POST', $url, $postData);
        } else {
            $this->doRequest('GET', $url);
        }
        return $this->client->getResponse()->getContent();
    }

    /**
     * Description
     * @param type GPlus $gplus 
     * @param type $type 
     * @param type $functionUrl 
     * @param type $isPage 
     * @param type array $params 
     * @return type
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
     * Description
     * @return type
     */
    private function getRequestId()
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
        $this->doRequest('GET', $this->urls['comment'], $params);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::load($content);
    }

    /**
     * Description
     * @param string $userId 
     * @return array
     */
    public function getNotifyPageData($userId)
    {
        $url = sprintf($this->urls['pagenotify'], $userId);
        $this->doRequest('GET', $url);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::load($content);
   }
    
    /**
    * Description
    * @return type
    */   
    public function getNotifyUserData()
    {
        $this->doRequest('GET', $this->urls['pagenotify']);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::load($content);
    }

    /**
     * Description
     * @param type $userId 
     * @param type $limit 
     * @return type
     */
    public function getActivityData($userId, $limit = 20)
    {
        return $this->getActivityNode(null, $userId, $limit);
    }

    /**
     * Description
     * @param string $node 
     * @param string $userId 
     * @param int $limit 
     * @return array
     */
    public function getActivityNodeData($node, $userId, $limit = 20)
    {
        $sp = sprintf(
            "%5B1%2C2%2C%22%s%22%2Cnull%2Cnull%2C%d%2Cnull%2C%22social%2Egoogle%2Ecom%22%2C%5B%5D%5D", 
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

    public function getHotData($limt = 20)
    {
        return $this->getHotNodeData(null, $limit);
    }

    /**
     * Description
     * @param type $node 
     * @param type $limit 
     * @return type
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
     * Description
     * @param type $limt 
     * @return type
     */
    public function getStreamData($limt = 20)
    {
        return $this->getStreamNodeData(null, $limit);
    }

    /**
     * Description
     * @param type $node 
     * @param type $limit 
     * @return type
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
     * Description
     * @param type array $params 
     * @return type
     */
    protected function requestActivity(array $params)
    {
        $this->doRequest('GET', $this->urls['activity'], $params);
        $content = $this->client->getResponse()->getContent();
        return JSONHelper::load($content);
    }
}