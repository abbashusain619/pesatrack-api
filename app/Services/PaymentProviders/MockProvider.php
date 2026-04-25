<?php

namespace App\Services\PaymentProviders;

class MockProvider implements AccountProviderInterface
{
    public function verifyAccount(string $accountIdentifier, string $providerType, ?string $providerName = null): array
    {
        return [
            'success' => true,
            'valid' => true,
            'message' => 'Account verified (mock)',
            'account_holder' => 'Mock User ' . substr($accountIdentifier, -4),
        ];
    }

    public function getBalance(string $accountIdentifier, string $providerType, ?string $providerName = null): array
    {
        // Deterministic pseudo‑random balance based on identifier
        $hash = hexdec(substr(md5($accountIdentifier), 0, 6));
        $balance = ($hash % 500000) / 100 + 10000;
        $currency = $this->getCurrencyForAccount($providerType, $providerName, $accountIdentifier);
        return [
            'success' => true,
            'balance' => round($balance, 2),
            'currency' => $currency,
        ];
    }

    public function getTransactions(string $accountIdentifier, int $limit = 50, string $providerType = 'mobile_money', ?string $providerName = null): array
    {
        $transactions = [];
        $currency = $this->getCurrencyForAccount($providerType, $providerName, $accountIdentifier);
        $maxDays = 30;
        for ($i = 0; $i < min($limit, 50); $i++) {
            $amount = rand(1000, 200000) / 100;
            $type = rand(0, 1) ? 'income' : 'expense';
            $transactions[] = [
                'id' => 'mock_txn_' . uniqid(),
                'amount' => $amount,
                'type' => $type,
                'description' => 'Mock ' . ($type == 'income' ? 'credit' : 'purchase') . ' ' . ($i+1),
                'reference' => 'REF' . rand(1000,9999),
                'transaction_date' => now()->subDays(rand(0, $maxDays))->format('Y-m-d'),
                'currency' => $currency,
            ];
        }
        // Sort descending by date
        usort($transactions, function($a, $b) {
            return strtotime($b['transaction_date']) - strtotime($a['transaction_date']);
        });
        return [
            'success' => true,
            'transactions' => $transactions,
        ];
    }

    private function getCurrencyForAccount(string $providerType, ?string $providerName, string $accountIdentifier): string
    {
        if ($providerType === 'mobile_money') {
            $mobileMap = [
                'mpesa_tz' => 'TZS', 'tigo_pesa' => 'TZS', 'airtel_tz' => 'TZS',
                'halopesa' => 'TZS', 'azampesa' => 'TZS',
                'mpesa_ke' => 'KES', 'airtel_ke' => 'KES',
                'mtn_mobile_money' => 'UGX', 'vodafone_cash' => 'GHS',
            ];
            return $mobileMap[$providerName] ?? 'TZS';
        }
        // For bank accounts, determine currency based on account number hash (simulate different currencies)
        $hash = hexdec(substr(md5($accountIdentifier), 0, 4));
        return ($hash % 2 === 0) ? 'USD' : 'TZS';
    }
}