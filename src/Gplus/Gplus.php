<?php

namespace Gplus;

/**
 * 
 * @author drahot
 */
class Gplus
{

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
    public function createComment($postId)
    {
        $postData = $this->getClient()->getCommentData($postId);
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
    public function createNotify()
    {
        $jsonData = $this->usePage 
                  ? $this->client->getNotifyPageData($this->userId)
                  : $this->client->getUserPageData()

        // 通知データを確認済みにする。
        $post = array(
            'time'  => date('Uu'),
            'at'    => $this->sendId,
        );
        $this->client->getGplusData($this, "notifications", "updatelastreadtime", array(), false, $post);

        foreach ($jsonData as $data) {
            if ($data[0] === 'on.nr') {
                $notifyData = array();
                $postIdData = array();
                $postData = array();
                foreach ($data[1][0] as $row) {
                    $notifyData[] = isset($row[2][0][1][0]) ? $row[2][0][1][0] : array();
                    $postIdData[] = isset($row[11]) ? $row[11] : '';
                    $postData[] = isset($row[18][0][0]) ? $row[11] : array();
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
    public function createActivity($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getActivityNodeData($node, $this->userId, $limit);
        } else {
            $jsonData = $this->client->getActivityData($this->userId, $limit);
        }
        if (count($jsonData) > 0)  {
            foreach ($jsonData[0] as $data) {
                if ($data[0] === "os.nu") {
                    $postData = $data[1][0];
                    $node = isset($data[1][1]) ? $data[1][1] : "";
                    return new Activity($this, $posData, $node);
                }
            }
        }
        return null;
    }
    
    public function createHot($node = null, $limit = 20)
    {
        if ($node) {
            $jsonData = $this->client->getHotNodeData($node, $this->userId, $limit);
        } else {
            $jsonData = $this->client->getHotData($this->userId, $limit);
        }
        if (count($jsonData) > 0)  {
            foreach ($jsonData[0] as $data) {
                if ($data[0] === "os.nu") {
                    $postData = $data[1][0];
                    $node = isset($data[1][1]) ? $data[1][1] : "";
                    return new Hot($this, $posData, $node);
                }
            }
        }
        return null;
    }  

}
