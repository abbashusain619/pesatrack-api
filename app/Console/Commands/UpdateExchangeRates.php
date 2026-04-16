<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange:update';
    protected $description = 'Update exchange rates from ExchangeRate-API (USD base)';

    public function handle()
    {
        $this->info('Updating exchange rates from ExchangeRate-API...');

        $apiKey = env('EXCHANGE_RATE_API_KEY');

        if (!$apiKey) {
            $this->error('EXCHANGE_RATE_API_KEY is not set in .env file');
            return 1;
        }

        try {
            $response = Http::timeout(30)->withoutVerifying()
                ->get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD");

            if (!$response->successful()) {
                $this->error('Failed to fetch rates. HTTP status: ' . $response->status());
                Log::error('ExchangeRate-API request failed', ['status' => $response->status()]);
                return 1;
            }

            $data = $response->json();

            if (isset($data['result']) && $data['result'] === 'error') {
                $this->error('API error: ' . ($data['error-type'] ?? 'Unknown error'));
                Log::error('ExchangeRate-API error', $data);
                return 1;
            }

            $rates = $data['conversion_rates']; // e.g., 1 USD = X foreign currency
            $updated = 0;
            $missing = [];

            foreach (Currency::all() as $currency) {
                $code = $currency->code;
                if ($code === 'USD') {
                    $currency->rate_to_usd = 1.0;
                    $currency->save();
                    $updated++;
                } elseif (isset($rates[$code])) {
                    // Convert "foreign currency per USD" to "USD per foreign currency"
                    $currency->rate_to_usd = 1.0 / $rates[$code];
                    $currency->save();
                    $updated++;
                } else {
                    $missing[] = $code;
                }
            }

            $this->info("Updated {$updated} currencies.");
            if (!empty($missing)) {
                $this->warn('Rates not found for: ' . implode(', ', $missing));
                Log::warning('Missing exchange rates', ['currencies' => $missing]);
            }

            // Verify a sample currency
            $sample = Currency::where('code', 'TZS')->first();
            if ($sample) {
                $this->info('Sample rate (TZS): 1 TZS = ' . $sample->rate_to_usd . ' USD');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            Log::error('ExchangeRate-API exception', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}