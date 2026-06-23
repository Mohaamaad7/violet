<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\AnalyticsClient;
use Spatie\Analytics\Period;

class AnalyticsService
{
    /**
     * Get a configured Analytics instance based on DB settings.
     * Overcomes config:cache issues.
     */
    public static function getClient(): ?Analytics
    {
        try {
            $propertyId = Setting::get('ga_property_id');
            $jsonFile = Setting::get('ga_service_account_json');

            if (!$propertyId || !$jsonFile) {
                return null;
            }

            // File path in storage
            $credentialsPath = storage_path('app/' . $jsonFile);

            if (!file_exists($credentialsPath)) {
                Log::warning('Analytics: Credentials file not found at ' . $credentialsPath);
                return null;
            }

            // Create BetaAnalyticsDataClient with the credentials
            $betaClient = new BetaAnalyticsDataClient([
                'credentials' => $credentialsPath,
            ]);

            // Create Spatie Analytics Client
            $analyticsClient = new AnalyticsClient($betaClient, $propertyId);

            // Return configured Analytics instance
            return new Analytics($analyticsClient, $propertyId);

        } catch (Exception $e) {
            Log::error('Analytics Service Initialization Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get cached daily visitors (Last 30 days)
     */
    public static function getDailyVisitors()
    {
        return Cache::remember('analytics_visitors', now()->addMinutes(30), function () {
            $client = self::getClient();
            if (!$client) {
                return collect();
            }

            try {
                return $client->fetchTotalVisitorsAndPageViews(Period::days(30));
            } catch (Exception $e) {
                Log::error('Analytics fetch visitors error: ' . $e->getMessage());
                return collect();
            }
        });
    }

    /**
     * Get cached top referrers
     */
    public static function getTopReferrers()
    {
        return Cache::remember('analytics_top_referrers', now()->addMinutes(30), function () {
            $client = self::getClient();
            if (!$client) {
                return collect();
            }

            try {
                return $client->fetchTopReferrers(Period::days(30), 10);
            } catch (Exception $e) {
                Log::error('Analytics fetch referrers error: ' . $e->getMessage());
                return collect();
            }
        });
    }

    /**
     * Get cached top pages
     */
    public static function getTopPages()
    {
        return Cache::remember('analytics_top_pages', now()->addMinutes(30), function () {
            $client = self::getClient();
            if (!$client) {
                return collect();
            }

            try {
                return $client->fetchMostVisitedPages(Period::days(30), 10);
            } catch (Exception $e) {
                Log::error('Analytics fetch pages error: ' . $e->getMessage());
                return collect();
            }
        });
    }

    /**
     * Get cached user types (New vs Returning)
     */
    public static function getUserTypes()
    {
        return Cache::remember('analytics_user_types', now()->addMinutes(30), function () {
            $client = self::getClient();
            if (!$client) {
                return collect();
            }

            try {
                return $client->fetchUserTypes(Period::days(30));
            } catch (Exception $e) {
                Log::error('Analytics fetch user types error: ' . $e->getMessage());
                return collect();
            }
        });
    }
    
    /**
     * Get top countries
     */
    public static function getTopCountries()
    {
        return Cache::remember('analytics_top_countries', now()->addMinutes(30), function () {
            $client = self::getClient();
            if (!$client) {
                return collect();
            }

            try {
                $response = $client->get(
                    Period::days(30),
                    ['activeUsers'],
                    ['country']
                );
                return $response;
            } catch (Exception $e) {
                Log::error('Analytics fetch countries error: ' . $e->getMessage());
                return collect();
            }
        });
    }
}
