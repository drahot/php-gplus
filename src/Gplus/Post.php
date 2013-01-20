<?php

namespace Gplus;

class Post
{

	private $gplus;

	public function __construct(Gplus $gplus)
	{
		$this->gplus = $gplus;
	}

	/**
	 * Description
	 * @param array $postData 
	 * @return void
	 */
	public function post(array $postData)
	{
		$postData = $this->initPostData($postData);
		if (!empty($postData['circleid'])) {
            $scopeType = 'focusGroup';
            $groupType = 'p';
            $me = 'false';
            $postData['circlename'] = 'Limited';
            if ($postData['circleid'][0] === 'p') {
            	$postData['circleid'] = substr($postData['circleid'], 1);
            }
            $postData['circleid'] = $this->gplus->getUserId();
		} else {
            $scopeType = 'anyone';
            $groupType = 'null';
            $me = 'true';
            $postData['circlename'] = 'anyone';
		}

		$domain = $this->getDomain($postData);
		$isLockComment = $this->getLockValue($postData, 'iscomment', $this->gplus->isLockComment());
		$isLockShare = $this->getLockValue($postData, 'isshare', $this->gplus->isLockShare());
		$imagemime = $this->getImageMIME($postData);
		$uploadData = '';
		if (!empty($postData['uploadimage'])) {
			$uploadImageData = $this->getUploadImageData($postData['uploadimage']);
			$this->gplus->getClient()->uploadImage($uploadImageData, $postData['uploadimage'][0]);
		}
		$scopeData = $this->getScopeData($postData, $scopeType, $groupType, $me);
		$linkData = (!empty($postData["linkurl"])) ? $this->getLinkData($postData, $domain, $imagemime) : 'null';

        $spar = $this->getSparData($postData, $linkData, $scopeData, $isLockComment, $isLockShare);
        $data = array(
            "spar" 	=> $spar,
            "at" 	=> $this->gplus->getSendId(),
        );
        $params = array(
        	"spam"	=> 20,
        );
        return $this->gplus->getClient()->getGplusData(
        	$this->gplus, "sharebox", "post", array("spam"	=> 20), true, $data
        );
	}

	/**
	 * Description
	 * @param string $postId 
	 * @param string $message 
	 * @return void
	 */
	public function edit($postId, $message)
	{
		$json = json_encode(array(
			$postId, $message, null, '[]', true, array(), null, null, null, null, false
		));
        $data = array(
        	"f.req" => $json,
            "at"	=> $this->gplus->getSendId(),
        );
        $params = array(
        	"spam" 		=> 20,
        );
        return $this->gplus->getClient()->getGplusData(
        	$this->gplus, "stream", "edit", $params, true, $data
        );
	}

	/**
	 * Description
	 * @param string $postId 
	 * @param string $message 
	 * @return void
	 */
	public function comment($postId, $message)
	{
        $data = array(
            "itemId" 			=> $postId,
            "clientId" 			=> "os". $postId . ":". date('Uu'),
            "text" 				=> $message,
            "timestamp_msec"	=> date('Uu'),
            "at"	=> $this->gplus->getSendId(),
        );
        $this->gplus->getClient()->getGplusData(
        	$this->gplus, "stream", "comment", array("spam" => 20), true, $data
        );
	}

	public function reshare($postId, $message, $circleId = "")
	{
		$data = array(
			"reshare"	=> $postId,
			"message"	=> $message,
			"circleid"	=> $circleId,
		);
		$this->post($data);
	}

	public function customLink(
		$message, 
		$linkUrl = "//plus.google.com", 
		$linkTitle = "", 
		$linkDescription = "", 
		$linkThumbnail = "", 
		$linkThumbnailType = "", 
		$imageSizex = 200, 
		$imageSizey = 200)
	{
		$data = array(
			"message"				=> $message,
			"linkurl"				=> $linkUrl,
			"linktitle"				=> $linkTitle,
			"linkdescription"		=> $linkDescription,
			"linkthumbnail"			=> $linkThumbnail,
			"linkthumbnailtype"		=> $linkThumbnailType,
			"linktx"				=> $imageSizex,
			"linkty"				=> $imageSizey,
		);
		$this->post($data);
	}

	public function postMessage($message, $circleId = "")
	{
		$data = array(
			"message" 	=> $message,
			"circleid"	=> $circleId,
		);
		$this->post($data);
	}

	public function postImage($message, array $uploadImage)
	{
		$data = array(
			"message" 		=> $message,
			"uploadimage"	=> $uploadImage,
		);
		$this->post($data);
	}

	public function lockComment($postId, $lock = true)
	{
        #pastdataを設定
        $data = array(
            "itemId" 	=> $postId,
            "disable" 	=> $lock ? "true" : "false",
            "at"		=> $this->gplus->getSendId(),
        );
        $this->gplus->getClient()->getGplusData(
        	$this->gplus, "stream", "disablecomments", array(), true, $data
        );
	}

