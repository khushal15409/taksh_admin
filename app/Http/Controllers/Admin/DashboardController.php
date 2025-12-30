<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Store;
use App\Models\Module;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\AdminRole;
use App\Scopes\ZoneScope;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class DashboardController extends Controller
{

    public function __construct()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
    public function user_dashboard(Request $request)
    {
        $params = [
            'zone_id' => $request['zone_id'] ?? 'all',
            'module_id' => Config::get('module.current_module_id'),
            'statistics_type' => $request['statistics_type'] ?? 'overall',
            'user_overview' => $request['user_overview'] ?? 'overall',
            'commission_overview' => $request['commission_overview'] ?? 'this_year',
            'business_overview' => $request['business_overview'] ?? 'overall',
        ];

        session()->put('dash_params', $params);
        $data = self::dashboard_data($request);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $delivery_commission = $data['delivery_commission'];
        $customers = User::zone($params['zone_id'])->take(2)->get();

        $delivery_man = DeliveryMan::with('last_location')->when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()
        ->limit(2)->get('image');

        $active_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->Active()->count();

        $inactive_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('application_status','approved')->where('active',0)->count();

        $blocked_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('application_status','approved')->where('status',0)->count();

        $newly_joined_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'))->count();

        $reviews = Review::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->whereHas('item.store', function ($query) use ($params){
                return $query->where('zone_id', $params['zone_id']);
            });
        })->count();

        $positive_reviews = Review::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->whereHas('item.store', function ($query) use ($params){
                return $query->where('zone_id', $params['zone_id']);
            });
        })->whereIn('rating', [4,5])->get()->count();
        $good_reviews = Review::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->whereHas('item.store', function ($query) use ($params){
                return $query->where('zone_id', $params['zone_id']);
            });
        })->where('rating', 3)->count();
        $neutral_reviews = Review::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->whereHas('item.store', function ($query) use ($params){
                return $query->where('zone_id', $params['zone_id']);
            });
        })->where('rating', 2)->count();
        $negative_reviews = Review::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->whereHas('item.store', function ($query) use ($params){
                return $query->where('zone_id', $params['zone_id']);
            });
        })->where('rating', 1)->count();

        $from = now()->startOfMonth(); // first date of the current month
        $to = now();
        $this_month = User::zone($params['zone_id'])->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'))->count();
        $number = 12;
        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $last_year_users = User::zone($params['zone_id'])
            ->whereMonth('created_at', 12)
            ->whereYear('created_at', now()->format('Y')-1)
            ->count();

        $users = User::zone($params['zone_id'])
            ->select(
                DB::raw('(count(id)) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )
            ->whereBetween('created_at', [Carbon::parse(now())->startOfYear(), Carbon::parse(now())->endOfYear()])
            ->groupBy('year', 'month')->get()->toArray();

        for ($inc = 1; $inc <= $number; $inc++) {
            $user_data[$inc] = 0;
            foreach ($users as $match) {
                if ($match['month'] == $inc) {
                    $user_data[$inc] = $match['total'];
                }
            }
        }

        $active_customers = User::zone($params['zone_id'])->where('status',1)->count();
        $blocked_customers = User::zone($params['zone_id'])->where('status',0)->count();
        $newly_joined = User::zone($params['zone_id'])->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'))->count();

        $employees = Admin::zone()->with(['role'])->where('role_id', '!=','1')
        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->get();

        $deliveryMen = DeliveryMan::with('last_location')->when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })->zonewise()->available()->active()->get();

        $deliveryMen = Helpers::deliverymen_list_formatting($deliveryMen);

        $module_type = Config::get('module.current_module_type');
        return view("admin-views.dashboard-{$module_type}", compact('data','reviews','this_month','user_data','neutral_reviews','good_reviews','negative_reviews','positive_reviews','employees','active_deliveryman','deliveryMen','inactive_deliveryman','newly_joined_deliveryman','delivery_man', 'total_sell', 'commission', 'delivery_commission', 'params','module_type', 'customers','active_customers','blocked_customers', 'newly_joined','last_year_users', 'blocked_deliveryman'));
    }

    public function transaction_dashboard(Request $request)
    {
        $module_type = Config::get('module.current_module_type');
        return view("admin-views.dashboard-{$module_type}");
    }

    public function dispatch_dashboard(Request $request)
    {
        $params = [
            'zone_id' => $request['zone_id'] ?? 'all',
            'module_id' => Config::get('module.current_module_id'),
            'statistics_type' => $request['statistics_type'] ?? 'overall',
            'user_overview' => $request['user_overview'] ?? 'overall',
            'commission_overview' => $request['commission_overview'] ?? 'this_year',
            'business_overview' => $request['business_overview'] ?? 'overall',
        ];

        session()->put('dash_params', $params);
        $data = self::dashboard_data($request);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $delivery_commission = $data['delivery_commission'];
        $label = $data['label'];
        $customers = User::zone($params['zone_id'])->take(2)->get();

        $delivery_man = DeliveryMan::with('last_location')->when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()
        ->limit(2)->get('image');

        $active_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('active',1)->count();

        $inactive_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('application_status','approved')->where('active',0)->count();

        $suspend_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('application_status','approved')->where('status',0)->count();

        $unavailable_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('active',1)->Unavailable()->count();

        $available_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->where('active',1)->Available()->count();

        $newly_joined_deliveryman = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })
        ->Zonewise()->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'))->count();

        $deliveryMen = DeliveryMan::when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        })->zonewise()->available()->active()->get();

        $deliveryMen = Helpers::deliverymen_list_formatting($deliveryMen);

        $module_type = Config::get('module.current_module_type');
        return view("admin-views.dashboard-{$module_type}", compact('data','active_deliveryman','deliveryMen','unavailable_deliveryman','available_deliveryman','inactive_deliveryman','newly_joined_deliveryman','delivery_man', 'total_sell', 'commission', 'delivery_commission','label', 'params','module_type','suspend_deliveryman'));
    }

    public function dashboard(Request $request)
    {
        // Check if user is logistics admin and show logistics dashboard
        $admin = auth('admin')->user();
        if ($admin && isset($admin->is_logistics) && $admin->is_logistics == 1) {
            return app(\App\Http\Controllers\Logistics\DashboardController::class)->dashboard();
        }

        $params = [
            'zone_id' => $request['zone_id'] ?? 'all',
            'module_id' => Config::get('module.current_module_id'),
            'statistics_type' => $request['statistics_type'] ?? 'overall',
            'user_overview' => $request['user_overview'] ?? 'overall',
            'commission_overview' => $request['commission_overview'] ?? 'this_year',
            'business_overview' => $request['business_overview'] ?? 'overall',
        ];
        session()->put('dash_params', $params);
        $data = self::dashboard_data($request);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $delivery_commission = $data['delivery_commission'];
        $label = $data['label'];
        
        // Add ORDER DETAILS data for all dashboards
        $data['order_30min'] = $this->get30MinOrders($params['zone_id']);
        $data['order_fm'] = $this->getFMOrders($params['zone_id']);
        $data['order_forward'] = $this->getForwardOrders($params['zone_id']);
        $data['order_rvp'] = $this->getRVPOrders($params['zone_id']);
        $data['order_3pc'] = $this->get3PCOrders($params['zone_id']);
        $data['order_rts'] = $this->getRTSOrders($params['zone_id']);
        
        $module_type = Config::get('module.current_module_type');
        if($module_type == 'settings'){
            return redirect()->route('admin.business-settings.business-setup');
        }
        if($module_type == 'rental' && addon_published_status('Rental') == 1){
            return redirect()->route('admin.rental.dashboard');
        }
        if($module_type == 'rental' && addon_published_status('Rental') == 0){
            return view('errors.404');
        }
        return view("admin-views.dashboard-{$module_type}", compact('data', 'total_sell', 'commission', 'delivery_commission', 'label','params','module_type'));

    }

    public function order(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'statistics_type') {
                $params['statistics_type'] = $request['statistics_type'];
            }
        }
        session()->put('dash_params', $params);

        if ($params['zone_id'] != 'all') {
            $store_ids = Store::where(['module_id' => $params['module_id']])->where(['zone_id' => $params['zone_id']])->pluck('id')->toArray();
        } else {
            $store_ids = Store::where(['module_id' => $params['module_id']])->pluck('id')->toArray();
        }
        $data = self::order_stats_calc($params['zone_id'], $params['module_id']);
        $module_type = Config::get('module.current_module_type');
        if ($module_type == 'parcel') {
            return response()->json([
                'view' => view('admin-views.partials._dashboard-order-stats-parcel', compact('data'))->render()
            ], 200);
        }elseif($module_type == 'food'){
            return response()->json([
                'view' => view('admin-views.partials._dashboard-order-stats-food', compact('data'))->render()
            ], 200);
        }
        return response()->json([
            'view' => view('admin-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    public function zone(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'zone_id') {
                $params['zone_id'] = $request['zone_id'];
            }
        }
        session()->put('dash_params', $params);

        $data = self::dashboard_data($request);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $popular = $data['popular'];
        $top_deliveryman = $data['top_deliveryman'];
        $top_rated_foods = $data['top_rated_foods'];
        $top_restaurants = $data['top_restaurants'];
        $top_customers = $data['top_customers'];
        $top_sell = $data['top_sell'];
        $delivery_commission = $data['delivery_commission'];
        $module_type = Config::get('module.current_module_type');

        return response()->json([
            'popular_restaurants' => view('admin-views.partials._popular-restaurants', compact('popular'))->render(),
            'top_deliveryman' => view('admin-views.partials._top-deliveryman', compact('top_deliveryman'))->render(),
            'top_rated_foods' => view('admin-views.partials._top-rated-foods', compact('top_rated_foods'))->render(),
            'top_restaurants' => view('admin-views.partials._top-restaurants', compact('top_restaurants'))->render(),
            'top_customers' => view('admin-views.partials._top-customer', compact('top_customers'))->render(),
            'top_selling_foods' => view('admin-views.partials._top-selling-foods', compact('top_sell'))->render(),

            'order_stats' =>$module_type == 'parcel'? view('admin-views.partials._dashboard-order-stats-parcel', compact('data'))->render():

            ($module_type == 'food'? view('admin-views.partials._dashboard-order-stats-food', compact('data'))->render():
            view('admin-views.partials._dashboard-order-stats', compact('data'))->render()),


            'user_overview' => view('admin-views.partials._user-overview-chart', compact('data'))->render(),
            'monthly_graph' => view('admin-views.partials._monthly-earning-graph', compact('total_sell', 'commission', 'delivery_commission'))->render(),
            'stat_zone' => view('admin-views.partials._zone-change', compact('data'))->render(),
        ], 200);
    }

    public function user_overview(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'user_overview') {
                $params['user_overview'] = $request['user_overview'];
            }
        }
        session()->put('dash_params', $params);

        $data = self::user_overview_calc($params['zone_id'], $params['module_id']);
        $module_type = Config::get('module.current_module_type');
        if ($module_type == 'parcel') {
            return response()->json([
                'view' => view('admin-views.partials._user-overview-chart-parcel', compact('data'))->render()
            ], 200);
        }

        return response()->json([
            'view' => view('admin-views.partials._user-overview-chart', compact('data'))->render()
        ], 200);
    }
    public function commission_overview(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'commission_overview') {
                $params['commission_overview'] = $request['commission_overview'];
            }
        }
        session()->put('dash_params', $params);

        $data = self::dashboard_data($request);

        return response()->json([
            'view' => view('admin-views.partials._commission-overview-chart', compact('data'))->render(),
            'gross_sale' => view('admin-views.partials._gross_sale', compact('data'))->render()
        ], 200);
    }

    public function order_stats_calc($zone_id, $module_id)
    {
        $params = session('dash_params');
        $module_type = Config::get('module.current_module_type');

        if ($module_id && $params['statistics_type'] == 'today') {
            $searching_for_dm = Order::SearchingForDeliveryman()->where('module_id', $module_id)->whereDate('created_at', Carbon::now());
            $accepted_by_dm = Order::AccepteByDeliveryman()->where('module_id', $module_id)->whereDate('accepted', Carbon::now());
            $preparing_in_rs = Order::Preparing()->where('module_id', $module_id)->whereDate('processing', Carbon::now());
            $picked_up = Order::ItemOnTheWay()->where('module_id', $module_id)->whereDate('picked_up', Carbon::now());
            $delivered = Order::Delivered()->where('module_id', $module_id)->whereDate('delivered', Carbon::now());
            $canceled = Order::where('module_id', $module_id)->where(['order_status' => 'canceled'])->whereDate('canceled', Carbon::now());
            $refund_requested = Order::where('module_id', $module_id)->where(['order_status' => 'refund_requested'])->whereDate('refund_requested', Carbon::now());
            $refunded = Order::where('module_id', $module_id)->where(['order_status' => 'refunded'])->whereDate('refunded', Carbon::now());
            $new_orders = Order::where('module_id', $module_id)->whereDate('schedule_at', Carbon::now());
            $new_items = Item::where('module_id', $module_id)->whereDate('created_at', Carbon::now());
            $new_stores = Store::where('module_id', $module_id)->whereDate('created_at', Carbon::now());
            $new_customers = User::whereDate('created_at', Carbon::now());
            if($module_type =='parcel'){
                $total_orders = Order::where('module_id', $module_id)->whereDate('created_at', Carbon::now());
            } else{
                $total_orders = Order::where('module_id', $module_id);
            }
            $total_items = Item::where('module_id', $module_id);
            $total_stores = Store::where('module_id', $module_id);
            $total_customers = User::all();
        } elseif($module_id && $params['statistics_type'] == 'this_year'){
            $searching_for_dm = Order::SearchingForDeliveryman()->where('module_id', $module_id)->whereYear('created_at', now()->format('Y'));
            $accepted_by_dm = Order::AccepteByDeliveryman()->where('module_id', $module_id)->whereYear('accepted', now()->format('Y'));
            $preparing_in_rs = Order::Preparing()->where('module_id', $module_id)->whereYear('processing', now()->format('Y'));
            $picked_up = Order::ItemOnTheWay()->where('module_id', $module_id)->whereYear('picked_up', now()->format('Y'));
            $delivered = Order::Delivered()->where('module_id', $module_id)->whereYear('delivered', now()->format('Y'));
            $canceled = Order::where('module_id', $module_id)->where(['order_status' => 'canceled'])->whereYear('canceled', now()->format('Y'));
            $refund_requested = Order::where('module_id', $module_id)->where(['order_status' => 'refund_requested'])->whereYear('refund_requested', now()->format('Y'));
            $refunded = Order::where('module_id', $module_id)->where(['order_status' => 'refunded'])->whereYear('refunded', now()->format('Y'));
            $new_orders = Order::where('module_id', $module_id)->whereYear('schedule_at', now()->format('Y'));
            $new_items = Item::where('module_id', $module_id)->whereYear('created_at', now()->format('Y'));
            $new_stores = Store::where('module_id', $module_id)->whereYear('created_at', now()->format('Y'));
            $new_customers = User::whereYear('created_at', now()->format('Y'));
            $total_orders = Order::where('module_id', $module_id);
            $total_items = Item::where('module_id', $module_id);
            $total_stores = Store::where('module_id', $module_id);
            $total_customers = User::all();
        } elseif($module_id && $params['statistics_type'] == 'this_month'){
            $searching_for_dm = Order::SearchingForDeliveryman()->where('module_id', $module_id)->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            $accepted_by_dm = Order::AccepteByDeliveryman()->where('module_id', $module_id)->whereMonth('accepted', now()->format('m'))->whereYear('accepted', now()->format('Y'));
            $preparing_in_rs = Order::Preparing()->where('module_id', $module_id)->whereMonth('processing', now()->format('m'))->whereYear('processing', now()->format('Y'));
            $picked_up = Order::ItemOnTheWay()->where('module_id', $module_id)->whereMonth('picked_up', now()->format('m'))->whereYear('picked_up', now()->format('Y'));
            $delivered = Order::Delivered()->where('module_id', $module_id)->whereMonth('delivered', now()->format('m'))->whereYear('delivered', now()->format('Y'));
            $canceled = Order::where('module_id', $module_id)->where(['order_status' => 'canceled'])->whereMonth('canceled', now()->format('m'))->whereYear('canceled', now()->format('Y'));
            $refund_requested = Order::where('module_id', $module_id)->where(['order_status' => 'refund_requested'])->whereMonth('refund_requested', now()->format('m'))->whereYear('refund_requested', now()->format('Y'));
            $refunded = Order::where('module_id', $module_id)->where(['order_status' => 'refunded'])->whereMonth('refunded', now()->format('m'))->whereYear('refunded', now()->format('Y'));
            $new_orders = Order::where('module_id', $module_id)->whereMonth('schedule_at', now()->format('m'))->whereYear('schedule_at', now()->format('Y'));
            $new_items = Item::where('module_id', $module_id)->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            $new_stores = Store::where('module_id', $module_id)->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            $new_customers = User::whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            $total_orders = Order::where('module_id', $module_id);
            $total_items = Item::where('module_id', $module_id);
            $total_stores = Store::where('module_id', $module_id);
            $total_customers = User::all();
        } elseif($module_id && $params['statistics_type'] == 'this_week'){
            $searching_for_dm = Order::SearchingForDeliveryman()->where('module_id', $module_id)->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $accepted_by_dm = Order::AccepteByDeliveryman()->where('module_id', $module_id)->whereBetween('accepted', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $preparing_in_rs = Order::Preparing()->where('module_id', $module_id)->whereBetween('processing', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $picked_up = Order::ItemOnTheWay()->where('module_id', $module_id)->whereBetween('picked_up', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $delivered = Order::Delivered()->where('module_id', $module_id)->whereBetween('delivered', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $canceled = Order::where('module_id', $module_id)->where(['order_status' => 'canceled'])->whereBetween('canceled', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $refund_requested = Order::where('module_id', $module_id)->where(['order_status' => 'refund_requested'])->whereBetween('refund_requested', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $refunded = Order::where('module_id', $module_id)->where(['order_status' => 'refunded'])->whereBetween('refunded', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $new_orders = Order::where('module_id', $module_id)->whereBetween('schedule_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $new_items = Item::where('module_id', $module_id)->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $new_stores = Store::where('module_id', $module_id)->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $new_customers = User::whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            $total_orders = Order::where('module_id', $module_id);
            $total_items = Item::where('module_id', $module_id);
            $total_stores = Store::where('module_id', $module_id);
            $total_customers = User::all();
        } elseif($module_id) {
            $searching_for_dm = Order::SearchingForDeliveryman()->where('module_id', $module_id);
            $accepted_by_dm = Order::AccepteByDeliveryman()->where('module_id', $module_id);
            $preparing_in_rs = Order::Preparing()->where('module_id', $module_id);
            $picked_up = Order::ItemOnTheWay()->where('module_id', $module_id);
            $delivered = Order::Delivered()->where('module_id', $module_id);
            $canceled = Order::Canceled()->where('module_id', $module_id);
            $refund_requested = Order::failed()->where('module_id', $module_id);
            $refunded = Order::Refunded()->where('module_id', $module_id);
            $new_orders = Order::where('module_id', $module_id)->whereDate('schedule_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_items = Item::where('module_id', $module_id)->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_stores = Store::where('module_id', $module_id)->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_customers = User::whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $total_orders = Order::where('module_id', $module_id);
            $total_items = Item::where('module_id', $module_id);
            $total_stores = Store::where('module_id', $module_id);
            $total_customers = User::all();
        } else {
            $searching_for_dm = Order::SearchingForDeliveryman();
            $accepted_by_dm = Order::AccepteByDeliveryman();
            $preparing_in_rs = Order::Preparing();
            $picked_up = Order::ItemOnTheWay();
            $delivered = Order::Delivered();
            $canceled = Order::Canceled();
            $refund_requested = Order::failed();
            $refunded = Order::Refunded();
            $new_orders = Order::whereDate('schedule_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_items = Item::whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_stores = Store::whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $new_customers = User::whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            $total_orders = Order::all();
            $total_items = Item::all();
            $total_stores = Store::all();
            $total_customers = User::all();
        }

        if (is_numeric($zone_id) && $module_id &&  !in_array($module_type ,['parcel']) ) {
            $searching_for_dm = $searching_for_dm->StoreOrder()->OrderScheduledIn(30)->where('zone_id', $zone_id)->count();
            $accepted_by_dm = $accepted_by_dm->StoreOrder()->where('zone_id', $zone_id)->count();
            $preparing_in_rs = $preparing_in_rs->StoreOrder()->where('zone_id', $zone_id)->count();
            $picked_up = $picked_up->StoreOrder()->where('zone_id', $zone_id)->count();
            $delivered = $delivered->StoreOrder()->where('zone_id', $zone_id)->count();
            $canceled = $canceled->StoreOrder()->where('zone_id', $zone_id)->count();
            $refund_requested = $refund_requested->StoreOrder()->where('zone_id', $zone_id)->count();
            $refunded = $refunded->StoreOrder()->where('zone_id', $zone_id)->count();
            $total_orders = $total_orders->StoreOrder()->where('zone_id', $zone_id)->count();
            $total_items = $total_items->count();
            $total_stores = $total_stores->where('zone_id', $zone_id)->count();
            $total_customers = $total_customers->count();
            $new_orders = $new_orders->StoreOrder()->where('zone_id', $zone_id)->count();
            $new_items = $new_items->count();
            $new_stores = $new_stores->where('zone_id', $zone_id)->count();
            $new_customers = $new_customers->count();
        } elseif($module_id && $module_type!='parcel') {
            $searching_for_dm = $searching_for_dm->StoreOrder()->OrderScheduledIn(30)->count();
            $accepted_by_dm = $accepted_by_dm->StoreOrder()->count();
            $preparing_in_rs = $preparing_in_rs->StoreOrder()->count();
            $picked_up = $picked_up->StoreOrder()->count();
            $delivered = $delivered->StoreOrder()->count();
            $canceled = $canceled->StoreOrder()->count();
            $refund_requested = $refund_requested->StoreOrder()->count();
            $refunded = $refunded->StoreOrder()->count();
            $total_orders = $total_orders->StoreOrder()->count();
            $total_items = $total_items->count();
            $total_stores = $total_stores->count();
            $total_customers = $total_customers->count();
            $new_orders = $new_orders->StoreOrder()->count();
            $new_items = $new_items->count();
            $new_stores = $new_stores->count();
            $new_customers = $new_customers->count();
        } elseif(is_numeric($zone_id) && $module_id && $module_type =='parcel') {
            $searching_for_dm = $searching_for_dm->ParcelOrder()->OrderScheduledIn(30)->where('zone_id', $zone_id)->count();
            $accepted_by_dm = $accepted_by_dm->ParcelOrder()->where('zone_id', $zone_id)->count();
            $preparing_in_rs = $preparing_in_rs->ParcelOrder()->where('zone_id', $zone_id)->count();
            $picked_up = $picked_up->ParcelOrder()->where('zone_id', $zone_id)->count();
            $delivered = $delivered->ParcelOrder()->where('zone_id', $zone_id)->count();
            $canceled = $canceled->ParcelOrder()->where('zone_id', $zone_id)->count();
            $refund_requested = $refund_requested->ParcelOrder()->where('zone_id', $zone_id)->count();
            $refunded = $refunded->ParcelOrder()->where('zone_id', $zone_id)->count();
            $total_orders = $total_orders->ParcelOrder()->where('zone_id', $zone_id)->count();
            $total_items = $total_items->count();
            $total_stores = $total_stores->where('zone_id', $zone_id)->count();
            $total_customers = $total_customers->where('zone_id', $zone_id)->count();
            $new_orders = $new_orders->ParcelOrder()->where('zone_id', $zone_id)->count();
            $new_items = $new_items->count();
            $new_stores = $new_stores->where('zone_id', $zone_id)->count();
            $new_customers = $new_customers->where('zone_id', $zone_id)->count();
        }
        elseif($module_id && $module_type =='parcel') {
            $searching_for_dm = $searching_for_dm->ParcelOrder()->OrderScheduledIn(30)->count();
            $accepted_by_dm = $accepted_by_dm->ParcelOrder()->count();
            $preparing_in_rs = $preparing_in_rs->ParcelOrder()->count();
            $picked_up = $picked_up->ParcelOrder()->count();
            $delivered = $delivered->ParcelOrder()->count();
            $canceled = $canceled->ParcelOrder()->count();
            $refund_requested = $refund_requested->ParcelOrder()->count();
            $refunded = $refunded->ParcelOrder()->count();
            $total_orders = $total_orders->ParcelOrder()->count();
            $total_items = $total_items->count();
            $total_stores = $total_stores->count();
            $total_customers = $total_customers->count();
            $new_orders = $new_orders->ParcelOrder()->count();
            $new_items = $new_items->count();
            $new_stores = $new_stores->count();
            $new_customers = $new_customers->count();
        }

        else{
            $searching_for_dm = $searching_for_dm->StoreOrder()->OrderScheduledIn(30)->count();
            $accepted_by_dm = $accepted_by_dm->StoreOrder()->count();
            $preparing_in_rs = $preparing_in_rs->StoreOrder()->count();
            $picked_up = $picked_up->StoreOrder()->count();
            $delivered = $delivered->StoreOrder()->count();
            $canceled = $canceled->StoreOrder()->count();
            $refund_requested = $refund_requested->StoreOrder()->count();
            $refunded = $refunded->StoreOrder()->count();
            $total_orders = $total_orders->count();
            $total_items = $total_items->count();
            $total_stores = $total_stores->count();
            $total_customers = $total_customers->count();
            $new_orders = $new_orders->count();
            $new_items = $new_items->count();
            $new_stores = $new_stores->count();
            $new_customers = $new_customers->count();
        }
        // Calculate new order statuses for ecommerce dashboard
        // Reuse existing query patterns and apply same filters
        $order_received = Order::where('module_id', $module_id)->where('order_status', 'pending');
        $order_accepted_new = Order::where('module_id', $module_id)->where('order_status', 'accepted');
        $ready_to_ship = Order::where('module_id', $module_id)->where('order_status', 'confirmed');
        $task_assigned = Order::where('module_id', $module_id)->where('order_status', 'accepted')->whereNotNull('delivery_man_id');
        $courier_assigned_pending = Order::where('module_id', $module_id)->where('order_status', 'pending')->whereNotNull('delivery_man_id');
        $total_assigned = Order::where('module_id', $module_id)->whereNotNull('delivery_man_id');
        $out_for_pickup = Order::where('module_id', $module_id)->where('order_status', 'handover');
        $rescheduled = Order::where('module_id', $module_id)->whereRaw('created_at <> schedule_at')->where('scheduled', 1);
        
        // Apply same filters as existing queries
        if (is_numeric($zone_id) && $module_id && !in_array($module_type, ['parcel'])) {
            $order_received = $order_received->StoreOrder()->where('zone_id', $zone_id)->count();
            $order_accepted_new = $order_accepted_new->StoreOrder()->where('zone_id', $zone_id)->count();
            $ready_to_ship = $ready_to_ship->StoreOrder()->where('zone_id', $zone_id)->count();
            $task_assigned = $task_assigned->StoreOrder()->where('zone_id', $zone_id)->count();
            $courier_assigned_pending = $courier_assigned_pending->StoreOrder()->where('zone_id', $zone_id)->count();
            $total_assigned = $total_assigned->StoreOrder()->where('zone_id', $zone_id)->count();
            $out_for_pickup = $out_for_pickup->StoreOrder()->where('zone_id', $zone_id)->count();
            $order_rescheduled = $rescheduled->StoreOrder()->where('zone_id', $zone_id)->count();
        } elseif ($module_id && $module_type != 'parcel') {
            $order_received = $order_received->StoreOrder()->count();
            $order_accepted_new = $order_accepted_new->StoreOrder()->count();
            $ready_to_ship = $ready_to_ship->StoreOrder()->count();
            $task_assigned = $task_assigned->StoreOrder()->count();
            $courier_assigned_pending = $courier_assigned_pending->StoreOrder()->count();
            $total_assigned = $total_assigned->StoreOrder()->count();
            $out_for_pickup = $out_for_pickup->StoreOrder()->count();
            $order_rescheduled = $rescheduled->StoreOrder()->count();
        } else {
            $order_received = $order_received->count();
            $order_accepted_new = $order_accepted_new->count();
            $ready_to_ship = $ready_to_ship->count();
            $task_assigned = $task_assigned->count();
            $courier_assigned_pending = $courier_assigned_pending->count();
            $total_assigned = $total_assigned->count();
            $out_for_pickup = $out_for_pickup->count();
            $order_rescheduled = $rescheduled->count();
        }
        
        $other_logistics_assigned = 0; // Can be customized based on your logistics system
        $order_picked_up = $picked_up;
        $shipped = 0; // Shipped status - update if you have this status
        $out_for_delivery = $picked_up;
        $order_delivered = $delivered;
        $order_canceled = $canceled;
        $on_hold = 0; // On Hold status - update if you have this status
        $reattempt = 0; // Reattempt status - update if you have this status
        
        // Seller-related order statuses
        $seller_return_to_seller = Order::where('module_id', $module_id)->where('order_status', 'seller_return_to_seller');
        $seller_shipped_to_seller = Order::where('module_id', $module_id)->where('order_status', 'seller_shipped_to_seller');
        $seller_out_for_delivery = Order::where('module_id', $module_id)->where('order_status', 'seller_out_for_delivery');
        $seller_delivered = Order::where('module_id', $module_id)->where('order_status', 'seller_delivered');
        $seller_rescheduled = Order::where('module_id', $module_id)->whereRaw('created_at <> schedule_at')->where('scheduled', 1)->where('order_status', 'seller_rescheduled');
        $seller_canceled = Order::where('module_id', $module_id)->where('order_status', 'seller_canceled');
        $seller_on_hold = Order::where('module_id', $module_id)->where('order_status', 'seller_on_hold');
        $seller_reattempt = Order::where('module_id', $module_id)->where('order_status', 'seller_reattempt');
        $seller_dto = Order::where('module_id', $module_id)->where('order_status', 'seller_dto');
        
        // Apply same filters as existing queries for seller statuses
        if (is_numeric($zone_id) && $module_id && !in_array($module_type, ['parcel'])) {
            $seller_return_to_seller = $seller_return_to_seller->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_shipped_to_seller = $seller_shipped_to_seller->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_out_for_delivery = $seller_out_for_delivery->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_delivered = $seller_delivered->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_rescheduled = $seller_rescheduled->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_canceled = $seller_canceled->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_on_hold = $seller_on_hold->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_reattempt = $seller_reattempt->StoreOrder()->where('zone_id', $zone_id)->count();
            $seller_dto = $seller_dto->StoreOrder()->where('zone_id', $zone_id)->count();
        } elseif ($module_id && $module_type != 'parcel') {
            $seller_return_to_seller = $seller_return_to_seller->StoreOrder()->count();
            $seller_shipped_to_seller = $seller_shipped_to_seller->StoreOrder()->count();
            $seller_out_for_delivery = $seller_out_for_delivery->StoreOrder()->count();
            $seller_delivered = $seller_delivered->StoreOrder()->count();
            $seller_rescheduled = $seller_rescheduled->StoreOrder()->count();
            $seller_canceled = $seller_canceled->StoreOrder()->count();
            $seller_on_hold = $seller_on_hold->StoreOrder()->count();
            $seller_reattempt = $seller_reattempt->StoreOrder()->count();
            $seller_dto = $seller_dto->StoreOrder()->count();
        } else {
            $seller_return_to_seller = $seller_return_to_seller->count();
            $seller_shipped_to_seller = $seller_shipped_to_seller->count();
            $seller_out_for_delivery = $seller_out_for_delivery->count();
            $seller_delivered = $seller_delivered->count();
            $seller_rescheduled = $seller_rescheduled->count();
            $seller_canceled = $seller_canceled->count();
            $seller_on_hold = $seller_on_hold->count();
            $seller_reattempt = $seller_reattempt->count();
            $seller_dto = $seller_dto->count();
        }

        $data = [
            'searching_for_dm' => $searching_for_dm,
            'accepted_by_dm' => $accepted_by_dm,
            'preparing_in_rs' => $preparing_in_rs,
            'picked_up' => $picked_up,
            'delivered' => $delivered,
            'canceled' => $canceled,
            'refund_requested' => $refund_requested,
            'refunded' => $refunded,
            'total_orders' => $total_orders,
            'total_items' => $total_items,
            'total_stores' => $total_stores,
            'total_customers' => $total_customers,
            'new_orders' => $new_orders,
            'new_items' => $new_items,
            'new_stores' => $new_stores,
            'new_customers' => $new_customers,
            // Additional data for ecommerce dashboard
            'total_delivered' => $delivered,
            'total_canceled' => $canceled,
            'total_sellers' => $total_stores,
            'total_services' => $total_items,
            // New order statuses for ecommerce dashboard
            'order_received' => $order_received,
            'order_accepted' => $order_accepted_new,
            'ready_to_ship' => $ready_to_ship,
            'task_assigned' => $task_assigned,
            'courier_assigned_pending' => $courier_assigned_pending,
            'other_logistics_assigned' => $other_logistics_assigned,
            'total_assigned' => $total_assigned,
            'out_for_pickup' => $out_for_pickup,
            'order_picked_up' => $order_picked_up,
            'shipped' => $shipped,
            'out_for_delivery' => $out_for_delivery,
            'order_delivered' => $order_delivered,
            'order_rescheduled' => $order_rescheduled,
            'order_canceled' => $order_canceled,
            'on_hold' => $on_hold,
            'reattempt' => $reattempt,
            // Seller-related order statuses
            'seller_return_to_seller' => $seller_return_to_seller,
            'seller_shipped_to_seller' => $seller_shipped_to_seller,
            'seller_out_for_delivery' => $seller_out_for_delivery,
            'seller_delivered' => $seller_delivered,
            'seller_rescheduled' => $seller_rescheduled,
            'seller_canceled' => $seller_canceled,
            'seller_on_hold' => $seller_on_hold,
            'seller_reattempt' => $seller_reattempt,
            'seller_dto' => $seller_dto,
        ];

        return $data;
    }

    public function user_overview_calc($zone_id, $module_id)
    {
        $params = session('dash_params');
        //zone
        if (is_numeric($zone_id)) {
            $customer = User::where('zone_id', $zone_id);
            $stores = Store::where('module_id', $module_id)->where(['zone_id' => $zone_id]);
            $delivery_man = DeliveryMan::where('application_status', 'approved')->where('zone_id', $zone_id)->Zonewise();
        } else {
            $customer = User::whereNotNull('id');
            $stores = Store::where('module_id', $module_id)->whereNotNull('id');
            $delivery_man = DeliveryMan::where('application_status', 'approved')->Zonewise();
        }
        //user overview
        if ($params['user_overview'] == 'overall') {
            $customer = $customer->count();
            $stores = $stores->count();
            $delivery_man = $delivery_man->count();
        } elseif($params['user_overview'] == 'this_month') {
            $customer = $customer->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->count();
            $stores = $stores->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->count();
            $delivery_man = $delivery_man->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->count();
        } elseif($params['user_overview'] == 'this_year') {
            $customer = $customer
                ->whereYear('created_at', date('Y'))->count();
            $stores = $stores
                ->whereYear('created_at', date('Y'))->count();
            $delivery_man = $delivery_man
                ->whereYear('created_at', date('Y'))->count();
        } else {
            $customer = $customer->whereDate('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')])->count();
            $stores = $stores->whereDate('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')])->count();
            $delivery_man = $delivery_man->whereDate('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')])->count();
        }
        $data = [
            'customer' => $customer,
            'stores' => $stores,
            'delivery_man' => $delivery_man
        ];
        return $data;
    }


    public function dashboard_data($request)
    {
        $params = session('dash_params');
        if (!url()->current() == $request->is('admin/users')) {
        $data_os = self::order_stats_calc($params['zone_id'], $params['module_id']);
        $data_uo = self::user_overview_calc($params['zone_id'], $params['module_id']);
        }
        $popular = Wishlist::with(['store'])
            ->whereHas('store')
            ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('module_id', $params['module_id']);
                });
            })
            ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('zone_id', $params['zone_id']);
                });
            })
            ->select('store_id', DB::raw('COUNT(store_id) as count'))->groupBy('store_id')
            ->having("count" , '>', 0)
            ->orderBy('count', 'DESC')
            ->limit(6)->get();
        $top_sell = Item::withoutGlobalScope(ZoneScope::class)
            ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('module_id', $params['module_id']);
                });
            })
            ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('module_id', $params['module_id'])->where('zone_id', $params['zone_id']);
                });
            })
            ->having("order_count" , '>', 0)
            ->orderBy("order_count", 'desc')
            ->take(6)
            ->get();
        $top_rated_foods = Item::withoutGlobalScope(ZoneScope::class)
            ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('module_id', $params['module_id']);
                });
            })
            ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->whereHas('store', function ($query) use ($params) {
                    return $query->where('zone_id', $params['zone_id']);
                });
            })
            ->having("rating_count" , '>', 0)
            ->orderBy('rating_count', 'desc')
            ->take(6)
            ->get();

        $top_deliveryman = DeliveryMan::withCount('orders')->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->where('zone_id', $params['zone_id']);
            })
            ->Zonewise()
            ->having("orders_count" , '>', 0)
            ->orderBy("orders_count", 'desc')
            ->take(6)
            ->get();

        $top_customers = User::when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->where('zone_id', $params['zone_id']);
            })
            ->having("order_count" , '>', 0)
            ->orderBy("order_count", 'desc')
            ->take(6)
            ->get();

        $top_restaurants = Store::when(is_numeric($params['module_id']), function ($q) use ($params) {
                return $q->where('module_id', $params['module_id']);
            })
            ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                return $q->where('zone_id', $params['zone_id']);
            })
            ->having("order_count" , '>', 0)
            ->orderBy("order_count", 'desc')
            ->take(6)
            ->get();


        // custom filtering for bar chart
        $months = array(
            '"'.translate('Jan').'"',
            '"'.translate('Feb').'"',
            '"'.translate('Mar').'"',
            '"'.translate('Apr').'"',
            '"'.translate('May').'"',
            '"'.translate('Jun').'"',
            '"'.translate('Jul').'"',
            '"'.translate('Aug').'"',
            '"'.translate('Sep').'"',
            '"'.translate('Oct').'"',
            '"'.translate('Nov').'"',
            '"'.translate('Dec').'"'
        );
        $days = array(
            '"'.translate('Mon').'"',
            '"'.translate('Tue').'"',
            '"'.translate('Wed').'"',
            '"'.translate('Thu').'"',
            '"'.translate('Fri').'"',
            '"'.translate('Sat').'"',
            '"'.translate('Sun').'"',
        );
        $total_sell = [];
        $commission = [];
        $label = [];
        $query = OrderTransaction::NotRefunded()
        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
            return $q->where('module_id', $params['module_id']);
        })
        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
            return $q->where('zone_id', $params['zone_id']);
        });
        switch ($params['commission_overview']) {
            case "this_year":
                for ($i = 1; $i <= 12; $i++) {
                    $total_sell[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum('order_amount');

                    $commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum(DB::raw('admin_commission + admin_expense - delivery_fee_comission'));

                    $delivery_commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum('delivery_fee_comission');
                }
                $label = $months;
                break;

            case "this_week":
                $weekStartDate = now()->startOfWeek(); // Start from Monday

                for ($i = 0; $i < 7; $i++) { // Loop through each day of the week
                    $currentDate = $weekStartDate->copy()->addDays($i); // Get the date for the current day in the loop

                    $total_sell[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->sum('order_amount');

                    $commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->sum(DB::raw('admin_commission + admin_expense - delivery_fee_comission'));

                    $delivery_commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->sum('delivery_fee_comission');
                }

                $label = $days;
                break;

            case "this_month":
                $start = now()->startOfMonth();
                $total_days = now()->daysInMonth;
                $weeks = array(
                    '"Day 1-7"',
                    '"Day 8-14"',
                    '"Day 15-21"',
                    '"Day 22-' . $total_days . '"',
                );

                for ($i = 1; $i <= 4; $i++) {
                    $end = $start->copy()->addDays(6); // Set the end date for each week

                    // Adjust for the last week of the month
                    if ($i == 4) {
                        $end = now()->endOfMonth();
                    }

                    $total_sell[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->sum('order_amount');

                    $commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->sum(DB::raw('admin_commission + admin_expense - delivery_fee_comission'));

                    $delivery_commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->sum('delivery_fee_comission');

                    // Move to the next week
                    $start = $end->copy()->addDay();
                }

                $label = $weeks;
                break;

            default:
                for ($i = 1; $i <= 12; $i++) {
                    $total_sell[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum('order_amount');

                    $commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum(DB::raw('admin_commission + admin_expense - delivery_fee_comission'));

                    $delivery_commission[$i] = OrderTransaction::NotRefunded()
                        ->when(is_numeric($params['module_id']), function ($q) use ($params) {
                            return $q->where('module_id', $params['module_id']);
                        })
                        ->when(is_numeric($params['zone_id']), function ($q) use ($params) {
                            return $q->where('zone_id', $params['zone_id']);
                        })
                        ->whereMonth('created_at', $i)->whereYear('created_at', now()->format('Y'))
                        ->sum('delivery_fee_comission');
                }
                $label = $months;
        }

        if (!url()->current() == $request->is('admin/users')) {
            $dash_data = array_merge($data_os, $data_uo);
        }

        $dash_data['popular'] = $popular;
        $dash_data['top_sell'] = $top_sell;
        $dash_data['top_rated_foods'] = $top_rated_foods;
        $dash_data['top_deliveryman'] = $top_deliveryman;
        $dash_data['top_restaurants'] = $top_restaurants;
        $dash_data['top_customers'] = $top_customers;
        $dash_data['total_sell'] = $total_sell;
        $dash_data['commission'] = $commission;
        $dash_data['delivery_commission'] = $delivery_commission;
        $dash_data['label'] = $label;
        return $dash_data;
    }

    // ORDER DETAILS Helper methods (same as Logistics Dashboard)
    private function get30MinOrders($zone_id)
    {
        $baseQuery = Order::where('created_at', '>=', Carbon::now()->subMinutes(30));
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        $oa = (clone $baseQuery)->where('order_status', 'accepted')->count();
        $oacap = (clone $baseQuery)->where('order_status', 'accepted')->whereNull('delivery_man_id')->count();
        $taksh_assign = (clone $baseQuery)->where('order_status', 'accepted')->whereNotNull('delivery_man_id')->count();
        $other_logistic = 0;
        $total_assign = $taksh_assign + $other_logistic;
        
        return [
            'op' => (clone $baseQuery)->where('order_status', 'pending')->count(),
            'oa' => $oa,
            'cap' => $oacap,
            'taksh_assign' => $taksh_assign,
            'other_logistic' => $other_logistic,
            'total_assign' => $total_assign,
            'ofp' => (clone $baseQuery)->whereIn('order_status', ['confirmed', 'processing'])->count(),
            'op2' => (clone $baseQuery)->where('order_status', 'processing')->count(),
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
            'rtsd' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'rtsto' => (clone $baseQuery)->where('order_status', 'refunded')->count(),
        ];
    }

    private function getFMOrders($zone_id)
    {
        $baseQuery = Order::query();
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        $oa = (clone $baseQuery)->where('order_status', 'accepted')->count();
        $cap = (clone $baseQuery)->where('order_status', 'accepted')->whereNull('delivery_man_id')->count();
        $taksh_assign = (clone $baseQuery)->where('order_status', 'accepted')->whereNotNull('delivery_man_id')->count();
        $other_logistic = 0;
        $total_assign = $taksh_assign + $other_logistic;
        
        return [
            'op' => (clone $baseQuery)->where('order_status', 'pending')->count(),
            'oa' => $oa,
            'cap' => $cap,
            'taksh_assign' => $taksh_assign,
            'other_logistic' => $other_logistic,
            'total_assign' => $total_assign,
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
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        return [
            'ofd' => (clone $baseQuery)->where('order_status', 'picked_up')->count(),
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
        ];
    }

    private function getRVPOrders($zone_id)
    {
        $baseQuery = Order::whereIn('order_status', ['refund_requested', 'refunded']);
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        return [
            'or' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'od' => (clone $baseQuery)->where('order_status', 'refunded')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
        ];
    }

    private function get3PCOrders($zone_id)
    {
        $baseQuery = Order::where('order_type', 'parcel');
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        return [
            'or1' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'oa' => (clone $baseQuery)->where('order_status', 'accepted')->count(),
            'or2' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'ofp' => (clone $baseQuery)->whereIn('order_status', ['confirmed', 'processing'])->count(),
            'op' => (clone $baseQuery)->where('order_status', 'processing')->count(),
            'os' => (clone $baseQuery)->where('order_status', 'handover')->count(),
            'ofd' => (clone $baseQuery)->where('order_status', 'picked_up')->count(),
            'od' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
            'or3' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
            'orts' => (clone $baseQuery)->where('order_status', 'refunded')->count(),
            'rtss' => (clone $baseQuery)->where('order_status', 'refunded')->whereNotNull('refunded')->count(),
        ];
    }

    private function getRTSOrders($zone_id)
    {
        $baseQuery = Order::whereIn('order_status', ['refund_requested', 'refunded']);
        $baseQuery = $this->applyZoneFilterForOrders($baseQuery, $zone_id);
        
        return [
            'torts' => (clone $baseQuery)->where('order_status', 'refund_requested')->count() + (clone $baseQuery)->where('order_status', 'refunded')->count(),
            'rvpirts' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('refund_requested')->count(),
            'srts' => (clone $baseQuery)->where('order_status', 'refunded')->count(),
            'rvprts' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('refund_requested')->count(),
            'ofd' => (clone $baseQuery)->where('order_status', 'refund_requested')->whereNotNull('picked_up')->count(),
            'od' => (clone $baseQuery)->where('order_status', 'refunded')->whereNotNull('delivered')->count(),
            'or' => (clone $baseQuery)->where('order_status', 'refund_requested')->count(),
            'oc' => (clone $baseQuery)->where('order_status', 'canceled')->count(),
            'dto' => (clone $baseQuery)->where('order_status', 'refunded')->count(),
        ];
    }

    private function applyZoneFilterForOrders($query, $zone_id)
    {
        if (is_numeric($zone_id)) {
            if (method_exists($query->getModel(), 'zone')) {
                return $query->zone($zone_id);
            } else {
                return $query->where('zone_id', $zone_id);
            }
        }
        return $query;
    }

    // Helper methods for new ecommerce dashboard order statuses
    private function getOrderStatusCount($module_id, $zone_id, $module_type, $params, $status, $useStoreOrder = true, $allowMissing = false)
    {
        $query = Order::query();
        
        if ($module_id) {
            $query->where('module_id', $module_id);
        }
        
        // Apply date filter based on statistics_type
        if (isset($params['statistics_type'])) {
            if ($params['statistics_type'] == 'today') {
                $query->whereDate('created_at', Carbon::now());
            } elseif ($params['statistics_type'] == 'this_year') {
                $query->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_month') {
                $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            }
        }
        
        $query->where('order_status', $status);
        
        if ($useStoreOrder && $module_type != 'parcel') {
            $query->StoreOrder();
        } elseif ($module_type == 'parcel') {
            $query->ParcelOrder();
        }
        
        $query = $this->applyZoneFilterForOrders($query, $zone_id);
        
        return $query->count();
    }

    private function getAssignedOrdersCount($module_id, $zone_id, $module_type, $params, $status = 'accepted', $pendingOnly = false)
    {
        $query = Order::query();
        
        if ($module_id) {
            $query->where('module_id', $module_id);
        }
        
        // Apply date filter
        if (isset($params['statistics_type'])) {
            if ($params['statistics_type'] == 'today') {
                $query->whereDate('created_at', Carbon::now());
            } elseif ($params['statistics_type'] == 'this_year') {
                $query->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_month') {
                $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            }
        }
        
        $query->where('order_status', $status)->whereNotNull('delivery_man_id');
        
        if ($pendingOnly) {
            $query->where('order_status', 'pending');
        }
        
        if ($module_type != 'parcel') {
            $query->StoreOrder();
        } elseif ($module_type == 'parcel') {
            $query->ParcelOrder();
        }
        
        $query = $this->applyZoneFilterForOrders($query, $zone_id);
        
        return $query->count();
    }

    private function getOtherLogisticsAssignedCount($module_id, $zone_id, $module_type, $params)
    {
        // For now, return 0 as this might need custom logic based on your logistics system
        // You can update this based on your specific requirements
        return 0;
    }

    private function getTotalAssignedCount($module_id, $zone_id, $module_type, $params)
    {
        $query = Order::query();
        
        if ($module_id) {
            $query->where('module_id', $module_id);
        }
        
        // Apply date filter
        if (isset($params['statistics_type'])) {
            if ($params['statistics_type'] == 'today') {
                $query->whereDate('created_at', Carbon::now());
            } elseif ($params['statistics_type'] == 'this_year') {
                $query->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_month') {
                $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            }
        }
        
        $query->whereNotNull('delivery_man_id');
        
        if ($module_type != 'parcel') {
            $query->StoreOrder();
        } elseif ($module_type == 'parcel') {
            $query->ParcelOrder();
        }
        
        $query = $this->applyZoneFilterForOrders($query, $zone_id);
        
        return $query->count();
    }

    private function getRescheduledOrdersCount($module_id, $zone_id, $module_type, $params)
    {
        $query = Order::query();
        
        if ($module_id) {
            $query->where('module_id', $module_id);
        }
        
        // Apply date filter
        if (isset($params['statistics_type'])) {
            if ($params['statistics_type'] == 'today') {
                $query->whereDate('created_at', Carbon::now());
            } elseif ($params['statistics_type'] == 'this_year') {
                $query->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_month') {
                $query->whereMonth('created_at', now()->format('m'))->whereYear('created_at', now()->format('Y'));
            } elseif ($params['statistics_type'] == 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
            }
        }
        
        // Rescheduled orders are those where schedule_at is different from created_at and scheduled = 1
        $query->whereRaw('created_at <> schedule_at')->where('scheduled', 1);
        
        if ($module_type != 'parcel') {
            $query->StoreOrder();
        } elseif ($module_type == 'parcel') {
            $query->ParcelOrder();
        }
        
        $query = $this->applyZoneFilterForOrders($query, $zone_id);
        
        return $query->count();
    }
}
