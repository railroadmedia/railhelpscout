<?php

namespace Railroad\RailHelpScout\Factories;

use HelpScout\Api\ApiClient;
use HelpScout\Api\ApiClientFactory;

class ClientFactory
{
    public static function build()
    : ApiClient
    {
        $credentials = config('railhelpscout.helpscout_credentials');

        $client = ApiClientFactory::createClient();

        $client->useClientCredentials($credentials['app_id'], $credentials['app_secret']);

        return $client;
    }
}
