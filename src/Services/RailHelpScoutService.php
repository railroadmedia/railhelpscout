<?php

namespace Railroad\RailHelpScout\Services;

use Carbon\Carbon;
use Railroad\RailHelpScout\Models\Customer as LocalCustomer;
use HelpScout\Api\ApiClient;
use HelpScout\Api\Customers\Customer;
use HelpScout\Api\Customers\Entry\Email;
use HelpScout\Api\Customers\Entry\Property;
use HelpScout\Api\Customers\Entry\PropertyOperation;
use HelpScout\Api\Entity\Collection;
use HelpScout\Api\Entity\PagedCollection;
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

    /**
     * @param int $userId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param array $email
     *
     * @throws Exception
     */
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

    /**
     * @param int $userId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param array $attributes
     *
     * @throws Exception
     */
    public function updateCustomer(
        $userId,
        $firstName,
        $lastName,
        $email,
        $attributes
    ) {

        $customer = $this->getCustomerById($userId);

        /*
        $emails = $customer->getEmails()->toArray();
        $customerEmail = array_shift($emails);

        if ($customerEmail->getValue() != $email) {

            $customerEmail->setValue($email);

            $this->client->customerEntry()->updateEmail($customer->getId(), $customerEmail);
        }
        */

        if ($customer->getFirstName() != $firstName || $customer->getLastName() != $lastName) {

            $customer
                ->setFirstName($firstName)
                ->setLastName($lastName);

            $this->client->customers()->update($customer);
        }

        $operations = $this->getCustomerUpdateAttributesOperations($customer, $attributes);

        if (count($operations)) {
            $this->client->customerProperty()->updateProperties($customer->getId(), new Collection($operations));
        }
    }

    /**
     * @param int $userId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param array $attributes
     *
     * @throws Exception
     */
    public function createOrUpdateCustomer(
        $userId,
        $firstName,
        $lastName,
        $email,
        $attributes
    ) {
        /**
         * @var $localCustomer LocalCustomer
         */
        $localCustomer = LocalCustomer::query()->where(
            [
                'internal_id' => $userId,
            ]
        )->first();

        if (empty($localCustomer)) {
            $this->createCustomer($userId, $firstName, $lastName, $email, $attributes);
        } else {
            $this->updateCustomer($userId, $firstName, $lastName, $email, $attributes);
        }
    }

    /**
     * @param int $userId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param array $attributes
     * @param Customer $customer
     *
     * @throws Exception
     */
    public function syncExistingCustomer(
        $userId,
        $firstName,
        $lastName,
        $email,
        $attributes,
        Customer $customer
    ) {
        // todo - add email update

        if ($customer->getFirstName() != $firstName || $customer->getLastName() != $lastName) {

            $customer
                ->setFirstName($firstName)
                ->setLastName($lastName);

            $this->client->customers()->update($customer);
        }

        $operations = $this->getCustomerUpdateAttributesOperations($customer, $attributes);

        if (count($operations)) {
            $this->client->customerProperty()->updateProperties($customer->getId(), new Collection($operations));
        }

        $localCustomer = new LocalCustomer();

        $localCustomer->internal_id = $userId;
        $localCustomer->external_id = $customer->getId();

        $localCustomer->setCreatedAt(Carbon::now());
        $localCustomer->setUpdatedAt(Carbon::now());

        $localCustomer->saveOrFail();
    }

    /**
     * @param int $userId
     *
     * @return Customer
     *
     * @throws Exception
     */
    public function getCustomerById($userId): Customer
    {
        /**
         * @var $localCustomer LocalCustomer
         */
        $localCustomer = LocalCustomer::query()->where(
            [
                'internal_id' => $userId,
            ]
        )->firstOrFail();

        $customer = $this->client->customers()->get($localCustomer->external_id);

        return $customer;
    }

    /**
     * @return PagedCollection
     */
    public function getCustomersPage(): PagedCollection
    {
        return $this->client->customers()->list();
    }

    /**
     * @param Customer $customer
     * @param array $attributes
     *
     * @return PropertyOperation[]|array
     *
     * @throws Exception
     */
    protected function getCustomerUpdateAttributesOperations(
        Customer $customer,
        array $attributes
    ): array {

        $props = $customer->getProperties();

        $operations = [];

        foreach ($props as $prop) {
            if (isset($attributes[$prop->getName()])) {
                if ($prop->getValue() != $attributes[$prop->getName()]) {
                    if ($attributes[$prop->getName()]) {
                        $operations[] = new PropertyOperation(
                            PropertyOperation::OPERATION_REPLACE,
                            $prop->getName(),
                            $attributes[$prop->getName()]
                        );
                    } else {
                        $operations[] = new PropertyOperation(
                            PropertyOperation::OPERATION_REMOVE,
                            $prop->getName()
                        );
                    }
                }
            } else if ($prop->getValue()) {
                $operations[] = new PropertyOperation(
                    PropertyOperation::OPERATION_REMOVE,
                    $prop->getName()
                );
            }
        }

        return $operations;
    }
}
