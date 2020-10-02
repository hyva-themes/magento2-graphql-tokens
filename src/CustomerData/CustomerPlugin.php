<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Wigman Interactive. All rights reserved.
 * This product is licensed per Magento production install
 */

declare(strict_types=1);

namespace Hyva\GraphqlTokens\CustomerData;

use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;

class CustomerPlugin {

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    public function __construct(
        CurrentCustomer $currentCustomer,
        TokenModelFactory $tokenModelFactory
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->tokenModelFactory = $tokenModelFactory;
    }

    /**
     * @param Customer $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(Customer $subject, array $result)
    {
        if (!$customerId = $this->currentCustomer->getCustomerId()) {
            return $result;
        }

        /** @var TokenModel $tokenModel */
        $tokenModel = $this->tokenModelFactory->create();
        $token = $tokenModel->loadByCustomerId($customerId)->getToken()
            ?? $tokenModel->createCustomerToken($customerId)->getToken();

        if ($token) {
            $result['signin_token'] = $token;
        }

        return $result;
    }
}
