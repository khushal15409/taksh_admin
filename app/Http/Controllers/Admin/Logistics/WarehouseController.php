<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Miniwarehouse;
use App\Models\LmCenter;
use App\Models\FmRtCenter;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get all warehouses for client-side DataTable
        $warehouses = Warehouse::with(['zone', 'miniwarehouses', 'lmCenters', 'fmRtCenters', 'warehouses'])
            ->latest()
            ->get();
        
        return view('admin-views.logistics.warehouse.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all active miniwarehouses, lm centers, fm/rt centers, and warehouses for mapping dropdowns
        $miniwarehouses = Miniwarehouse::where('status', 1)->get();
        $lmCenters = LmCenter::where('status', 1)->get();
        $fmRtCenters = FmRtCenter::where('status', 1)->get();
        $warehouses = Warehouse::where('status', 1)->get();
        
        // Get all already mapped IDs (items that are mapped to other warehouses)
        $mappedMiniwarehouseIds = \DB::table('warehouse_miniwarehouse')->pluck('miniwarehouse_id')->unique()->toArray();
        $mappedLmCenterIds = \DB::table('warehouse_lm_center')->pluck('lm_center_id')->unique()->toArray();
        $mappedFmRtCenterIds = \DB::table('warehouse_fm_rt_center')->pluck('fm_rt_center_id')->unique()->toArray();
        $mappedWarehouseIds = \DB::table('warehouse_warehouse')->pluck('mapped_warehouse_id')->unique()->toArray();
        
        return view('admin-views.logistics.warehouse.create', compact('miniwarehouses', 'lmCenters', 'fmRtCenters', 'warehouses', 'mappedMiniwarehouseIds', 'mappedLmCenterIds', 'mappedFmRtCenterIds', 'mappedWarehouseIds'));
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
            // Only validate if files are actually present
            // Note: PHP upload_max_filesize is 2MB, so max validation is set to 2048KB
            if ($request->hasFile('warehouse_images')) {
                $files = $request->file('warehouse_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'warehouse_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'warehouse_images.*.image' => translate('messages.Please upload image files only!'),
                        'warehouse_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'warehouse_images.*.max' => translate('messages.Image size should be less than 2MB!'),
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

            $warehouse = new Warehouse();
            $warehouse->name = $request->name;
            $warehouse->owner_name = $request->owner_name ?? 'taksh';
            $warehouse->full_address = $request->full_address;
            $warehouse->location = $request->location ?? null;
            $warehouse->pincode = $request->pincode;
            $warehouse->latitude = $request->latitude ?? null;
            $warehouse->longitude = $request->longitude ?? null;
            $warehouse->state = $request->state ?? null;
            $warehouse->city = $request->city ?? null;
            $warehouse->email = $request->email ?? null;
            $warehouse->mobile_number = $request->mobile_number ?? null;
            $warehouse->zone_id = null;
            $warehouse->status = 1;

            // Handle image uploads
            $images = [];
            if ($request->hasFile('warehouse_images')) {
                $uploadedFiles = $request->file('warehouse_images');
                
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
                                
                                $imageName = Helpers::upload('warehouse/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                } else {
                                    \Log::warning('Image upload returned default image for warehouse_images[' . $index . ']');
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for warehouse_images[' . $index . ']: ' . $e->getMessage());
                                \Log::error('Stack trace: ' . $e->getTraceAsString());
                                // Continue with other images even if one fails
                            }
                        } else {
                            \Log::warning('Upload error code for warehouse_images[' . $index . ']: ' . $image->getError());
                        }
                    } else {
                        \Log::warning('Invalid file for warehouse_images[' . $index . ']');
                    }
                }
            }
            $warehouse->images = !empty($images) ? $images : null;

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
                            
                            $fileName = Helpers::upload('warehouse/documents/', $format, $file);
                            
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
                            
                            $fileName = Helpers::upload('warehouse/documents/', $format, $file);
                            
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

            $warehouse->documents = !empty($documents) ? $documents : null;

            $warehouse->save();

            // Sync mappings
            if ($request->has('miniwarehouse_ids') && is_array($request->miniwarehouse_ids)) {
                $warehouse->miniwarehouses()->sync($request->miniwarehouse_ids);
            }
            if ($request->has('lm_center_ids') && is_array($request->lm_center_ids)) {
                $warehouse->lmCenters()->sync($request->lm_center_ids);
            }
            if ($request->has('fm_rt_center_ids') && is_array($request->fm_rt_center_ids)) {
                $warehouse->fmRtCenters()->sync($request->fm_rt_center_ids);
            }

            Toastr::success(translate('messages.warehouse_added_successfully'));
            return redirect()->route('admin.logistics.warehouse.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_add_warehouse') . ': ' . $e->getMessage());
            \Log::error('Warehouse creation error: ' . $e->getMessage(), [
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
        $warehouse = Warehouse::with(['miniwarehouses', 'lmCenters', 'fmRtCenters', 'warehouses'])->findOrFail($id);
        $miniwarehouses = Miniwarehouse::where('status', 1)->get();
        $lmCenters = LmCenter::where('status', 1)->get();
        $fmRtCenters = FmRtCenter::where('status', 1)->get();
        $warehouses = Warehouse::where('status', 1)->where('id', '!=', $id)->get();
        
        // Get all already mapped IDs (items that are mapped to other warehouses, excluding current one)
        $mappedMiniwarehouseIds = \DB::table('warehouse_miniwarehouse')
            ->where('warehouse_id', '!=', $id)
            ->pluck('miniwarehouse_id')
            ->unique()
            ->toArray();
        $mappedLmCenterIds = \DB::table('warehouse_lm_center')
            ->where('warehouse_id', '!=', $id)
            ->pluck('lm_center_id')
            ->unique()
            ->toArray();
        $mappedFmRtCenterIds = \DB::table('warehouse_fm_rt_center')
            ->where('warehouse_id', '!=', $id)
            ->pluck('fm_rt_center_id')
            ->unique()
            ->toArray();
        $mappedWarehouseIds = \DB::table('warehouse_warehouse')
            ->where('warehouse_id', '!=', $id)
            ->pluck('mapped_warehouse_id')
            ->unique()
            ->toArray();
        
        return view('admin-views.logistics.warehouse.edit', compact('warehouse', 'miniwarehouses', 'lmCenters', 'fmRtCenters', 'warehouses', 'mappedMiniwarehouseIds', 'mappedLmCenterIds', 'mappedFmRtCenterIds', 'mappedWarehouseIds'));
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
            if ($request->hasFile('warehouse_images')) {
                $files = $request->file('warehouse_images');
                if (is_array($files) && count($files) > 0) {
                    $request->validate([
                        'warehouse_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                    ], [
                        'warehouse_images.*.image' => translate('messages.Please upload image files only!'),
                        'warehouse_images.*.mimes' => translate('messages.Image must be jpeg, jpg, png, gif, or webp!'),
                        'warehouse_images.*.max' => translate('messages.Image size should be less than 2MB!'),
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

            $warehouse = Warehouse::findOrFail($id);
            $warehouse->name = $request->name;
            $warehouse->full_address = $request->full_address;
            $warehouse->location = $request->location ?? null;
            $warehouse->pincode = $request->pincode;
            $warehouse->latitude = $request->latitude ?? null;
            $warehouse->longitude = $request->longitude ?? null;
            $warehouse->state = $request->state ?? null;
            $warehouse->city = $request->city ?? null;
            $warehouse->email = $request->email ?? null;
            $warehouse->mobile_number = $request->mobile_number ?? null;

            // Handle image updates
            $images = $warehouse->images ?? [];
            
            // Remove deleted images
            if ($request->has('removed_images') && !empty($request->removed_images)) {
                $removedImageNames = explode(',', $request->removed_images);
                $images = array_filter($images, function($image) use ($removedImageNames) {
                    return !in_array($image['img'], $removedImageNames);
                });
                $images = array_values($images); // Re-index array
            }
            
            // Add new images
            if ($request->hasFile('warehouse_images')) {
                $uploadedFiles = $request->file('warehouse_images');
                
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
                                
                                $imageName = Helpers::upload('warehouse/', $format, $image);
                                
                                if ($imageName && $imageName !== 'def.png') {
                                    $images[] = [
                                        'img' => $imageName,
                                        'storage' => Helpers::getDisk()
                                    ];
                                }
                            } catch (\Exception $e) {
                                \Log::error('Image upload error for warehouse_images[' . $index . ']: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            $warehouse->images = !empty($images) ? $images : null;

            // Handle document uploads
            $documents = $warehouse->documents ?? [];
            
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
                            
                            $fileName = Helpers::upload('warehouse/documents/', $format, $file);
                            
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
                            
                            $fileName = Helpers::upload('warehouse/documents/', $format, $file);
                            
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

            $warehouse->documents = !empty($documents) ? $documents : null;
            $warehouse->save();

            // Sync mappings
            if ($request->has('miniwarehouse_ids') && is_array($request->miniwarehouse_ids)) {
                $warehouse->miniwarehouses()->sync($request->miniwarehouse_ids);
            }
            if ($request->has('lm_center_ids') && is_array($request->lm_center_ids)) {
                $warehouse->lmCenters()->sync($request->lm_center_ids);
            }
            if ($request->has('fm_rt_center_ids') && is_array($request->fm_rt_center_ids)) {
                $warehouse->fmRtCenters()->sync($request->fm_rt_center_ids);
            }
            if ($request->has('warehouse_ids') && is_array($request->warehouse_ids)) {
                $warehouse->warehouses()->sync($request->warehouse_ids);
            }

            Toastr::success(translate('messages.warehouse_updated_successfully'));
            return redirect()->route('admin.logistics.warehouse.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_update_warehouse') . ': ' . $e->getMessage());
            \Log::error('Warehouse update error: ' . $e->getMessage(), [
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
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        Toastr::success(translate('messages.warehouse_deleted_successfully'));
        return back();
    }

    /**
     * Update status
     */
    public function status(Request $request)
    {
        $warehouse = Warehouse::findOrFail($request->id);
        $warehouse->status = $request->status;
        $warehouse->save();
        Toastr::success(translate('messages.warehouse_status_updated'));
        return back();
    }
}
