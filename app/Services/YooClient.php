<?php


namespace App\Services;


use YooKassa\Client\ApiClientInterface;
use YooKassa\Helpers\Config\ConfigurationLoaderInterface;

class YooClient extends \YooKassa\Client
{
    public function __construct(ApiClientInterface $apiClient = null, ConfigurationLoaderInterface $configLoader = null)
    {
        parent::__construct($apiClient, $configLoader);
        self::setAuth(getenv('PAYMENTSYSTEM_CLIENT_ID'), getenv('PAYMENTSYSTEM_CLIENT_SECRET'));
    }

}
