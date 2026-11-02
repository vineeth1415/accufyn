<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{
    /**
     * Get current installed version.
     */
    public function getCurrentVersion(): string
    {
        $versionFile = storage_path('version.json');
        if (File::exists($versionFile)) {
            $data = json_decode(File::get($versionFile), true);
            if (!empty($data['version'])) {
                return $data['version'];
            }
        }
        return '1.0.0';
    }

    /**
     * Show Updates dashboard page.
     */
    public function index()
    {
        $currentVersion = $this->getCurrentVersion();
        $updateInfo = $this->fetchUpdateInfo($currentVersion);

        return view('updates.index', compact('currentVersion', 'updateInfo'));
    }

    /**
     * Check for updates via AJAX.
     */
    public function check()
    {
        $currentVersion = $this->getCurrentVersion();
        $updateInfo = $this->fetchUpdateInfo($currentVersion);

        return response()->json($updateInfo);
    }

    /**
     * One-Click Apply Update.
     */
    public function apply(Request $request)
    {
        $currentVersion = $this->getCurrentVersion();

        try {
            // 1. Run any pending database migrations
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = Artisan::output();

            // 2. Clear application and template caches
            try {
                Artisan::call('optimize:clear');
            } catch (Exception $e) {}

            // 3. Update version file
            $newVersion = '1.1.3';
            $versionData = [
                'version' => $newVersion,
                'app_name' => 'WebotApp Accounting',
                'updated_at' => date('Y-m-d H:i:s'),
                'previous_version' => $currentVersion,
                'notes' => 'v1.1.3 Pending Invoices, Rupee Defaults & Password Change Release'
            ];
            File::put(storage_path('version.json'), json_encode($versionData, JSON_PRETTY_PRINT));

            // Also update storage/installed if present
            if (File::exists(storage_path('installed'))) {
                $installed = json_decode(File::get(storage_path('installed')), true) ?? [];
                $installed['app_version'] = $newVersion;
                $installed['last_updated_at'] = date('Y-m-d H:i:s');
                File::put(storage_path('installed'), json_encode($installed, JSON_PRETTY_PRINT));
            }

            return response()->json([
                'success' => true,
                'message' => "System successfully updated to v{$newVersion}!",
                'current_version' => $newVersion,
                'output' => $migrateOutput
            ]);
        } catch (Exception $e) {
            Log::error('Auto-update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch update details from central WebotApp Lab licensing server.
     */
    protected function fetchUpdateInfo(string $currentVersion): array
    {
        $purchaseCode = env('PURCHASE_CODE', '');
        if (empty($purchaseCode) && File::exists(storage_path('installed'))) {
            $installed = json_decode(File::get(storage_path('installed')), true) ?? [];
            $purchaseCode = $installed['purchase_code'] ?? '';
        }

        $latestKnown = '1.1.3';
        $payload = null;

        try {
            $response = Http::timeout(4)->get('https://lab.webotapp.com/api/license/check/webotapp-accounting', [
                'current_version' => $currentVersion,
                'purchase_code' => $purchaseCode,
            ]);

            if ($response->successful()) {
                $payload = $response->json();
            }
        } catch (Exception $e) {
            Log::warning('Unable to fetch live update info: ' . $e->getMessage());
        }

        if (!$payload) {
            $payload = [
                'latest_version' => $latestKnown,
                'current_version' => $currentVersion,
                'has_update' => version_compare($currentVersion, $latestKnown, '<'),
                'update_eligible' => true,
                'license_type' => 'regular',
                'is_extended' => false,
                'updates_remaining' => 'Lifetime',
                'release_date' => '2026-09-21',
                'title' => "WebotApp Accounts v{$latestKnown} Pending Invoices, Rupee Defaults & Password Change Release",
                'changelog' => [
                    'Added Administrator Change Password and security credentials in Settings',
                    'Default system currency set to Indian Rupee (INR / ₹) across all dashboards, ledgers, and installers',
                    'Newly created invoices default to PENDING status preventing premature customer email notifications',
                    'Automated customer email delivery with invoice summary and online pay link triggered on marking as Sent',
                    'Quick status switcher directly from invoice table row and details page (Pending, Sent, Paid, Cancelled)',
                    'Inline + New Customer modal inside invoice creation and editing views without lost item inputs',
                    'Direct downloadable release archive integration with 1-click in-dashboard update'
                ],
                'download_url' => 'https://lab.webotapp.com/uploads/products/webotapp-accounts-source.zip'
            ];
        }

        // Safeguard: If central server has not restarted or returns an older version than latestKnown, enforce latestKnown
        if (version_compare($payload['latest_version'] ?? '1.0.0', $latestKnown, '<')) {
            $payload['latest_version'] = $latestKnown;
            $payload['has_update'] = version_compare($currentVersion, $latestKnown, '<');
            $payload['title'] = "WebotApp Accounts v{$latestKnown} Pending Invoices, Rupee Defaults & Password Change Release";
            $payload['changelog'] = [
                'Added Administrator Change Password and security credentials in Settings',
                'Default system currency set to Indian Rupee (INR / ₹) across all dashboards, ledgers, and installers',
                'Newly created invoices default to PENDING status preventing premature customer email notifications',
                'Automated customer email delivery with invoice summary and online pay link triggered on marking as Sent',
                'Quick status switcher directly from invoice table row and details page (Pending, Sent, Paid, Cancelled)',
                'Inline + New Customer modal inside invoice creation and editing views without lost item inputs',
                'Direct downloadable release archive integration with 1-click in-dashboard update'
            ];
        }

        return $payload;
    }
}
