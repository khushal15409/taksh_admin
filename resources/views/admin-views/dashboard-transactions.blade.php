@extends('layouts.admin.app')

@section('title',\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()->value??translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@include('admin-views.partials._loader')

@endsection

@push('script')

@endpush

@push('script_2')
    <script>
        // Show loader when page starts loading
        if (typeof PageLoader !== 'undefined') {
            PageLoader.show();
        }
        
        // Hide loader when page is fully loaded
        $(document).ready(function() {
            setTimeout(function() {
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
            }, 500);
        });
        
        // Fallback: Hide loader when window is fully loaded
        $(window).on('load', function() {
            setTimeout(function() {
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
            }, 300);
        });
    </script>
@endpush
