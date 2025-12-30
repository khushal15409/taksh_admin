<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Miniwarehouse;
use App\Models\LmCenter;
use App\Models\FmRtCenter;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;

class MiniwarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get all miniwarehouses for client-side DataTable
        $miniwarehouses = Miniwarehouse::with(['zone', 'lmCenters', 'fmRtCenters'])
            ->latest()
            ->get();
        
        return view('admin-views.logistics.miniwarehouse.index', compact('miniwarehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all active lm centers and fm/rt centers for mapping dropdowns
        $lmCenters = LmCenter::where('status', 1)->get();
        $fmRtCenters = FmRtCenter::where('status', 1)->get();
        
        // Get all already mapped IDs (items that are mapped to other miniwarehouses)
        $mappedLmCenterIds = \DB::table('miniwarehouse_lm_center')->pluck('lm_center_id')->unique()->toArray();
        $mappedFmRtCenterIds = \DB::table('miniwarehouse_fm_rt_center')->pluck('fm_rt_center_id')->unique()->toArray();
        
        return view('admin-views.logistics.miniwarehouse.create', compact('lmCenters', 'fmRtCenters', 'mappedLmCenterIds', 'mappedFmRtCenterIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Basic validation first
            $request->validate([
                'name' => 'required|max:191',
                'full_address' => 'required',
                'pincode' => 'required|digits:6',
                'latitude' => 'required|max:50',
                'longitude' => 'required|max:50',
                'email' => 'nullable|email|max:191',
                'mobile_number' => 'nullable|digits:10',
            ], [
                'name.required' => translate('messages.Name is required!'),
                'full_address.required' => translate('messages.Full Address is required!'),
                'pincode.required' => translate('messages.Pincode is required!'),
                'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
                'latitude.required' => translate('messages.Latitude is required!'),
                'longitude.required' => translate('messages.Longitude is required!'),
                'email.email' => translate('messages.Please provide a valid email address!'),
                'mobile_number.digits' => translate('messages.Mobile number must be exactly 10 digits!'),
            ]);

            // Validate images separately to avoid blocking form submission
            // Note: PHP upload_max_filesize is 2MB, so max validation is set to 2048KB
            if ($request->hasFile('miniwarehouse_images')) {
                $files = $request->file('miniwarehouse_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'miniwarehouse_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'miniwarehouse_images.*.image' => translate('messages.Please upload image files only!'),
                        'miniwarehouse_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'miniwarehouse_images.*.max' => translate('messages.Image size should be less than 2MB!'),
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

            $miniwarehouse = new Miniwarehouse();
            $miniwarehouse->name = $request->name;
            $miniwarehouse->owner_name = $request->owner_name ?? 'taksh';
            $miniwarehouse->full_address = $request->full_address;
            $miniwarehouse->location = $request->location ?? null;
            $miniwarehouse->pincode = $request->pincode;
            $miniwarehouse->latitude = $request->latitude ?? null;
            $miniwarehouse->longitude = $request->longitude ?? null;
            $miniwarehouse->state = $request->state ?? null;
            $miniwarehouse->city = $request->city ?? null;
            $miniwarehouse->email = $request->email ?? null;
            $miniwarehouse->mobile_number = $request->mobile_number ?? null;
            $miniwarehouse->zone_id = null;
            $miniwarehouse->status = 1;

            // Handle image uploads
            $images = [];
            if ($request->hasFile('miniwarehouse_images')) {
                $uploadedFiles = $request->file('miniwarehouse_images');
                
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
                                
                                $imageName = Helpers::upload('miniwarehouse/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                } else {
                                    \Log::warning('Image upload returned default image for miniwarehouse_images[' . $index . ']');
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for miniwarehouse_images[' . $index . ']: ' . $e->getMessage());
                                \Log::error('Stack trace: ' . $e->getTraceAsString());
                                // Continue with other images even if one fails
                            }
                        } else {
                            \Log::warning('Upload error code for miniwarehouse_images[' . $index . ']: ' . $image->getError());
                        }
                    } else {
                        \Log::warning('Invalid file for miniwarehouse_images[' . $index . ']');
                    }
                }
            }
            $miniwarehouse->images = !empty($images) ? $images : null;

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
                            
                            $fileName = Helpers::upload('miniwarehouse/documents/', $format, $file);
                            
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
                            
                            $fileName = Helpers::upload('miniwarehouse/documents/', $format, $file);
                            
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

            $miniwarehouse->documents = !empty($documents) ? $documents : null;

            $miniwarehouse->save();

            // Sync mappings
            if ($request->has('lm_center_ids') && is_array($request->lm_center_ids)) {
                $miniwarehouse->lmCenters()->sync($request->lm_center_ids);
            }
            if ($request->has('fm_rt_center_ids') && is_array($request->fm_rt_center_ids)) {
                $miniwarehouse->fmRtCenters()->sync($request->fm_rt_center_ids);
            }

            Toastr::success(translate('messages.miniwarehouse_added_successfully'));
            return redirect()->route('admin.logistics.miniwarehouse.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_add_miniwarehouse') . ': ' . $e->getMessage());
            \Log::error('Miniwarehouse creation error: ' . $e->getMessage(), [
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
        $miniwarehouse = Miniwarehouse::with(['lmCenters', 'fmRtCenters'])->findOrFail($id);
        $lmCenters = LmCenter::where('status', 1)->get();
        $fmRtCenters = FmRtCenter::where('status', 1)->get();
        
        // Get all already mapped IDs (items that are mapped to other miniwarehouses, excluding current one)
        $mappedLmCenterIds = \DB::table('miniwarehouse_lm_center')
            ->where('miniwarehouse_id', '!=', $id)
            ->pluck('lm_center_id')
            ->unique()
            ->toArray();
        $mappedFmRtCenterIds = \DB::table('miniwarehouse_fm_rt_center')
            ->where('miniwarehouse_id', '!=', $id)
            ->pluck('fm_rt_center_id')
            ->unique()
            ->toArray();
        
        return view('admin-views.logistics.miniwarehouse.edit', compact('miniwarehouse', 'lmCenters', 'fmRtCenters', 'mappedLmCenterIds', 'mappedFmRtCenterIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Basic validation first
            $request->validate([
                'name' => 'required|max:191',
                'full_address' => 'required',
                'pincode' => 'required|digits:6',
                'latitude' => 'required|max:50',
                'longitude' => 'required|max:50',
                'email' => 'nullable|email|max:191',
                'mobile_number' => 'nullable|digits:10',
            ], [
                'name.required' => translate('messages.Name is required!'),
                'full_address.required' => translate('messages.Full Address is required!'),
                'pincode.required' => translate('messages.Pincode is required!'),
                'pincode.digits' => translate('messages.Pincode must be exactly 6 digits!'),
                'latitude.required' => translate('messages.Latitude is required!'),
                'longitude.required' => translate('messages.Longitude is required!'),
                'email.email' => translate('messages.Please provide a valid email address!'),
                'mobile_number.digits' => translate('messages.Mobile number must be exactly 10 digits!'),
            ]);

            // Validate images separately to avoid blocking form submission
            if ($request->hasFile('miniwarehouse_images')) {
                $files = $request->file('miniwarehouse_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'miniwarehouse_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'miniwarehouse_images.*.image' => translate('messages.Please upload image files only!'),
                        'miniwarehouse_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'miniwarehouse_images.*.max' => translate('messages.Image size should be less than 2MB!'),
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

            $miniwarehouse = Miniwarehouse::findOrFail($id);
            $miniwarehouse->name = $request->name;
            $miniwarehouse->full_address = $request->full_address;
            $miniwarehouse->location = $request->location ?? null;
            $miniwarehouse->pincode = $request->pincode;
            $miniwarehouse->latitude = $request->latitude ?? null;
            $miniwarehouse->longitude = $request->longitude ?? null;
            $miniwarehouse->state = $request->state ?? null;
            $miniwarehouse->city = $request->city ?? null;
            $miniwarehouse->email = $request->email ?? null;
            $miniwarehouse->mobile_number = $request->mobile_number ?? null;

            // Handle image updates
            $images = $miniwarehouse->images ?? [];
            
            // Remove deleted images
            if ($request->has('removed_images') && !empty($request->removed_images)) {
                $removedImageNames = explode(',', $request->removed_images);
                $images = array_filter($images, function($image) use ($removedImageNames) {
                    return !in_array($image['img'], $removedImageNames);
                });
                $images = array_values($images); // Re-index array
            }
            
            // Add new images
            if ($request->hasFile('miniwarehouse_images')) {
                $uploadedFiles = $request->file('miniwarehouse_images');
                
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
                                
                                $imageName = Helpers::upload('miniwarehouse/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for miniwarehouse_images[' . $index . ']: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            $miniwarehouse->images = !empty($images) ? $images : null;

            // Handle document uploads
            $documents = $miniwarehouse->documents ?? [];
            
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
                            
                            $fileName = Helpers::upload('miniwarehouse/documents/', $format, $file);
                            
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
                            
                            $fileName = Helpers::upload('miniwarehouse/documents/', $format, $file);
                            
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

            $miniwarehouse->documents = !empty($documents) ? $documents : null;
            $miniwarehouse->save();

            // Sync mappings
            if ($request->has('lm_center_ids') && is_array($request->lm_center_ids)) {
                $miniwarehouse->lmCenters()->sync($request->lm_center_ids);
            }
            if ($request->has('fm_rt_center_ids') && is_array($request->fm_rt_center_ids)) {
                $miniwarehouse->fmRtCenters()->sync($request->fm_rt_center_ids);
            }

            Toastr::success(translate('messages.miniwarehouse_updated_successfully'));
            return redirect()->route('admin.logistics.miniwarehouse.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_update_miniwarehouse') . ': ' . $e->getMessage());
            \Log::error('Miniwarehouse update error: ' . $e->getMessage(), [
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
        $miniwarehouse = Miniwarehouse::findOrFail($id);
        $miniwarehouse->delete();
        Toastr::success(translate('messages.miniwarehouse_deleted_successfully'));
        return back();
    }

    /**
     * Update status
     */
    public function status(Request $request)
    {
        $miniwarehouse = Miniwarehouse::findOrFail($request->id);
        $miniwarehouse->status = $request->status;
        $miniwarehouse->save();
        Toastr::success(translate('messages.miniwarehouse_status_updated'));
        return back();
    }
}
