<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); // Pastikan semua rute di controller ini dilindungi oleh middleware auth
    }

    /**
     * Display the dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get dashboard statistics for total assets, items on loan, and assets under maintenance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        try {
            // Validasi apakah tabel-tabel ada di database
            $tablesExist = Schema::hasTable('assets') && Schema::hasTable('loans') && Schema::hasTable('maintenances');
            if (!$tablesExist) {
                throw new \Exception('One or more required tables (assets, loans, maintenances) are missing in the database.');
            }

            // Ambil data statistik
            $totalAset = Asset::count() ?? 0;
            $barangDipinjam = Loan::where('status', 'dipinjam')->count() ?? 0;
            $asetMaintenance = Maintenance::where('status', 'ongoing')->count() ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalAset' => $totalAset,
                    'barangDipinjam' => $barangDipinjam,
                    'asetMaintenance' => $asetMaintenance,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'totalAset' => 0,
                    'barangDipinjam' => 0,
                    'asetMaintenance' => 0,
                ],
                'error' => 'Failed to fetch stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get asset conditions for the dashboard chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssetConditions()
    {
        try {
            // Validasi apakah tabel assets ada
            if (!Schema::hasTable('assets')) {
                throw new \Exception('Table "assets" is missing in the database.');
            }

            // Validasi apakah kolom condition ada
            if (!Schema::hasColumn('assets', 'condition')) {
                throw new \Exception('Column "condition" is missing in the "assets" table.');
            }

            // Ambil data kondisi aset
            $conditions = [
                'Baik' => Asset::where('condition', 'baik')->count() ?? 0,
                'Rusak Ringan' => Asset::where('condition', 'rusak_ringan')->count() ?? 0,
                'Rusak Berat' => Asset::where('condition', 'rusak_berat')->count() ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => array_keys($conditions),
                    'data' => array_values($conditions),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'labels' => ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                    'data' => [0, 0, 0],
                ],
                'error' => 'Failed to fetch asset conditions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get asset types for the dashboard chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssetTypes()
    {
        try {
            // Validasi apakah tabel assets ada
            if (!Schema::hasTable('assets')) {
                throw new \Exception('Table "assets" is missing in the database.');
            }

            // Validasi apakah kolom category ada
            if (!Schema::hasColumn('assets', 'category')) {
                throw new \Exception('Column "category" is missing in the "assets" table.');
            }

            // Ambil data jenis aset
            $types = [
                'Komputer' => Asset::where('category', 'komputer')->count() ?? 0,
                'Laptop' => Asset::where('category', 'laptop')->count() ?? 0,
                'LED Monitor' => Asset::where('category', 'led_monitor')->count() ?? 0,
                'Printer' => Asset::where('category', 'printer')->count() ?? 0,
                'Electrolux' => Asset::where('category', 'electrolux')->count() ?? 0,
                'Furniture' => Asset::where('category', 'furniture')->count() ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => array_keys($types),
                    'data' => array_values($types),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'labels' => ['Komputer', 'Laptop', 'LED Monitor', 'Printer', 'Electrolux', 'Furniture'],
                    'data' => [0, 0, 0, 0, 0, 0],
                ],
                'error' => 'Failed to fetch asset types: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the activity report view.
     *
     * @return \Illuminate\View\View
     */
    public function activityReport()
    {
        try {
            return view('reports.activity');
        } catch (\Exception $e) {
            return response()->view('errors.custom', [
                'error' => 'Failed to load activity report: ' . $e->getMessage(),
            ], 500);
        }
    }
}