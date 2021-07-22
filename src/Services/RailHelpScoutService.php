<?php

namespace Railroad\RailHelpScout\Services;

use HelpScout\Api\ApiClient;
use HelpScout\Api\Customers\Customer;
use HelpScout\Api\Customers\Entry\Email;
use HelpScout\Api\Customers\Entry\Property;
use HelpScout\Api\Entity\Collection;
use Railroad\RailHelpScout\Factories\ClientFactory;

class RailHelpScoutService
{
    /**
     * @var ApiClient
     */
    private $client;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory::build();
    }

    public function createCustomer(
        $email,
        $attributes
    ) {
        $customer = new Customer();
        $customer->addEmail($email, 'other');

        $properties = [];

        foreach ($attributes as $key => $value) {

            $prop = new Property();

            $prop
                ->setName($key)
                ->setValue($value);

            $properties[] = $prop;
        }

        $customer->setProperties(new Collection($properties));

        $customerId = $this->client->customers()->create($customer);

        dd($customerId);
    }

    public function updateCustomer(
    ) {

    }

    public function deleteCustomer(
    ) {
    }
}
