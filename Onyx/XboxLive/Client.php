<?php namespace Onyx\XboxLive;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise as GuzzlePromise;
use Onyx\Destiny\Client as DestinyClient;
use Onyx\XboxLive\Helpers\Network\XboxAPI;
use Onyx\XboxLive\Constants as XboxConstants;

class Client extends XboxAPI {

    public $acceptedGameIds = ['972249091', '247546985', '1144039928', '1292135256', '219630713']; // gta5 destiny mcc titanfall h5

    public function fetchAccountsPresence($accounts)
    {
        $client = new GuzzleClient([
            'base_uri' => XboxConstants::$getBaseXboxAPI
        ]);

        // Set up getCommands
        $requests = array();
        foreach ($accounts as $account)
        {
            if ($account->xuid == null)
            {
                $destiny = new DestinyClient();
                $account = $destiny->fetchAccountData($account);
            }

            $url = sprintf(XboxConstants::$getPresenceUrl, $account->xuid);
            $requests[$account->seo] = $client->getAsync($url, [
                'headers' => ['X-AUTH' => env('XBOXAPI_KEY')]
            ]);
        }

        $results = GuzzlePromise\Unwrap($requests);

        return $results;
    }

    public function prettifyOnlineStatus($presence, $accounts)
    {
        $key = 'online_status';

        if (Cache::has($key))
        {
            return Cache::get($key);
        }
        else
        {
            $user_string = '<strong>Online Status</strong><br/>';
            $found = false;

            foreach ($presence as $seo => $response)
            {
                $data = json_decode($response->getBody(), true);

                if (isset($data['state']) && $data['state'] == "Online")
                {
                    foreach ($data['devices'] as $device)
                    {
                        if ($device['type'] == "XboxOne")
                        {
                            foreach ($device['titles'] as $title)
                            {
                                if (in_array($title['id'], $this->acceptedGameIds))
                                {
                                    $found = true;
                                    $gt = $accounts->where('seo', $seo)->first();
                                    $user_string .= "<strong>" . $gt->gamertag . ": </strong>" . $title['name'];
                                    if (isset($title['activity']))
                                    {
                                        $user_string .= " (" . $title['activity']['richPresence'] . ")";
                                    }
                                    $user_string .= "<br/>";
                                }
                            }
                        }
                    }
                }
            }

            if (! $found)
            {
                $user_string = 'No-one is online. Pity us.';
            }
            Cache::put($key, $user_string, 5);
            return $user_string;
        }
    }
}