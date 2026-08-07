<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by OrderService::createPendingOrder() when one or more cart items
 * went out of stock or changed price since being added to the cart. Caught
 * by the Checkout Livewire component to show inline errors instead of
 * placing an order against stale cart data.
 */
class CartValidationException extends Exception
{
    /**
     * @param  array<int, string>  $issues
     */
    public function __construct(protected array $issues)
    {
        parent::__construct(implode(' ', $issues) ?: 'Your cart changed since you last viewed it. Please review it before continuing.');
    }

    /**
     * @return array<int, string>
     */
    public function issues(): array
    {
        return $this->issues;
    }
}