	public function sparks($sparksId, $message)
	{
		$data = array(
			"message" 	=> $message,
			"sparksid"	=> $sparksId,
		);
		$this->post($data);
	}

	/**
	 * Description
	 * @param array $postData 
	 * @return array
	 */
	private function initPostData(array $postData)
	{
		return array_merge(
			array(
				'message'				=> '',
				'reshare'				=> 'null',
				'linkurl'				=> '',
				'linkdescription'		=> '',
				'linktitle'				=> '',
				'linktype'				=> 'text/html',
				'linkthumbnail'			=> '',
				'linktx'				=> '200',
				'linkty'				=> '200',
				'circleid'				=> '',
				'iscomment'				=> 'null',
				'isshare'				=> 'null',
				'linkthumbnailtype'		=> '',
				'linkimage'				=> '',
				'uploadimage'			=> '',
				'linkfavicon'			=> '',
				'linknofavicon'			=> 'false',
				'sparksid'				=> 'null',
			),
			$postData
		);
	}

	/**
	 * Description
	 * @param array $postData 
	 * @return string
	 */
	private function getDomain(array $postData)
	{
		$domain = '';
		if (empty($postData['linknofavicon'])) {
			if (!empty($postData['linkfavicon'])) {
		        if (preg_match('///(.[^/]*)/?/', $postData['linkfavicon'], $matches)) {
		            $domain = '?domain=' . $matches[1];
		        }
			} else {
				if ($postData['linktitle']) {
			        if (preg_match('///(.[^/]*)/?/', $postData['linkurl'], $matches)) {
			            $domain = '?domain=' . $matches[1];
		        	}
		        }
			}
		}
		return $domain;
	}

	/**
	 * Description
	 * @param type array $postData 
	 * @param type $name 
	 * @param type $default 
	 * @return type
	 */	
	private function getLockValue(array $postData, $name, $default)	
	{
		if ($postData[$name] !== 'null') {
			return !empty($postData[$name]) ? true : false;
		}
		return $default;
	}

	/**
	 * Description
	 * @param array $postData 
	 * @return string 
	 */
	private function getImageMIME(array $postData)
	{
        if (!empty($postData["linkthumbnailtype"])) {
            $imagemime = $postData['linkthumbnailtype'];
        } else {
			$ext = pathinfo($postData['linkthumbnail'], PATHINFO_EXTENSION);
			switch ($ext) {
				case 'jpg':
				case 'jpeg':
					$imagemime = 'image/jpeg';
					break;
				case 'png':
					$imagemime = 'image/png';
					break;
				case 'bmp':
					$imagemime = 'image/bmp';
					break;
				case 'gif':
					$imagemime = 'image/gif';
					break;
				default:
					$imagemime = 'image/jpeg';
					break;
			}
		}
		return $imagemime;
	}

	/**
	 * Description
	 * @param array $uploadImage 
	 * @return string
	 */
	private function getUploadImageData(array $uploadImage)
	{
		$data = array(
			"createSessionRequest" => array(
				"fields" => array(
					array(
						"external" => array(
							"filename" => pathinfo($uploadImage[0], PATHINFO_BASENAME),
							"formPost" => array(),
							"name"		=> "file",
							"filesize" => filesize($uploadImage[0]),
						)
					),
					array(
						"inlined" => array(
							"content" 		=> date('Uu'),
							"contentType" 	=> "text/plain",
							"name"			=> "batchid",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> "sharebox",
							"contentType" 	=> "text/plain",
							"name"			=> "client",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> "true",
							"contentType" 	=> "text/plain",
							"name"			=> "disable_asbe_notification",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> "updates",
							"contentType" 	=> "text/plain",
							"name"			=> "streamid",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> "true",
							"contentType" 	=> "text/plain",
							"name"			=> "use_upload_size_pref",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> $this->gplus->getUserId(),
							"contentType" 	=> "text/plain",
							"name"			=> "effective_id",
						)
					),
					array(
						"inlined" => array(
							"content" 		=> $this->gplus->getUserId(),
							"contentType" 	=> "text/plain",
							"name"			=> "owner_name",
						)
					),

				),
			),
			"protocolVersion" => "0.8",
		);
		$json = json_encode($data);
		return substr($json, 1, -1);
	}

