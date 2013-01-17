<?php

namespace Gplus;

class Post
{

	private $gplus;

	public function __construct(Gplus $gplus)
	{
		$this->gplus = $gplus;
	}

	public function post(array $postData)
	{
		$postData = array_merge(
			array(
				'message'				=> '',
				'reshare'				=> 'null',
				'linkurl'				=> '',
				'linkdescription'		=> '',
				'linktitle'				=> '',
				'linktype'				=> 'text/html',
				'linkthumbnail'			=> '',
				'linktx'				=> 200,
				'linkty'				=> 200,
				'circleid'				=> '',
				'iscomment'				=> 'null',
				'isshare'				=> 'null',
				'linkthumbnailtype'		=> '',
				'linkimage'				=> '',
				'uploadimage'			=> '',
				'linkfavicon'			=> '',
				'linknofavicon'			=> false,
				'sparksid'				=> 'null',
			),
			$postData
		);
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

		// if (!$postData['linknofavicon']) {
		// 	if ($postData['linkfavicon']) {
		// 		$domain = 
		// 	} else {

		// 	}
		// }
	}

}