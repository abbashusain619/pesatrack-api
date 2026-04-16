<?php

namespace App\Services;

class AccountVerificationService
{
    /**
     * Verify an M-Pesa phone number.
     * 
     * @param string $phoneNumber
     * @return array ['valid' => bool, 'message' => string]
     */
    public function verifyMpesa(string $phoneNumber): array
    {
        // TODO: Replace with real API call to Vodacom/Airtel
        // Accept Tanzania format: 255 followed by 7 or 6, then 8 digits
        if (preg_match('/^255[67]\d{8}$/', $phoneNumber)) {
            return [
                'valid' => true,
                'message' => 'Phone number format is valid (mock)'
            ];
        }
        
        return [
            'valid' => false,
            'message' => 'Invalid phone number format. Use 2557XXXXXXXX (Tanzania)'
        ];
    }

    /**
     * Verify a bank account number.
     * 
     * @param string $accountNumber
     * @param string $bankCode
     * @return array ['valid' => bool, 'message' => string]
     */
    public function verifyBank(string $accountNumber, string $bankCode): array
    {
        // TODO: Replace with real bank API or open banking integration
        if (empty($accountNumber) || empty($bankCode)) {
            return [
                'valid' => false,
                'message' => 'Account number and bank code are required'
            ];
        }
        
        // Mock: accept any non-empty values
        return [
            'valid' => true,
            'message' => 'Account number is valid (mock)'
        ];
    }
}