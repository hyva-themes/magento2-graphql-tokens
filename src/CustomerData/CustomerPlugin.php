<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes. All rights reserved.
 * See LICENSE.md for license details
 */

declare(strict_types=1);

namespace Hyva\GraphqlTokens\CustomerData;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;

class CustomerPlugin
{

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    public function __construct(
        CurrentCustomer $currentCustomer,
        TokenModelFactory $tokenModelFactory,
        DateTime $dateTime,
        Date $date,
        OauthHelper $oauthHelper
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->oauthHelper = $oauthHelper;
        $this->dateTime = $dateTime;
        $this->date = $date;
    }

    /**
     * Check if token is expired. Copied from
     * \Magento\Webapi\Model\Authorization\TokenUserContext::isTokenExpired
     *
     * @param Token $token
     * @return bool
     */
    private function isTokenExpired(Token $token): bool
    {
        if ($token->getUserType() == UserContextInterface::USER_TYPE_ADMIN) {
            $tokenTtl = $this->oauthHelper->getAdminTokenLifetime();
        } elseif ($token->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $tokenTtl = $this->oauthHelper->getCustomerTokenLifetime();
        } else {
            // other user-type tokens are considered always valid
            return false;
        }

        if (empty($tokenTtl)) {
            return false;
        }

        if ($this->dateTime->strToTime($token->getCreatedAt()) < ($this->date->gmtTimestamp() - $tokenTtl * 3600)) {
            return true;
        }

        return false;
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

        $token = $tokenModel->loadByCustomerId($customerId);

        if (!$token->getId() || $token->getRevoked() || $this->isTokenExpired($token)) {
            // if there exist an entry in oauth_token table for the customer, then
            // remove it before attempting createCustomerToken
            if ($token->getId()) {
                $token->delete();
                $tokenModel = $this->tokenModelFactory->create();
            }

            $token = $tokenModel->createCustomerToken($customerId);
        }

        if ($tokenValue = $token->getToken()) {
            $result['signin_token'] = $tokenValue;
        }

        return $result;
    }
}
