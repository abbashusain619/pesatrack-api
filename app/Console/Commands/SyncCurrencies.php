<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncCurrencies extends Command
{
    protected $signature = 'currencies:sync';
    protected $description = 'Fetch currency list from ExchangeRate-API and update the database';

    public function handle()
    {
        $this->info('Syncing currencies from ExchangeRate-API...');

        $apiKey = env('EXCHANGE_RATE_API_KEY');

        if (!$apiKey) {
            $this->error('EXCHANGE_RATE_API_KEY is not set in .env file');
            return 1;
        }

        try {
            // ExchangeRate-API endpoint for supported codes
            $response = Http::withoutVerifying()
                ->get("https://v6.exchangerate-api.com/v6/{$apiKey}/codes");

            if (!$response->successful()) {
                $this->error('Failed to fetch currency list. HTTP status: ' . $response->status());
                return 1;
            }

            $data = $response->json();
            
            if (isset($data['result']) && $data['result'] === 'error') {
                $this->error('API error: ' . ($data['error-type'] ?? 'Unknown error'));
                return 1;
            }

            $currencies = $data['supported_codes']; // Array of [code, name]
            $count = 0;

            foreach ($currencies as [$code, $name]) {
                Currency::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'rate_to_usd' => 1] // temporary rate
                );
                $count++;
            }

            $this->info("Synced {$count} currencies successfully.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}