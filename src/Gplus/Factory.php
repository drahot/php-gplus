<?php

namespace Gplus;

class Factory
{
	
	public static function create($mailAddress, $password, $pageId = '')
	{
		$client = new Client;
		$client->doLogin($mailAddress, $password);
		list($sendId, $userId) = $client->doPlus();

		if (!empty($pageId)) {
			$userId = $pageId;
			$usePageId = true;
		} else  {
			$usePageId = false;
		}

		$gplus = new Gplus(
			$client, $mailAddress, $password, $sendId, $userId, $usePageId
		);
		return $gplus;
	}

}


