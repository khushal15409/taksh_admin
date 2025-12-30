@extends('layouts.admin.app')

@section('title',\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()->value??translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@include('admin-views.partials._loader')
    <div class="content container-fluid">
        @if(auth('admin')->user()->role_id == 1)
        @php($mod = \App\Models\Module::find(Config::get('module.current_module_id')))
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/eshop.svg')}}" src="{{ $mod->icon_full_url }}"
                        width="38" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">{{translate($mod->module_name)}} {{translate('messages.Dashboard')}}.</h1>
                            <p class="page-header-text m-0">{{translate('Hello, Here You Can Manage Your')}} {{translate($mod->module_name)}} {{translate('orders by Zone.')}}</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-auto min--280">
                    <select name="zone_id" class="form-control js-select2-custom fetch_data_zone_wise">
                        <option value="all">{{ translate('messages.All_Zones') }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $zone)
                            <option
                                value="{{$zone['id']}}" {{$params['zone_id'] == $zone['id']?'selected':''}}>
                                {{$zone['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats -->
        <div class="card mb-3">
            <div class="card-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-end">
                    <div class="status-filter-wrap">
                        <div class="statistics-btn-grp">
                            <label>
                                <input type="radio" name="statistics" class="order_stats_update" value="this_year" {{$params['statistics_type'] == 'this_year'?'checked':''}} hidden>
                                <span>{{ translate('This_Year') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" class="order_stats_update" value="this_month" {{$params['statistics_type'] == 'this_month'?'checked':''}} hidden>
                                <span>{{ translate('This_Month') }}</span>
                            </label>
                            <label>
                                <input type="radio" name="statistics" class="order_stats_update" value="this_week" {{$params['statistics_type'] == 'this_week'?'checked':''}} hidden>
                                <span>{{ translate('This_Week') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/orders.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Order</h6>
                            <h3 class="count">{{ $data['total_orders'] ?? 0 }}</h3>
                            <div class="subtxt">{{ $data['new_orders'] ?? 0 }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Success</h6>
                            <h3 class="count">{{ $data['total_delivered'] ?? $data['delivered'] ?? 0 }}</h3>
                            <div class="subtxt">Delivered orders</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Cancel</h6>
                            <h3 class="count">{{ $data['total_canceled'] ?? $data['canceled'] ?? 0 }}</h3>
                            <div class="subtxt">Canceled orders</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/customers.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Customer</h6>
                            <h3 class="count">{{ $data['total_customers'] ?? 0 }}</h3>
                            <div class="subtxt">{{ $data['new_customers'] ?? 0 }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/stores.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Seller</h6>
                            <h3 class="count">{{ $data['total_sellers'] ?? $data['total_stores'] ?? 0 }}</h3>
                            <div class="subtxt">{{ $data['new_stores'] ?? 0 }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/products.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Services</h6>
                            <h3 class="count">{{ $data['total_services'] ?? $data['total_items'] ?? 0 }}</h3>
                            <div class="subtxt">{{ $data['new_items'] ?? 0 }} {{ translate('newly added') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <!-- 1. Order Received -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['order_received'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Received</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['order_received'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 2. Order Accepted -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['accepted'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Accepted</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['order_accepted'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 3. Order Rejected -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rejected'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Rejected</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['order_rejected'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 4. Ready to Ship -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['confirmed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Ready to Ship</span>
                                        </h6>
                                        <span class="card-title text-FFA800">
                                            {{$data['ready_to_ship'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 5. Taksh Assigned -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['taksh_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Taksh Assigned</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['taksh_assigned'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 6. Un-Assigned Pending -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['unassigned_pending'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Un-Assigned Pending</span>
                                        </h6>
                                        <span class="card-title text-warning">
                                            {{$data['unassigned_pending'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 7. Other Logistics Assigned -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['other_logistics_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Other Logistics Assigned</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['other_logistics_assigned'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 8. Total Assigned -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['total_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Total Assigned</span>
                                        </h6>
                                        <span class="card-title text-primary">
                                            {{$data['total_assigned'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 9. Out for Pickup -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['handover'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Out for Pickup</span>
                                        </h6>
                                        <span class="card-title text-FFA800">
                                            {{$data['out_for_pickup'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 10. Order Picked-Up -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Picked-Up</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['order_picked_up'] ?? $data['picked_up'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 11. Order Connected to Hub -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['connected_to_hub'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Connected to Hub</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['connected_to_hub'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 12. Order Received at Center -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['received_at_center'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Received at Center</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['received_at_center'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 13. Out for Delivery -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Out for Delivery</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['out_for_delivery'] ?? $data['picked_up'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 14. Order Delivered -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Delivered</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['order_delivered'] ?? $data['delivered'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 15. Order Rescheduled -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rescheduled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Rescheduled</span>
                                        </h6>
                                        <span class="card-title text-warning">
                                            {{$data['order_rescheduled'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 16. Order Cancelled -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Cancelled</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['order_canceled'] ?? $data['canceled'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 17. On Hold -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['on_hold'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>On Hold</span>
                                        </h6>
                                        <span class="card-title text-warning">
                                            {{$data['on_hold'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 18. Order Reattempt -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['reattempt'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Order Reattempt</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['reattempt'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 19. Return to Origin -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_to_origin'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Return to Origin</span>
                                        </h6>
                                        <span class="card-title text-warning">
                                            {{$data['return_to_origin'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 20. Return to Origin Connected to Hub -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_connected_to_hub'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Return to Origin Connected to Hub</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['return_connected_to_hub'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 21. Received at Hub -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['received_at_hub'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Received at Hub</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['received_at_hub'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <!-- 22. Hub Connected to Destination -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['hub_connected_to_destination'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>Hub Connected to Destination</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{$data['hub_connected_to_destination'] ?? 0}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Stats -->

        <!-- ORDER DETAILS -->
        @include('admin-views.partials._order-details')

        <div class="row g-2">
            <div class="col-lg-8 col--xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center __gap-12px">
                            <div class="__gross-amount" id="gross_sale">
                                <h6>{{\App\CentralLogics\Helpers::format_currency(array_sum($total_sell))}}</h6>
                                <span>{{ translate('messages.Gross Sale') }}</span>
                            </div>
                            <div class="chart--label __chart-label p-0 move-left-100 ml-auto">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{ translate('sale') }} ({{ date("Y") }})
                                </span>
                            </div>
                            <select class="custom-select border-0 text-center w-auto ml-auto commission_overview_stats_update" name="commission_overview">
                                    <option
                                    value="this_year" {{$params['commission_overview'] == 'this_year'?'selected':''}}>
                                    {{translate('This year')}}
                                </option>
                                <option
                                    value="this_month" {{$params['commission_overview'] == 'this_month'?'selected':''}}>
                                    {{translate('This month')}}
                                </option>
                                <option
                                    value="this_week" {{$params['commission_overview'] == 'this_week'?'selected':''}}>
                                    {{translate('This week')}}
                                </option>
                            </select>
                        </div>
                        <div id="commission-overview-board">

                            <div id="grow-sale-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col--xl-4">
                <!-- Card -->
                <div class="card h-100">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <h5 class="card-header-title">
                            {{translate('User Statistics')}}
                        </h5>
                        <div id="stat_zone">

                            @include('admin-views.partials._zone-change',['data'=>$data])


                        </div>
                        <select class="custom-select border-0 text-center w-auto user_overview_stats_update" name="user_overview">
                                <option
                                value="this_year" {{$params['user_overview'] == 'this_year'?'selected':''}}>
                                {{translate('This year')}}
                            </option>
                            <option
                                value="this_month" {{$params['user_overview'] == 'this_month'?'selected':''}}>
                                {{translate('This month')}}
                            </option>
                            <option
                                value="this_week" {{$params['user_overview'] == 'this_week'?'selected':''}}>
                                {{translate('This week')}}
                            </option>
                            <option
                                value="overall" {{$params['user_overview'] == 'overall'?'selected':''}}>
                                {{translate('messages.Overall')}}
                            </option>
                        </select>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body" id="user-overview-board">
                        <div class="position-relative pie-chart">
                            <div id="dognut-pie"></div>
                            <!-- Total Orders -->
                            <div class="total--orders">
                                <h3 class="text-uppercase mb-xxl-2">{{ $data['customer'] + $data['stores'] + $data['delivery_man'] }}</h3>
                                <span class="text-capitalize">{{translate('messages.total_users')}}</span>
                            </div>
                            <!-- Total Orders -->
                        </div>
                        <div class="d-flex flex-wrap justify-content-center mt-4">
                            <div class="chart--label">
                                <span class="indicator chart-bg-1"></span>
                                <span class="info">
                                    {{translate('messages.customer')}} {{$data['customer']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{translate('messages.store')}} {{$data['stores']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-3"></span>
                                <span class="info">
                                    {{translate('messages.delivery_man')}} {{$data['delivery_man']}}
                                </span>
                            </div>
                        </div>

                    </div>
                    <!-- End Body -->
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-restaurants-view">
                    @include('admin-views.partials._top-restaurants',['top_restaurants'=>$data['top_restaurants']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="popular-restaurants-view">
                    @include('admin-views.partials._popular-restaurants',['popular'=>$data['popular']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-selling-foods-view">
                    @include('admin-views.partials._top-selling-foods',['top_sell'=>$data['top_sell']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-rated-foods-view">
                    @include('admin-views.partials._top-rated-foods',['top_rated_foods'=>$data['top_rated_foods']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-deliveryman-view">
                    @include('admin-views.partials._top-deliveryman',['top_deliveryman'=>$data['top_deliveryman']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-customer-view">
                    @include('admin-views.partials._top-customer',['top_customers'=>$data['top_customers']])
                </div>
                <!-- End Card -->
            </div>

        </div>
        @else
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('messages.welcome')}}, {{auth('admin')->user()->f_name}}.</h1>
                    <p class="page-header-text">{{translate('messages.employee_welcome_message')}}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        @endif
    </div>
@endsection

@push('script')
    <script src="{{asset('assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- Apex Charts -->
    <script src="{{asset('assets/admin/js/apex-charts/apexcharts.js')}}"></script>
    <!-- Apex Charts -->

@endpush


@push('script_2')
    <script>
        // Show loader when page starts loading
        if (typeof PageLoader !== 'undefined') {
            PageLoader.show();
        }
        
        // Hide loader when page is fully loaded and charts are rendered
        $(document).ready(function() {
            // Wait for charts and all content to load
            setTimeout(function() {
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
            }, 1000);
        });
        
        // Fallback: Hide loader when window is fully loaded
        $(window).on('load', function() {
            setTimeout(function() {
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
            }, 500);
        });
    </script>

    <!-- Dognut Pie Chart -->
    <script>
        "use strict";
        let options;
        let chart;
        options = {
            series: [{{ $data['customer']}}, {{$data['stores']}}, {{$data['delivery_man']}}],
            chart: {
                width: 320,
                type: 'donut',
            },
            labels: ['{{ translate('Customer') }}', '{{ translate('Store') }}', '{{ translate('Delivery man') }}'],
            dataLabels: {
                enabled: false,
                style: {
                    colors: ['#005555', '#00aa96', '#b9e0e0',]
                }
            },
            responsive: [{
                breakpoint: 1650,
                options: {
                    chart: {
                        width: 250
                    },
                }
            }],
            colors: ['#005555','#00aa96', '#111'],
            fill: {
                colors: ['#005555','#00aa96', '#b9e0e0']
            },
            legend: {
                show: false
            },
        };

        chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
        chart.render();


        options = {
            series: [{
                name: '{{ translate('Gross Sale') }}',
                data: [{{ implode(",",$total_sell) }}]
            },{
                name: '{{ translate('Admin Comission') }}',
                data: [{{ implode(",",$commission) }}]
            },{
                name: '{{ translate('Delivery Comission') }}',
                data: [{{ implode(",",$delivery_commission) }}]
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show:false
                },
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            colors: ['#76ffcd','#ff6d6d', '#005555'],
            dataLabels: {
                enabled: false,
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            fill: {
                type: 'gradient',
                colors: ['#76ffcd','#ff6d6d', '#005555'],
            },
            xaxis: {
                //   type: 'datetime',
                categories: [{!! implode(",",$label) !!}]
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };

        chart = new ApexCharts(document.querySelector("#grow-sale-chart"), options);
        chart.render();

    <!-- Dognut Pie Chart -->
        // INITIALIZATION OF CHARTJS
        // =======================================================
        Chart.plugins.unregister(ChartDataLabels);

        $('.js-chart').each(function () {
            $.HSCore.components.HSChartJS.init($(this));
        });

        let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

        $('.order_stats_update').on('change', function (){
            let type = $(this).val();
            order_stats_update(type);
        })
        function order_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.order')}}',
                data: {
                    statistics_type: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('statistics_type',type);
                    $('#order_stats').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        $('.fetch_data_zone_wise').on('change', function (){
            let zone_id = $(this).val();
            fetch_data_zone_wise(zone_id);
        })

        function fetch_data_zone_wise(zone_id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.zone')}}',
                data: {
                    zone_id: zone_id
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('zone_id', zone_id);
                    $('#order_stats').html(data.order_stats);
                    $('#user-overview-board').html(data.user_overview);
                    $('#monthly-earning-graph').html(data.monthly_graph);
                    $('#popular-restaurants-view').html(data.popular_restaurants);
                    $('#top-deliveryman-view').html(data.top_deliveryman);
                    $('#top-rated-foods-view').html(data.top_rated_foods);
                    $('#top-restaurants-view').html(data.top_restaurants);
                    $('#top-selling-foods-view').html(data.top_selling_foods);
                    $('#stat_zone').html(data.stat_zone);
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        $('.user_overview_stats_update').on('change', function (){
            let type = $(this).val();
            user_overview_stats_update(type);
        })

        function user_overview_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.user-overview')}}',
                data: {
                    user_overview: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('user_overview',type);
                    $('#user-overview-board').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        $('.commission_overview_stats_update').on('change', function (){
            let type = $(this).val();
            commission_overview_stats_update(type);
        })

        function commission_overview_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.commission-overview')}}',
                data: {
                    commission_overview: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('commission_overview',type);
                    $('#commission-overview-board').html(data.view)
                    $('#gross_sale').html(data.gross_sale)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        function insert_param(key, value) {
            key = encodeURIComponent(key);
            value = encodeURIComponent(value);
            // kvp looks like ['key1=value1', 'key2=value2', ...]
            let kvp = document.location.search.substr(1).split('&');
            let i = 0;

            for (; i < kvp.length; i++) {
                if (kvp[i].startsWith(key + '=')) {
                    let pair = kvp[i].split('=');
                    pair[1] = value;
                    kvp[i] = pair.join('=');
                    break;
                }
            }
            if (i >= kvp.length) {
                kvp[kvp.length] = [key, value].join('=');
            }
            // can return this or...
            let params = kvp.join('&');
            // change url page with new params
            window.history.pushState('page2', 'Title', '{{url()->current()}}?' + params);
        }
    </script>
@endpush
