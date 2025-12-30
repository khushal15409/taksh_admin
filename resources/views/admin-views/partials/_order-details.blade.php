        <!-- ORDER DETAILS -->
        <style>
            .order-details-card {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(0, 0, 0, 0.08);
                border-radius: 8px;
                transition: all 0.3s ease;
                background: #fff;
            }
            .order-details-card:hover {
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.08);
                transform: translateY(-2px);
            }
            .order-details-card .card-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                border-bottom: 2px solid #e9ecef;
                padding: 1rem 1.5rem;
                border-radius: 8px 8px 0 0;
            }
            .order-details-card .card-header .card-title {
                font-weight: 600;
                color: #2c3e50;
                font-size: 1.1rem;
                letter-spacing: 0.3px;
            }
            .order-details-card .card-body {
                padding: 1.5rem;
                background: #ffffff;
            }
        </style>
        <div class="row g-3">
            <!-- 30 MINIT ORDER DETAIL Card -->
            <div class="col-12">
                <div class="card h-100 order-details-card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">30 MINIT ORDER DETAIL</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['order_received'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER RECEIVED</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">{{ $data['order_30min']['order_received'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['accepted'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>SELLER ORDER ACCEPTED</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_30min']['seller_order_accepted'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rejected'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>VENDOR ORDER REJECTED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_30min']['vendor_order_rejected'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['confirmed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>READY TO SHIP</span>
                                        </h6>
                                        <span class="card-title text-FFA800">{{ $data['order_30min']['ready_to_ship'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['taksh_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TAKSH ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_30min']['taksh_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['unassigned_pending'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>UNASSIGN PENDING ORDER</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_30min']['unassigned_pending'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['other_logistics_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OTHER LOGISTICS ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_30min']['other_logistics_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['total_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-primary">{{ $data['order_30min']['total_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['handover'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OUT FOR PICKUP</span>
                                        </h6>
                                        <span class="card-title text-FFA800">{{ $data['order_30min']['out_for_pickup'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER PICKED-UP</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_30min']['order_picked_up'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER DELIVERED</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_30min']['order_delivered'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER CANCELLED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_30min']['order_cancelled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_to_seller_delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>RETURN TO SELLER DELIVERED</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_30min']['return_to_seller_delivered'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_to_seller_rejected'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>RETURN TO SELLER REJECTED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_30min']['return_to_seller_rejected'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['loss'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>LOSS</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_30min']['loss'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FM ORDER DETAILS Card -->
            <div class="col-12">
                <div class="card h-100 order-details-card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">FM ORDER DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['order_received'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER RECEIVED</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">{{ $data['order_fm']['order_received'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['accepted'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>SELLER ORDER ACCEPTED</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_fm']['seller_order_accepted'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rejected'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>SELLER ORDER REJECTED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_fm']['seller_order_rejected'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['confirmed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>READY TO SHIP</span>
                                        </h6>
                                        <span class="card-title text-FFA800">{{ $data['order_fm']['ready_to_ship'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['taksh_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TAKSH ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_fm']['taksh_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['unassigned_pending'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>UNASSIGN PENDING ORDER</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_fm']['unassigned_pending'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['other_logistics_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OTHER LOGISTICS ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_fm']['other_logistics_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['total_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-primary">{{ $data['order_fm']['total_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['handover'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OUT FOR PICKUP</span>
                                        </h6>
                                        <span class="card-title text-FFA800">{{ $data['order_fm']['out_for_pickup'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER PICKED-UP</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_fm']['order_picked_up'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['connected_to_hub'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER CONNECTED TO HUB</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_fm']['connected_to_hub'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rescheduled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER RESCHEDULED</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_fm']['order_rescheduled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LM ORDER DETAILS Card -->
            <div class="col-12">
                <div class="card h-100 order-details-card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">LM ORDER DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['total_order_in_at_lm'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL ORDER IN AT LM</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">{{ $data['order_lm']['total_order_in_at_lm'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OUT FOR DELIVERY</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_lm']['out_for_delivery'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER DELIVERED</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_lm']['order_delivered'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rescheduled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER RESCHEDULED</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_lm']['order_rescheduled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER CANCELLED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_lm']['order_cancelled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['on_hold'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ON HOLD</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_lm']['on_hold'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['reattempt'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER REATTEMPT</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_lm']['order_reattempt'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_to_seller'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>RETURN TO SELLER</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_lm']['return_to_seller'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['return_to_seller_connected_to_hub'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>RETURN TO SELLER ORDER CONNECTED TO HUB</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_lm']['return_to_seller_connected_to_hub'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REVERSE PICKUP DETAILS Card -->
            <div class="col-12">
                <div class="card h-100 order-details-card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">REVERSE PICKUP DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['customer_return_request_placed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>CUSTOMER RETURN REQUEST PLACED</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">{{ $data['order_reverse_pickup']['customer_return_request_placed'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['request_accepted'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>REQUEST ACCEPTED</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_reverse_pickup']['request_accepted'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['request_rejected'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>REQUEST REJECTED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_reverse_pickup']['request_rejected'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['taksh_assigned'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TAKSH ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_reverse_pickup']['taksh_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['unassigned_pending'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>UNASSIGN PENDING</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_reverse_pickup']['unassigned_pending'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['other_logistics_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OTHER LOGISTICS ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_reverse_pickup']['other_logistics_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['total_assign'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL ASSIGN</span>
                                        </h6>
                                        <span class="card-title text-primary">{{ $data['order_reverse_pickup']['total_assign'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['handover'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OUT FOR PICKUP</span>
                                        </h6>
                                        <span class="card-title text-FFA800">{{ $data['order_reverse_pickup']['out_for_pickup'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>REVERSE PICKUP ORDER PICKED-UP</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_reverse_pickup']['reverse_pickup_order_picked_up'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>REVERSE PICKUP ORDER CANCELLED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_reverse_pickup']['reverse_pickup_order_cancelled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rescheduled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>RESCHE.REVERSE PICKUP CONNECTED TO HUB</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_reverse_pickup']['resche_reverse_pickup_connected_to_hub'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RT ORDER DETAILS Card -->
            <div class="col-12">
                <div class="card h-100 order-details-card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">RT ORDER DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['order_received'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL ORDER RECEIVED</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">{{ $data['order_rt']['total_order_received'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['shipped'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL SHIPMENT</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_rt']['total_shipment'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rvp'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>TOTAL RVP</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_rt']['total_rvp'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['item_on_the_way'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>OUT FOR DELIVERY TO SELLER</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_rt']['out_for_delivery_to_seller'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>DELIVERD TO SELLER</span>
                                        </h6>
                                        <span class="card-title text-success">{{ $data['order_rt']['delivered_to_seller'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['rescheduled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>SELLER RESCHEDULED</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_rt']['seller_rescheduled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>SELLER CANNELED</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_rt']['seller_cancelled'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['on_hold'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ON HOLD</span>
                                        </h6>
                                        <span class="card-title text-warning">{{ $data['order_rt']['on_hold'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['reattempt'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>ORDER REATTEMPT</span>
                                        </h6>
                                        <span class="card-title text-info">{{ $data['order_rt']['order_reattempt'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['loss'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>LOSS</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_rt']['loss'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.dashboard-list',['dto'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>DTO</span>
                                        </h6>
                                        <span class="card-title text-danger">{{ $data['order_rt']['dto'] ?? 0 }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
