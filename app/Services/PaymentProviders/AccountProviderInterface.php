<?php

namespace App\Services\PaymentProviders;

interface AccountProviderInterface
{
    public function getBalance(string $accountIdentifier, string $providerType = 'mobile_money'): array;
    public function getTransactions(string $accountIdentifier, int $limit = 50): array;
    public function verifyAccount(string $accountIdentifier, string $providerType): array;
}