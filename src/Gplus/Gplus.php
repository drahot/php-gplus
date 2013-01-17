<?php

namespace Gplus;

/**
 * 
 * @author drahot
 */
class Gplus
{

    const SEARCH_MODE_ALL               = 1;

    const SEARCH_MODE_PEOPLE_AND_PAGES  = 2;

    const SEARCH_MODE_POSTS             = 3;

    const SEARCH_MODE_SPARKS            = 4;

    const SEARCH_MODE_HANGOUTS          = 5;

    const SEARCH_RANGE_ALL              = 1;

    const SEARCH_RANGE_CIRCLES          = 2;

    const SEARCH_RANGE_ME               = 5;

    const SEARCH_TYPE_BEST              = 1;

    const SEARCH_TYPE_NEW               = 2;
   
    private $client;
    private $mailAddress;
    private $password;
    private $sendId;
    private $userId;    
    private $usePageId;

    public function __construct(
        Client $client, 
        $mailAddress, 
        $password, 
        $sendId, 
        $userId, 
        $usePageId)
    {
        $this->client = $client;
        $this->mailAddress = $mailAddress;
        $this->password = $password;
        $this->sendId = $sendId;
        $this->userId = $userId;
        $this->usePageId = $usePageId;
    }

    public function getMailAddress()
    {
        return $this->mailAddress;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSendId()
    {
        return $this->sendId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function usePage()
    {
        return $this->usePageId;
    }

    public function getLastPostData()
    {
        return $this->client->getLastPostData($this->userId);
    }

    public function getNotifyCount()
    {
        return $this->client->getNotifyCount($this);
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * Description
     * @param string $postId 
     * @return GPlus\Comment
     */
    public function getComment($postId)
    {
        $postData = $this->client->getCommentData($postId);
        if (is_array($postData) && count($postData) > 0) {
        foreach ($postData[0] as $data) {
            if ($data[0] === "os.u") {
                return new Comment($this, array($data[1]));
            }
        }
        return null;
    }

    /**
     * Description
     * @return GPlus\Notify
     */
    public function getNotify()
    {
        $jsonData = $this->usePage 
                  ? $this->client->getNotifyPageData($this->userId)
                  : $this->client->getUserPageData()

        // 通知データを確認済みにする。
        $post = array(
            'time'  => date('Uu'),
            'at'    => $this->sendId,
        );
        $this->client->getGplusData(
            $this, 
            "notifications", 
            "updatelastreadtime", 
            array(), 
            false, 
            $post
        );

        foreach ($jsonData as $data) {
            if ($data[0] === 'on.nr') {
                $notifyData = array();
                $postIdData = array();
                $postData = array();
                foreach ($data[1][0] as $row) {
                    $notifyData[] = isset($row[2][0][1][0]) ? $row[2][0][1][0] : array();
                    $postIdData[] = isset($row[11]) ? $row[11] : '';
                    $postData[] = isset($row[18][0][0]) ? $row[18][0][0] : array();
                }
                return new Notify($this, $postData, $notifyData, $postIdData);
            }
        }
        return null;
    }

    /**
     * Description
     * @param type $node 
     * @param type $limit 
     * @return type
     */
    public function getActivity($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getActivityNodeData($node, $this->userId, $limit);
        } else {
            $jsonData = $this->client->getActivityData($this->userId, $limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new Activity($this, $postData, $node);
        }
        return null;
    }
    
    /**
     * Description
     * @param type $node 
     * @param type $limit 
     * @return type
     */
    public function getHot($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getHotNodeData($node, $limit);
        } else {
            $jsonData = $this->client->getHotData($limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new Hot($this, $postData, $node);
        }
        return null;
    }  

    /**
     * Description
     * @param type $node 
     * @param type $limit 
     * @return type
     */
    public function getStream($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getStreamNodeData($node, $limit);
        } else {
            $jsonData = $this->client->getStreamData($limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new Stream($this, $postData, $node);
        }
        return null;
    }

    /**
     * Description
     * @param type $query 
     * @param type $node 
     * @param type $mode 
     * @param type $range 
     * @param type $type 
     * @return type
     */
    public function getSearch(
        $query, 
        $node = "",
        $mode = self::SEARCH_MODE_ALL, 
        $range = self::SEARCH_RANEG_ALL, 
        $type = self::SEARCH_TYPE_NEW)
    {
        $modes = array(
            self::SEARCH_MODE_ALL, 
            self::SEARCH_MODE_PEOPLE_AND_PAGES,
            self::SEARCH_MODE_POSTS, 
            self::SEARCH_MODE_SPARKS, 
            self::SEARCH_MODE_HANGOUTS
        );
        if (!in_array($mode, $modes){
            throw new \InvalidArgumentException("Invalid mode parameter!");
        }
        $ranges = array(
            self::SEARCH_RANGE_ALL, self::SEARCH_RANGE_CIRCLES, self::SEARCH_RANGE_ME
        );
        if (!in_array($range, $ranges))) {
            throw new \InvalidArgumentException("Invalid range parameter!");
        }
        $types = array(self::SEARCH_TYPE_BEST, self::SEARCH_TYPE_NEW);
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("Invalid range parameter!");
        }

        $jsonData = $this->client->getSearchData($this->sendId, $query, $node, $mode, $range, $type);
        foreach ($jsonData as $data) {
            if ($data[0] === "sp.sqr") {
                $postData = $data[1][1][0][0];
                $node = isset($data[1][1]) ? $data[1][1] : "";
                return new Search($this, $postData, $node);
            }
        }
        return null;
    }

    /**
     * Description
     * @return type
     */
    public function getPost()
    {
        return new Post($this);
    }

    /**
     * Description
     * @param type array $jsonData 
     * @return type
     */
    private function getPostDataAndNode(array $jsonData)
    {
        $postData   = null;
        $node       = null;
        if ($jsonData) {
            foreach ($jsonData[0] as $data) {
                if ($data[0] === "os.nu") {
                    $postData = $data[1][0];
                    $node = isset($data[1][1]) ? $data[1][1] : "";
                    break;
                }
            }
        }
        return array($postData, $node);
    }

}
