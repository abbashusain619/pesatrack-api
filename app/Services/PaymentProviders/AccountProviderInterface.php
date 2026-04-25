<?php

namespace App\Services\PaymentProviders;

interface AccountProviderInterface
{
    /**
     * Verify account existence and ownership.
     *
     * @param string $accountIdentifier Phone number or bank account number
     * @param string $providerType 'mobile_money' or 'bank'
     * @param string|null $providerName Specific provider code (e.g., 'mpesa_tz', 'crdb')
     * @return array ['valid' => bool, 'message' => string, 'account_holder' => string|null]
     */
    public function verifyAccount(string $accountIdentifier, string $providerType, ?string $providerName = null): array;

    /**
     * Fetch current balance.
     *
     * @param string $accountIdentifier
     * @param string $providerType
     * @param string|null $providerName
     * @return array ['success' => bool, 'balance' => float, 'currency' => string]
     */
    public function getBalance(string $accountIdentifier, string $providerType, ?string $providerName = null): array;

    /**
     * Fetch recent transactions.
     *
     * @param string $accountIdentifier
     * @param int $limit
     * @param string $providerType
     * @param string|null $providerName
     * @return array ['success' => bool, 'transactions' => array]
     */
    public function getTransactions(string $accountIdentifier, int $limit = 50, string $providerType = 'mobile_money', ?string $providerName = null): array;
}