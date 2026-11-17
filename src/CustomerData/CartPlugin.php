<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Hyva\GraphqlTokens\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

class CartPlugin
{
    /**
     * @var Quote|null
     */
    protected $quote = null;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $checkoutCart;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    public function __construct(
        Session $checkoutSession,
        CurrentCustomer $currentCustomer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $checkoutCart,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutCart = $checkoutCart;
        $this->storeManager = $storeManager;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->currentCustomer = $currentCustomer;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, array $result)
    {
        $storeViewCode = (string) $this->storeManager->getStore()->getCode();
        $cartId = (int) $this->getQuote()->getId();
        $maskedCartId = '';

        if ($cartId) {
            $maskedCartId = $this->getQuoteMaskId($cartId);

            if (!$maskedCartId) {
                /** @var QuoteIdMask $quoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $quoteIdMask->setQuoteId($cartId);
                $this->quoteIdMaskResource->save($quoteIdMask);
                $maskedCartId = $quoteIdMask->getMaskedId() ?: '';
            }
        }

        $result['cartId'] = $maskedCartId;
        $result['storeViewCode'] = $storeViewCode;

        return $result;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutCart->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get masked id for cart
     *
     * @param int $quoteId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getQuoteMaskId(int $quoteId): string
    {
        try {
            $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new NoSuchEntityException(__('Current user does not have an active cart.'));
        }
        return $maskedId;
    }
}
