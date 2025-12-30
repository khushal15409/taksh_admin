@extends('layouts.admin.app')

@section('title','Pending Mapping')

@include('admin-views.partials._loader')

@push('css_or_js')
<style>
    /* DataTable Controls Styling */
    .dataTables_wrapper {
        padding: 15px 0;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    
    .dataTables_wrapper .dataTables_length label {
        font-weight: 500;
        color: #5e6278;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.925rem;
        color: #5e6278;
        background-color: #fff;
        min-width: 80px;
    }
    
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #009ef7;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.25);
    }
    
    .dataTables_wrapper .dataTables_filter {
        text-align: right;
    }
    
    .dataTables_wrapper .dataTables_filter label {
        font-weight: 500;
        color: #5e6278;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        padding: 0.55rem 0.75rem;
        font-size: 0.925rem;
        color: #5e6278;
        background-color: #fff;
        min-width: 200px;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #009ef7;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.25);
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 0.85em;
        font-size: 0.925rem;
        color: #5e6278;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 15px;
    }
    
    .dataTables_wrapper .dataTables_paginate .pagination {
        margin: 0;
        justify-content: flex-end;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin-left: 0.25rem;
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        color: #5e6278;
        background-color: #fff;
        cursor: pointer;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #009ef7;
        background-color: #f1faff;
        border-color: #009ef7;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        color: #fff;
        background-color: #009ef7;
        border-color: #009ef7;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #b5b5c3;
        cursor: not-allowed;
        background-color: #f5f8fa;
    }
    
    /* Tab Styling */
    .nav-tabs {
        border-bottom: 2px solid #e4e6ef;
        margin-bottom: 20px;
    }
    
    .nav-tabs .nav-item {
        margin-bottom: -2px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #5e6278;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    /* Prevent content flash on tab navigation */
    .tab-content {
        min-height: 400px;
    }
    
    .tab-pane {
        opacity: 1;
        transition: opacity 0.2s ease-in-out;
    }
    
    .nav-tabs .nav-link:hover {
        border-bottom-color: #009ef7;
        color: #009ef7;
    }
    
    .nav-tabs .nav-link.active {
        color: #009ef7;
        border-bottom-color: #009ef7;
        background-color: transparent;
    }
    
    .tab-content {
        padding-top: 20px;
    }
    
    @media (max-width: 767px) {
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            text-align: left;
            margin-bottom: 10px;
        }
        
        .dataTables_wrapper .dataTables_filter label {
            justify-content: flex-start;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            text-align: center;
        }
        
        .dataTables_wrapper .dataTables_paginate .pagination {
            justify-content: center;
        }
        
        .nav-tabs .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-list-outlined"></i>
                </span>
                <span>
                    Pending Mapping
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <h5 class="card-title">Pending Mappings</h5>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="pendingMappingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'warehouse' ? 'active' : '' }}" 
                                   id="warehouse-tab" 
                                   data-toggle="tab" 
                                   href="#warehouse" 
                                   role="tab"
                                   data-tab="warehouse"
                                   onclick="navigateToTab('warehouse'); return false;">
                                    Pending Warehouse
                                    <span class="badge badge-soft-dark ml-2">{{ count($pendingWarehouses) }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'miniwarehouse' ? 'active' : '' }}" 
                                   id="miniwarehouse-tab" 
                                   data-toggle="tab" 
                                   href="#miniwarehouse" 
                                   role="tab"
                                   data-tab="miniwarehouse"
                                   onclick="navigateToTab('miniwarehouse'); return false;">
                                    Pending Miniwarehouse
                                    <span class="badge badge-soft-dark ml-2">{{ count($pendingMiniwarehouses) }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'lm-center' ? 'active' : '' }}" 
                                   id="lm-center-tab" 
                                   data-toggle="tab" 
                                   href="#lm-center" 
                                   role="tab"
                                   data-tab="lm-center"
                                   onclick="navigateToTab('lm-center'); return false;">
                                    Pending LM Center
                                    <span class="badge badge-soft-dark ml-2">{{ count($pendingLmCenters) }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'fm-rt-center' ? 'active' : '' }}" 
                                   id="fm-rt-center-tab" 
                                   data-toggle="tab" 
                                   href="#fm-rt-center" 
                                   role="tab"
                                   data-tab="fm-rt-center"
                                   onclick="navigateToTab('fm-rt-center'); return false;">
                                    Pending FM/RT Center
                                    <span class="badge badge-soft-dark ml-2">{{ count($pendingFmRtCenters) }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'pincode' ? 'active' : '' }}" 
                                   id="pincode-tab" 
                                   data-toggle="tab" 
                                   href="#pincode" 
                                   role="tab"
                                   data-tab="pincode"
                                   onclick="navigateToTab('pincode'); return false;">
                                    Pending Pincode
                                    <span class="badge badge-soft-dark ml-2">{{ count($pendingPincodes) }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $tab == 'active-pincode' ? 'active' : '' }}" 
                                   id="active-pincode-tab" 
                                   data-toggle="tab" 
                                   href="#active-pincode" 
                                   role="tab"
                                   data-tab="active-pincode"
                                   onclick="navigateToTab('active-pincode'); return false;">
                                    Active Pincode
                                    <span class="badge badge-soft-dark ml-2">{{ count($activePincodes) }}</span>
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content" id="pendingMappingTabContent">
                            <!-- Pending Warehouse Tab -->
                            <div class="tab-pane fade {{ $tab == 'warehouse' ? 'show active' : '' }}" id="warehouse" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="warehouseTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Image</th>
                                            <th class="border-0">{{translate('messages.name')}}</th>
                                            <th class="border-0">Owner Name</th>
                                            <th class="border-0">Full Address</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">{{translate('messages.zone')}}</th>
                                            <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pendingWarehouses as $key=>$warehouse)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>
                                                    @php
                                                        $firstImage = null;
                                                        if ($warehouse->images && is_array($warehouse->images) && count($warehouse->images) > 0) {
                                                            $firstImage = $warehouse->images[0];
                                                        }
                                                    @endphp
                                                    @if($firstImage && isset($firstImage['img']))
                                                        <img src="{{ \App\CentralLogics\Helpers::get_full_url('warehouse', $firstImage['img'], $firstImage['storage'] ?? 'public') }}" 
                                                             alt="{{ $warehouse->name }}" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <img src="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}" 
                                                             alt="No Image" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; opacity: 0.5;">
                                                    @endif
                                                </td>
                                                <td>{{$warehouse->name}}</td>
                                                <td>{{$warehouse->owner_name ?? 'N/A'}}</td>
                                                <td>{{Str::limit($warehouse->full_address, 30, '...')}}</td>
                                                <td>{{$warehouse->pincode}}</td>
                                                <td>{{$warehouse->zone->name ?? 'N/A'}}</td>
                                                <td class="text-center">
                                                    <a href="{{route('admin.logistics.warehouse.edit',[$warehouse['id']])}}" class="btn btn-sm btn-white" title="Edit">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Pending Miniwarehouse Tab -->
                            <div class="tab-pane fade {{ $tab == 'miniwarehouse' ? 'show active' : '' }}" id="miniwarehouse" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="miniwarehouseTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Image</th>
                                            <th class="border-0">{{translate('messages.name')}}</th>
                                            <th class="border-0">Owner Name</th>
                                            <th class="border-0">Full Address</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">{{translate('messages.zone')}}</th>
                                            <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pendingMiniwarehouses as $key=>$miniwarehouse)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>
                                                    @php
                                                        $firstImage = null;
                                                        if ($miniwarehouse->images && is_array($miniwarehouse->images) && count($miniwarehouse->images) > 0) {
                                                            $firstImage = $miniwarehouse->images[0];
                                                        }
                                                    @endphp
                                                    @if($firstImage && isset($firstImage['img']))
                                                        <img src="{{ \App\CentralLogics\Helpers::get_full_url('miniwarehouse', $firstImage['img'], $firstImage['storage'] ?? 'public') }}" 
                                                             alt="{{ $miniwarehouse->name }}" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <img src="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}" 
                                                             alt="No Image" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; opacity: 0.5;">
                                                    @endif
                                                </td>
                                                <td>{{$miniwarehouse->name}}</td>
                                                <td>{{$miniwarehouse->owner_name ?? 'N/A'}}</td>
                                                <td>{{Str::limit($miniwarehouse->full_address, 30, '...')}}</td>
                                                <td>{{$miniwarehouse->pincode}}</td>
                                                <td>{{$miniwarehouse->zone->name ?? 'N/A'}}</td>
                                                <td class="text-center">
                                                    <a href="{{route('admin.logistics.miniwarehouse.edit',[$miniwarehouse['id']])}}" class="btn btn-sm btn-white" title="Edit">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Pending LM Center Tab -->
                            <div class="tab-pane fade {{ $tab == 'lm-center' ? 'show active' : '' }}" id="lm-center" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="lmCenterTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Center Name</th>
                                            <th class="border-0">Owner Name</th>
                                            <th class="border-0">Full Address</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">{{translate('messages.zone')}}</th>
                                            <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pendingLmCenters as $key=>$lmCenter)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{$lmCenter->center_name}}</td>
                                                <td>{{$lmCenter->owner_name ?? 'N/A'}}</td>
                                                <td>{{Str::limit($lmCenter->full_address, 30, '...')}}</td>
                                                <td>{{$lmCenter->pincode}}</td>
                                                <td>{{$lmCenter->zone->name ?? 'N/A'}}</td>
                                                <td class="text-center">
                                                    <a href="{{route('admin.logistics.lm-center.edit',[$lmCenter['id']])}}" class="btn btn-sm btn-white" title="Edit">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Pending FM/RT Center Tab -->
                            <div class="tab-pane fade {{ $tab == 'fm-rt-center' ? 'show active' : '' }}" id="fm-rt-center" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="fmRtCenterTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Image</th>
                                            <th class="border-0">Center Name</th>
                                            <th class="border-0">Owner Name</th>
                                            <th class="border-0">Full Address</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">{{translate('messages.zone')}}</th>
                                            <th class="border-0 text-center">{{translate('messages.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pendingFmRtCenters as $key=>$fmRtCenter)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>
                                                    @php
                                                        $firstImage = null;
                                                        if ($fmRtCenter->images && is_array($fmRtCenter->images) && count($fmRtCenter->images) > 0) {
                                                            $firstImage = $fmRtCenter->images[0];
                                                        }
                                                    @endphp
                                                    @if($firstImage && isset($firstImage['img']))
                                                        <img src="{{ \App\CentralLogics\Helpers::get_full_url('fm_rt_center', $firstImage['img'], $firstImage['storage'] ?? 'public') }}" 
                                                             alt="{{ $fmRtCenter->center_name }}" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <img src="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}" 
                                                             alt="No Image" 
                                                             class="img-thumbnail" 
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; opacity: 0.5;">
                                                    @endif
                                                </td>
                                                <td>{{$fmRtCenter->center_name}}</td>
                                                <td>{{$fmRtCenter->owner_name ?? 'N/A'}}</td>
                                                <td>{{Str::limit($fmRtCenter->full_address, 30, '...')}}</td>
                                                <td>{{$fmRtCenter->pincode}}</td>
                                                <td>{{$fmRtCenter->zone->name ?? 'N/A'}}</td>
                                                <td class="text-center">
                                                    <a href="{{route('admin.logistics.fm-rt-center.edit',[$fmRtCenter['id']])}}" class="btn btn-sm btn-white" title="Edit">
                                                        <i class="tio-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Pending Pincode Tab -->
                            <div class="tab-pane fade {{ $tab == 'pincode' ? 'show active' : '' }}" id="pincode" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="pincodeTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">Office Name</th>
                                            <th class="border-0">District</th>
                                            <th class="border-0">State</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($pendingPincodes as $key=>$pincode)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{$pincode->pincode}}</td>
                                                <td>{{$pincode->officename ?? 'N/A'}}</td>
                                                <td>{{$pincode->district ?? 'N/A'}}</td>
                                                <td>{{$pincode->statename ?? 'N/A'}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Active Pincode Tab -->
                            <div class="tab-pane fade {{ $tab == 'active-pincode' ? 'show active' : '' }}" id="active-pincode" role="tabpanel">
                                <div class="table-responsive datatable-custom">
                                    <table id="activePincodeTable"
                                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">{{translate('messages.sl')}}</th>
                                            <th class="border-0">Pincode</th>
                                            <th class="border-0">Office Name</th>
                                            <th class="border-0">District</th>
                                            <th class="border-0">State</th>
                                            <th class="border-0">Mapped LM Center</th>
                                            <th class="border-0">Mapped Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($activePincodes as $key=>$activePincode)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{$activePincode->pincode}}</td>
                                                <td>{{$activePincode->officename ?? 'N/A'}}</td>
                                                <td>{{$activePincode->district ?? 'N/A'}}</td>
                                                <td>{{$activePincode->statename ?? 'N/A'}}</td>
                                                <td>
                                                    <a href="{{route('admin.logistics.lm-center.edit',[$activePincode->lm_center_id])}}" class="text-primary">
                                                        {{$activePincode->center_name}}
                                                    </a>
                                                </td>
                                                <td>{{$activePincode->mapped_at ? \Carbon\Carbon::parse($activePincode->mapped_at)->format('d M Y') : 'N/A'}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    // Function to navigate to tab without showing loader
    function navigateToTab(tabName) {
        // Prevent loader from showing
        if (typeof window !== 'undefined') {
            window._preventLoaderOnNavigation = true;
        }
        // Navigate to the tab
        window.location.href = '{{ route("admin.logistics.pending-mapping.index") }}?tab=' + tabName;
    }
    
    $(document).ready(function() {
        // Hide loader immediately on page load to prevent blink
        if (typeof PageLoader !== 'undefined') {
            PageLoader.hide();
        }
        
        // Initialize DataTables for each table with full configuration
        // Check if table exists and hasn't been initialized to prevent reinitialization errors
        if ($('#warehouseTable').length && !$.fn.DataTable.isDataTable('#warehouseTable')) {
            var warehouseTable = $('#warehouseTable').DataTable({
                paging: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                searching: true,
                info: true,
                order: [],
                orderCellsTop: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                    lengthMenu: "Show _MENU_ entries",
                    search: "Search:",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
        
        if ($('#miniwarehouseTable').length && !$.fn.DataTable.isDataTable('#miniwarehouseTable')) {
            var miniwarehouseTable = $('#miniwarehouseTable').DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            info: true,
            order: [],
            orderCellsTop: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
            });
        }
        
        if ($('#lmCenterTable').length && !$.fn.DataTable.isDataTable('#lmCenterTable')) {
            var lmCenterTable = $('#lmCenterTable').DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            info: true,
            order: [],
            orderCellsTop: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
            });
        }
        
        if ($('#fmRtCenterTable').length && !$.fn.DataTable.isDataTable('#fmRtCenterTable')) {
            var fmRtCenterTable = $('#fmRtCenterTable').DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            info: true,
            order: [],
            orderCellsTop: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
            });
        }
        
        if ($('#pincodeTable').length && !$.fn.DataTable.isDataTable('#pincodeTable')) {
            var pincodeTable = $('#pincodeTable').DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: true,
            info: true,
            order: [],
            orderCellsTop: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                lengthMenu: "Show _MENU_ entries",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
            });
        }
        
        // Initialize activePincodeTable only if it exists and hasn't been initialized
        if ($('#activePincodeTable').length && !$.fn.DataTable.isDataTable('#activePincodeTable')) {
            var activePincodeTable = $('#activePincodeTable').DataTable({
                paging: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                searching: true,
                info: true,
                order: [],
                orderCellsTop: true,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    zeroRecords: '<div class="text-center p-4"><img class="w-7rem mb-3" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description"><h5>{{translate('no_data_found')}}</h5></div>',
                    lengthMenu: "Show _MENU_ entries",
                    search: "Search:",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
        
        // Ensure controls are visible after initialization
        setTimeout(function() {
            $('.dataTables_length').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_filter').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_info').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_paginate').css({'display': 'block', 'visibility': 'visible'});
        }, 200);
        
        // Handle tab switching - prevent loader from showing on tab navigation
        $('.nav-link[onclick*="pending-mapping"]').on('click', function(e) {
            // Prevent loader from showing on tab navigation
            if (typeof PageLoader !== 'undefined') {
                // Temporarily disable the beforeunload listener
                window._preventLoaderOnNavigation = true;
            }
        });
        
        // Handle tab switching - reinitialize DataTable for active tab (if using Bootstrap tabs without page reload)
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            var tableId = target.replace('#', '') + 'Table';
            
            // Redraw the table to ensure proper display
            if ($('#' + tableId).length) {
                $('#' + tableId).DataTable().columns.adjust().draw();
            }
        });
    });
</script>
@endpush

