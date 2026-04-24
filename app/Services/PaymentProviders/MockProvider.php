<?php

namespace App\Services\PaymentProviders;

class MockProvider implements AccountProviderInterface
{
    public function getBalance(string $accountIdentifier, string $providerType = 'mobile_money'): array
    {
        return [
            'success' => true,
            'balance' => rand(10000, 500000) / 100,
            'currency' => 'TZS',
            'account_holder' => 'Test User',
            'provider' => $providerType,
        ];
    }

    public function getTransactions(string $accountIdentifier, int $limit = 50): array
    {
        $transactions = [];
        for ($i = 0; $i < $limit; $i++) {
            $transactions[] = [
                'id' => 'txn_' . uniqid(),
                'amount' => rand(1000, 200000) / 100,
                'type' => rand(0,1) ? 'credit' : 'debit',
                'description' => 'Mock transaction ' . ($i+1),
                'created_at' => now()->subDays(rand(0,30))->toIso8601String(),
            ];
        }
        return ['success' => true, 'transactions' => $transactions];
    }

    public function verifyAccount(string $accountIdentifier, string $providerType): array
    {
        return [
            'success' => true,
            'valid' => true,
            'message' => 'Account verified (mock)',
            'account_holder' => 'Mock User',
        ];
    }
}