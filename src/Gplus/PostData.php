<?php

namespace Gplus;

/**
 * Post Data Class 
 * @author drahot
 */
class PostData
{

    private $gplus      = null;
    private $postData   = null;
    private $node       = null;

    /**
     * Constructor
     * @param GPlus $gplus 
     * @param array $postData 
     * @param string $node 
     * @return void
     */
    public function __construct(GPlus $gplus, array $postData, $node = null)
    {
        $this->gplus    = $gplus;
        $this->postData = $postData;
        $this->node     = $node;
    }
    
    /**
     * Get Post Data
     * @return array
     */    
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * Get Node
     * @return string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Get Count
     * @return int
     */
    public function getCount()
    {
        return count($this->postData);
    }

    /**
     * Get UserId
     * @param int $row 
     * @return string
     */
    public function getUserId($row = 0)
    {
        if (is_array($this->postData)) {
            return $this->getString($row, 16);
        }
        return "";
    }

    /**
     * Get PostId
     * @param int $row 
     * @return string
     */
    public function getPostId($row = 0)
    {
        if (is_array($this->postData)) {
            return $this->getString($row, 8);
        }
        return "";
    }
    
    /**
     * Get Username
     * @param int $row 
     * @return string
     */    
    public function getUserName($row = 0)
    {
        if (is_array($this->postData)) {
            return $this->getString($row, 3);
        }
        return "";
    }

    /**
     * Get Body
     * @param int $row 
     * @return string
     */
    public function getBody($row = 0)
    {
        if (is_array($this->postData)) {
            $body = $this->getString($row, 47);
            if ($body !== "null") {
                return $body;
            } else {
                return $this->getString($row, 4);
            }
        }
        return "";
    }
    
    /**
     * Get Reshare PostId
     * @param int $row 
     * @return string
     */    
    public function getResharePostId($row = 0)
    {
        if (is_array($this->postData)) {
            $postId = $this->getString($row, 39);
            if ($postId !== "null") {
                return $postId; 
            }
        }
        return "";
    }   
    
    /**
     * Get Share Count
     * @param int $row 
     * @return int
     */
    public function getShareCount($row = 0)
    {
        if (is_array($this->postData)) {
            return intval($this->getString($row, 96));
        }
        return 0;
    }   

    /**
     * Get PlusOne Count
     * @param int $row 
     * @return int
     */
    public function getPlusOneCount($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][73][16])) {
                $plusOne = $this->postData[$row][73][16];
                if ($plusOne !== "null") {
                    return intval($plusOne);
                }
            }           
        }
        return 0;
    }

    /**
     * Get Comment Total
     * @param int $row 
     * @return int
     */
    public function getCommentTotal($row = 0)
    {
        if (is_array($this->postData)) {
            return intval($this->getString($row, 93));
        }
        return 0;
    }

    /**
     * Get Comment Count
     * @param int $row 
     * @return int
     */
    public function getCommentCount($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][7]) && is_array($this->postData[$row][7])) {
                return count($this->postData[$row][7]);
            }
        }
        return 0;
    }
    
    /**
     * Get Comment UserId
     * @param int $row 
     * @param int $commentRow 
     * @return string
     */
    public function getCommentUserId($row = 0, $commentRow = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][7][$commentRow][6])) {
                return $this->postData[$row][7][$commentRow][6];
            }           
        }
        return "";
    }

    /**
     * Get Comment Username
     * @param int $row 
     * @param int $commentRow 
     * @return string
     */    
    public function getCommentUserName($row = 0, $commentRow = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][7][$commentRow][1])) {
                return $this->postData[$row][7][$commentRow][1];
            }           
        }
        return "";
    }   

    /**
     * Get Comment Id
     * @param int $row 
     * @param int $commentRow 
     * @return string
     */
    public function getCommentId($row = 0, $commentRow = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][7][$commentRow][4])) {
                return $this->postData[$row][7][$commentRow][4];
            }           
        }
        return "";
    }

    /**
     * Get Comment Body
     * @param int $row 
     * @param int $commentRow
     * @return string
     */
    public function getCommentBody($row = 0, $commentRow = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][7][$commentRow][2])) {
                return $this->postData[$row][7][$commentRow][2];
            }           
        }
        return "";
    }

    /**
     * Get Sparks Id
     * @param int $row 
     * @return string
     */
    public function getSparksId($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][88])) {
                return $this->postData[$row][88];
            }           
        }
        return "";
    }

    /**
     * Get Sparks Title
     * @param int $row 
     * @return string
     */
    public function getSparksTitle($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][11][0][3])) {
                return $this->postData[$row][11][0][3];
            }           
        }
        return "";
    }

    /**
     * Get Sparks Author
     * @param int $row 
     * @return string
     */
    public function getSparksAuthor($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][82][2][3][0])) {
                return $this->postData[$row][82][2][3][0];
            }
        }
        return "";
    }

    /**
     * Get Sparks Description
     * @param int $row 
     * @return string
     */
    public function getSparksDescription($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][11][0][21])) {
                return $this->postData[$row][11][0][21];
            }
        }
        return "";
    }

    /**
     * Get Sparks Link
     * @param int $row 
     * @return string
     */
    public function getSparksLink($row = 0)
    {
        if (is_array($this->postData)) {
            if (isset($this->postData[$row][13])) {
                return $this->postData[$row][13];
            }
        }
        return "";
    }

    /**
     * Get Post Data 
     * @param int $row 
     * @param int $col 
     * @return string
     */
    protected function getString($row, $col)
    {
        return (isset($this->postData[$row][$col])) ? $this->postData[$row][$col] : "";
    }

}