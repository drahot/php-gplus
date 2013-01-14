<?php

namespace GPlus;

/**
 * 
 * @author drahot
 */
abstract class PostData
{

	private $gplus = null;
	private $postJSON = null;
	private $node = null;
	// private $num = 0;
	// private $mode = null;
	// private $query = null;
	// private $range = null;
	// private $type = null;

	public function __construct(
		GPlus $gplus, 
		array $postJSON, 
		$node = null)
	{
		$this->gplus = $gplus;
		$this->postJSON = $postJSON;
		$this->node = $node;
	}
	
	public function getPostJSON()
	{
		return $this->postJSON;
	}

	public function setPostJSON(array $postJSON)
	{
		$this->postJSON = $postJSON;
	}

	public function getNode()
	{
		return $this->node;
	}

	public function setNode($node)
	{
		$this->ndoe = $node;
	}

	// public function getNum()
	// {
	// 	return $this->node;
	// }

	// public function setNum($num)
	// {
	// 	$this->num = $num;
	// }

	// public function getQuery()
	// {
	// 	return $this->query;
	// }

	// public function setQuery($query)
	// {
	// 	$this->query = $query;
	// }

	// public function getMode()
	// {
	// 	return $this->mode;
	// }

	// public function setMode($mode)
	// {
	// 	$this->mode = $mode;
	// }

	// public function getRange()
	// {
	// 	return $this->range;
	// }

	// public function setRange($range)
	// {
	// 	$this->range = $range;
	// }

	// public function getType()
	// {
	// 	return $this->type;	
	// }

	// public function setType($type)
	// {
	// 	$this->type = $type;
	// }

	public function getPostCount()
	{
		return count($this->postJSON);
	}
	
	public function getPostUserName($row = 0)
	{
		if (is_array($this->postJSON)) {
			return $this->getString($row, 3);
		}
		return "";
	}

	public function getPostBody($row = 0)
	{
		if (is_array($this->postJSON)) {
			$body = $this->getString($row, 47);
			if ($body !== "null") {
				return $body;
			} else {
				return $this->getString($row, 4);
			}
		}
		return "";
	}

	public function getPostUserId($row = 0)
	{
		if (is_array($this->postJSON)) {
			return $this->getString($row, 16);
		}
		return "";
	}

	public function getPostId($row = 0)
	{
		if (is_array($this->postJSON)) {
			return $this->getString($row, 8);
		}
		return "";
	}
	
	public function getResharePostId($row = 0)
	{
		if (is_array($this->postJSON)) {
			$postId = $this->getString($row, 39);
            if ($postId !== "null") {
            	return $postId;	
            }
		}
        return "";
	}	

	public function getCommentTotal($row = 0)
	{
		if (is_array($this->postJSON)) {
			return intval($this->getString($row, 93));
		}
		return 0;
	}

	public function getCommentCount($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][7]) && is_array($this->postJSON[$row][7])) {
				return count($this->postJSON[$row][7]);
			}
		}
		return 0;
	}
	
	public function getShareCount($row = 0)
	{
		if (is_array($this->postJSON)) {
			return intval($this->getString($row, 96));
		}
		return 0;
	}	

	public function getPlusOneLength($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][73][16])) {
				$plusOne = $this->postJSON[$row][73][16];
				if ($plusOne !== "null") {
					return intval($plusOne);
				}
			}			
		}
		return 0;
	}
	
	public function getCommentUserName($row = 0, $col = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][7][$col][1])) {
 				return $this->postJSON[$row][7][$col][1];
			}			
		}
		return "";
	}	

	public function getCommentUserId($row = 0, $col = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][7][$col][6])) {
 				return $this->postJSON[$row][7][$col][6];
			}			
		}
		return "";
	}

	public function getCommentId($row = 0, $col = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][7][$col][4])) {
 				return $this->postJSON[$row][7][$col][4];
			}			
		}
		return "";
	}

	public function getCommentBody($row = 0, $col = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][7][$col][2])) {
 				return $this->postJSON[$row][7][$col][2];
			}			
		}
		return "";
	}

	public function getSparksId($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][88])) {
 				return $this->postJSON[$row][88];
			}			
		}
		return "";
	}

	public function getSparksTitle($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][11][0][3])) {
 				return $this->postJSON[$row][11][0][3];
			}			
		}
		return "";
	}

	public function getSparksAuthor($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][82][2][3][0])) {
 				return $this->postJSON[$row][82][2][3][0];
			}
		}
		return "";
	}

	public function getSparksDescription($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][11][0][21])) {
 				return $this->postJSON[$row][11][0][21];
			}
		}
		return "";
	}

	public function getSparksLink($row = 0)
	{
		if (is_array($this->postJSON)) {
			if (isset($this->postJSON[$row][13])) {
 				return $this->postJSON[$row][13];
			}
		}
		return "";
	}

	protected function getString($row, $col)
	{
		return (isset($this->postJSON[$row][$col])) ? $this->postJSON[$row][$col] : "";
	}

}