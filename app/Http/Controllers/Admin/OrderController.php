<?php

namespace App\Http\Controllers\Admin;

use App\Mail\OrderVerificationMail;
use App\Mail\PlaceOrder;
use App\Mail\UserOfflinePaymentMail;
use App\Models\Item;
use App\Models\Zone;
use App\Models\Order;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\Refund;
use App\Models\Category;
use App\Scopes\ZoneScope;
use App\Scopes\StoreScope;
use App\Models\DeliveryMan;
use App\Models\OrderDetail;
use App\Models\Translation;
use App\Exports\OrderExport;
use App\Mail\RefundRejected;
use App\Models\ItemCampaign;
use App\Models\RefundReason;
use App\Traits\PlaceNewOrder;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\CouponLogic;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\StoreOrderlistExport;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use MatanYadaev\EloquentSpatial\Objects\Point;

class OrderController extends Controller
{
    use PlaceNewOrder;
    
    public function dashboard_list($status, Request $request)
    {
        // Map status to exact dashboard titles
        $statusTitles = [
            'order_received' => 'Order Received',
            'accepted' => 'Order Accepted',
            'rejected' => 'Order Rejected',
            'confirmed' => 'Ready to Ship',
            'taksh_assigned' => 'Taksh Assigned',
            'unassigned_pending' => 'Un-Assigned Pending',
            'other_logistics_assigned' => 'Other Logistics Assigned',
            'total_assigned' => 'Total Assigned',
            'handover' => 'Out for Pickup',
            'item_on_the_way' => 'Order Picked-Up',
            'connected_to_hub' => 'Order Connected to Hub',
            'received_at_center' => 'Order Received at Center',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Order Delivered',
            'rescheduled' => 'Order Rescheduled',
            'canceled' => 'Order Cancelled',
            'on_hold' => 'On Hold',
            'reattempt' => 'Order Reattempt',
            'return_to_origin' => 'Return to Origin',
            'return_connected_to_hub' => 'Return to Origin Connected to Hub',
            'received_at_hub' => 'Received at Hub',
            'hub_connected_to_destination' => 'Hub Connected to Destination',
        ];
        
        // Get the title from mapping or fallback to formatted status
        $statusTitle = $statusTitles[$status] ?? ucwords(str_replace(['_', '-'], ' ', $status));
        
        return view('admin-views.order.dashboard-list', compact('status', 'statusTitle'));
    }
    
    public function list($status, Request $request)
    {
        // dd($status);
        $key = explode(' ', $request['search']);
        if (session()->has('zone_filter') == false) {
            session()->put('zone_filter', 0);
        }
        $module_id = $request->query('module_id', null);
        if (session()->has('order_filter')) {
            $request = json_decode(session('order_filter'));
        }
        Order::where(['checked' => 0])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'store'])
            ->when(isset($module_id), function ($query) use ($module_id) {
                return $query->module($module_id);
            })
            ->when(isset($request->zone), function ($query) use ($request) {
                return $query->whereHas('store', function ($q) use ($request) {
                    return $q->whereIn('zone_id', $request->zone);
                });
            })
            ->when($status == 'scheduled', function ($query) {
                return $query->whereRaw('created_at <> schedule_at');
            })
            ->when($status == 'searching_for_deliverymen', function ($query) {
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'pending', function ($query) {
                return $query->where('order_status', 'pending');
            })
            ->when($status == 'accepted', function ($query) {
                return $query->where('order_status', 'confirmed');
            })
            ->when($status == 'confirmed', function ($query) {
                return $query->whereIn('order_status', ['confirmed', 'accepted'])->whereNotNull('confirmed');
            })
            ->when($status == 'processing', function ($query) {
                return $query->where('order_status', 'processing');
            })
            ->when($status == 'handover', function ($query) {
                return $query->where('order_status', 'handover');
            })
            ->when($status == 'picked_up', function ($query) {
                return $query->where('order_status', 'picked_up');
            })
            ->when($status == 'delivered', function ($query) {
                return $query->Delivered();
            })
            ->when($status == 'canceled', function ($query) {
                return $query->where('order_status', 'canceled');
            })
            ->when($status == 'refund_requested', function ($query) {
                return $query->RefundRequested();
            })
            ->when($status == 'refunded', function ($query) {
                return $query->Refunded();
            })
            ->when($status == 'failed', function ($query) {
                return $query->Failed();
            })
            ->when($status == 'item_on_the_way', function ($query) {
                return $query->where('order_status', 'picked_up');
            })
            ->when(isset($key) && count($key) > 0, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            })
            ->when(isset($request->from) && isset($request->to), function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })
            ->StoreOrder()
            ->OrderScheduledIn(30)
            ->orderBy('schedule_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(config('default_pagination'));

        $total = $orders->total();

        return view('admin-views.order.list', compact('orders', 'status', 'total'));
    }
}
