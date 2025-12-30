<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Miniwarehouse;
use App\Models\LmCenter;
use App\Models\FmRtCenter;
use App\Models\Pincode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingMappingController extends Controller
{
    /**
     * Display a listing of pending mappings.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'warehouse');
        
        // Get pending warehouses (not mapped to any warehouse, miniwarehouse, lm center, or fm/rt center)
        // A warehouse is considered "pending" if it has NO mappings at all
        $mappedWarehouseIds = collect();
        
        // Check warehouse_warehouse table (both directions)
        if (DB::getSchemaBuilder()->hasTable('warehouse_warehouse')) {
            $mappedWarehouseIds = $mappedWarehouseIds->merge(DB::table('warehouse_warehouse')->pluck('warehouse_id'));
            $mappedWarehouseIds = $mappedWarehouseIds->merge(DB::table('warehouse_warehouse')->pluck('mapped_warehouse_id'));
        }
        
        // Check warehouse_miniwarehouse table
        if (DB::getSchemaBuilder()->hasTable('warehouse_miniwarehouse')) {
            $mappedWarehouseIds = $mappedWarehouseIds->merge(DB::table('warehouse_miniwarehouse')->pluck('warehouse_id'));
        }
        
        // Check warehouse_lm_center table
        if (DB::getSchemaBuilder()->hasTable('warehouse_lm_center')) {
            $mappedWarehouseIds = $mappedWarehouseIds->merge(DB::table('warehouse_lm_center')->pluck('warehouse_id'));
        }
        
        // Check warehouse_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('warehouse_fm_rt_center')) {
            $mappedWarehouseIds = $mappedWarehouseIds->merge(DB::table('warehouse_fm_rt_center')->pluck('warehouse_id'));
        }
        
        $mappedWarehouseIds = $mappedWarehouseIds->unique()->toArray();
        
        $pendingWarehouses = Warehouse::where('status', 1)
            ->whereNotIn('id', $mappedWarehouseIds)
            ->latest()
            ->get();
        
        // Get pending miniwarehouses (not mapped to any warehouse, lm center, or fm/rt center)
        $mappedMiniwarehouseIds = collect();
        
        // Check warehouse_miniwarehouse table
        if (DB::getSchemaBuilder()->hasTable('warehouse_miniwarehouse')) {
            $mappedMiniwarehouseIds = $mappedMiniwarehouseIds->merge(DB::table('warehouse_miniwarehouse')->pluck('miniwarehouse_id'));
        }
        
        // Check miniwarehouse_lm_center table
        if (DB::getSchemaBuilder()->hasTable('miniwarehouse_lm_center')) {
            $mappedMiniwarehouseIds = $mappedMiniwarehouseIds->merge(DB::table('miniwarehouse_lm_center')->pluck('miniwarehouse_id'));
        }
        
        // Check miniwarehouse_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('miniwarehouse_fm_rt_center')) {
            $mappedMiniwarehouseIds = $mappedMiniwarehouseIds->merge(DB::table('miniwarehouse_fm_rt_center')->pluck('miniwarehouse_id'));
        }
        
        $mappedMiniwarehouseIds = $mappedMiniwarehouseIds->unique()->toArray();
        
        $pendingMiniwarehouses = Miniwarehouse::where('status', 1)
            ->whereNotIn('id', $mappedMiniwarehouseIds)
            ->latest()
            ->get();
        
        // Get pending LM Centers (not mapped to any warehouse, miniwarehouse, fm/rt center, or pincode)
        $mappedLmCenterIds = collect();
        
        // Check warehouse_lm_center table
        if (DB::getSchemaBuilder()->hasTable('warehouse_lm_center')) {
            $mappedLmCenterIds = $mappedLmCenterIds->merge(DB::table('warehouse_lm_center')->pluck('lm_center_id'));
        }
        
        // Check miniwarehouse_lm_center table
        if (DB::getSchemaBuilder()->hasTable('miniwarehouse_lm_center')) {
            $mappedLmCenterIds = $mappedLmCenterIds->merge(DB::table('miniwarehouse_lm_center')->pluck('lm_center_id'));
        }
        
        // Check lm_center_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('lm_center_fm_rt_center')) {
            $mappedLmCenterIds = $mappedLmCenterIds->merge(DB::table('lm_center_fm_rt_center')->pluck('lm_center_id'));
        }
        
        // Check lm_center_pincode table (pincode mappings)
        if (DB::getSchemaBuilder()->hasTable('lm_center_pincode')) {
            $mappedLmCenterIds = $mappedLmCenterIds->merge(DB::table('lm_center_pincode')->pluck('lm_center_id'));
        }
        
        $mappedLmCenterIds = $mappedLmCenterIds->unique()->toArray();
        
        $pendingLmCenters = LmCenter::where('status', 1)
            ->whereNotIn('id', $mappedLmCenterIds)
            ->latest()
            ->get();
        
        // Get pending FM/RT Centers (not mapped to any warehouse, miniwarehouse, LM center, or pincode)
        $mappedFmRtCenterIds = collect();
        
        // Check warehouse_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('warehouse_fm_rt_center')) {
            $mappedFmRtCenterIds = $mappedFmRtCenterIds->merge(DB::table('warehouse_fm_rt_center')->pluck('fm_rt_center_id'));
        }
        
        // Check miniwarehouse_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('miniwarehouse_fm_rt_center')) {
            $mappedFmRtCenterIds = $mappedFmRtCenterIds->merge(DB::table('miniwarehouse_fm_rt_center')->pluck('fm_rt_center_id'));
        }
        
        // Check lm_center_fm_rt_center table
        if (DB::getSchemaBuilder()->hasTable('lm_center_fm_rt_center')) {
            $mappedFmRtCenterIds = $mappedFmRtCenterIds->merge(DB::table('lm_center_fm_rt_center')->pluck('fm_rt_center_id'));
        }
        
        // Check fm_rt_center_pincode table (pincode mappings)
        if (DB::getSchemaBuilder()->hasTable('fm_rt_center_pincode')) {
            $mappedFmRtCenterIds = $mappedFmRtCenterIds->merge(DB::table('fm_rt_center_pincode')->pluck('fm_rt_center_id'));
        }
        
        $mappedFmRtCenterIds = $mappedFmRtCenterIds->unique()->toArray();
        
        $pendingFmRtCenters = FmRtCenter::where('status', 1)
            ->whereNotIn('id', $mappedFmRtCenterIds)
            ->latest()
            ->get();
        
        // Get pending pincodes (not mapped to any LM center or FM/RT center)
        $mappedPincodeIds = collect();
        
        if (DB::getSchemaBuilder()->hasTable('lm_center_pincode')) {
            $mappedPincodeIds = $mappedPincodeIds->merge(DB::table('lm_center_pincode')->pluck('pincode_id'));
        }
        
        if (DB::getSchemaBuilder()->hasTable('fm_rt_center_pincode')) {
            $mappedPincodeIds = $mappedPincodeIds->merge(DB::table('fm_rt_center_pincode')->pluck('pincode_id'));
        }
        
        $mappedPincodeIds = $mappedPincodeIds->unique()->toArray();
        
        $pendingPincodes = Pincode::whereNotIn('id', $mappedPincodeIds)
            ->orderBy('pincode')
            ->get();
        
        // Get active pincodes (mapped to LM centers)
        $activePincodes = collect();
        
        if (DB::getSchemaBuilder()->hasTable('lm_center_pincode')) {
            $activePincodeMappings = DB::table('lm_center_pincode')
                ->join('pincodes', 'lm_center_pincode.pincode_id', '=', 'pincodes.id')
                ->join('lm_centers', 'lm_center_pincode.lm_center_id', '=', 'lm_centers.id')
                ->select(
                    'pincodes.id as pincode_id',
                    'pincodes.pincode',
                    'pincodes.officename',
                    'pincodes.district',
                    'pincodes.statename',
                    'lm_centers.id as lm_center_id',
                    'lm_centers.center_name',
                    'lm_center_pincode.created_at as mapped_at'
                )
                ->orderBy('pincodes.pincode')
                ->get();
            
            $activePincodes = $activePincodeMappings;
        }
        
        return view('admin-views.logistics.pending-mapping.index', compact(
            'tab',
            'pendingWarehouses',
            'pendingMiniwarehouses',
            'pendingLmCenters',
            'pendingFmRtCenters',
            'pendingPincodes',
            'activePincodes'
        ));
    }
}

