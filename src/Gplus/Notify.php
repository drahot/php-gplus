<?php

namespace Gplus;

/**
 * Notify Data Class
 * @author drahot
 */
class Notify extends PostData
{
    
    /**
     * Declare Constants
     * @var int
     */    
    const NOTIFY_TYPE_MYCOMMENT     = 1;
    const NOTIFY_TYPE_MENTION       = 2;
    const NOTIFY_TYPE_PLUSONE       = 3;
    const NOTIFY_TYPE_CIRCLEIN      = 4;
    const NOTIFY_TYPE_OTHERCOMMENT  = 5;
    const SEX_TYPE_MALE             = 1;
    const SEX_TYPE_FEMALE           = 2;
    const SEX_TYPE_OTHER            = 3;

    /**
     * 
     */
    private $typeTable = array(
        2   => self::NOTIFY_TYPE_MYCOMMENT,
        15  => self::NOTIFY_TYPE_MENTION,
        16  => self::NOTIFY_TYPE_MENTION,
        20  => self::NOTIFY_TYPE_PLUSONE,
        6   => self::NOTIFY_TYPE_CIRCLEIN,
        3   => self::NOTIFY_TYPE_OTHERCOMMENT,
    );

    /**
     * Notify Data
     * @var array
     */
    private $notifyData;

    /**
     * PostId Data
     * @var array
     */
    private $postIdData;

    /**
     * Constructor
     * @param GPlus $gplus 
     * @param array $postData 
     * @param array $notifyData 
     * @return void
     */
    public function __construct(GPlus $gplus, array $postData, array $notifyData, array $postIdData)
    {
        parent::__construct($gplus, $postData);
        $this->notifyData = $notifyData;
        $this->postIdData = $postIdData;
    }

    /**
     * Get Notify Data
     * @return array
     */
    public function getNotifyData()
    {
        return $this->notifyData;
    }

    /**
     * Description
     * @param int $row 
     * @return int
     */
    public function getType($row = 0)
    {
        $value = $this->notifyData[$row][1];
        return isset($this->typeTable[$value]) ? $this->typeTable[$value] : -1;
    }

    /**
     * Get User Name
     * @param int $row 
     * @return string
     */
    public function getUserName($row = 0)
    {
        return isset($this->notifyData[$row][1][0]) ? $this->notifyData[$row][1][0] : "";
    }   

    /**
     * Get UserId
     * @param int $row 
     * @return string
     */
    public function getUserId($row = 0)
    {
        return isset($this->notifyData[$row][2][1]) ? $this->notifyData[$row][2][1] : "";
    }   

    /**
     * Get PostId
     * @param int $row 
     * @return string
     */
    public function getPostId($row = 0)
    {
        $postId = isset($this->postIdData[$row]) ? $this->postIdData[$row] : "";
        if (strpos($postId, 'g:') !== false) {
            return '';
        }
        return $postId;
    }

    /**
     * Get Icon
     * @param int $row 
     * @return string
     */
    public function getIcon($row = 0)
    {
        return isset($this->notifyData[$row][2][2]) ? "http:".  $this->notifyData[$row][2][2] : "";
    }   

    /**
     * Get Sex
     * @param type $row 
     * @return type
     */
    public function getSex($row = 0)
    {
        $sex = isset($this->notifyData[$row][2][2]) ? $this->notifyData[$row][2][2] : "";
        if ($sex === "male") {
            return self::SEX_TYPE_MALE;
        } elseif ($sex === "female") {
            return self::SEX_TYPE_FEMALE;
        }
        return self::SEX_TYPE_OTHER;
    }

}