<?php

namespace Railroad\RailHelpScout\Services;

use Carbon\Carbon;
use Railroad\RailHelpScout\Models\Customer as LocalCustomer;
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
        $userId,
        $firstName,
        $lastName,
        $email,
        $attributes
    ) {
        $customer = new Customer();
        $customer
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->addEmail($email, 'other');

        $properties = [];

        foreach ($attributes as $key => $value) {

            if ($value) {
                $prop = new Property();

                $prop
                    ->setName($key)
                    ->setSlug($key)
                    ->setValue($value);

                $properties[] = $prop;
            }
        }

        $customer->setProperties(new Collection($properties));

        $customerId = $this->client->customers()->create($customer);

        $localCustomer = new LocalCustomer();

        $localCustomer->internal_id = $userId;
        $localCustomer->external_id = $customerId;

        $localCustomer->setCreatedAt(Carbon::now());
        $localCustomer->setUpdatedAt(Carbon::now());

        $localCustomer->saveOrFail();
    }

    public function updateCustomer(
    ) {

    }

    public function deleteCustomer(
    ) {
    }
}
