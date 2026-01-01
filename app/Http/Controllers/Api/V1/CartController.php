<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ItemCampaign;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Get module ID safely from request or config
     * Falls back to first active module if none is found (module_id is required in carts table)
     */
    private function getModuleId($request)
    {
        $moduleId = $request->header('moduleId');
        if ($moduleId && is_numeric($moduleId)) {
            $module = Module::find((int)$moduleId);
            if ($module) {
                return (int)$moduleId;
            }
        }
        $moduleData = config('module.current_module_data');
        if (isset($moduleData) && is_array($moduleData) && isset($moduleData['id'])) {
            return (int)$moduleData['id'];
        }
        // Fallback to first active module (module_id is required, cannot be null)
        $fallbackModule = Module::active()->first();
        if ($fallbackModule) {
            return $fallbackModule->id;
        }
        // Last resort - return null (should not happen if modules are configured)
        return null;
    }

    public function get_carts(Request $request)
    {
        // guest_id is optional - if not provided, return empty cart
        $guest_id = $request->query('guest_id') ?? $request->input('guest_id');
        
        // If user is authenticated, use user ID
        if ($request->user) {
            $user_id = $request->user->id;
            $is_guest = 0;
        } 
        // If guest_id is provided, use it
        elseif ($guest_id) {
            // Ensure user_id is treated as integer for consistency with database casts
            $user_id = is_numeric($guest_id) ? (int)$guest_id : $guest_id;
            $is_guest = 1;
        } 
        // If neither user nor guest_id, return empty cart
        else {
            return response()->json([], 200);
        }
        $module_id = $this->getModuleId($request);
        
        // If module_id is null, it means no module is configured - return empty cart
        if (!$module_id) {
            return response()->json([], 200);
        }
        
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
        ->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			$data->item = Helpers::cart_product_data_formatting($data->item, $data->variation,$data->add_on_ids,
            $data->add_on_qtys, false, app()->getLocale());
			return $data;
		});
        return response()->json($carts, 200);
    }

    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'item_id' => 'required|integer',
            'model' => 'required|string|in:Item,ItemCampaign',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Handle guest_id from query or body
        $guest_id = $request->query('guest_id') ?? $request->input('guest_id');
        // Ensure user_id is treated as integer for consistency with database casts
        $user_id = $request->user ? $request->user->id : (is_numeric($guest_id) ? (int)$guest_id : $guest_id);
        $is_guest = $request->user ? 0 : 1;
        $model = $request->model === 'Item' ? 'App\Models\Item' : 'App\Models\ItemCampaign';
        $item = $request->model === 'Item' ? Item::find($request->item_id) : ItemCampaign::find($request->item_id);

        // Check if item exists
        if (!$item) {
            return response()->json([
                'errors' => [
                    ['code' => 'item_not_found', 'message' => translate('messages.product_not_found')]
                ]
            ], 404);
        }

        $module_id = $this->getModuleId($request);
        if (!$module_id) {
            return response()->json([
                'errors' => [
                    ['code' => 'module_id_required', 'message' => translate('messages.module_id_required')]
                ]
            ], 403);
        }
        $cart = Cart::where('item_id', $request->item_id)
            ->where('item_type', $model)
            ->where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->first();

        if ($cart && json_decode($cart->variation, true) == ($request->variation ?? [])) {

            return response()->json([
                'errors' => [
                    ['code' => 'cart_item', 'message' => translate('messages.Item_already_exists')]
                ]
            ], 403);
        }

        // Check maximum cart quantity - only validate if maximum_cart_quantity is set (not null and not 0)
        // 0 or null means no limit, so skip validation
        if($item->maximum_cart_quantity && $item->maximum_cart_quantity > 0 && ($request->quantity > $item->maximum_cart_quantity)){
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')]
                ]
            ], 403);
        }

        $cart = new Cart();
        $cart->user_id = $user_id;
        $cart->module_id = $module_id;
        $cart->item_id = $request->item_id;
        $cart->is_guest = $is_guest;
        $cart->add_on_ids = isset($request->add_on_ids)?json_encode($request->add_on_ids):json_encode([]);
        $cart->add_on_qtys = isset($request->add_on_qtys)?json_encode($request->add_on_qtys):json_encode([]);
        $cart->item_type = $model;
        $cart->price = $request->price;
        $cart->quantity = $request->quantity;
        $cart->variation = isset($request->variation)?json_encode($request->variation):json_encode([]);
        $cart->save();

        // Associate cart with item (optional relationship)
        try {
            $item->carts()->save($cart);
        } catch (\Exception $e) {
            // If relationship save fails, continue - cart is already saved
        }

        $module_id = $this->getModuleId($request);
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
        ->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			$data->item = Helpers::cart_product_data_formatting($data->item, $data->variation,$data->add_on_ids,
            $data->add_on_qtys, false, app()->getLocale());
            return $data;
		});
        return response()->json($carts, 200);
    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $cart = Cart::find($request->cart_id);
        
        if (!$cart) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_not_found', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }
        
        $item = $cart->item_type === 'App\Models\Item' ? Item::find($cart->item_id) : ItemCampaign::find($cart->item_id);
        
        if (!$item) {
            return response()->json([
                'errors' => [
                    ['code' => 'item_not_found', 'message' => translate('messages.product_not_found')]
                ]
            ], 404);
        }
        
        // Check maximum cart quantity - only validate if maximum_cart_quantity is set (not null and not 0)
        // 0 or null means no limit, so skip validation
        if($item->maximum_cart_quantity && $item->maximum_cart_quantity > 0 && ($request->quantity > $item->maximum_cart_quantity)){
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')]
                ]
            ], 403);
        }

        // Only update quantity (required) and optional fields if provided
        $cart->quantity = $request->quantity;
        
        // Update price only if provided
        if ($request->has('price')) {
            $cart->price = $request->price;
        }
        
        // Update add_ons only if provided
        if ($request->has('add_on_ids')) {
            $cart->add_on_ids = json_encode($request->add_on_ids);
        }
        
        if ($request->has('add_on_qtys')) {
            $cart->add_on_qtys = json_encode($request->add_on_qtys);
        }
        
        // Update variation only if provided
        if ($request->has('variation')) {
            $cart->variation = json_encode($request->variation);
        }
        
        $cart->save();

        // Get user_id and is_guest from the cart (not from request)
        $user_id = $cart->user_id;
        $is_guest = $cart->is_guest;
        
        $module_id = $this->getModuleId($request);
        if (!$module_id) {
            $module_id = $cart->module_id; // Use cart's module_id as fallback
        }
        
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
        ->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			$data->item = Helpers::cart_product_data_formatting($data->item, $data->variation,$data->add_on_ids,
            $data->add_on_qtys, false, app()->getLocale());
            return $data;
		});
        return response()->json($carts, 200);
    }

    public function remove_cart_item(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Handle guest_id from query or body
        $guest_id = $request->query('guest_id') ?? $request->input('guest_id');
        // Ensure user_id is treated as integer for consistency with database casts
        $user_id = $request->user ? $request->user->id : (is_numeric($guest_id) ? (int)$guest_id : $guest_id);
        $is_guest = $request->user ? 0 : 1;

        $cart = Cart::find($request->cart_id);
        $cart?->delete();

        $module_id = $this->getModuleId($request);
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
        ->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			$data->item = Helpers::cart_product_data_formatting($data->item, $data->variation,$data->add_on_ids,
            $data->add_on_qtys, false, app()->getLocale());
            return $data;
		});
        return response()->json($carts, 200);
    }

    public function remove_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Handle guest_id from query or body
        $guest_id = $request->query('guest_id') ?? $request->input('guest_id');
        // Ensure user_id is treated as integer for consistency with database casts
        $user_id = $request->user ? $request->user->id : (is_numeric($guest_id) ? (int)$guest_id : $guest_id);
        $is_guest = $request->user ? 0 : 1;

        $module_id = $this->getModuleId($request);
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get();

        foreach($carts as $cart){
            $cart?->delete();
        }


        $module_id = $this->getModuleId($request);
        // module_id is required for carts, so always filter by it
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
        ->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			$data->item = Helpers::cart_product_data_formatting($data->item, $data->variation,$data->add_on_ids,
            $data->add_on_qtys, false, app()->getLocale());
            return $data;
		});
        return response()->json($carts, 200);
    }

    /**
     * Add multiple products to cart at once (bulk add)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulk_add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.model' => 'required|string|in:Item,ItemCampaign',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Handle guest_id from query or body
        $guest_id = $request->query('guest_id') ?? $request->input('guest_id');
        // Ensure user_id is treated as integer for consistency with database casts
        $user_id = $request->user ? $request->user->id : (is_numeric($guest_id) ? (int)$guest_id : $guest_id);
        $is_guest = $request->user ? 0 : 1;
        $module_id = $this->getModuleId($request);
        $added_items = [];
        $errors = [];

        foreach ($request->items as $itemData) {
            try {
                $model = $itemData['model'] === 'Item' ? 'App\Models\Item' : 'App\Models\ItemCampaign';
                $item = $itemData['model'] === 'Item' ? Item::find($itemData['item_id']) : ItemCampaign::find($itemData['item_id']);

                if (!$item) {
                    $errors[] = [
                        'item_id' => $itemData['item_id'],
                        'message' => translate('messages.product_not_found')
                    ];
                    continue;
                }

                // Check if item already exists in cart with same variation
                $existing_cart = Cart::where('item_id', $itemData['item_id'])
                    ->where('item_type', $model)
                    ->where('user_id', $user_id)
                    ->where('is_guest', $is_guest)
                    ->where('module_id', $module_id)
                    ->first();

                if ($existing_cart) {
                    $existing_variation = json_decode($existing_cart->variation, true) ?? [];
                    $request_variation = $itemData['variation'] ?? [];
                    if ($existing_variation == $request_variation) {
                        $errors[] = [
                            'item_id' => $itemData['item_id'],
                            'message' => translate('messages.Item_already_exists')
                        ];
                        continue;
                    }
                }

                // Check maximum cart quantity
                if ($item->maximum_cart_quantity && ($itemData['quantity'] > $item->maximum_cart_quantity)) {
                    $errors[] = [
                        'item_id' => $itemData['item_id'],
                        'message' => translate('messages.maximum_cart_quantity_exceeded')
                    ];
                    continue;
                }

                // Create cart item
                $cart = new Cart();
                $cart->user_id = $user_id;
                $cart->module_id = $module_id;
                $cart->item_id = $itemData['item_id'];
                $cart->is_guest = $is_guest;
                $cart->add_on_ids = isset($itemData['add_on_ids']) ? json_encode($itemData['add_on_ids']) : json_encode([]);
                $cart->add_on_qtys = isset($itemData['add_on_qtys']) ? json_encode($itemData['add_on_qtys']) : json_encode([]);
                $cart->item_type = $model;
                $cart->price = $itemData['price'];
                $cart->quantity = $itemData['quantity'];
                $cart->variation = isset($itemData['variation']) ? json_encode($itemData['variation']) : json_encode([]);
                $cart->save();

                $item->carts()->save($cart);
                $added_items[] = $itemData['item_id'];

            } catch (\Exception $e) {
                $errors[] = [
                    'item_id' => $itemData['item_id'] ?? null,
                    'message' => translate('messages.something_went_wrong')
                ];
            }
        }

        // Get updated cart list
        $carts = Cart::where('user_id', $user_id)
            ->where('is_guest', $is_guest)
            ->where('module_id', $module_id)
            ->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });

        $response = [
            'carts' => $carts,
            'added_items' => $added_items,
            'added_count' => count($added_items),
            'total_count' => count($request->items),
        ];

        if (count($errors) > 0) {
            $response['errors'] = $errors;
        }

        return response()->json($response, 200);
    }
}
