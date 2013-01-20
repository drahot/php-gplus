<?php

namespace Gplus;

/**
 * Gplus Factory
 * @author drahot
 */
final class Factory
{
    /**
     * Constructor
     * @return void
     */
    private function __construct()
    {
    }    

    /**
     * Create Gplus Object
     * @param string $mailAddress 
     * @param string $password 
     * @param string $pageId 
     * @return Gplus\Gplus
     */
    public static function create($mailAddress, $password, $pageId = '')
    {
        $client = new Client;
        $client->login($mailAddress, $password);
        list($sendId, $userId) = $client->GetUserData();

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
