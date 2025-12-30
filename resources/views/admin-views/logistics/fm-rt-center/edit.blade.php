@extends('layouts.admin.app')

@section('title','Update FM/RT Center')

@include('admin-views.partials._loader')

@push('css_or_js')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075), 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 0.5rem;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        padding: 1rem 1.25rem;
    }
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.125);
        padding: 1rem 1.25rem;
    }
    .dropzone-custom {
        min-height: 200px;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        background: #f9fafb;
        cursor: pointer;
        position: relative;
    }
    .dropzone-custom:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .dropzone-custom input[type="file"] {
        display: none;
    }
    .dropzone-custom.dz-drag-hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .dropzone-custom .dz-message {
        text-align: center;
        margin: 0;
        cursor: pointer;
    }
    .dropzone-custom label.dz-message {
        pointer-events: auto;
    }
    .dropzone-custom .dz-message i {
        font-size: 48px;
        color: #9ca3af;
        display: block;
        margin-bottom: 10px;
    }
    .dropzone-custom .dz-message-text {
        display: block;
        font-size: 16px;
        color: #374151;
        margin-bottom: 5px;
    }
    .dropzone-custom .dz-message-desc {
        display: block;
        font-size: 14px;
        color: #3b82f6;
        cursor: pointer;
        text-decoration: underline;
    }
    .dropzone-custom .dz-message-desc:hover {
        color: #2563eb;
    }
    .dropzone-custom .dz-message-text {
        cursor: pointer;
    }
    .dropzone-custom .dz-preview {
        display: inline-block;
        margin: 10px;
        position: relative;
        z-index: 10;
    }
    .dropzone-custom .dz-preview .dz-image {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
    }
    .dropzone-custom .dz-preview .dz-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .dropzone-custom .dz-preview .dz-remove {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
    }
    .dropzone-custom .dz-preview .dz-remove:hover {
        background: #dc2626;
    }
    .existing-image-wrapper {
        position: relative;
        display: inline-block;
        margin: 5px;
    }
    .existing-image-wrapper .remove-existing-image {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        padding: 0;
        line-height: 1;
        border: none;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .existing-image-wrapper .remove-existing-image:hover {
        background: #dc2626;
    }
    .invalid-feedback {
        display: block !important;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8h.8'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .form-control.is-valid {
        border-color: #28a745;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    Update FM/RT Center
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{route('admin.logistics.fm-rt-center.update',[$fmRtCenter['id']])}}" method="post" enctype="multipart/form-data" id="fm-rt-center-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Left Card: Form Fields (col-md-8) -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">FM/RT Center Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="center_name">Center Name <span class="text-danger">*</span></label>
                                        <input type="text" name="center_name" id="center_name" class="form-control" placeholder="Enter center name" value="{{old('center_name', $fmRtCenter->center_name)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_name">Owner Name</label>
                                        <input type="text" name="owner_name" id="owner_name" class="form-control" value="{{old('owner_name', $fmRtCenter->owner_name ?? 'taksh')}}" readonly style="background-color: #e9ecef;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="pincode">Pincode <span class="text-danger">*</span></label>
                                        <input type="text" name="pincode" id="pincode" class="form-control js-masked-input" placeholder="XXXXXX" value="{{old('pincode', $fmRtCenter->pincode)}}" required maxlength="6" pattern="[0-9]{6}" data-hs-mask-options='{"template": "000000"}' title="Please enter exactly 6 digits">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" value="{{old('email', $fmRtCenter->email)}}" maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="mobile_number">Mobile Number</label>
                                        <input type="text" name="mobile_number" id="mobile_number" class="form-control js-masked-input" placeholder="XXXXXXXXXX" value="{{old('mobile_number', $fmRtCenter->mobile_number)}}" maxlength="10" pattern="[0-9]{10}" data-hs-mask-options='{"template": "0000000000"}' title="Please enter exactly 10 digits">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="latitude">Latitude <span class="text-danger">*</span></label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Enter latitude" value="{{old('latitude', $fmRtCenter->latitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="longitude">Longitude <span class="text-danger">*</span></label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Enter longitude" value="{{old('longitude', $fmRtCenter->longitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="input-label" for="full_address">Full Address <span class="text-danger">*</span></label>
                                        <textarea name="full_address" id="full_address" class="form-control" rows="3" placeholder="Enter full address" required>{{old('full_address', $fmRtCenter->full_address)}}</textarea>
                                    </div>
                                </div>
                                
                                <!-- Document Upload Section -->
                                <div class="col-md-12">
                                    <hr class="my-3">
                                    <h6 class="mb-3">Documents</h6>
                                </div>
                                
                                @php
                                    $documents = $fmRtCenter->documents ?? [];
                                @endphp
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="rent_agreement">Rent Agreement</label>
                                        @if(isset($documents['rent_agreement']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['rent_agreement']['file'], $documents['rent_agreement']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="rent_agreement">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="rent_agreement" id="rent_agreement" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="permission_letter_local">Permission Letter of Local</label>
                                        @if(isset($documents['permission_letter_local']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['permission_letter_local']['file'], $documents['permission_letter_local']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="permission_letter_local">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="permission_letter_local" id="permission_letter_local" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="electricity_bill">Electricity Bill</label>
                                        @if(isset($documents['electricity_bill']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['electricity_bill']['file'], $documents['electricity_bill']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="electricity_bill">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="electricity_bill" id="electricity_bill" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="cin">CIN</label>
                                        @if(isset($documents['cin']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['cin']['file'], $documents['cin']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="cin">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="cin" id="cin" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="gst">GST</label>
                                        @if(isset($documents['gst']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['gst']['file'], $documents['gst']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="gst">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="gst" id="gst" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="coi">COI</label>
                                        @if(isset($documents['coi']))
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $documents['coi']['file'], $documents['coi']['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger remove-document" data-document-type="coi">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="coi" id="coi" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                    </div>
                                </div>
                                
                                <!-- Other Documents Section -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="upload_other_documents" id="upload_other_documents" value="1" {{old('upload_other_documents', (isset($documents['other_documents']) && count($documents['other_documents']) > 0) ? '1' : '') ? 'checked' : ''}}>
                                            <label class="form-check-label" for="upload_other_documents">
                                                Do you want to upload other documents?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(isset($documents['other_documents']) && is_array($documents['other_documents']) && count($documents['other_documents']) > 0)
                                    <div class="col-md-12 mb-3">
                                        <label class="input-label">Existing Other Documents</label>
                                        <div class="d-flex flex-wrap" style="gap: 10px;">
                                            @foreach($documents['other_documents'] as $index => $otherDoc)
                                                <div class="existing-other-doc-wrapper" data-doc-index="{{$index}}">
                                                    <div class="card" style="width: 200px;">
                                                        <div class="card-body p-2">
                                                            <h6 class="card-title mb-1" style="font-size: 12px;">{{$otherDoc['name'] ?? 'Document'}}</h6>
                                                            <a href="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center/documents', $otherDoc['file'], $otherDoc['storage'] ?? 'public') }}" target="_blank" class="btn btn-sm btn-info btn-block">
                                                                <i class="tio-download"></i> View
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger btn-block remove-other-doc" data-doc-index="{{$index}}">
                                                                <i class="tio-delete"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="col-md-12" id="other_documents_section" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label" for="other_document_name">Other Document Name</label>
                                                <input type="text" name="other_document_name" id="other_document_name" class="form-control" placeholder="Enter document name" value="{{old('other_document_name')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="input-label" for="other_document_file">Other Document File</label>
                                                <input type="file" name="other_document_file" id="other_document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="location" id="location" value="{{old('location', $fmRtCenter->location)}}">
                                <input type="hidden" name="state" id="state" value="{{old('state', $fmRtCenter->state)}}">
                                <input type="hidden" name="city" id="city" value="{{old('city', $fmRtCenter->city)}}">
                                <input type="hidden" name="removed_documents" id="removed_documents" value="">
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Card: Image Dropzone and Mapping (col-md-4) -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Images & Mapping</h5>
                        </div>
                        <div class="card-body">
                            <!-- Image Upload Section -->
                            <div class="form-group">
                                <label class="input-label">FM/RT Center Images</label>
                                <div id="fm-rt-center-images-dropzone" class="dropzone-custom">
                                    <input type="file" name="fm_rt_center_images[]" id="fm_rt_center_images_input" multiple accept="image/*" style="display:none;">
                                    <input type="hidden" name="removed_images" id="removed_images" value="">
                                    <label for="fm_rt_center_images_input" class="dz-message" style="cursor: pointer; margin: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <i class="tio-cloud-upload"></i>
                                        <span class="dz-message-text">Drop files here to upload</span>
                                        <small class="dz-message-desc">or click to browse</small>
                                    </label>
                                </div>
                                @if($fmRtCenter->images && is_array($fmRtCenter->images) && count($fmRtCenter->images) > 0)
                                    <div class="mt-3">
                                        <label class="input-label">Current Images</label>
                                        <div class="d-flex flex-wrap" id="existing-images" style="gap: 10px;">
                                            @foreach($fmRtCenter->images as $index => $image)
                                                <div class="existing-image-wrapper" data-image-index="{{$index}}" data-image-name="{{$image['img']}}">
                                                    <img src="{{ \App\CentralLogics\Helpers::get_full_url('fm-rt-center', $image['img'], $image['storage'] ?? 'public') }}" 
                                                         alt="Image {{$index + 1}}" 
                                                         class="img-thumbnail" 
                                                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                                                    <button type="button" class="remove-existing-image">×</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Mapping Section -->
                            <div class="form-group mt-4">
                                <h6 class="mb-3">Mapping</h6>
                                <div class="form-group">
                                    <label class="input-label" for="pincode_ids">Map Pincode</label>
                                    <select name="pincode_ids[]" id="pincode_ids" class="form-control js-select2-custom" multiple="multiple">
                                        @foreach($pincodes as $pincode)
                                            @php
                                                $isMapped = in_array($pincode->id, $mappedPincodeIds);
                                                $isSelected = in_array($pincode->id, old('pincode_ids', $fmRtCenter->pincodes->pluck('id')->toArray()));
                                            @endphp
                                            <option value="{{$pincode->id}}" 
                                                {{$isSelected ? 'selected' : ''}} 
                                                {{$isMapped ? 'disabled' : ''}}
                                                data-pincode="{{$pincode->pincode}}"
                                                data-area="{{$pincode->area_name}}"
                                                data-city="{{$pincode->city}}"
                                                data-state="{{$pincode->state}}">
                                                {{$pincode->pincode}} - {{$pincode->area_name}} ({{$pincode->city}}, {{$pincode->state}})
                                                @if($isMapped && !$isSelected) [Already Mapped] @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Pincodes already mapped to other FM/RT Centers are disabled</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
<script>
    $(document).ready(function() {
        // Hide loader when page is ready
        if (typeof PageLoader !== 'undefined') {
            PageLoader.hide();
        }
        
        // Initialize Select2 for multiple select dropdowns
        $('.js-select2-custom').select2({
            placeholder: "Select options",
            allowClear: true,
            width: '100%'
        });

        // Prevent selecting disabled options in pincode dropdown
        $('#pincode_ids').on('select2:select', function (e) {
            var data = e.params.data;
            if (data.element && data.element.disabled) {
                e.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.warning('This pincode is already mapped to another FM/RT Center and cannot be selected.');
                } else {
                    alert('This pincode is already mapped to another FM/RT Center and cannot be selected.');
                }
            }
        });

        // Initialize input masks
        if (typeof $.HSCore !== 'undefined' && $.HSCore.components.HSMask && typeof $.HSCore.components.HSMask.init === 'function') {
            var maskedInputs = $('.js-masked-input');
            if (maskedInputs.length > 0) {
                maskedInputs.each(function() {
                    var $input = $(this);
                    if ($input.length && $input.attr('data-hs-mask-options')) {
                        try {
                            $.HSCore.components.HSMask.init($input);
                        } catch(e) {
                            console.warn('HSMask initialization error:', e);
                        }
                    }
                });
            }
        }

        // Additional validation for pincode (6 digits only)
        $('#pincode').on('input', function() {
            var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            $(this).val(value);
        });

        // Additional validation for mobile number (10 digits only)
        $('#mobile_number').on('input', function() {
            var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            $(this).val(value);
        });

        // Handle other documents checkbox toggle
        $('#upload_other_documents').on('change', function() {
            if ($(this).is(':checked')) {
                $('#other_documents_section').slideDown();
            } else {
                $('#other_documents_section').slideUp();
                $('#other_document_name').val('');
                $('#other_document_file').val('');
            }
        });

        // Show other documents section if checkbox is checked on page load
        if ($('#upload_other_documents').is(':checked')) {
            $('#other_documents_section').show();
        }

        // Handle document removal
        var removedDocuments = [];
        $('.remove-document').on('click', function() {
            var documentType = $(this).data('document-type');
            if (confirm('Are you sure you want to remove this document?')) {
                removedDocuments.push(documentType);
                $('#removed_documents').val(removedDocuments.join(','));
                $(this).closest('.form-group').find('input[type="file"]').val('');
                $(this).closest('.mb-2').remove();
            }
        });

        // Handle other document removal
        $('.remove-other-doc').on('click', function() {
            var docIndex = $(this).data('doc-index');
            if (confirm('Are you sure you want to remove this document?')) {
                var currentRemoved = $('#removed_documents').val();
                var removedList = currentRemoved ? currentRemoved.split(',') : [];
                removedList.push('other_doc_' + docIndex);
                $('#removed_documents').val(removedList.join(','));
                $(this).closest('.existing-other-doc-wrapper').remove();
            }
        });

        // Custom Dropzone implementation
        var dropzone = $('#fm-rt-center-images-dropzone');
        var fileInput = $('#fm_rt_center_images_input');
        var filePreviews = [];
        var removedImages = [];

        // Handle existing image removal
        $('.remove-existing-image').on('click', function() {
            var wrapper = $(this).closest('.existing-image-wrapper');
            var imageName = wrapper.data('image-name');
            removedImages.push(imageName);
            wrapper.remove();
            updateRemovedImagesInput();
        });

        function updateRemovedImagesInput() {
            $('#removed_images').val(removedImages.join(','));
        }

        // Handle clicks on dropzone - but not on previews
        dropzone.on('click', function(e) {
            var clickedElement = $(e.target);
            
            // Don't trigger if clicking on preview image or remove button
            if (clickedElement.closest('.dz-preview').length > 0 || 
                clickedElement.hasClass('dz-remove') || 
                clickedElement.closest('.dz-remove').length > 0) {
                return true;
            }
            
            // If clicking on the label or its children, let the label handle it naturally
            if (clickedElement.closest('label[for="fm_rt_center_images_input"]').length > 0) {
                return true; // Let the label handle the click
            }
            
            // For other areas of dropzone, trigger file input
            var input = document.getElementById('fm_rt_center_images_input');
            if (input) {
                input.click();
            }
            return false;
        });

        // Handle file selection
        fileInput.on('change', function(e) {
            var files = Array.from(this.files || []);
            if (files.length === 0) return;
            
            var existingFileNames = filePreviews.map(function(item) {
                return item.file.name + '_' + item.file.size;
            });
            
            // Check file sizes (2MB = 2 * 1024 * 1024 bytes)
            var maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            files.forEach(function(file) {
                // Check file size
                if (file.size > maxSize) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File "' + file.name + '" is too large. Maximum size is 2MB.');
                    } else {
                        alert('File "' + file.name + '" is too large. Maximum size is 2MB.');
                    }
                    return;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File "' + file.name + '" is not an image file.');
                    } else {
                        alert('File "' + file.name + '" is not an image file.');
                    }
                    return;
                }
                
                var fileKey = file.name + '_' + file.size;
                if (existingFileNames.indexOf(fileKey) === -1) {
                    addFilePreview(file);
                }
            });
        });

        // Drag and drop
        dropzone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.addClass('dz-drag-hover');
        });

        dropzone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.removeClass('dz-drag-hover');
        });

        dropzone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.removeClass('dz-drag-hover');
            
            var droppedFiles = Array.from(e.originalEvent.dataTransfer.files);
            var currentFiles = Array.from(fileInput[0].files || []);
            
            // Validate dropped files
            var maxSize = 2 * 1024 * 1024; // 2MB
            var validFiles = [];
            
            droppedFiles.forEach(function(file) {
                // Check file size
                if (file.size > maxSize) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File "' + file.name + '" is too large. Maximum size is 2MB.');
                    } else {
                        alert('File "' + file.name + '" is too large. Maximum size is 2MB.');
                    }
                    return;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File "' + file.name + '" is not an image file.');
                    } else {
                        alert('File "' + file.name + '" is not an image file.');
                    }
                    return;
                }
                
                validFiles.push(file);
            });
            
            if (validFiles.length === 0) return;
            
            var allFiles = currentFiles.concat(validFiles);
            
            // Update file input
            var dataTransfer = new DataTransfer();
            allFiles.forEach(function(file) {
                dataTransfer.items.add(file);
            });
            fileInput[0].files = dataTransfer.files;
            
            // Add previews for dropped files
            validFiles.forEach(function(file) {
                addFilePreview(file);
            });
        });

        function addFilePreview(file) {
            if (!file.type.match('image.*')) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please select image files only');
                } else {
                    alert('Please select image files only');
                }
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                var fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                var preview = $('<div class="dz-preview" data-file-id="' + fileId + '">' +
                    '<div class="dz-image">' +
                    '<img src="' + e.target.result + '" alt="' + file.name + '">' +
                    '</div>' +
                    '<div class="dz-remove" data-file-id="' + fileId + '" data-file-name="' + file.name + '" data-file-size="' + file.size + '">×</div>' +
                    '</div>');
                
                dropzone.find('.dz-message').hide();
                dropzone.append(preview);
                filePreviews.push({id: fileId, file: file});

                // Remove file handler
                preview.find('.dz-remove').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var fileIdToRemove = $(this).data('file-id');
                    removeFile(fileIdToRemove);
                    return false;
                });
            };
            reader.readAsDataURL(file);
        }

        function removeFile(fileId) {
            var preview = dropzone.find('.dz-preview[data-file-id="' + fileId + '"]');
            if (preview.length === 0) return;
            
            var fileName = preview.find('.dz-remove').data('file-name');
            var fileSize = preview.find('.dz-remove').data('file-size');
            
            // Remove from filePreviews
            filePreviews = filePreviews.filter(function(item) {
                return item.id !== fileId;
            });
            
            // Update file input - remove the file
            try {
                var dataTransfer = new DataTransfer();
                Array.from(fileInput[0].files || []).forEach(function(file) {
                    if (!(file.name === fileName && file.size === fileSize)) {
                        dataTransfer.items.add(file);
                    }
                });
                fileInput[0].files = dataTransfer.files;
            } catch(err) {
                // Fallback: just clear and re-add remaining files
                fileInput.val('');
            }
            
            preview.remove();
            
            if (dropzone.find('.dz-preview').length === 0 && $('#existing-images .existing-image-wrapper').length === 0) {
                dropzone.find('.dz-message').show();
            }
        }

        // Reset form
        $('#reset_btn').click(function(){
            $('form')[0].reset();
            $('.js-select2-custom').val(null).trigger('change');
            dropzone.find('.dz-preview').remove();
            dropzone.find('.dz-message').show();
            filePreviews = [];
            removedImages = [];
            fileInput.val('');
            $('#removed_images').val('');
            // Reset validation
            var form = $('#fm-rt-center-form');
            if (form.data('validator')) {
                form.data('validator').resetForm();
            }
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.is-valid').removeClass('is-valid');
            form.find('.invalid-feedback').remove();
        });

        // Initialize jQuery Validation using HSValidation component
        if (typeof $.HSCore !== 'undefined' && $.HSCore.components.HSValidation) {
            $.HSCore.components.HSValidation.init($('#fm-rt-center-form'), {
                rules: {
                    center_name: {
                        required: true,
                        maxlength: 191
                    },
                    pincode: {
                        required: true,
                        digits: true,
                        minlength: 6,
                        maxlength: 6
                    },
                    full_address: {
                        required: true
                    },
                    email: {
                        email: true,
                        maxlength: 191
                    },
                    mobile_number: {
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    },
                    latitude: {
                        required: true,
                        maxlength: 50
                    },
                    longitude: {
                        required: true,
                        maxlength: 50
                    }
                },
                messages: {
                    center_name: {
                        required: "Center name is required",
                        maxlength: "Center name must not exceed 191 characters"
                    },
                    pincode: {
                        required: "Pincode is required",
                        digits: "Pincode must contain only digits",
                        minlength: "Pincode must be exactly 6 digits",
                        maxlength: "Pincode must be exactly 6 digits"
                    },
                    full_address: {
                        required: "Full address is required"
                    },
                    email: {
                        email: "Please enter a valid email address",
                        maxlength: "Email must not exceed 191 characters"
                    },
                    mobile_number: {
                        digits: "Mobile number must contain only digits",
                        minlength: "Mobile number must be exactly 10 digits",
                        maxlength: "Mobile number must be exactly 10 digits"
                    },
                    latitude: {
                        required: "Latitude is required",
                        maxlength: "Latitude must not exceed 50 characters"
                    },
                    longitude: {
                        required: "Longitude is required",
                        maxlength: "Longitude must not exceed 50 characters"
                    }
                },
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback d-block');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                    // Hide loader when validation error is shown
                    if (typeof PageLoader !== 'undefined') {
                        PageLoader.hide();
                    }
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                invalidHandler: function(event, validator) {
                    // Hide loader when form validation fails
                    if (typeof PageLoader !== 'undefined') {
                        PageLoader.hide();
                    }
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        $('html, body').animate({
                            scrollTop: firstInvalidElement.offset().top - 100
                        }, 500);
                        firstInvalidElement.focus();
                    }
                },
                submitHandler: function(form) {
                    // This will only be called if validation passes
                    if (typeof PageLoader !== 'undefined') {
                        PageLoader.show();
                    }
                    // Allow form to submit normally
                    return true;
                }
            });
        }
        
        // Ensure validation runs on submit
        $('#fm-rt-center-form').on('submit', function(e) {
            var form = $(this);
            
            // Check if form is valid - this will trigger validation if not already done
            if (form.length) {
                if (!form.valid()) {
                    // Validation failed - invalidHandler will be called
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endpush