	/**
	 * Description
	 * @param array $postData 
	 * @param string $scopeType 
	 * @param string $groupType 
	 * @param string $me 
	 * @return string
	 */
	private function getScopeData(array $postData, $scopeType, $groupType, $me)
	{
		$data = array(
            "aclEntries" => array(
            	array(
            		'scope'	=> array(
                        "scopeType" 	=> $scopeType,
                        "name"	 		=> $postData["circlename"],
                        "id"	 		=> $postData["circleid"],
                        "me"			=> $me,
                        "requiresKey"   => "false",
                        "groupType"		=> $groupType
                    ),
                    "role"	=> 20,
            	),
            	array(
            		'scope'	=> array(
                        "scopeType" 	=> $scopeType,
                        "name"	 		=> $postData["circlename"],
                        "id"	 		=> $postData["circleid"],
                        "me"			=> $me,
                        "requiresKey"   => "false",
                        "groupType"		=> $groupType
                    ),
                    "role"	=> 60,
            	),
            ),
		);
		$json = JSONHelper::encode($data);
		// return substr($json, 1, -1);
		return $json;
	}

	private function getLinkData()
	{
		if (!empty($postData["linkimage"])) {
			$linkImage1 = array("null", $postData["linkimage"], $postData["linktx"], $postData["linkty"]);
			$linkImage2 = array(
				array("null", $postData["linkimage"], 120, 96.14035087719297),
				array("null", $postData["linkimage"], 120, 96.14035087719297)
			);
		} else {
			$linkImage1 = "null";
			if (!empty($postData["linknofavicon"])) {
				$linkImage2 = "null";
			} else {
				if (!empty($postData["linktitle"])) {
				$linkImage2 = array(
					array("null", "//s2.googleusercontent.com/s2/favicons". $domain, "null", "null"),
					array("null", "//s2.googleusercontent.com/s2/favicons". $domain, "null", "null")
				);
				} else {
					$linkImage2 = "null";
				}
			}
		}
		if (!empty($postData["linkthumbnail"])) {
			$linkThumbnailType1 = array("null", $postData["linkthumbnail"]);
			$linkThumbnailType2 = array(
				"null", $postData["linkurl"], "null", $imagemime, "photo", 
				"null", "null", "null", "null", "null", "null", "null", 
				$postData["linktx"], $postData["linkty"]
			);
			$linkThumbnailType3 = array(
                array(
                	"null", $postData["linkthumbnail"], "null", "null"
                ),
                array(
					"null", $postData["linkthumbnail"], "null", "null"
                )
            );
		} else {
			$linkThumbnailType1 = "null";
			$linkThumbnailType2 = "null";
			$linkThumbnailType3 = "null";
		}
		$linkData = JSONHelper::encode(
			array(
 				JSONHelper::encode(
	 				array(
		 				"null", "null", "null", $postData["linkurl"], "null", $linkImage1,
			 			"null", "null", "null", array(), "null", "null", "null", "null", "null", "null",
						"null", "null", "null", "null", "null", $postData["linkdescription"], "null", "null",
						array("null", $postData["linkurl"], "null", $postData["linktype"], "document"),
		                "null", "null", "null", "null", "null", "null", "null", "null",
		                "null", "null", "null", "null", "null", "null", "null", "null",
		                $linkImage2, "null","null","null","null","null",
		                array(
							array("null","","http://google.com/profiles/media/provider","")	
		                )
					)
				),
 				JSONHelper::encode(
	 				array(
		 				"null", "null", "null", "null", "null",
		 				$linkThumbnailType1,
		                "null","null","null", array(), "null", "null", "null", "null", "null", 
		                "null", "null", "null", "null", "null", "null", "null", "null", "null", 
		 				$linkThumbnailType2,
		                "null", "null", "null", "null", "null", "null", "null", "null", 
		                "null", "null", "null", "null", "null", "null", "null", "null",
		 				$linkThumbnailType3,
                		"null","null","null","null","null",
                		array(
                			array(
		                        "null", "images", "http://google.com/profiles/media/provider", ""
                			)
                		),
					)
				),
			)
		);
		return $linkData;
	}

	/**
	 * Description
	 * @param array $postData 
	 * @param string $linkData 
	 * @param string $scopeData 
	 * @param bool $isLockComment 
	 * @param bool $isLockShare 
	 * @return string
	 */
	private function getSparData(array $postData, $linkData, $scopeData, $isLockComment, $isLockShare)
	{
        return JSONHelper::encode(
        	array(
	        	$postData["message"],
	        	"oz:" . $this->gplus->getUserId(). "." . sprintf("%x", time()) . ".0",
	        	$postData["reshare"],
	        	"null",
	        	"null",
	        	"null",
	        	$linkData,
	        	"null",
	            $scopeData,
	            "true",
	            array(),
	            "false",
	            "false",
	            "null",
	            array(),
	            "false",
	            "false",
	            "null",
	            $postData["sparksid"],
	            "null",
	            "null",
	            "null",
	            "null",
	            "null",
	            "null",
	            "null",
	            "null",
	            $isLockComment,
	            $isLockShare,
	            "false"
        	)
        );
	}

}