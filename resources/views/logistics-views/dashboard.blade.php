@extends('layouts.admin.app')

@section('title', 'Logistics Dashboard')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('assets/admin/img/grocery.svg')}}" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">Welcome, Logistics Team.</h1>
                            <p class="page-header-text m-0">Logistics Dashboard Overview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="card mb-3">
            <div class="card-body pt-0">
                <div class="row g-2">
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/orders.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Orders</h6>
                            <h3 class="count">{{ number_format($data['total_fr']) }}</h3>
                            <div class="subtxt">{{ $data['order_30min']['op'] ?? 0 }} pending</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/customers.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Customers</h6>
                            <h3 class="count">{{ number_format($data['total_customers']) }}</h3>
                            <div class="subtxt">{{ number_format($data['new_customers']) }} newly added</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/stores.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Total Sellers</h6>
                            <h3 class="count">{{ number_format($data['total_sellers']) }}</h3>
                            <div class="subtxt">{{ number_format($data['new_sellers']) }} newly added</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/stats/products.svg')}}" alt="dashboard/stats">
                            <h6 class="name">Warehouses</h6>
                            <h3 class="count">{{ number_format($data['total_wh']) }}</h3>
                            <div class="subtxt">{{ number_format($data['total_mwh']) }} mobile</div>
                        </div>
                    </div>
                </div>

                <!-- Order Status Cards -->
                <div class="row g-2 mt-2">
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>Pending Orders</span>
                                </h6>
                                <span class="card-title text-3F8CE8">
                                    {{ $data['order_30min']['op'] ?? 0 }}
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>Accepted Orders</span>
                                </h6>
                                <span class="card-title text-success">
                                    {{ $data['order_30min']['oa'] ?? 0 }}
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>Processing</span>
                                </h6>
                                <span class="card-title text-FFA800">
                                    {{ $data['order_30min']['ofp'] ?? 0 }}
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>Delivered</span>
                                </h6>
                                <span class="card-title text-success">
                                    {{ $data['order_30min']['od'] ?? 0 }}
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>Canceled</span>
                                </h6>
                                <span class="card-title text-danger">
                                    {{ $data['order_30min']['oc'] ?? 0 }}
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                    <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                    <span>RTS Orders</span>
                                </h6>
                                <span class="card-title text-warning">
                                    {{ ($data['order_30min']['rtsd'] ?? 0) + ($data['order_30min']['rtsto'] ?? 0) }}
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats -->

        <!-- ORDER DETAILS -->
        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">ORDER DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <!-- 30 MIN. ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">30 MIN. ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Order Placement">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-3F8CE8">{{ $data['order_30min']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Vendor order accept">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OA</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_30min']['oa'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Courier Assign Pending">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>CAP</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_30min']['cap'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Taksh Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Taksh Assign</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_30min']['taksh_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Other Logistic">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Other Logistic</span>
                                            </h6>
                                            <span class="card-title text-secondary">{{ $data['order_30min']['other_logistic'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Total Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Total Assign</span>
                                            </h6>
                                            <span class="card-title text-primary">{{ $data['order_30min']['total_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Out for pickup">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_30min']['ofp'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Order picked">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_30min']['op2'] ?? $data['order_30min']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Order Delivered">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OD</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_30min']['od'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Cancelled due to not delivered">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OC</span>
                                            </h6>
                                            <span class="card-title text-danger">{{ $data['order_30min']['oc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Return to Seller">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RTSD</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_30min']['rtsd'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Cancelled from Seller">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RTSDTO</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_30min']['rtsto'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- FM ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">FM ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OP">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-3F8CE8">{{ $data['order_fm']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OA">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OA</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_fm']['oa'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=CAP">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>CAP</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_fm']['cap'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Taksh Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Taksh Assign</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_fm']['taksh_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Other Logistic">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/stores.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Other Logistic</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_fm']['other_logistic'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Total Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Total Assign</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_fm']['total_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OFP">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_fm']['ofp'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OP">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_fm']['op2'] ?? $data['order_fm']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OR">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_fm']['or'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OC">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OC</span>
                                            </h6>
                                            <span class="card-title text-danger">{{ $data['order_fm']['oc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=OS">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OS</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_fm']['os'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- FORWARD ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">FORWARD ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>TO</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_forward']['to'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFD</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_forward']['ofd'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OD</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_forward']['od'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_forward']['or'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OC</span>
                                            </h6>
                                            <span class="card-title text-danger">{{ $data['order_forward']['oc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>ORTO</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_forward']['orto'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>ORTS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_forward']['orts'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- RVP ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">RVP ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>CRP</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rvp']['crp'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RA</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_rvp']['ra'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rvp']['rr'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Courier Assign Pending">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>CAP</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rvp']['cap'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Taksh Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Taksh Assign</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_rvp']['taksh_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Other Logistic">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Other Logistic</span>
                                            </h6>
                                            <span class="card-title text-secondary">{{ $data['order_rvp']['other_logistic'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}?tab=Total Assign">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>Total Assign</span>
                                            </h6>
                                            <span class="card-title text-primary">{{ $data['order_rvp']['total_assign'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_rvp']['ofp'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_rvp']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rvp']['or'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RVRS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rvp']['rvrs'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RVPC</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_rvp']['rvpc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 3PC ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">3PC ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_3pc']['or1'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OA</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_3pc']['oa'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_3pc']['or2'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_3pc']['ofp'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OP</span>
                                            </h6>
                                            <span class="card-title text-FFA800">{{ $data['order_3pc']['op'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OS</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_3pc']['os'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFD</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_3pc']['ofd'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OD</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_3pc']['od'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_3pc']['or3'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OC</span>
                                            </h6>
                                            <span class="card-title text-danger">{{ $data['order_3pc']['oc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>ORTS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_3pc']['orts'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RTSS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_3pc']['rtss'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- RTS ORDER -->
                        <div class="order-section">
                            <h6 class="mb-3">RTS ORDER</h6>
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>TORTS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rts']['torts'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>SRTS</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_rts']['srts'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>RVPRTS</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rts']['rvprts'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OFD</span>
                                            </h6>
                                            <span class="card-title text-info">{{ $data['order_rts']['ofd'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OD</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_rts']['od'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OR</span>
                                            </h6>
                                            <span class="card-title text-warning">{{ $data['order_rts']['or'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>OC</span>
                                            </h6>
                                            <span class="card-title text-danger">{{ $data['order_rts']['oc'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <a class="order--card h-100" href="{{route('admin.users.customer.view', [1])}}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                                <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                                <span>DTO</span>
                                            </h6>
                                            <span class="card-title text-success">{{ $data['order_rts']['dto'] ?? 0 }}</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUSTOMER, SELLER, WAREHOUSE, EMPLOYEE DETAILS -->
        <div class="row g-2">
            <!-- CUSTOMER DETAILS -->
            <div class="col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h5 class="card-header-title">CUST. DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item">
                            <span class="stat-label">TOTAL CUS.</span>
                            <span class="stat-value">{{ number_format($data['total_customers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">ACTIV CUS</span>
                            <span class="stat-value">{{ number_format($data['active_customers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">NEW CUS.</span>
                            <span class="stat-value">{{ number_format($data['new_customers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">DE.ACT. CUS</span>
                            <span class="stat-value">{{ number_format($data['deactivated_customers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">BLOCK CUS</span>
                            <span class="stat-value">{{ number_format($data['blocked_customers']) }}</span>
                        </div>
                        <div class="mt-3">
                            <h6 class="mb-2">CUS. Growth</h6>
                            <div id="customer-growth-chart" style="height: 150px;"></div>
                        </div>
                        <div class="mt-3">
                            <h6 class="mb-2">CUS. SATI.</h6>
                            <div id="customer-satisfaction-chart" style="height: 150px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SELLER DETAILS -->
            <div class="col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h5 class="card-header-title">SELERS DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item">
                            <span class="stat-label">TOTAL SELER</span>
                            <span class="stat-value">{{ number_format($data['total_sellers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">ACT SEL</span>
                            <span class="stat-value">{{ number_format($data['active_sellers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">NEW SEL.</span>
                            <span class="stat-value">{{ number_format($data['new_sellers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">DE.ACT.SEL</span>
                            <span class="stat-value">{{ number_format($data['deactivated_sellers']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">BLOCK SEL</span>
                            <span class="stat-value">{{ number_format($data['blocked_sellers']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WH & MWH DETAILS -->
            <div class="col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h5 class="card-header-title">WH & MWH DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item">
                            <span class="stat-label">TOTAL WH</span>
                            <span class="stat-value">{{ number_format($data['total_wh']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">TOTAL MWH</span>
                            <span class="stat-value">{{ number_format($data['total_mwh']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPLOYEE DETAILS -->
            <div class="col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <h5 class="card-header-title">EMP. DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-item">
                            <span class="stat-label">Total EMP.</span>
                            <span class="stat-value">{{ number_format($data['total_employees']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">ACT.</span>
                            <span class="stat-value">{{ number_format($data['active_employees']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">DEA</span>
                            <span class="stat-value">{{ number_format($data['deactivated_employees']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">NEW</span>
                            <span class="stat-value">{{ number_format($data['new_employees']) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">BLOCKED CUS</span>
                            <span class="stat-value">{{ number_format($data['blocked_employees'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{asset('public/assets/admin')}}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{asset('public/assets/admin')}}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
    <!-- Apex Charts -->
    <script src="{{asset('assets/admin/js/apex-charts/apexcharts.js')}}"></script>
    <!-- Apex Charts -->
@endpush

@push('script_2')
<script>
    "use strict";
    
    // Customer Growth Chart
    var customerGrowthOptions = {
        series: [{
            name: 'Customer Growth',
            data: [
                {{ $data['total_customers'] - ($data['new_customers'] * 4) }},
                {{ $data['total_customers'] - ($data['new_customers'] * 3) }},
                {{ $data['total_customers'] - ($data['new_customers'] * 2) }},
                {{ $data['total_customers'] - $data['new_customers'] }},
                {{ $data['total_customers'] }}
            ]
        }],
        chart: {
            height: 150,
            type: 'line',
            toolbar: {
                show: false
            }
        },
        colors: ['#4CAF50'],
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5']
        },
        tooltip: {
            enabled: false
        }
    };
    var customerGrowthChart = new ApexCharts(document.querySelector("#customer-growth-chart"), customerGrowthOptions);
    customerGrowthChart.render();

    // Customer Satisfaction Chart
    var customerSatisfactionOptions = {
        series: [{
            name: 'Satisfaction',
            data: [
                {{ min(100, ($data['active_customers'] / max($data['total_customers'], 1)) * 100) }},
                {{ min(100, (($data['active_customers'] + $data['new_customers']) / max($data['total_customers'], 1)) * 100) }},
                {{ min(100, ($data['active_customers'] / max($data['total_customers'], 1)) * 100) }},
                {{ min(100, (($data['active_customers'] + 50) / max($data['total_customers'], 1)) * 100) }},
                {{ min(100, ($data['active_customers'] / max($data['total_customers'], 1)) * 100) }}
            ]
        }],
        chart: {
            height: 150,
            type: 'bar',
            toolbar: {
                show: false
            }
        },
        colors: ['#4CAF50'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
            }
        },
        xaxis: {
            categories: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5']
        },
        tooltip: {
            enabled: false
        }
    };
    var customerSatisfactionChart = new ApexCharts(document.querySelector("#customer-satisfaction-chart"), customerSatisfactionOptions);
    customerSatisfactionChart.render();
</script>
@endpush
