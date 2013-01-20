<?php

namespace Gplus;

/**
 * Gplus Class
 * @author drahot
 */
class Gplus
{
    /**
     * Declare constants 
     */
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
   
    /**
     * Declare instance variable 
     */
    private $client;
    private $mailAddress;
    private $password;
    private $sendId;
    private $userId;    
    private $usePageId;
    private $isLockComment = false;
    private $isLockShare = false;

    /**
     * Constructor
     * @param Client $client 
     * @param string $mailAddress 
     * @param string $password 
     * @param string $sendId 
     * @param string $userId 
     * @param bool $usePageId 
     * @return void
     */    
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

    /**
     * Get Comment Data
     * @param string $postId 
     * @return Gplus\Comment
     */
    public function comment($postId)
    {
        $postData = $this->client->getCommentData($postId);
        if (is_array($postData) && count($postData) > 0) {
            foreach ($postData[0] as $data) {
                if ($data[0] === "os.u") {
                    return new PostData($this, array($data[1]));
                }
            }
        }
        return null;
    }

    /**
     * Get Notify Data
     * @return Gplus\Notify
     */
    public function notify()
    {
        $jsonData = $this->usePage() 
                  ? $this->client->getNotifyPageData($this->userId)
                  : $this->client->getNotifyUserData();

        if (!$jsonData) {
            return null;                        
        }

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
     * Get Activity Data
     * @param string $node 
     * @param int $limit 
     * @return Gplus\PostData
     */
    public function activity($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getActivityNodeData($node, $this->userId, $limit);
        } else {
            $jsonData = $this->client->getActivityData($this->userId, $limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new PostData($this, $postData, $node);
        }
        return null;
    }
    
    /**
     * Get Hot Data
     * @param string $node 
     * @param int $limit 
     * @return Gplus\PostData
     */
    public function hot($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getHotNodeData($node, $limit);
        } else {
            $jsonData = $this->client->getHotData($limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new PostData($this, $postData, $node);
        }
        return null;
    }  

    /**
     * Get Stream Data
     * @param string $node 
     * @param int $limit 
     * @return Gplus\PostData
     */
    public function stream($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getStreamNodeData($node, $limit);
        } else {
            $jsonData = $this->client->getStreamData($limit);
        }
        list($postData, $node) = $this->getPostDataAndNode($jsonData);
        if ($postData) {
            return new PostData($this, $postData, $node);
        }
        return null;
    }

    /**
     * Get Search
     * @param string $query 
     * @param string $node 
     * @param int $mode 
     * @param int $range 
     * @param int $type 
     * @return Gplus\Search
     */
    public function search(
        $query, 
        $node   = "",
        $mode   = self::SEARCH_MODE_ALL, 
        $range  = self::SEARCH_RANGE_ALL, 
        $type   = self::SEARCH_TYPE_NEW)
    {
        $modes = array(
            self::SEARCH_MODE_ALL, 
            self::SEARCH_MODE_PEOPLE_AND_PAGES,
            self::SEARCH_MODE_POSTS, 
            self::SEARCH_MODE_SPARKS, 
            self::SEARCH_MODE_HANGOUTS
        );
        if (!in_array($mode, $modes)) {
            throw new \InvalidArgumentException("Invalid mode parameter!");
        }
        $ranges = array(
            self::SEARCH_RANGE_ALL, self::SEARCH_RANGE_CIRCLES, self::SEARCH_RANGE_ME
        );
        if (!in_array($range, $ranges)) {
            throw new \InvalidArgumentException("Invalid range parameter!");
        }
        $types = array(self::SEARCH_TYPE_BEST, self::SEARCH_TYPE_NEW);
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("Invalid range parameter!");
        }

        $jsonData = $this->client->getSearchData($this->sendId, $query, $node, $mode, $range, $type);
        if ($jsonData) {
            foreach ($jsonData as $data) {
                if ($data[0] === "sp.sqr") {
                    $postData = $data[1][1][0][0];
                    $node = isset($data[1][1]) ? $data[1][1] : "";
                    return new PostData($this, $postData, $node);
                }
            }
        }
        return null;
    }

    /**
     * Get Post Object
     * @return Gplus\Post
     */
    public function post()
    {
        return new Post($this);
    }

    /**
     * Get PostData And Node
     * @param array $jsonData 
     * @return array
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

    /**
     * Get MailAddress
     * @return string
     */    
    public function getMailAddress()
    {
        return $this->mailAddress;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get sendId
     * @return string
     */
    public function getSendId()
    {
        return $this->sendId;
    }

    /**
     * Get UserId
     * @return type
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get UsePage
     * @return bool
     */
    public function usePage()
    {
        return $this->usePageId;
    }

    /**
     * Get Last Post Data
     * @return array
     */
    public function getLastPostData()
    {
        return $this->client->getLastPostData($this->userId);
    }

    /**
     * Get Notify Count
     * @return type
     */
    public function getNotifyCount()
    {
        return $this->client->getNotifyCount($this);
    }

    /**
     * Get Http Client
     * @return type
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get Comment Lock
     * @return bool
     */
    public function isLockComment()
    {
        return $this->isLockComment;
    }

    /**
     * Set Comment Lock
     * @param bool $isLockComment 
     * @return void
     */
    public function setLockComment($isLockComment)
    {
        $this->isLockComment = $isLockComment;        
    }
    
    /**
     * Get Share Lock
     * @return bool
     */
    public function isLockShare()
    {
        return $this->isLockShare;
    }

    /**
     * Set Share Lock
     * @param bool $isLockShare 
     * @return void
     */
    public function seLockShare($isLockShare)
    {
        $this->isLockShare = $isLockShare;
    }

}
