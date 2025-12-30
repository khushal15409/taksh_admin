<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LmCenter;
use App\Models\Pincode;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;

class LmCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get all LM centers for client-side DataTable
        $lmCenters = LmCenter::with(['zone', 'pincodes'])
            ->latest()
            ->get();
        
        return view('admin-views.logistics.lm-center.index', compact('lmCenters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all pincodes
        $pincodes = Pincode::orderBy('pincode')->get();
        
        // Get all already mapped pincode IDs (pincodes that are mapped to any LM Center)
        $mappedPincodeIds = \DB::table('lm_center_pincode')->pluck('pincode_id')->unique()->toArray();
        
        return view('admin-views.logistics.lm-center.create', compact('pincodes', 'mappedPincodeIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'center_name' => 'required|max:191',
            'full_address' => 'required',
            'pincode' => 'required|digits:6',
            'latitude' => 'required|max:50',
            'longitude' => 'required|max:50',
            'owner_name' => 'required|max:191',
            'owner_address' => 'required',
            'owner_pincode' => 'required|digits:6',
            'owner_latitude' => 'required|max:50',
            'owner_longitude' => 'required|max:50',
            'owner_mobile' => 'required|digits:10',
            'owner_email' => 'required|email|max:191',
            'bank_name' => 'required|max:191',
            'bank_account_number' => 'required|max:191',
            'bank_ifsc_code' => 'required|max:50',
            'bank_branch' => 'required|max:191',
            'bank_holder_name' => 'required|max:191',
            'bank_document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'aadhaar_card' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'aadhaar_number' => 'required|string|max:20',
            'pan_card' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'pan_card_number' => 'required|string|max:20',
        ], [
            'center_name.required' => translate('messages.Center Name is required!'),
            'full_address.required' => translate('messages.Full Address is required!'),
            'pincode.required' => translate('messages.Pincode is required!'),
            'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
            'latitude.required' => translate('messages.Latitude is required!'),
            'longitude.required' => translate('messages.Longitude is required!'),
            'owner_name.required' => translate('messages.Owner Name is required!'),
            'owner_address.required' => translate('messages.Owner Address is required!'),
            'owner_pincode.required' => translate('messages.Owner Pincode is required!'),
            'owner_pincode.digits' => translate('messages.Owner Pincode must be exactly 6 digits!'),
            'owner_latitude.required' => translate('messages.Owner Latitude is required!'),
            'owner_longitude.required' => translate('messages.Owner Longitude is required!'),
            'owner_mobile.required' => translate('messages.Owner Mobile is required!'),
            'owner_mobile.digits' => translate('messages.Owner Mobile number must be exactly 10 digits!'),
            'owner_email.required' => translate('messages.Owner Email is required!'),
            'owner_email.email' => translate('messages.Please provide a valid owner email address!'),
            'bank_name.required' => translate('messages.Bank Name is required!'),
            'bank_account_number.required' => translate('messages.Bank Account Number is required!'),
            'bank_ifsc_code.required' => translate('messages.Bank IFSC Code is required!'),
            'bank_branch.required' => translate('messages.Bank Branch is required!'),
            'bank_holder_name.required' => translate('messages.Bank Account Holder Name is required!'),
            'bank_document.required' => translate('messages.Bank Document is required!'),
            'aadhaar_card.required' => translate('messages.Aadhaar Card is required!'),
            'aadhaar_number.required' => translate('messages.Aadhaar Number is required!'),
            'pan_card.required' => translate('messages.PAN Card is required!'),
            'pan_card_number.required' => translate('messages.PAN Card Number is required!'),
        ]);

        $lmCenter = new LmCenter();
        $lmCenter->center_name = $request->center_name;
        $lmCenter->full_address = $request->full_address;
        $lmCenter->location = $request->location;
        $lmCenter->pincode = $request->pincode;
        $lmCenter->latitude = $request->latitude;
        $lmCenter->longitude = $request->longitude;
        $lmCenter->owner_name = $request->owner_name;
        $lmCenter->owner_address = $request->owner_address;
        $lmCenter->owner_pincode = $request->owner_pincode;
        $lmCenter->owner_latitude = $request->owner_latitude;
        $lmCenter->owner_longitude = $request->owner_longitude;
        $lmCenter->owner_mobile = $request->owner_mobile;
        $lmCenter->owner_email = $request->owner_email;
        $lmCenter->owner_id = $request->owner_id;
        $lmCenter->bank_name = $request->bank_name;
        $lmCenter->bank_account_number = $request->bank_account_number;
        $lmCenter->bank_ifsc_code = $request->bank_ifsc_code;
        $lmCenter->bank_branch = $request->bank_branch;
        $lmCenter->bank_holder_name = $request->bank_holder_name;
        $lmCenter->email = $request->email ?? null;
        $lmCenter->mobile_number = $request->mobile_number ?? null;
        $lmCenter->state = $request->state;
        $lmCenter->city = $request->city;
        $lmCenter->zone_id = null;
        $lmCenter->status = 1;
        
        // Handle document uploads
        if ($request->hasFile('aadhaar_card')) {
            $file = $request->file('aadhaar_card');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->aadhaar_card = Helpers::upload('lm-center/documents', $extension, $file);
        }
        if ($request->hasFile('pan_card')) {
            $file = $request->file('pan_card');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->pan_card = Helpers::upload('lm-center/documents', $extension, $file);
        }
        if ($request->hasFile('bank_document')) {
            $file = $request->file('bank_document');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->bank_document = Helpers::upload('lm-center/documents', $extension, $file);
        }
        // Store Aadhar and PAN numbers
        $lmCenter->aadhaar_number = $request->aadhaar_number;
        $lmCenter->pan_card_number = $request->pan_card_number;
        
        $lmCenter->save();

        // Sync Pincode mappings
        if ($request->has('pincode_ids')) {
            $lmCenter->pincodes()->sync($request->pincode_ids);
        }

        Toastr::success(translate('messages.lm_center_added_successfully'));
        return redirect()->route('admin.logistics.lm-center.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lmCenter = LmCenter::with(['pincodes'])->findOrFail($id);
        
        // Get all pincodes
        $pincodes = Pincode::orderBy('pincode')->get();
        
        // Get all already mapped pincode IDs (pincodes that are mapped to other LM Centers, excluding current one)
        $mappedPincodeIds = \DB::table('lm_center_pincode')
            ->where('lm_center_id', '!=', $id)
            ->pluck('pincode_id')
            ->unique()
            ->toArray();
        
        return view('admin-views.logistics.lm-center.edit', compact('lmCenter', 'pincodes', 'mappedPincodeIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $lmCenter = LmCenter::findOrFail($id);
        
        $request->validate([
            'center_name' => 'required|max:191',
            'full_address' => 'required',
            'pincode' => 'required|digits:6',
            'latitude' => 'required|max:50',
            'longitude' => 'required|max:50',
            'owner_name' => 'required|max:191',
            'owner_address' => 'required',
            'owner_pincode' => 'required|digits:6',
            'owner_latitude' => 'required|max:50',
            'owner_longitude' => 'required|max:50',
            'owner_mobile' => 'required|digits:10',
            'owner_email' => 'required|email|max:191',
            'bank_name' => 'required|max:191',
            'bank_account_number' => 'required|max:191',
            'bank_ifsc_code' => 'required|max:50',
            'bank_branch' => 'required|max:191',
            'bank_holder_name' => 'required|max:191',
            'bank_document' => (!empty($lmCenter->bank_document) && !$request->hasFile('bank_document') && $request->remove_bank_document != '1') ? 'nullable' : 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'aadhaar_card' => (!empty($lmCenter->aadhaar_card) && !$request->hasFile('aadhaar_card')) ? 'nullable' : 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'aadhaar_number' => 'required|string|max:20',
            'pan_card' => (!empty($lmCenter->pan_card) && !$request->hasFile('pan_card')) ? 'nullable' : 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'pan_card_number' => 'required|string|max:20',
        ], [
            'center_name.required' => translate('messages.Center Name is required!'),
            'full_address.required' => translate('messages.Full Address is required!'),
            'pincode.required' => translate('messages.Pincode is required!'),
            'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
            'latitude.required' => translate('messages.Latitude is required!'),
            'longitude.required' => translate('messages.Longitude is required!'),
            'owner_name.required' => translate('messages.Owner Name is required!'),
            'owner_address.required' => translate('messages.Owner Address is required!'),
            'owner_pincode.required' => translate('messages.Owner Pincode is required!'),
            'owner_pincode.digits' => translate('messages.Owner Pincode must be exactly 6 digits!'),
            'owner_latitude.required' => translate('messages.Owner Latitude is required!'),
            'owner_longitude.required' => translate('messages.Owner Longitude is required!'),
            'owner_mobile.required' => translate('messages.Owner Mobile is required!'),
            'owner_mobile.digits' => translate('messages.Owner Mobile number must be exactly 10 digits!'),
            'owner_email.required' => translate('messages.Owner Email is required!'),
            'owner_email.email' => translate('messages.Please provide a valid owner email address!'),
            'bank_name.required' => translate('messages.Bank Name is required!'),
            'bank_account_number.required' => translate('messages.Bank Account Number is required!'),
            'bank_ifsc_code.required' => translate('messages.Bank IFSC Code is required!'),
            'bank_branch.required' => translate('messages.Bank Branch is required!'),
            'bank_holder_name.required' => translate('messages.Bank Account Holder Name is required!'),
            'bank_document.required' => translate('messages.Bank Document is required!'),
            'aadhaar_card.required' => translate('messages.Aadhaar Card is required!'),
            'aadhaar_number.required' => translate('messages.Aadhaar Number is required!'),
            'pan_card.required' => translate('messages.PAN Card is required!'),
            'pan_card_number.required' => translate('messages.PAN Card Number is required!'),
        ]);

        $lmCenter->center_name = $request->center_name;
        $lmCenter->full_address = $request->full_address;
        $lmCenter->location = $request->location;
        $lmCenter->pincode = $request->pincode;
        $lmCenter->latitude = $request->latitude;
        $lmCenter->longitude = $request->longitude;
        $lmCenter->owner_name = $request->owner_name;
        $lmCenter->owner_address = $request->owner_address;
        $lmCenter->owner_pincode = $request->owner_pincode;
        $lmCenter->owner_latitude = $request->owner_latitude;
        $lmCenter->owner_longitude = $request->owner_longitude;
        $lmCenter->owner_mobile = $request->owner_mobile;
        $lmCenter->owner_email = $request->owner_email;
        $lmCenter->owner_id = $request->owner_id;
        $lmCenter->bank_name = $request->bank_name;
        $lmCenter->bank_account_number = $request->bank_account_number;
        $lmCenter->bank_ifsc_code = $request->bank_ifsc_code;
        $lmCenter->bank_branch = $request->bank_branch;
        $lmCenter->bank_holder_name = $request->bank_holder_name;
        $lmCenter->email = $request->email ?? null;
        $lmCenter->mobile_number = $request->mobile_number ?? null;
        $lmCenter->state = $request->state;
        $lmCenter->city = $request->city;
        
        // Handle document uploads
        if ($request->hasFile('aadhaar_card')) {
            if ($lmCenter->aadhaar_card) {
                try {
                    Storage::disk(Helpers::getDisk())->delete('lm-center/documents/' . $lmCenter->aadhaar_card);
                } catch (\Exception $e) {}
            }
            $file = $request->file('aadhaar_card');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->aadhaar_card = Helpers::upload('lm-center/documents', $extension, $file);
        }
        if ($request->hasFile('pan_card')) {
            if ($lmCenter->pan_card) {
                try {
                    Storage::disk(Helpers::getDisk())->delete('lm-center/documents/' . $lmCenter->pan_card);
                } catch (\Exception $e) {}
            }
            $file = $request->file('pan_card');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->pan_card = Helpers::upload('lm-center/documents', $extension, $file);
        }
        if ($request->hasFile('bank_document')) {
            if ($lmCenter->bank_document) {
                try {
                    Storage::disk(Helpers::getDisk())->delete('lm-center/documents/' . $lmCenter->bank_document);
                } catch (\Exception $e) {}
            }
            $file = $request->file('bank_document');
            $extension = $file->getClientOriginalExtension();
            $lmCenter->bank_document = Helpers::upload('lm-center/documents', $extension, $file);
        }
        // Store Aadhar and PAN numbers
        $lmCenter->aadhaar_number = $request->aadhaar_number;
        $lmCenter->pan_card_number = $request->pan_card_number;
        
        $lmCenter->save();

        // Sync Pincode mappings
        $lmCenter->pincodes()->sync($request->pincode_ids ?? []);

        Toastr::success(translate('messages.lm_center_updated_successfully'));
        return redirect()->route('admin.logistics.lm-center.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $lmCenter = LmCenter::findOrFail($id);
        $lmCenter->delete();
        Toastr::success(translate('messages.lm_center_deleted_successfully'));
        return back();
    }

    /**
     * Update status
     */
    public function status(Request $request)
    {
        $lmCenter = LmCenter::findOrFail($request->id);
        $lmCenter->status = $request->status;
        $lmCenter->save();
        Toastr::success(translate('messages.lm_center_status_updated'));
        return back();
    }

    /**
     * Verify Aadhaar or PAN Card
     */
    public function verifyDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|in:aadhaar,pan',
            'number' => 'required|string|max:20',
        ]);

        $type = $request->type;
        $number = $request->number;

        // TODO: Replace this with actual third-party API call
        // Example structure for third-party API integration:
        /*
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.verification-service.com/verify', [
                'json' => [
                    'type' => $type,
                    'number' => $number,
                ],
                'headers' => [
                    'Authorization' => 'Bearer YOUR_API_KEY',
                    'Content-Type' => 'application/json',
                ],
            ]);
            
            $result = json_decode($response->getBody()->getContents(), true);
            
            if ($result['status'] === 'success' && $result['verified'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($type) . ' verified successfully',
                    'data' => $result['data'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($type) . ' verification failed',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification service error: ' . $e->getMessage(),
            ], 500);
        }
        */

        // Placeholder: Simulate API verification
        // Remove this and use actual API above
        $isValid = false;
        if ($type === 'aadhaar') {
            // Aadhaar validation: 12 digits
            $isValid = preg_match('/^\d{12}$/', $number);
        } elseif ($type === 'pan') {
            // PAN validation: 5 letters, 4 digits, 1 letter (e.g., ABCDE1234F)
            $isValid = preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($number));
        }

        if ($isValid) {
            // Simulate API delay
            sleep(1);
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' verified successfully',
                'verified' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ' . $type . ' number format',
            ], 400);
        }
    }
}

