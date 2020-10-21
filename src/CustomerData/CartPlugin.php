<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes. All rights reserved.
 * See LICENSE.md for license details
 */

declare(strict_types=1);

namespace Hyva\GraphqlTokens\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

class CartPlugin {

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
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    public function __construct(
        Session $checkoutSession,
        CurrentCustomer $currentCustomer,
        \Magento\Checkout\Model\Cart $checkoutCart,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutCart = $checkoutCart;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->currentCustomer = $currentCustomer;
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
        $cartId = (int) $this->getQuote()->getId();
        if ($cartId) {
            $cartId = $this->getQuoteMaskId($cartId) ?: $cartId;
        }
        $result['cartId'] = $cartId;

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
