@extends('layouts.admin.app')

@section('title','Update LM Center')

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
    .form-control.is-valid {
        border-color: #28a745;
    }
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
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
                    Update LM Center
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form action="{{route('admin.logistics.lm-center.update',[$lmCenter['id']])}}" method="post" enctype="multipart/form-data" id="lm-center-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Left Card: Form Fields (col-md-8) -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">LM Center Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Section 1: Center Information -->
                                <div class="col-12">
                                    <h6 class="mb-3">Center Information</h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="center_name">Center Name <span class="text-danger">*</span></label>
                                        <input type="text" name="center_name" id="center_name" class="form-control" placeholder="Enter center name" value="{{old('center_name', $lmCenter->center_name)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="pincode">Pincode <span class="text-danger">*</span></label>
                                        <input type="text" name="pincode" id="pincode" class="form-control js-masked-input" placeholder="XXXXXX" value="{{old('pincode', $lmCenter->pincode)}}" required maxlength="6" pattern="[0-9]{6}" data-hs-mask-options='{"template": "000000"}' title="Please enter exactly 6 digits">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="latitude">Latitude <span class="text-danger">*</span></label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Enter latitude" value="{{old('latitude', $lmCenter->latitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="longitude">Longitude <span class="text-danger">*</span></label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Enter longitude" value="{{old('longitude', $lmCenter->longitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="input-label" for="full_address">Address <span class="text-danger">*</span></label>
                                        <textarea name="full_address" id="full_address" class="form-control" rows="3" placeholder="Enter full address" required>{{old('full_address', $lmCenter->full_address)}}</textarea>
                                    </div>
                                </div>

                                <!-- Section 2: Owner Information -->
                                <div class="col-12">
                                    <h6 class="mt-3 mb-3">Owner Information</h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_name">Owner Name <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_name" id="owner_name" class="form-control" placeholder="Enter owner name" value="{{old('owner_name', $lmCenter->owner_name)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_pincode">Owner Pincode <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_pincode" id="owner_pincode" class="form-control js-masked-input" placeholder="XXXXXX" value="{{old('owner_pincode', $lmCenter->owner_pincode)}}" required maxlength="6" pattern="[0-9]{6}" data-hs-mask-options='{"template": "000000"}' title="Please enter exactly 6 digits">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_address">Owner Address <span class="text-danger">*</span></label>
                                        <textarea name="owner_address" id="owner_address" class="form-control" rows="3" placeholder="Enter owner address" required>{{old('owner_address', $lmCenter->owner_address)}}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_latitude">Owner Latitude <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_latitude" id="owner_latitude" class="form-control" placeholder="Enter owner latitude" value="{{old('owner_latitude', $lmCenter->owner_latitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_longitude">Owner Longitude <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_longitude" id="owner_longitude" class="form-control" placeholder="Enter owner longitude" value="{{old('owner_longitude', $lmCenter->owner_longitude)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_mobile">Owner Mobile <span class="text-danger">*</span></label>
                                        <input type="text" name="owner_mobile" id="owner_mobile" class="form-control js-masked-input" placeholder="XXXXXXXXXX" value="{{old('owner_mobile', $lmCenter->owner_mobile)}}" required maxlength="10" pattern="[0-9]{10}" data-hs-mask-options='{"template": "0000000000"}' title="Please enter exactly 10 digits">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="owner_email">Owner Email <span class="text-danger">*</span></label>
                                        <input type="email" name="owner_email" id="owner_email" class="form-control" placeholder="Enter owner email" value="{{old('owner_email', $lmCenter->owner_email)}}" required maxlength="191">
                                    </div>
                                </div>

                                <!-- Section 4: Bank Details -->
                                <div class="col-12">
                                    <h6 class="mt-3 mb-3">Bank Details</h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_name">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control" placeholder="Enter bank name" value="{{old('bank_name', $lmCenter->bank_name)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_account_number">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" placeholder="Enter account number" value="{{old('bank_account_number', $lmCenter->bank_account_number)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_ifsc_code">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_ifsc_code" id="bank_ifsc_code" class="form-control" placeholder="Enter IFSC code" value="{{old('bank_ifsc_code', $lmCenter->bank_ifsc_code)}}" required maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_branch">Branch <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_branch" id="bank_branch" class="form-control" placeholder="Enter branch" value="{{old('bank_branch', $lmCenter->bank_branch)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_holder_name">Account Holder Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_holder_name" id="bank_holder_name" class="form-control" placeholder="Enter account holder name" value="{{old('bank_holder_name', $lmCenter->bank_holder_name)}}" required maxlength="191">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label" for="bank_document">Bank Document <span class="text-danger">*</span></label>
                                        @if($lmCenter->bank_document)
                                            <div class="mb-2">
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('lm-center/documents', $lmCenter->bank_document, 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="tio-download"></i> View Current File
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeBankDocument()">
                                                    <i class="tio-delete"></i> Remove
                                                </button>
                                            </div>
                                        @endif
                                        <input type="file" name="bank_document" id="bank_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{!$lmCenter->bank_document ? 'required' : ''}}>
                                        <input type="hidden" name="existing_bank_document" value="{{$lmCenter->bank_document ?? ''}}">
                                        <small class="text-muted">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 10MB)</small>
                                        <input type="hidden" name="remove_bank_document" id="remove_bank_document" value="0">
                                    </div>
                                </div>

                                <input type="hidden" name="location" id="location" value="{{old('location', $lmCenter->location)}}">
                                <input type="hidden" name="state" id="state" value="{{old('state', $lmCenter->state)}}">
                                <input type="hidden" name="city" id="city" value="{{old('city', $lmCenter->city)}}">
                                <input type="hidden" name="email" id="email" value="{{old('email', $lmCenter->email)}}">
                                <input type="hidden" name="mobile_number" id="mobile_number" value="{{old('mobile_number', $lmCenter->mobile_number)}}">
                                <input type="hidden" name="owner_id" id="owner_id" value="{{old('owner_id', $lmCenter->owner_id)}}">
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
                
                <!-- Right Card: Documents and Mapping (col-md-4) -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Documents & Mapping</h5>
                        </div>
                        <div class="card-body">
                            <!-- Section 3: Documents -->
                            <div class="form-group">
                                <h6 class="mb-3">Documents</h6>
                                
                                <!-- Aadhar and PAN Numbers in Same Row -->
                                <div class="form-group">
                                    <label class="input-label" for="aadhaar_number">Aadhar Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="aadhaar_number" id="aadhaar_number" class="form-control" placeholder="Enter 12-digit Aadhar number" value="{{old('aadhaar_number', $lmCenter->aadhaar_number)}}" required maxlength="12">
                                        <button type="button" class="btn btn-primary" id="verify_aadhaar_btn" disabled>
                                            <i class="tio-checkmark-circle"></i> Verify
                                        </button>
                                    </div>
                                    <small class="text-success d-none mt-1" id="aadhaar_verified_msg">
                                        <i class="tio-checkmark-circle"></i> Aadhar verified successfully
                                    </small>
                                </div>
                                
                                <!-- Aadhar Card File Upload -->
                                <div class="form-group mt-3">
                                    <label class="input-label" for="aadhaar_card">Aadhaar Card Document <span class="text-danger">*</span></label>
                                    @if($lmCenter->aadhaar_card)
                                        <div class="mb-2">
                                            <a href="{{ \App\CentralLogics\Helpers::get_full_url('lm-center/documents', $lmCenter->aadhaar_card, 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="tio-download"></i> View Current File
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" name="aadhaar_card" id="aadhaar_card" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" {{!$lmCenter->aadhaar_card ? 'required' : ''}}>
                                    <input type="hidden" name="existing_aadhaar_card" value="{{$lmCenter->aadhaar_card ?? ''}}">
                                    <small class="text-muted d-block mt-1">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</small>
                                </div>
                                    
                                <div class="form-group">
                                    <label class="input-label" for="pan_card_number">PAN Card Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="pan_card_number" id="pan_card_number" class="form-control" placeholder="Enter PAN card number" value="{{old('pan_card_number', $lmCenter->pan_card_number)}}" required maxlength="10" style="text-transform: uppercase;">
                                        <button type="button" class="btn btn-primary" id="verify_pan_btn" disabled>
                                            <i class="tio-checkmark-circle"></i> Verify
                                        </button>
                                    </div>
                                    <small class="text-success d-none mt-1" id="pan_verified_msg">
                                        <i class="tio-checkmark-circle"></i> PAN verified successfully
                                    </small>
                                </div>

                                <!-- PAN Card File Upload -->
                                <div class="form-group mt-3">
                                    <label class="input-label" for="pan_card">PAN Card Document <span class="text-danger">*</span></label>
                                    @if($lmCenter->pan_card)
                                        <div class="mb-2">
                                            <a href="{{ \App\CentralLogics\Helpers::get_full_url('lm-center/documents', $lmCenter->pan_card, 'public') }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="tio-download"></i> View Current File
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" name="pan_card" id="pan_card" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" {{!$lmCenter->pan_card ? 'required' : ''}}>
                                    <input type="hidden" name="existing_pan_card" value="{{$lmCenter->pan_card ?? ''}}">
                                    <small class="text-muted d-block mt-1">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</small>
                                </div>
                                    @if($lmCenter->pan_card)
                                        <div class="mt-2">
                                            <small class="text-info">Current: 
                                                <a href="{{ \App\CentralLogics\Helpers::get_full_url('lm-center/documents', $lmCenter->pan_card, 'public') }}" target="_blank" class="text-primary">
                                                    <i class="tio-download"></i> View
                                                </a>
                                            </small>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</small>
                                    <small class="text-info d-block mt-1">Please verify PAN number first to enable file upload</small>
                                </div>
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
                                                $isSelected = in_array($pincode->id, old('pincode_ids', $lmCenter->pincodes->pluck('id')->toArray()));
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
                                    <small class="text-muted d-block mt-1">Pincodes already mapped to other LM Centers are disabled</small>
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
    function removeBankDocument() {
        if (confirm('Are you sure you want to remove the bank document?')) {
            $('#remove_bank_document').val('1');
            $('.btn-danger').closest('.mb-2').remove();
            $('#bank_document').val('');
        }
    }
    
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
                    toastr.warning('This pincode is already mapped to another LM Center and cannot be selected.');
                } else {
                    alert('This pincode is already mapped to another LM Center and cannot be selected.');
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
        $('#pincode, #owner_pincode').on('input', function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            $(this).val(value);
        });

        // Additional validation for mobile number (10 digits only)
        $('#owner_mobile').on('input', function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            $(this).val(value);
        });

        // Aadhar number validation (12 digits)
        var aadhaarVerified = {{ $lmCenter->aadhaar_verified ? 'true' : 'false' }};
        var aadhaarNumberInput = $('#aadhaar_number');
        var verifyAadhaarBtn = $('#verify_aadhaar_btn');
        var aadhaarCardInput = $('#aadhaar_card');
        var aadhaarVerifiedMsg = $('#aadhaar_verified_msg');
        
        function validateAadhaar() {
            if (aadhaarNumberInput.length === 0) return;
            
            var value = aadhaarNumberInput.val().replace(/\D/g, '');
            if (value.length > 12) {
                value = value.substring(0, 12);
            }
            aadhaarNumberInput.val(value);
            
            // Remove previous validation classes
            aadhaarNumberInput.removeClass('is-valid is-invalid');
            
            // Enable verify button if valid (12 digits)
            if (value.length === 12) {
                verifyAadhaarBtn.prop('disabled', false);
                aadhaarNumberInput.addClass('is-valid');
                if (!aadhaarVerified) {
                    aadhaarVerifiedMsg.addClass('d-none');
                    // Keep file input enabled since it's required (or allow if existing file)
                    aadhaarCardInput.prop('disabled', false);
                }
            } else if (value.length > 0) {
                verifyAadhaarBtn.prop('disabled', true);
                aadhaarNumberInput.addClass('is-invalid');
                aadhaarVerified = false;
                aadhaarVerifiedMsg.addClass('d-none');
                // Keep file input enabled since it's required (or allow if existing file)
                aadhaarCardInput.prop('disabled', false);
            } else {
                verifyAadhaarBtn.prop('disabled', true);
                aadhaarNumberInput.removeClass('is-valid is-invalid');
                if (!aadhaarVerified) {
                    aadhaarVerifiedMsg.addClass('d-none');
                    // Keep file input enabled since it's required (or allow if existing file)
                    aadhaarCardInput.prop('disabled', false);
                }
            }
        }
        
        // Bind events with proper check
        if (aadhaarNumberInput.length > 0) {
            aadhaarNumberInput.on('input', validateAadhaar);
            aadhaarNumberInput.on('blur', validateAadhaar);
            aadhaarNumberInput.on('keyup', validateAadhaar);
            
            // Initialize validation on page load
            setTimeout(function() {
                if (aadhaarNumberInput.val().length > 0) {
                    validateAadhaar();
                }
            }, 100);
        }

        // PAN card number validation (5 letters, 4 digits, 1 letter)
        var panVerified = {{ $lmCenter->pan_verified ? 'true' : 'false' }};
        var panCardNumberInput = $('#pan_card_number');
        var verifyPanBtn = $('#verify_pan_btn');
        var panCardInput = $('#pan_card');
        var panVerifiedMsg = $('#pan_verified_msg');
        
        function validatePAN() {
            if (panCardNumberInput.length === 0) return;
            
            var value = panCardNumberInput.val().toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            panCardNumberInput.val(value);
            
            // Remove previous validation classes
            panCardNumberInput.removeClass('is-valid is-invalid');
            
            // Enable verify button if valid format (10 characters)
            var panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
            if (value.length === 10 && panPattern.test(value)) {
                verifyPanBtn.prop('disabled', false);
                panCardNumberInput.addClass('is-valid');
                if (!panVerified) {
                    panVerifiedMsg.addClass('d-none');
                    // Keep file input enabled since it's required (or allow if existing file)
                    panCardInput.prop('disabled', false);
                }
            } else if (value.length > 0) {
                verifyPanBtn.prop('disabled', true);
                panCardNumberInput.addClass('is-invalid');
                panVerified = false;
                panVerifiedMsg.addClass('d-none');
                // Keep file input enabled since it's required (or allow if existing file)
                panCardInput.prop('disabled', false);
            } else {
                verifyPanBtn.prop('disabled', true);
                panCardNumberInput.removeClass('is-valid is-invalid');
                if (!panVerified) {
                    panVerifiedMsg.addClass('d-none');
                    // Keep file input enabled since it's required (or allow if existing file)
                    panCardInput.prop('disabled', false);
                }
            }
        }
        
        // Bind events with proper check
        if (panCardNumberInput.length > 0) {
            panCardNumberInput.on('input', validatePAN);
            panCardNumberInput.on('blur', validatePAN);
            panCardNumberInput.on('keyup', validatePAN);
            
            // Initialize validation on page load
            setTimeout(function() {
                if (panCardNumberInput.val().length > 0) {
                    validatePAN();
                }
            }, 100);
        }

        // Initialize verification status on page load
        @if($lmCenter->aadhaar_verified && $lmCenter->aadhaar_number)
            if ($('#aadhaar_number').val().length === 12) {
                $('#aadhaar_verified_msg').removeClass('d-none');
                // Keep file input enabled since it's required
                $('#aadhaar_card').prop('disabled', false);
                $('#verify_aadhaar_btn').html('<i class="tio-checkmark-circle"></i> Verified').removeClass('btn-primary').addClass('btn-success');
            }
        @endif

        @if($lmCenter->pan_verified && $lmCenter->pan_card_number)
            if ($('#pan_card_number').val().length === 10) {
                $('#pan_verified_msg').removeClass('d-none');
                // Keep file input enabled since it's required
                $('#pan_card').prop('disabled', false);
                $('#verify_pan_btn').html('<i class="tio-checkmark-circle"></i> Verified').removeClass('btn-primary').addClass('btn-success');
            }
        @endif
        
        // Ensure file inputs are always enabled since they're required
        $('#aadhaar_card').prop('disabled', false);
        $('#pan_card').prop('disabled', false);

        // Verify Aadhar
        $('#verify_aadhaar_btn').on('click', function() {
            var aadhaarNumber = $('#aadhaar_number').val();
            if (!aadhaarNumber || aadhaarNumber.length !== 12) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter a valid 12-digit Aadhar number');
                } else {
                    alert('Please enter a valid 12-digit Aadhar number');
                }
                return;
            }

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="tio-sync"></i> Verifying...');

            $.ajax({
                url: '{{ route("admin.logistics.lm-center.verify-document") }}',
                method: 'POST',
                data: {
                    type: 'aadhaar',
                    number: aadhaarNumber,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        aadhaarVerified = true;
                        aadhaarNumberInput.addClass('is-valid').removeClass('is-invalid');
                        aadhaarVerifiedMsg.removeClass('d-none');
                        aadhaarCardInput.prop('disabled', false);
                        $btn.html('<i class="tio-checkmark-circle"></i> Verified').removeClass('btn-primary').addClass('btn-success');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        }
                    } else {
                        aadhaarVerified = false;
                        aadhaarNumberInput.addClass('is-invalid').removeClass('is-valid');
                        $btn.prop('disabled', false).html(originalText);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Verification failed');
                        } else {
                            alert(response.message || 'Verification failed');
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Verification failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });

        // Verify PAN
        $('#verify_pan_btn').on('click', function() {
            var panNumber = $('#pan_card_number').val();
            var panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
            if (!panNumber || !panPattern.test(panNumber)) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter a valid PAN card number (e.g., ABCDE1234F)');
                } else {
                    alert('Please enter a valid PAN card number (e.g., ABCDE1234F)');
                }
                return;
            }

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="tio-sync"></i> Verifying...');

            $.ajax({
                url: '{{ route("admin.logistics.lm-center.verify-document") }}',
                method: 'POST',
                data: {
                    type: 'pan',
                    number: panNumber,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        panVerified = true;
                        panCardNumberInput.addClass('is-valid').removeClass('is-invalid');
                        panVerifiedMsg.removeClass('d-none');
                        panCardInput.prop('disabled', false);
                        $btn.html('<i class="tio-checkmark-circle"></i> Verified').removeClass('btn-primary').addClass('btn-success');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        }
                    } else {
                        panVerified = false;
                        panCardNumberInput.addClass('is-invalid').removeClass('is-valid');
                        $btn.prop('disabled', false).html(originalText);
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Verification failed');
                        } else {
                            alert(response.message || 'Verification failed');
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    var errorMsg = 'Verification failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });

        // File size validation for document uploads
        $('#aadhaar_card, #pan_card').on('change', function() {
            var file = this.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('File size must be less than 10MB.');
                    } else {
                        alert('File size must be less than 10MB.');
                    }
                    $(this).val('');
                }
            }
        });

        // Reset form
        $('#reset_btn').click(function(){
            $('form')[0].reset();
            $('.js-select2-custom').val(null).trigger('change');
        });
        
        // Initialize jQuery Validation using HSValidation component
        if (typeof $.HSCore !== 'undefined' && $.HSCore.components.HSValidation) {
            $.HSCore.components.HSValidation.init($('#lm-center-form'), {
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
                    latitude: {
                        required: true,
                        maxlength: 50
                    },
                    longitude: {
                        required: true,
                        maxlength: 50
                    },
                    owner_name: {
                        required: true,
                        maxlength: 191
                    },
                    owner_address: {
                        required: true
                    },
                    owner_pincode: {
                        required: true,
                        digits: true,
                        minlength: 6,
                        maxlength: 6
                    },
                    owner_latitude: {
                        required: true,
                        maxlength: 50
                    },
                    owner_longitude: {
                        required: true,
                        maxlength: 50
                    },
                    owner_mobile: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 10
                    },
                    owner_email: {
                        required: true,
                        email: true,
                        maxlength: 191
                    },
                    bank_name: {
                        required: true,
                        maxlength: 191
                    },
                    bank_account_number: {
                        required: true,
                        maxlength: 191
                    },
                    bank_ifsc_code: {
                        required: true,
                        maxlength: 50
                    },
                    bank_branch: {
                        required: true,
                        maxlength: 191
                    },
                    bank_holder_name: {
                        required: true,
                        maxlength: 191
                    },
                    aadhaar_number: {
                        required: true,
                        maxlength: 12
                    },
                    pan_card_number: {
                        required: true,
                        maxlength: 10
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
                    latitude: {
                        required: "Latitude is required",
                        maxlength: "Latitude must not exceed 50 characters"
                    },
                    longitude: {
                        required: "Longitude is required",
                        maxlength: "Longitude must not exceed 50 characters"
                    },
                    owner_name: {
                        required: "Owner name is required",
                        maxlength: "Owner name must not exceed 191 characters"
                    },
                    owner_address: {
                        required: "Owner address is required"
                    },
                    owner_pincode: {
                        required: "Owner pincode is required",
                        digits: "Owner pincode must contain only digits",
                        minlength: "Owner pincode must be exactly 6 digits",
                        maxlength: "Owner pincode must be exactly 6 digits"
                    },
                    owner_latitude: {
                        required: "Owner latitude is required",
                        maxlength: "Owner latitude must not exceed 50 characters"
                    },
                    owner_longitude: {
                        required: "Owner longitude is required",
                        maxlength: "Owner longitude must not exceed 50 characters"
                    },
                    owner_mobile: {
                        required: "Owner mobile is required",
                        digits: "Owner mobile must contain only digits",
                        minlength: "Owner mobile must be exactly 10 digits",
                        maxlength: "Owner mobile must be exactly 10 digits"
                    },
                    owner_email: {
                        required: "Owner email is required",
                        email: "Please enter a valid email address",
                        maxlength: "Email must not exceed 191 characters"
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
                }
            });
        }
        
        // Form validation on submit - Custom validation for Aadhar and PAN
        $('#lm-center-form').on('submit', function(e) {
            var isValid = true;
            var firstError = null;
            
            // Validate Aadhar if number is entered
            if (aadhaarNumberInput.length > 0 && aadhaarNumberInput.val().length > 0) {
                // Remove existing error message
                aadhaarNumberInput.closest('.form-group').find('.invalid-feedback').remove();
                
                if (aadhaarNumberInput.val().length !== 12) {
                    e.preventDefault();
                    isValid = false;
                    aadhaarNumberInput.addClass('is-invalid').removeClass('is-valid');
                    var errorMsg = $('<div class="invalid-feedback d-block">Aadhar number must be exactly 12 digits</div>');
                    aadhaarNumberInput.closest('.form-group').append(errorMsg);
                    if (!firstError) {
                        firstError = aadhaarNumberInput;
                    }
                }
                if (!aadhaarVerified && aadhaarCardInput.length > 0 && aadhaarCardInput[0].files.length > 0) {
                    e.preventDefault();
                    isValid = false;
                    aadhaarCardInput.closest('.form-group').find('.invalid-feedback').remove();
                    var errorMsg = $('<div class="invalid-feedback d-block">Please verify Aadhar number before uploading document</div>');
                    aadhaarCardInput.closest('.form-group').append(errorMsg);
                    if (!firstError) {
                        firstError = aadhaarCardInput;
                    }
                }
            }
            
            // Validate PAN if number is entered
            if (panCardNumberInput.length > 0 && panCardNumberInput.val().length > 0) {
                // Remove existing error message
                panCardNumberInput.closest('.form-group').find('.invalid-feedback').remove();
                
                var panPattern = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                if (!panPattern.test(panCardNumberInput.val())) {
                    e.preventDefault();
                    isValid = false;
                    panCardNumberInput.addClass('is-invalid').removeClass('is-valid');
                    var errorMsg = $('<div class="invalid-feedback d-block">Please enter a valid PAN card number (e.g., ABCDE1234F)</div>');
                    panCardNumberInput.closest('.form-group').append(errorMsg);
                    if (!firstError) {
                        firstError = panCardNumberInput;
                    }
                }
                if (!panVerified && panCardInput.length > 0 && panCardInput[0].files.length > 0) {
                    e.preventDefault();
                    isValid = false;
                    panCardInput.closest('.form-group').find('.invalid-feedback').remove();
                    var errorMsg = $('<div class="invalid-feedback d-block">Please verify PAN number before uploading document</div>');
                    panCardInput.closest('.form-group').append(errorMsg);
                    if (!firstError) {
                        firstError = panCardInput;
                    }
                }
            }
            
            // Scroll to first error if any
            if (!isValid && firstError) {
                // Hide loader when validation fails
                if (typeof PageLoader !== 'undefined') {
                    PageLoader.hide();
                }
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                firstError.focus();
                return false;
            }
            
            // Check if HSValidation also has errors
            var form = $('#lm-center-form');
            if (form.length && form.data('validator')) {
                if (!form.valid()) {
                    // Hide loader when HSValidation fails
                    if (typeof PageLoader !== 'undefined') {
                        PageLoader.hide();
                    }
                    return false;
                }
            }
            
            // Show loader on form submission only if all validations pass
            if (isValid && typeof PageLoader !== 'undefined') {
                PageLoader.show();
            }
        });
    });
</script>
@endpush
