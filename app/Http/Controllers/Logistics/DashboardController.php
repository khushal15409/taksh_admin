<?php

namespace App\Http\Controllers\Logistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Store;
use App\Models\Admin;
use App\Models\DeliveryMan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get zone_id from session or default to 'all'
        $zone_id = session('dash_params')['zone_id'] ?? 'all';
        
        // Fetch real data from database
        $data = [
            // Order Details - 30 MIN ORDER (Orders created in last 30 minutes)
            'order_30min' => $this->get30MinOrders($zone_id),
            
            // FM ORDER (Fullfillment Orders)
            'order_fm' => $this->getFMOrders($zone_id),
            
            // FORWARD ORDER (Forwarded Orders)
            'order_forward' => $this->getForwardOrders($zone_id),
            
            // RVP ORDER (Return/Reverse Orders)
            'order_rvp' => $this->getRVPOrders($zone_id),
            
            // 3PC ORDER (Third Party Courier Orders)
            'order_3pc' => $this->get3PCOrders($zone_id),
            
            // RTS ORDER (Return to Sender Orders)
            'order_rts' => $this->getRTSOrders($zone_id),
            
            // Customer Details
            'total_customers' => $this->getTotalCustomers($zone_id),
            'active_customers' => $this->getActiveCustomers($zone_id),
            'new_customers' => $this->getNewCustomers($zone_id),
            'deactivated_customers' => $this->getDeactivatedCustomers($zone_id),
            'blocked_customers' => $this->getBlockedCustomers($zone_id),
            
            // Seller Details
            'total_sellers' => $this->getTotalSellers($zone_id),
            'active_sellers' => $this->getActiveSellers($zone_id),
            'new_sellers' => $this->getNewSellers($zone_id),
            'deactivated_sellers' => $this->getDeactivatedSellers($zone_id),
            'blocked_sellers' => $this->getBlockedSellers($zone_id),
            
            // Warehouse & Mobile Warehouse Details (using stores as warehouses)
            'total_wh' => $this->getTotalWarehouses($zone_id),
            'total_mwh' => $this->getTotalMobileWarehouses($zone_id),
            'mwh_lmc' => $this->getMWHLMC($zone_id),
            'lmc' => $this->getLMC($zone_id),
            
            // Employee Details
            'total_employees' => $this->getTotalEmployees($zone_id),
            'active_employees' => $this->getActiveEmployees($zone_id),
            'deactivated_employees' => $this->getDeactivatedEmployees($zone_id),
            'new_employees' => $this->getNewEmployees($zone_id),
            'blocked_employees' => $this->getBlockedEmployees($zone_id),
            
            // Transaction Details
            'total_fr' => $this->getTotalFR($zone_id),
            'total_pen_cov' => $this->getTotalPenCov($zone_id),
            'total_db' => $this->getTotalDB($zone_id),
            'active_da' => $this->getActiveDA($zone_id),
            'blocked' => $this->getBlockedTransactions($zone_id),
        ];
        
        // Set module_type to use the default admin sidebar (grocery is the default)
        $module_type = 'grocery';
        
        return view('logistics-views.dashboard', compact('data', 'module_type'));
    }

    // Helper methods for Order Details
    private function get30MinOrders($zone_id)
    {
        $baseQuery = Order::where('created_at', '>=', Carbon::now()->subMinutes(30));
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        // OA - Order Accepted
        $oa = (clone $baseQuery)->where('order_status', 'accepted')->count();
        
        // OACAP - Order Accepted Courier Assign Pending (orders accepted but not yet assigned to courier)
        $oacap = (clone $baseQuery)->where('order_status', 'accepted')->whereNull('delivery_man_id')->count();
        
        // Taksh Assign - orders assigned to Taksh delivery man
        // Assuming Taksh delivery men can be identified by a specific field or all assigned orders are Taksh
        $taksh_assign = (clone $baseQuery)->where('order_status', 'accepted')->whereNotNull('delivery_man_id')->count();
        
        // Other Logistic - orders assigned to other logistics (if you have a way to distinguish)
        // For now, set to 0 as we need additional logic to distinguish between Taksh and other logistics
        $other_logistic = 0;
        
        // Total Assign - Taksh Assign + Other Logistic
        $total_assign = $taksh_assign + $other_logistic;
        
        return [
            'op' => (clone $baseQuery)->where('order_status', 'pending')->count(), // Order Pending
            'oa' => $oa, // Order Accepted
            'cap' => $oacap, // Courier Assign Pending (replacing OACAP)
            'taksh_assign' => $taksh_assign, // Taksh Assign
            'other_logistic' => $other_logistic, // Other Logistic
            'total_assign' => $total_assign, // Total Assign
            'ofp' => (clone $baseQuery)->whereIn('order_status', ['confirmed', 'processing'])->count(), // Order For Processing
            'op2' => (clone $baseQuery)->where('order_status', 'processing')->count(), // Order Processing (duplicate key renamed)
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(), // Order Delivered
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(), // Order Canceled
            'rtsd' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // RTS Delivered
            'rtsto' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // RTS To Origin
        ];
    }

    private function getFMOrders($zone_id)
    {
        $baseQuery = Order::query();
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        // OA - Order Accepted
        $oa = (clone $baseQuery)->where('order_status', 'accepted')->count();
        
        // CAP - Courier Assign Pending (orders accepted but not yet assigned to courier)
        $cap = (clone $baseQuery)->where('order_status', 'accepted')->whereNull('delivery_man_id')->count();
        
        // Taksh Assign - orders assigned to delivery man (assuming all assigned are Taksh for now)
        // Note: If you need to distinguish between Taksh and other logistics, you'll need to add a delivery_type column or use delivery_man relationship
        $taksh_assign = (clone $baseQuery)->where('order_status', 'accepted')->whereNotNull('delivery_man_id')->count();
        
        // Other Logistic - set to 0 for now since we can't distinguish without delivery_type column
        $other_logistic = 0;
        
        // Total Assign - Taksh Assign + Other Logistic
        $total_assign = $taksh_assign + $other_logistic;
        
        return [
            'op' => (clone $baseQuery)->where('order_status', 'pending')->count(),
            'oa' => $oa,
            'cap' => $cap, // Courier Assign Pending
            'taksh_assign' => $taksh_assign, // Taksh Assign
            'other_logistic' => $other_logistic, // Other Logistic
            'total_assign' => $total_assign, // Total Assign (Taksh + Other)
            'ofp' => (clone $baseQuery)->whereIn('order_status', ['confirmed', 'processing'])->count(),
            'op2' => (clone $baseQuery)->where('order_status', 'processing')->count(),
            'or' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
            'os' => (clone $baseQuery)->where('order_status', 'handover')->count(),
        ];
    }

    private function getForwardOrders($zone_id)
    {
        $baseQuery = Order::whereNotNull('delivery_man_id');
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        return [
            'to' => (clone $baseQuery)->where('order_status', 'picked_up')->count(), // To Origin
            'ofd' => (clone $baseQuery)->where('order_status', 'picked_up')->count(), // Order For Delivery
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(), // Order Delivered
            'or' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Order Return
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(), // Order Canceled
            'orto' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('delivery_man_id')->count(), // Order Return To Origin
            'orts' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Order Return To Sender
        ];
    }

    private function getRVPOrders($zone_id)
    {
        $baseQuery = Order::whereIn('order_status', ['refund_requested', 'refunded']);
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        // RA - Return Accepted (orders with refund_requested status that have been accepted)
        $ra = (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('accepted')->count();
        
        // CAP - Courier Assign Pending (orders accepted for return but not yet assigned to courier)
        $cap = (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('accepted')->whereNull('delivery_man_id')->count();
        
        // Taksh Assign - orders assigned to Taksh delivery man
        $taksh_assign = (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('accepted')->whereNotNull('delivery_man_id')->count();
        
        // Other Logistic - orders assigned to other logistics (if you have a way to distinguish)
        // For now, set to 0 as we need additional logic to distinguish between Taksh and other logistics
        $other_logistic = 0;
        
        // Total Assign - Taksh Assign + Other Logistic
        $total_assign = $taksh_assign + $other_logistic;
        
        return [
            'crp' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Customer Return Pending
            'ra' => $ra, // Return Accepted
            'rr' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('refund_requested')->count(), // Return Requested
            'cap' => $cap, // Courier Assign Pending
            'taksh_assign' => $taksh_assign, // Taksh Assign
            'other_logistic' => $other_logistic, // Other Logistic
            'total_assign' => $total_assign, // Total Assign
            'ofp' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('processing')->count(), // Order For Processing
            'op' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('processing')->count(), // Order Processing
            'or' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Order Refunded
            'rvrs' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Reverse
            'rvpc' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Reverse Completed
        ];
    }

    private function get3PCOrders($zone_id)
    {
        $baseQuery = Order::where('order_type', 'parcel');
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        return [
            'or1' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Order Return (row 1)
            'oa' => (clone $baseQuery)->where('order_status', 'accepted')->count(), // Order Accepted
            'or2' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Order Return (row 1, duplicate)
            'ofp' => (clone $baseQuery)->whereIn('order_status', ['confirmed', 'processing'])->count(), // Order For Processing
            'op' => (clone $baseQuery)->where('order_status', 'processing')->count(), // Order Processing
            'os' => (clone $baseQuery)->where('order_status', 'handover')->count(), // Order Shipped
            'ofd' => (clone $baseQuery)->where('order_status', 'picked_up')->count(), // Order For Delivery (row 2)
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(), // Order Delivered
            'or3' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Order Return (row 2)
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(), // Order Canceled
            'orts' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Order Return To Sender
            'rtss' => (clone $baseQuery)->where('order_status', 'refunded')->whereNotNull('refunded')->count(), // RTS Status
        ];
    }

    private function getRTSOrders($zone_id)
    {
        $baseQuery = Order::whereIn('order_status', ['refund_requested', 'refunded']);
        $baseQuery = $this->applyZoneFilter($baseQuery, $zone_id);
        
        return [
            'torts' => (clone $baseQuery)->where('order_status', 'refund_requested')->count() + (clone $baseQuery)->where('order_status', 'refunded')->count(), // Total RTS
            'rvpirts' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('refund_requested')->count(), // RVP In RTS
            'srts' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Success RTS
            'rvprts' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('refund_requested')->count(), // RVP RTS
            'ofd' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('picked_up')->count(), // Order For Delivery
            'od' => (clone $baseQuery)->where('order_status', 'refunded')->whereNotNull('delivered')->count(), // Order Delivered
            'or' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(), // Order Return
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(), // Order Canceled
            'dto' => (clone $baseQuery)->where('order_status', 'refunded')->count(), // Delivered To Origin
        ];
    }

    // Helper methods for Customer Details
    private function getTotalCustomers($zone_id)
    {
        $query = User::query();
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getActiveCustomers($zone_id)
    {
        $query = User::where('status', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getNewCustomers($zone_id)
    {
        $query = User::whereDate('created_at', '>=', Carbon::now()->subDays(30));
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getDeactivatedCustomers($zone_id)
    {
        $query = User::where('status', 0);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getBlockedCustomers($zone_id)
    {
        $query = User::where('status', 0);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    // Helper methods for Seller Details
    private function getTotalSellers($zone_id)
    {
        $query = Store::query();
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getActiveSellers($zone_id)
    {
        $query = Store::where('status', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getNewSellers($zone_id)
    {
        $query = Store::whereDate('created_at', '>=', Carbon::now()->subDays(30));
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getDeactivatedSellers($zone_id)
    {
        $query = Store::where('status', 0);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getBlockedSellers($zone_id)
    {
        $query = Store::where('status', 0)->where('active', 0);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    // Helper methods for Warehouse Details
    private function getTotalWarehouses($zone_id)
    {
        // Using stores as warehouses
        $query = Store::where('module_id', '!=', null);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getTotalMobileWarehouses($zone_id)
    {
        // Mobile warehouses - stores with delivery enabled
        $query = Store::where('status', 1)->where('active', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getMWHLMC($zone_id)
    {
        // MWH + LMC - Active stores with delivery
        $query = Store::where('status', 1)->where('active', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getLMC($zone_id)
    {
        // LMC - Stores with take_away enabled
        $query = Store::where('status', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    // Helper methods for Employee Details
    private function getTotalEmployees($zone_id)
    {
        $query = Admin::where('role_id', '!=', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getActiveEmployees($zone_id)
    {
        $query = Admin::where('role_id', '!=', 1)->where('is_logged_in', 1);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getDeactivatedEmployees($zone_id)
    {
        $query = Admin::where('role_id', '!=', 1)->where('is_logged_in', 0);
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getNewEmployees($zone_id)
    {
        $query = Admin::where('role_id', '!=', 1)->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getBlockedEmployees($zone_id)
    {
        // Admin table doesn't have a status column, so returning 0 for blocked employees
        // If you need to track blocked employees, you may need to add a status column or use a different field
        return 0;
    }

    // Helper methods for Transaction Details
    private function getTotalFR($zone_id)
    {
        // Total Financial Records - Total orders
        $query = Order::query();
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getTotalPenCov($zone_id)
    {
        // Total Pending Coverage - Pending orders
        $query = Order::where('order_status', 'pending');
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getTotalDB($zone_id)
    {
        // Total Database - All transactions
        $query = Order::query();
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getActiveDA($zone_id)
    {
        // Active Delivery Addresses - Orders with delivery addresses
        $query = Order::whereNotNull('delivery_address_id');
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    private function getBlockedTransactions($zone_id)
    {
        // Blocked - Canceled orders
        $query = Order::where('order_status', 'canceled');
        return $this->applyZoneFilter($query, $zone_id)->count();
    }

    // Helper method to apply zone filter
    private function applyZoneFilter($query, $zone_id)
    {
        if (is_numeric($zone_id)) {
            if (method_exists($query->getModel(), 'zone')) {
                return $query->zone($zone_id);
            } elseif ($query->getModel() instanceof Order || $query->getModel() instanceof Store) {
                return $query->where('zone_id', $zone_id);
            } elseif ($query->getModel() instanceof User) {
                // Users don't have zone_id directly, filter through orders
                return $query;
            }
        }
        return $query;
    }
}
