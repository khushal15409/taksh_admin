@extends('layouts.admin.app')

@section('title', translate('messages.Order List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-xl-10 col-md-9 col-sm-8 mb-3 mb-sm-0">
                    <h1 class="page-header-title text-capitalize m-0">
                        <span class="page-header-icon">
                            <img src="{{asset('public/assets/admin/img/order.png')}}" class="w--26" alt="">
                        </span>
                        <span>
                            {{ $statusTitle }}
                            <span class="badge badge-soft-dark ml-2" id="total-count">0</span>
                        </span>
                    </h1>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Page Header -->
        
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-1 border-0">
                <div class="search--button-wrapper">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <!-- Length Menu -->
                        <div class="table-top">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0">{{ translate('messages.show') }}:</label>
                                <select id="datatableEntries" class="form-control form-control-sm" style="width: auto; min-width: 80px;">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="-1">{{ translate('messages.all') }}</option>
                                </select>
                                <label class="ml-2 mb-0">{{ translate('messages.entries') }}</label>
                            </div>
                        </div>
                        <!-- Search - Right Side -->
                        <div class="search-form min--260 ml-auto">
                            <div class="input-group input--group">
                                <input id="datatableSearch" type="search" name="search" class="form-control h--40px"
                                        placeholder="{{ translate('messages.Ex:') }} Tracking ID, Order ID" 
                                        aria-label="{{translate('messages.search')}}">
                                <button type="button" class="btn btn--secondary" id="searchBtn"><i class="tio-search"></i></button>
                            </div>
                        </div>
                        <!-- End Search -->
                    </div>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="dashboardOrderTable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">SR. NO.</th>
                        <th class="border-0">TRACKING ID</th>
                        <th class="border-0">ORDER ID</th>
                        <th class="border-0">CUSTOMER NAME</th>
                        <th class="border-0">CUSTOMER MOB.</th>
                        <th class="border-0">CUSTOMER ADDR.</th>
                        <th class="border-0">PRODUCT CONTENT</th>
                        <th class="border-0">PRODUCT TYPE</th>
                        <th class="border-0">PRODUCT VALUE</th>
                        <th class="border-0">SELLER NAME</th>
                        <th class="border-0">SELLER MOB.</th>
                        <th class="border-0">SELLER ADDR.</th>
                        <th class="text-center border-0">STATUS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Data will be loaded via DataTable -->
                    </tbody>
                </table>
            </div>
            <!-- End Table -->
            
            <!-- Pagination Info -->
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-sm-12 col-md-5">
                        <div class="dataTables_info" id="dashboardOrderTable_info" role="status" aria-live="polite">
                            {{ translate('messages.showing') }} 0 {{ translate('messages.to') }} 0 {{ translate('messages.of') }} 0 {{ translate('messages.entries') }}
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="dashboardOrderTable_paginate">
                            <!-- Pagination will be inserted here by DataTable -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#dashboardOrderTable').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            searching: false, // Disable default search box since we have custom one
            ordering: true,
            order: [[0, 'asc']],
            info: true,
            autoWidth: false,
            responsive: true,
            dom: 'rtip', // r = processing, t = table, i = info, p = pagination
            lengthChange: false, // Hide default length menu since we have custom one
            language: {
                paginate: {
                    previous: "<i class='tio-arrow-backward'></i>",
                    next: "<i class='tio-arrow-forward'></i>",
                    first: "<i class='tio-skip-backward'></i>",
                    last: "<i class='tio-skip-forward'></i>"
                },
                lengthMenu: "{{ translate('messages.show') }} _MENU_ {{ translate('messages.entries') }}",
                zeroRecords: "{{ translate('messages.no_data_available') }}",
                info: "{{ translate('messages.showing') }} _START_ {{ translate('messages.to') }} _END_ {{ translate('messages.of') }} _TOTAL_ {{ translate('messages.entries') }}",
                infoEmpty: "{{ translate('messages.showing') }} 0 {{ translate('messages.to') }} 0 {{ translate('messages.of') }} 0 {{ translate('messages.entries') }}",
                infoFiltered: "({{ translate('messages.filtered_from') }} _MAX_ {{ translate('messages.total') }} {{ translate('messages.entries') }})",
                search: "{{ translate('messages.search') }}:",
                processing: "{{ translate('messages.processing') }}...",
                emptyTable: "{{ translate('messages.no_data_available') }}"
            },
            columns: [
                { data: 'sr_no', name: 'sr_no', orderable: true },
                { data: 'tracking_id', name: 'tracking_id', orderable: true },
                { data: 'order_id', name: 'order_id', orderable: true },
                { data: 'customer_name', name: 'customer_name', orderable: true },
                { data: 'customer_mob', name: 'customer_mob', orderable: true },
                { data: 'customer_addr', name: 'customer_addr', orderable: false },
                { data: 'product_content', name: 'product_content', orderable: false },
                { data: 'product_type', name: 'product_type', orderable: true },
                { data: 'product_value', name: 'product_value', orderable: true },
                { data: 'seller_name', name: 'seller_name', orderable: true },
                { data: 'seller_mob', name: 'seller_mob', orderable: true },
                { data: 'seller_addr', name: 'seller_addr', orderable: false },
                { data: 'status', name: 'status', orderable: true, className: 'text-center' }
            ],
            // For now, using empty data - will be populated later
            data: [],
            drawCallback: function(settings) {
                // Update total count badge
                var api = this.api();
                var total = api.page.info().recordsTotal;
                $('#total-count').text(total);
            }
        });

        // Length menu change handler
        $('#datatableEntries').on('change', function () {
            var val = $(this).val();
            if (val == -1) {
                table.page.len(Number.MAX_SAFE_INTEGER).draw();
            } else {
                table.page.len(parseInt(val)).draw();
            }
        });

        // Search functionality
        $('#datatableSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Search button click
        $('#searchBtn').on('click', function () {
            table.search($('#datatableSearch').val()).draw();
        });

        // Prevent form submission on Enter key
        $('.search-form').on('submit', function(e) {
            e.preventDefault();
            table.search($('#datatableSearch').val()).draw();
        });
    });
</script>
@endpush

