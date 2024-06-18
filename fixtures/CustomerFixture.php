<?php

namespace Shopware\Production\Fixtures;

use Basecom\FixturePlugin\Fixture;
use Basecom\FixturePlugin\FixtureBag;
use Basecom\FixturePlugin\FixtureHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class CustomerFixture extends Fixture
{
    private const CUSTOMER_ID = '0d8eefdd6d32456385580e2ff42431a9';
    private const ADDRESS_ID = 'e27dc2b4e85f4a0f9a912a09f07701a0';

    public function __construct(
        private FixtureHelper             $helper,
        private EntityRepositoryInterface $customerRepository
    )
    {
    }

    public function load(FixtureBag $bag): void
    {
        $salesChannel = $this->helper->SalesChannel()->getStorefrontSalesChannel();
        $salutation = $this->helper->Customer()->getNotSpecifiedSalutation();
        if (!$salesChannel) {
            throw new \LogicException('No sales channel found.');
        }

        $this->customerRepository->upsert([[
            'id' => self::CUSTOMER_ID,
            'salesChannelId' => $salesChannel->getId(),
            'groupId' => $salesChannel->getCustomerGroupId(),
            'defaultPaymentMethodId' => $this->helper->PaymentMethod()->getInvoicePaymentMethod()->getId(),
            'defaultBillingAddress' => [
                'id' => self::ADDRESS_ID,
                'salutationId' => $salutation->getId(),
                'countryId' => $this->helper->SalesChannel()->getCountry('de')->getId(),
                'firstName' => 'Zoey',
                'lastName' => 'Smith',
                'zipcode' => '12345',
                'street' => 'Musterstraße 1',
                'city' => 'Schöppingen',
            ],
            'defaultShippingAddressId' => self::ADDRESS_ID,
            'salutationId' => $salutation->getId(),
            'customerNumber' => '12345',
            'firstName' => 'Zoey',
            'lastName' => 'Smith',
            'email' => 'user@example.com',
            'password' => 'password'
        ]], Context::createDefaultContext());
    }
}
