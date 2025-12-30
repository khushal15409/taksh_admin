@extends('layouts.admin.app')

@section('title','Warehouse Creation')

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
    }
    
    /* Ensure DataTable controls are visible */
    .dataTables_length,
    .dataTables_filter,
    .dataTables_info,
    .dataTables_paginate {
        display: block !important;
        visibility: visible !important;
    }
    
    .dataTables_wrapper .dataTables_length {
        float: left;
        padding-top: 0.755em;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
        padding-top: 0.755em;
    }
    
    .dataTables_wrapper .dataTables_info {
        float: left;
        padding-top: 0.755em;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: 0.25em;
    }
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-shop-outlined"></i>
                </span>
                <span>
                    Warehouse Creation
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        Warehouse List<span class="badge badge-soft-dark ml-2" id="warehouse-count">{{count($warehouses)}}</span>
                    </h5>
                    <div>
                        <a href="{{route('admin.logistics.warehouse.create')}}" class="btn btn--primary m-0 pull-right">
                            <i class="tio-add-circle"></i> {{translate('messages.add_new')}}
                        </a>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                           data-hs-datatables-options='{
                             "order": [],
                             "orderCellsTop": true,
                             "paging": true,
                             "pageLength": 25,
                             "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                             "searching": true,
                             "info": true,
                             "dom": "<\"row\"<\"col-sm-12 col-md-6\"l><\"col-sm-12 col-md-6\"f>>rtip"
                           }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">{{translate('messages.sl')}}</th>
                        <th class="border-0">Image</th>
                        <th class="border-0">{{translate('messages.name')}}</th>
                        <th class="border-0">Owner Name</th>
                        <th class="border-0">Full Address</th>
                        <th class="border-0">Location</th>
                        <th class="border-0">Pincode</th>
                        <th class="border-0">{{translate('messages.zone')}}</th>
                        <th class="border-0 text-center">{{translate('messages.status')}}</th>
                        <th class="border-0 text-center">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($warehouses as $key=>$warehouse)
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
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$warehouse->name}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$warehouse->owner_name ?? 'taksh'}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{Str::limit($warehouse->full_address, 30, '...')}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$warehouse->location ?? 'N/A'}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$warehouse->pincode}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$warehouse->zone->name ?? 'N/A'}}
                                </span>
                            </td>
                            <td class="text-center">
                                <label class="toggle-switch toggle-switch-sm" for="status-{{$warehouse['id']}}">
                                    <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                           data-id="status-{{$warehouse['id']}}"
                                           data-type="status"
                                           data-image-on='{{asset('/public/assets/admin/img/modal/zone-status-on.png')}}'
                                           data-image-off="{{asset('/public/assets/admin/img/modal/zone-status-off.png')}}"
                                           data-title-on="Want to activate this warehouse?"
                                           data-title-off="Want to deactivate this warehouse?"
                                           id="status-{{$warehouse['id']}}" {{$warehouse->status?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form action="{{route('admin.logistics.warehouse.status')}}" method="post" id="status-{{$warehouse['id']}}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$warehouse['id']}}">
                                    <input type="hidden" name="status" value="{{$warehouse->status?0:1}}">
                                </form>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.logistics.warehouse.edit',[$warehouse['id']])}}" title="{{translate('messages.edit')}}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="warehouse-{{$warehouse['id']}}" data-message="Want to delete this warehouse?" title="{{translate('messages.delete')}}">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('admin.logistics.warehouse.destroy',[$warehouse['id']])}}"
                                            method="post" id="warehouse-{{$warehouse['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
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
@endsection

@push('script_2')
<script>
    $(document).ready(function () {
        // Show loader when page starts loading
        if (typeof PageLoader !== 'undefined') {
            PageLoader.show();
        }
        
        // Initialize DataTable directly to ensure controls are shown
        var table = $('#columnSearchDatatable').DataTable({
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
            },
            initComplete: function() {
                // Hide loader when DataTable is initialized
                if (typeof PageLoader !== 'undefined') {
                    setTimeout(function() {
                        PageLoader.hide();
                    }, 300);
                }
            }
        });
        
        // Update count badge when table is drawn
        table.on('draw', function () {
            var info = table.page.info();
            $('#warehouse-count').text(info.recordsTotal);
        });
        
        // Ensure controls are visible after initialization
        setTimeout(function() {
            $('.dataTables_length').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_filter').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_info').css({'display': 'block', 'visibility': 'visible'});
            $('.dataTables_paginate').css({'display': 'block', 'visibility': 'visible'});
        }, 200);
        
        // Handle loader for form submissions (delete, status change)
        $('.form-alert').on('click', function() {
            if (typeof PageLoader !== 'undefined') {
                PageLoader.show();
            }
        });
        
        // Hide loader if page is fully loaded (fallback)
        $(window).on('load', function() {
            setTimeout(function() {
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
            }, 500);
        });
    });
</script>
@endpush

