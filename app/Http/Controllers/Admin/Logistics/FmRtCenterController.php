<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\FmRtCenter;
use App\Models\Pincode;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;

class FmRtCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get all FM/RT centers for client-side DataTable
        $fmRtCenters = FmRtCenter::with(['zone', 'pincodes'])
            ->latest()
            ->get();
        
        return view('admin-views.logistics.fm-rt-center.index', compact('fmRtCenters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all pincodes
        $pincodes = Pincode::orderBy('pincode')->get();
        
        // Get all already mapped pincode IDs (pincodes that are mapped to any FM/RT Center)
        $mappedPincodeIds = \DB::table('fm_rt_center_pincode')->pluck('pincode_id')->unique()->toArray();
        
        return view('admin-views.logistics.fm-rt-center.create', compact('pincodes', 'mappedPincodeIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Basic validation first
            $request->validate([
                'center_name' => 'required|max:191',
                'full_address' => 'required',
                'pincode' => 'required|digits:6',
                'latitude' => 'required|max:50',
                'longitude' => 'required|max:50',
                'owner_name' => 'required|max:191',
                'owner_address' => 'required',
                'owner_pincode' => 'nullable|digits:6',
                'owner_mobile' => 'nullable|digits:10',
                'owner_email' => 'nullable|email|max:191',
                'aadhaar_card' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'pan_card' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ], [
                'center_name.required' => translate('messages.Center Name is required!'),
                'full_address.required' => translate('messages.Full Address is required!'),
                'pincode.required' => translate('messages.Pincode is required!'),
                'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
                'latitude.required' => translate('messages.Latitude is required!'),
                'longitude.required' => translate('messages.Longitude is required!'),
                'owner_name.required' => translate('messages.Owner Name is required!'),
                'owner_address.required' => translate('messages.Owner Address is required!'),
                'owner_pincode.digits' => translate('messages.Owner Pincode must be exactly 6 digits!'),
                'owner_mobile.digits' => translate('messages.Owner Mobile number must be exactly 10 digits!'),
                'owner_email.email' => translate('messages.Please provide a valid owner email address!'),
            ]);

            // Validate images separately to avoid blocking form submission
            if ($request->hasFile('fm_rt_center_images')) {
                $files = $request->file('fm_rt_center_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'fm_rt_center_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'fm_rt_center_images.*.image' => translate('messages.Please upload image files only!'),
                        'fm_rt_center_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'fm_rt_center_images.*.max' => translate('messages.Image size should be less than 2MB!'),
                    ]);
                }
            }

            // Validate documents
            $documentFields = ['rent_agreement', 'permission_letter_local', 'electricity_bill', 'cin', 'gst', 'coi', 'other_document_file'];
            foreach ($documentFields as $field) {
                if ($request->hasFile($field)) {
                    $request->validate([
                        $field => 'mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
                    ], [
                        $field . '.mimes' => translate('messages.Document must be PDF, JPG, PNG, DOC, or DOCX!'),
                        $field . '.max' => translate('messages.Document size should be less than 5MB!'),
                    ]);
                }
            }

            $fmRtCenter = new FmRtCenter();
            $fmRtCenter->center_name = $request->center_name;
            $fmRtCenter->full_address = $request->full_address;
            $fmRtCenter->location = $request->location ?? null;
            $fmRtCenter->pincode = $request->pincode;
            $fmRtCenter->latitude = $request->latitude ?? null;
            $fmRtCenter->longitude = $request->longitude ?? null;
            $fmRtCenter->owner_name = $request->owner_name ?? 'taksh';
            $fmRtCenter->state = $request->state ?? null;
            $fmRtCenter->city = $request->city ?? null;
            $fmRtCenter->email = $request->email ?? null;
            $fmRtCenter->mobile_number = $request->mobile_number ?? null;
            $fmRtCenter->zone_id = null;
            $fmRtCenter->status = 1;

            // Handle image uploads
            $images = [];
            if ($request->hasFile('fm_rt_center_images')) {
                $uploadedFiles = $request->file('fm_rt_center_images');
                
                // Ensure it's an array (Laravel returns array for multiple files)
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }
                
                foreach ($uploadedFiles as $index => $image) {
                    // Check if file exists and is valid
                    if ($image && $image->isValid()) {
                        // Check upload error code
                        if ($image->getError() === UPLOAD_ERR_OK) {
                            try {
                                // Get original extension
                                $extension = $image->getClientOriginalExtension();
                                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                
                                // Use original extension if valid, otherwise default to png
                                $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'png';
                                
                                $imageName = Helpers::upload('fm-rt-center/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                } else {
                                    \Log::warning('Image upload returned default image for fm_rt_center_images[' . $index . ']');
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for fm_rt_center_images[' . $index . ']: ' . $e->getMessage());
                                \Log::error('Stack trace: ' . $e->getTraceAsString());
                                // Continue with other images even if one fails
                            }
                        } else {
                            \Log::warning('Upload error code for fm_rt_center_images[' . $index . ']: ' . $image->getError());
                        }
                    } else {
                        \Log::warning('Invalid file for fm_rt_center_images[' . $index . ']');
                    }
                }
            }
            $fmRtCenter->images = !empty($images) ? $images : null;

            // Handle document uploads
            $documents = [];
            $documentFields = [
                'rent_agreement',
                'permission_letter_local',
                'electricity_bill',
                'cin',
                'gst',
                'coi'
            ];

            foreach ($documentFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                        try {
                            $extension = $file->getClientOriginalExtension();
                            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                            $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'pdf';
                            
                            $fileName = Helpers::upload('fm-rt-center/documents/', $format, $file);
                            
                            if ($fileName && $fileName !== 'def.png') {
                                $documents[$field] = [
                                    'file' => $fileName,
                                    'storage' => Helpers::getDisk()
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::error('Document upload error for ' . $field . ': ' . $e->getMessage());
                        }
                    }
                }
            }

            // Handle other documents
            if ($request->has('upload_other_documents') && $request->upload_other_documents == '1') {
                if ($request->hasFile('other_document_file') && $request->has('other_document_name')) {
                    $file = $request->file('other_document_file');
                    $documentName = $request->other_document_name;
                    
                    if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK && !empty($documentName)) {
                        try {
                            $extension = $file->getClientOriginalExtension();
                            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                            $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'pdf';
                            
                            $fileName = Helpers::upload('fm-rt-center/documents/', $format, $file);
                            
                            if ($fileName && $fileName !== 'def.png') {
                                $documents['other_documents'] = [
                                    [
                                        'name' => $documentName,
                                        'file' => $fileName,
                                        'storage' => Helpers::getDisk()
                                    ]
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::error('Other document upload error: ' . $e->getMessage());
                        }
                    }
                }
            }

            $fmRtCenter->documents = !empty($documents) ? $documents : null;

            $fmRtCenter->save();

            // Sync Pincode mappings
            if ($request->has('pincode_ids')) {
                $fmRtCenter->pincodes()->sync($request->pincode_ids);
            }

            Toastr::success(translate('messages.fm_rt_center_added_successfully'));
            return redirect()->route('admin.logistics.fm-rt-center.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_add_fm_rt_center') . ': ' . $e->getMessage());
            \Log::error('FM/RT Center creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $fmRtCenter = FmRtCenter::with('pincodes')->findOrFail($id);
        
        // Get all pincodes
        $pincodes = Pincode::orderBy('pincode')->get();
        
        // Get all already mapped pincode IDs (pincodes that are mapped to other FM/RT Centers, excluding current one)
        $mappedPincodeIds = \DB::table('fm_rt_center_pincode')
            ->where('fm_rt_center_id', '!=', $id)
            ->pluck('pincode_id')
            ->unique()
            ->toArray();
        
        return view('admin-views.logistics.fm-rt-center.edit', compact('fmRtCenter', 'pincodes', 'mappedPincodeIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Basic validation first
            $request->validate([
                'center_name' => 'required|max:191',
                'full_address' => 'required',
                'pincode' => 'required|digits:6',
                'latitude' => 'required|max:50',
                'longitude' => 'required|max:50',
                'email' => 'nullable|email|max:191',
                'mobile_number' => 'nullable|digits:10',
            ], [
                'center_name.required' => translate('messages.Center Name is required!'),
                'full_address.required' => translate('messages.Full Address is required!'),
                'pincode.required' => translate('messages.Pincode is required!'),
                'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
                'latitude.required' => translate('messages.Latitude is required!'),
                'longitude.required' => translate('messages.Longitude is required!'),
                'email.email' => translate('messages.Please provide a valid email address!'),
                'mobile_number.digits' => translate('messages.Mobile number must be exactly 10 digits!'),
            ]);

            // Validate images separately to avoid blocking form submission
            if ($request->hasFile('fm_rt_center_images')) {
                $files = $request->file('fm_rt_center_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'fm_rt_center_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'fm_rt_center_images.*.image' => translate('messages.Please upload image files only!'),
                        'fm_rt_center_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'fm_rt_center_images.*.max' => translate('messages.Image size should be less than 2MB!'),
                    ]);
                }
            }

            $fmRtCenter = FmRtCenter::findOrFail($id);
            $fmRtCenter->center_name = $request->center_name;
            $fmRtCenter->full_address = $request->full_address;
            $fmRtCenter->location = $request->location ?? null;
            $fmRtCenter->pincode = $request->pincode;
            $fmRtCenter->latitude = $request->latitude ?? null;
            $fmRtCenter->longitude = $request->longitude ?? null;
            $fmRtCenter->owner_name = $request->owner_name ?? 'taksh';
            $fmRtCenter->state = $request->state ?? null;
            $fmRtCenter->city = $request->city ?? null;
            $fmRtCenter->email = $request->email ?? null;
            $fmRtCenter->mobile_number = $request->mobile_number ?? null;

            // Handle image updates
            $images = $fmRtCenter->images ?? [];
            
            // Remove deleted images
            if ($request->has('removed_images') && !empty($request->removed_images)) {
                $removedImageNames = explode(',', $request->removed_images);
                foreach ($removedImageNames as $removedImageName) {
                    if (!empty($removedImageName)) {
                        // Find and remove the image from array
                        $images = array_filter($images, function($image) use ($removedImageName) {
                            return $image['img'] !== $removedImageName;
                        });
                        // Delete physical file
                        try {
                            Storage::disk(Helpers::getDisk())->delete('fm-rt-center/' . $removedImageName);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to delete image: ' . $removedImageName);
                        }
                    }
                }
                $images = array_values($images); // Re-index array
            }
            
            // Add new images
            if ($request->hasFile('fm_rt_center_images')) {
                $uploadedFiles = $request->file('fm_rt_center_images');
                
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }
                
                foreach ($uploadedFiles as $index => $image) {
                    if ($image && $image->isValid()) {
                        if ($image->getError() === UPLOAD_ERR_OK) {
                            try {
                                $extension = $image->getClientOriginalExtension();
                                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                
                                $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'png';
                                
                                $imageName = Helpers::upload('fm-rt-center/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for fm_rt_center_images[' . $index . ']: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            $fmRtCenter->images = !empty($images) ? $images : null;

            // Handle document uploads
            $documents = $fmRtCenter->documents ?? [];
            
            $documentFields = [
                'rent_agreement',
                'permission_letter_local',
                'electricity_bill',
                'cin',
                'gst',
                'coi'
            ];

            foreach ($documentFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                        try {
                            $extension = $file->getClientOriginalExtension();
                            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                            $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'pdf';
                            
                            $fileName = Helpers::upload('fm-rt-center/documents/', $format, $file);
                            
                            if ($fileName && $fileName !== 'def.png') {
                                $documents[$field] = [
                                    'file' => $fileName,
                                    'storage' => Helpers::getDisk()
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::error('Document upload error for ' . $field . ': ' . $e->getMessage());
                        }
                    }
                }
            }

            // Initialize other_documents array if it doesn't exist
            if (!isset($documents['other_documents']) || !is_array($documents['other_documents'])) {
                $documents['other_documents'] = [];
            }

            // Handle other documents - preserve existing ones and add new ones
            if ($request->has('upload_other_documents') && $request->upload_other_documents == '1') {
                if ($request->hasFile('other_document_file') && $request->has('other_document_name')) {
                    $file = $request->file('other_document_file');
                    $documentName = $request->other_document_name;
                    
                    if ($file && $file->isValid() && $file->getError() === UPLOAD_ERR_OK && !empty($documentName)) {
                        try {
                            $extension = $file->getClientOriginalExtension();
                            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                            $format = in_array(strtolower($extension), $allowedExtensions) ? strtolower($extension) : 'pdf';
                            
                            $fileName = Helpers::upload('fm-rt-center/documents/', $format, $file);
                            
                            if ($fileName && $fileName !== 'def.png') {
                                $documents['other_documents'][] = [
                                    'name' => $documentName,
                                    'file' => $fileName,
                                    'storage' => Helpers::getDisk()
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::error('Other document upload error: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Remove deleted documents if needed
            if ($request->has('removed_documents') && !empty($request->removed_documents)) {
                $removedDocNames = explode(',', $request->removed_documents);
                foreach ($removedDocNames as $docName) {
                    if (isset($documents[$docName])) {
                        unset($documents[$docName]);
                    }
                    // Handle removal of other documents by index
                    if (strpos($docName, 'other_doc_') === 0) {
                        $index = (int) str_replace('other_doc_', '', $docName);
                        if (isset($documents['other_documents'][$index])) {
                            unset($documents['other_documents'][$index]);
                            $documents['other_documents'] = array_values($documents['other_documents']); // Re-index
                        }
                    }
                }
            }

            // Clean up empty other_documents array
            if (empty($documents['other_documents'])) {
                unset($documents['other_documents']);
            }

            $fmRtCenter->documents = !empty($documents) ? $documents : null;
            $fmRtCenter->save();

            // Sync Pincode mappings
            $fmRtCenter->pincodes()->sync($request->pincode_ids ?? []);

            Toastr::success(translate('messages.fm_rt_center_updated_successfully'));
            return redirect()->route('admin.logistics.fm-rt-center.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_update_fm_rt_center') . ': ' . $e->getMessage());
            \Log::error('FM/RT Center update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $fmRtCenter = FmRtCenter::findOrFail($id);
        $fmRtCenter->delete();
        Toastr::success(translate('messages.fm_rt_center_deleted_successfully'));
        return back();
    }

    /**
     * Update status
     */
    public function status(Request $request)
    {
        $fmRtCenter = FmRtCenter::findOrFail($request->id);
        $fmRtCenter->status = $request->status;
        $fmRtCenter->save();
        Toastr::success(translate('messages.fm_rt_center_status_updated'));
        return back();
    }
}

