<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use App\Models\Order;
use App\Models\Store;
use App\Models\Review;
use App\Models\Allergy;
use App\Models\Category;
use App\Models\Nutrition;
use App\Models\GenericName;
use App\Models\PriorityList;
use App\Models\Zone; 
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\StoreLogic;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ItemController extends Controller
{

    public function get_latest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'category_id' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id')??null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $filter = $request['filter'] ? (is_array($request['filter']) ? $request['filter'] : str_getcsv(trim($request['filter'], "[]"), ',')) : '';

        $rating_count = $request->query('rating_count');

        $items = ProductLogic::get_latest_products($zone_id, $request['limit'], $request['offset'], $request['store_id'], $request['category_id'], $type,$min,$max,$product_id,$filter,$rating_count);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_new_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id')??null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $limit = isset($request['limit'])?$request['limit']:50;
        $offset = isset($request['offset'])?$request['offset']:1;

        $items = ProductLogic::get_new_products($zone_id, $type,$min,$max,$product_id,$limit,$offset);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_searched_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $product_search_default_status =BusinessSetting::where('key', 'product_search_default_status')->first()?->value ?? 1;
        $product_search_sort_by_general =PriorityList::where('name', 'product_search_sort_by_general')->where('type','general')->first()?->value ?? '';
        $product_search_sort_by_unavailable =PriorityList::where('name', 'product_search_sort_by_unavailable')->where('type','unavailable')->first()?->value ?? '';
        $product_search_sort_by_temp_closed =PriorityList::where('name', 'product_search_sort_by_temp_closed')->where('type','temp_closed')->first()?->value ?? '';


        $zone_id = $request->header('zoneId');

        $key = explode(' ', $request['name']);

        $limit = $request['limit']??10;
        $offset = $request['offset']??1;
        $category_ids = $request['category_ids']?(is_array($request['category_ids'])?$request['category_ids']:json_decode($request['category_ids'])):'';
        $filter = $request['filter']?(is_array($request['filter'])?$request['filter']:str_getcsv(trim($request['filter'], "[]"), ',')):'';
        $type = $request->query('type', 'all');
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $rating_count = $request->query('rating_count');

        $query = Item::active()->type($type)
        ->with('store', function($query){
            $query->withCount(['campaigns'=> function($query){
                $query->Running();
            }]);
        })
        ->select(['items.*'])
        ->selectSub(function ($subQuery) {
            $subQuery->selectRaw('active as temp_available')
                ->from('stores')
                ->whereColumn('stores.id', 'items.store_id');
        }, 'temp_available');


        if ($product_search_default_status != '1'){
            if(config('module.current_module_data')['module_type']  !== 'food'){
                if($product_search_sort_by_unavailable == 'remove'){
                    $query = $query->where('stock', '>', 0);
                }elseif($product_search_sort_by_unavailable == 'last'){
                    $query = $query->orderByRaw('CASE WHEN stock = 0 THEN 1 ELSE 0 END');
                }

            }

            if($product_search_sort_by_temp_closed == 'remove'){
                $query = $query->having('temp_available', '>', 0);
            }elseif($product_search_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('temp_available');
            }
        }


        $query= $query->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($category_ids, function($query)use($category_ids){
            $query->whereHas('category',function($q)use($category_ids){
                return $q->whereIn('id',$category_ids)->orWhereIn('parent_id', $category_ids);
            });
        })
        ->when($request->store_id, function($query) use($request){
            return $query->where('store_id', $request->store_id);
        })
        ->whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })
        ->whereHas('store', function($query)use($zone_id){
            $query->when(config('module.current_module_data'), function($query){
                $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules',function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                });
            })->whereIn('zone_id', json_decode($zone_id, true));
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }

            $relationships = [
                'translations' => 'value',
                'tags' => 'tag',
                'nutritions' => 'nutrition',
                'allergies' => 'allergy',
                'category.parent' => 'name',
                'category' => 'name',
                'generic' => 'generic_name',
                'ecommerce_item_details.brand' => 'name',
                'pharmacy_item_details.common_condition' => 'name',
            ];
            $q->applyRelationShipSearch(relationships:$relationships ,searchParameter:$key);
        })
        ->when($rating_count, function($query) use ($rating_count){
            $query->where('avg_rating', '>=' , $rating_count);
        })
        ->when($min && $max, function($query)use($min,$max){
            $query->whereBetween('price',[$min,$max]);
        })
        ->orderByRaw("FIELD(name, ?) DESC", [$request['name']])

        ->when($filter&&in_array('top_rated',$filter),function ($qurey){
            $qurey->withCount('reviews')->orderBy('reviews_count','desc');
        })
        ->when($filter&&in_array('popular',$filter),function ($qurey){
            $qurey->popular();
        })
        ->when($filter&&in_array('discounted',$filter),function ($qurey){
            $qurey->Discounted()->orderBy('discount','desc');
        })
        ->when($filter&&in_array('high',$filter),function ($qurey){
            $qurey->orderBy('price', 'desc');
        })
        ->when($filter&&in_array('low',$filter),function ($qurey){
            $qurey->orderBy('price', 'asc');
        });


        $item_categories=  $query->pluck('category_id')->toArray();
        $items = $query->paginate($limit, ['*'], 'page', $offset);
        $item_categories = array_unique($item_categories);

        $categories = Category::withCount(['products','childes'])->with(['childes' => function($query)  {
            $query->withCount(['products','childes']);
        }])
        ->where(['position'=>0,'status'=>1])
        ->when(config('module.current_module_data'), function($query){
            $query->module(config('module.current_module_data')['id']);
        })
        ->whereIn('id',$item_categories)
        ->orderBy('priority','desc')->get();

        $data =  [
            'total_size' => $items->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $items->items(),
            'categories'=>$categories
        ];

        $data['products'] = Helpers::product_data_formatting($data['products'], true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_searched_products_suggestion(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zone_id = $request->header('zoneId');

        $key = explode(' ', $request['name']);

        $limit = $request['limit']??10;
        $offset = $request['offset']??1;

        $type = $request->query('type', 'all');

        $items = Item::active()->type($type)

        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->store_id, function($query) use($request){
            return $query->where('store_id', $request->store_id);
        })
        ->whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })
        ->whereHas('store', function($query)use($zone_id){
            $query->when(config('module.current_module_data'), function($query){
                $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules',function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                });
            })->whereIn('zone_id', json_decode($zone_id, true));
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('translations',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('value', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('tags',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('tag', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('nutritions',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('nutrition', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('allergies',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('allergy', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('generic',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('generic_name', 'like', "%{$value}%");
                    };
                });
            });
        })->select(['name','image'])

        ->paginate($limit, ['*'], 'page', $offset);

        $data =  [
            'total_size' => $items->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $items->items()
        ];

        return response()->json($data, 200);
    }

    public function get_popular_products(Request $request)
    {
        $delivery_type = $request->query('delivery_type');
        $longitude = $request->query('longitude') ?? $request->header('longitude');
        $latitude = $request->query('latitude') ?? $request->header('latitude');
        $module_id = (int) ($request->query('module_id') ?? $request->header('moduleId') ?? config('module.current_module_data')['id'] ?? null);
        
        // If delivery_type is 30_minute, filter items from stores that support 30-minute delivery
        if ($delivery_type === '30_minute' && $latitude && $longitude) {
            $max_distance = 5000; // 5km in meters
            $limit = (int) ($request->query('limit') ?? $request->input('limit', 20));
            $offset = (int) ($request->query('offset') ?? $request->input('offset', 0));
            
            $items_query = Item::where('items.status', 1)
                ->where('items.is_approved', 1)
                ->whereHas('store', function($query) use ($latitude, $longitude, $max_distance, $module_id) {
                    $query->where('active', 1)
                        ->when($module_id, function($q) use ($module_id) {
                            $q->where('module_id', $module_id);
                        })
                        ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
                        ->whereRaw("CASE 
                            WHEN delivery_time IS NULL THEN 0
                            WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                            WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                            ELSE 0
                        END <= 30");
                })
                ->with(['store' => function($query) use ($latitude, $longitude) {
                    $query->selectRaw('stores.*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude]);
                }])
                ->selectRaw('items.*, 
                    CASE 
                        WHEN stores.delivery_time IS NULL THEN 30
                        WHEN stores.delivery_time LIKE "%hours%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(stores.delivery_time, "-", 1), " ", 1) AS UNSIGNED) * 60
                        WHEN stores.delivery_time LIKE "%min%" OR stores.delivery_time LIKE "%minute%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(stores.delivery_time, "-", 1), " ", 1) AS UNSIGNED)
                        ELSE 30
                    END as estimated_delivery_time')
                ->join('stores', 'items.store_id', '=', 'stores.id')
                ->orderBy('order_count', 'desc') // Popular items by order count
                ->orderBy('estimated_delivery_time', 'asc')
                ->orderByRaw('ST_Distance_Sphere(point(stores.longitude, stores.latitude), point(?, ?))', [$longitude, $latitude]);
            
            $total_size = (clone $items_query)->count();
            $items_list = $items_query->skip($offset)->take($limit)->get();
            
            $items = [
                'total_size' => $total_size,
                'limit' => $limit,
                'offset' => $offset,
                'products' => $items_list
            ];
        } else {
            // Original logic for non-30-minute delivery
            if (!$request->hasHeader('zoneId')) {
                $errors = [];
                array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }

            $type = $request->query('type', 'all');
            $zone_id = $request->header('zoneId');
            $items = ProductLogic::popular_products($zone_id, $request['limit'], $request['offset'], $type);
        }
        
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        
        // Format items in the requested format if delivery_type is 30_minute
        if ($delivery_type === '30_minute') {
            $formatted_items = [];
            foreach ($items['products'] as $item) {
                $estimated_delivery_time = 30; // Default
                if (isset($item->estimated_delivery_time)) {
                    $estimated_delivery_time = (int) $item->estimated_delivery_time;
                } elseif (isset($item->store) && isset($item->store->delivery_time) && $item->store->delivery_time) {
                    $delivery_time_str = $item->store->delivery_time;
                    if (preg_match('/(\d+)\s*(?:hours?|hrs?)/i', $delivery_time_str, $matches)) {
                        $estimated_delivery_time = (int)$matches[1] * 60;
                    } elseif (preg_match('/(\d+)\s*(?:min|minutes?)/i', $delivery_time_str, $matches)) {
                        $estimated_delivery_time = (int)$matches[1];
                    }
                }
                
                $formatted_items[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'estimated_delivery_time' => (int) $estimated_delivery_time,
                    'eta_label' => "ETA {$estimated_delivery_time} mins"
                ];
            }
            
            return response()->json([
                'items' => $formatted_items,
                'total_size' => $items['total_size'],
                'limit' => $items['limit'],
                'offset' => $items['offset']
            ], 200);
        }
        
        return response()->json($items, 200);
    }

    public function get_most_reviewed_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');

        $zone_id= $request->header('zoneId');
        $items = ProductLogic::most_reviewed_products($zone_id, $request['limit'], $request['offset'], $type);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_discounted_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $category_ids = $request->query('category_ids', '');

        $zone_id= $request->header('zoneId');
        $items = ProductLogic::discounted_products($zone_id, $request['limit'], $request['offset'], $type, $category_ids);
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_cart_suggest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');

        $type = $request->query('type', 'all');
        $recommended = $request->query('recommended');

        $items = ProductLogic::cart_suggest_products($zone_id, $request['store_id'], $request['limit'], $request['offset'], $type,$recommended);
        $items['items'] = Helpers::product_data_formatting($items['items'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    /**
     * Get detailed product information
     * 
     * @param string|int $id Product ID or slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_product($id)
    {
        try {
            // Validate input
            if (empty($id)) {
                return response()->json([
                    'errors' => [
                        ['code' => 'product-001', 'message' => translate('messages.invalid_data')]
                    ]
                ], 400);
            }

            // Load product with all necessary relationships
            $item = Item::withCount('whislists')
                ->with([
                    'tags',
                    'nutritions',
                    'allergies',
                    'generic',
                    'reviews' => function($query) {
                        $query->with('customer')->active()->latest()->limit(10);
                    },
                    'store',
                    'category',
                    'unit',
                    'module',
                    'pharmacy_item_details',
                    'ecommerce_item_details'
                ])
                ->active()
                ->when(config('module.current_module_data'), function($query) {
                    $query->module(config('module.current_module_data')['id']);
                })
                ->when(is_numeric($id), function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->when(!is_numeric($id), function ($query) use ($id) {
                    $query->where('slug', $id);
                })
                ->first();

            // Check if product exists
            if (!$item) {
                return response()->json([
                    'errors' => [
                        ['code' => 'product-002', 'message' => translate('messages.product_not_found')]
                    ]
                ], 404);
            }

            // Get store details
            $store = StoreLogic::get_store_details($item->store_id);
            $storeDetails = null;
            $storeZoneId = null;
            
            if ($store) {
                // Store zone_id before formatting (for related products filtering)
                $storeZoneId = $store->zone_id ?? null;
                
                // Get store category IDs
                $category_ids = DB::table('items')
                    ->join('categories', 'items.category_id', '=', 'categories.id')
                    ->selectRaw('categories.position as positions, IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
                    ->where('items.store_id', $item->store_id)
                    ->where('categories.status', 1)
                    ->groupBy('categories', 'positions')
                    ->get();

                $store = Helpers::store_data_formatting($store);
                $store['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
                $store['category_details'] = Category::whereIn('id', $store['category_ids'])->get();
                
                // Get store price range
                $price_range = Item::withoutGlobalScopes()
                    ->where('store_id', $item->store_id)
                    ->select(DB::raw('MIN(price) AS min_price, MAX(price) AS max_price'))
                    ->first();
                
                $store['price_range'] = [
                    'min_price' => $price_range->min_price ?? 0,
                    'max_price' => $price_range->max_price ?? 0
                ];
                
                $storeDetails = $store;
            }

            // Format product data
            $product = Helpers::product_data_formatting($item, false, false, app()->getLocale());
            
            // Get reviews summary
            $reviewsSummary = [
                'total_reviews' => $item->rating_count ?? 0,
                'average_rating' => (float) ($item->avg_rating ?? 0),
                'rating_breakdown' => $this->getRatingBreakdown($item->id),
            ];

            // Get related products (same category, excluding current product)
            $relatedProductsQuery = Item::active()
                ->with(['store', 'category'])
                ->where('category_id', $item->category_id)
                ->where('id', '!=', $item->id)
                ->when(config('module.current_module_data') && isset(config('module.current_module_data')['id']), function($query) {
                    $moduleId = config('module.current_module_data')['id'];
                    $query->module($moduleId);
                })
                ->when($storeZoneId, function($query) use ($storeZoneId) {
                    // Filter by store's zone if available
                    $query->whereHas('store', function($q) use ($storeZoneId) {
                        $q->where('zone_id', $storeZoneId);
                    });
                });

            // Get related products count
            $relatedProductsCount = (clone $relatedProductsQuery)->count();
            
            // Get related products list (limit to 20)
            $relatedProducts = (clone $relatedProductsQuery)
                ->orderBy('order_count', 'desc')
                ->orderBy('avg_rating', 'desc')
                ->limit(20)
                ->get();
            
            // Format related products
            $relatedProductsFormatted = Helpers::product_data_formatting($relatedProducts, true, false, app()->getLocale());

            // Build comprehensive response
            $response = [
                'product' => $product,
                'store_details' => $storeDetails,
                'reviews_summary' => $reviewsSummary,
                'related_products_count' => $relatedProductsCount,
                'related_products' => $relatedProductsFormatted,
                'is_wishlisted' => auth('api')->check() ? $item->whislists()->where('user_id', auth('api')->id())->exists() : false,
            ];

            return response()->json($response, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [
                    ['code' => 'product-003', 'message' => translate('messages.something_went_wrong')]
                ]
            ], 500);
        }
    }

    /**
     * Get rating breakdown for a product
     * 
     * @param int $itemId
     * @return array
     */
    private function getRatingBreakdown($itemId)
    {
        try {
            $breakdown = Review::where('item_id', $itemId)
                ->active()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            return [
                '5' => (int) ($breakdown[5] ?? 0),
                '4' => (int) ($breakdown[4] ?? 0),
                '3' => (int) ($breakdown[3] ?? 0),
                '2' => (int) ($breakdown[2] ?? 0),
                '1' => (int) ($breakdown[1] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                '5' => 0,
                '4' => 0,
                '3' => 0,
                '2' => 0,
                '1' => 0,
            ];
        }
    }

    public function get_related_products(Request $request,$id)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');
        if (Item::find($id)) {
            $items = ProductLogic::get_related_products($zone_id,$id);
            $items = Helpers::product_data_formatting($items, true, false, app()->getLocale());
            return response()->json($items, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }
    public function get_related_store_products(Request $request,$id)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id= $request->header('zoneId');
        if (Item::find($id)) {
            $items = ProductLogic::get_related_store_products($zone_id,$id);
            $items = Helpers::product_data_formatting($items, true, false, app()->getLocale());
            return response()->json($items, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }

    public function get_recommended(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $filter = $request->query('filter', 'all');

        $zone_id= $request->header('zoneId');
        $items = ProductLogic::recommended_items($zone_id, $request->store_id,$request['limit'], $request['offset'], $type, $filter);
        $items['items'] = Helpers::product_data_formatting($items['items'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_set_menus()
    {
        try {
            $items = Helpers::product_data_formatting(Item::active()->with(['rating'])->where(['set_menu' => 1, 'status' => 1])->get(), true, false, app()->getLocale());
            return response()->json($items, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Set menu not found!']
            ], 404);
        }
    }

    public function get_product_reviews(Request $request, $item_id)
    {
        if(isset($request['limit']) && ($request['limit'] != null) && isset($request['offset']) && ($request['offset'] != null)){

            $reviews = Review::with(['customer', 'item'])->where(['item_id' => $item_id])->active()->paginate($request['limit'], ['*'], 'page', $request['offset']);
            $total = $reviews->total();
        }else{

            $reviews = Review::with(['customer', 'item'])->where(['item_id' => $item_id])->active()->get();
            $total = $reviews->count();
        }

        $storage = [];
        foreach ($reviews as $temp) {
            $temp['attachment'] = json_decode($temp['attachment']);
            $temp['item_name'] = null;
            if($temp->item)
            {
                $temp['item_name'] = $temp->item->name;
                if(count($temp->item->translations)>0)
                {
                    $translate = array_column($temp->item->translations->toArray(), 'value', 'key');
                    $temp['item_name'] = $translate['name'];
                }
            }

            unset($temp['item']);
            array_push($storage, $temp);
        }

        $data =  [
            'total_size' => $total,
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'reviews' => $storage
        ];

        return response()->json($data, 200);
    }

    public function get_product_rating($id)
    {
        try {
            $item = Item::find($id);
            $overallRating = ProductLogic::get_overall_rating($item->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required|numeric|max:5',
        ]);

        $order = Order::find($request->order_id);
        if (isset($order) == false) {
            $validator->errors()->add('order_id', translate('messages.order_data_not_found'));
        }

        $item = Item::find($request->item_id);
        if (isset($order) == false) {
            $validator->errors()->add('item_id', translate('messages.item_not_found'));
        }

        $multi_review = Review::where(['item_id' => $request->item_id, 'user_id' => $request->user()->id, 'order_id'=>$request->order_id])->first();
        if (isset($multi_review)) {
            return response()->json([
                'errors' => [
                    ['code'=>'review','message'=> translate('messages.already_submitted')]
                ]
            ], 403);
        } else {
            $review = new Review;
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image_array = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                if ($image != null) {
                    if (!Storage::disk('public')->exists('review')) {
                        Storage::disk('public')->makeDirectory('review');
                    }
                    array_push($image_array, Storage::disk('public')->put('review', $image));
                }
            }
        }

        $order?->OrderReference?->update([
            'is_reviewed' => 1
        ]);

        $review->user_id = $request->user()->id;
        $review->item_id = $request->item_id;
        $review->order_id = $request->order_id;
        $review->module_id = $order->module_id;
        $review->comment = $request?->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();

        if($item->store)
        {
            $store_rating = StoreLogic::update_store_rating($item->store->rating, (int)$request->rating);
            $item->store->rating = $store_rating;
            $item->store->save();
        }

        $item->rating = ProductLogic::update_rating($item->rating, (int)$request->rating);
        $item->avg_rating = ProductLogic::get_avg_rating(json_decode($item->rating, true));
        $item->save();
        $item->increment('rating_count');

        return response()->json(['message' => translate('messages.review_submited_successfully')], 200);
    }

    public function item_or_store_search(Request $request){

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        if (!$request->hasHeader('longitude') || !$request->hasHeader('latitude')) {
            $errors = [];
            array_push($errors, ['code' => 'longitude-latitude', 'message' => translate('messages.longitude-latitude_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        $zone_id= $request->header('zoneId');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $key = explode(' ', $request->name);

        $items = Item::active()->whereHas('store', function($query)use($zone_id){
            $query->when(config('module.current_module_data'), function($query){
                $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules',function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                });
            })->whereIn('zone_id', json_decode($zone_id, true));
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orwhere('name', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%");
            }

            $relationships = [
                'translations' => 'value',
                'tags' => 'tag',
                'nutritions' => 'nutrition',
                'allergies' => 'allergy',
                'category.parent' => 'name',
                'category' => 'name',
                'generic' => 'generic_name',
                'ecommerce_item_details.brand' => 'name',
                'pharmacy_item_details.common_condition' => 'name',
            ];
            $q->applyRelationShipSearch(relationships:$relationships ,searchParameter:$key);
        })
        ->limit(50)
        ->get(['id','name','image']);

        $stores = Store::
        whereHas('zone.modules', function($query){
            $query->where('modules.id', config('module.current_module_data')['id']);
        })
        ->withOpen($longitude??0,$latitude??0)
        ->with(['discount'=>function($q){
            return $q->validate();
        }])->weekday()

        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }

            $relationships = [
                'translations' => 'value',
                'items.nutritions' => 'nutrition',
                'items.allergies' => 'allergy',
                'items.generic' => 'generic_name',
                'items.ecommerce_item_details.brand' => 'name',
                'items.pharmacy_item_details.common_condition' => 'name'
            ];
            $q->applyRelationShipSearch(relationships:$relationships ,searchParameter:$key);
        })
        ->when(config('module.current_module_data'), function($query)use($zone_id){
            $query->module(config('module.current_module_data')['id']);
            if(!config('module.current_module_data')['all_zone_service']) {
                $query->whereIn('zone_id', json_decode($zone_id, true));
            }
        })
        ->active()
        ->limit(50)
        ->select(['id','name','logo'])
        ->get();

        return [
            'items' => $items,
            'stores' => $stores
        ];

    }

    public function get_store_condition_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id= $request->header('zoneId');

        $type = $request->query('type', 'all');
        $limit = $request['limit'];
        $offset = $request['offset'];

        $paginator = Item::
        whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })
        ->whereHas('store', function($query)use($zone_id){
            $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                $query->when(config('module.current_module_data'), function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                });
            });
        })
        ->whereHas('pharmacy_item_details',function($q){
            return $q->whereNotNull('common_condition_id');
        })
        ->whereHas('ecommerce_item_details',function($q){
            return $q->whereNotNull('brand_id');
        })
        ->when(is_numeric($request->store_id),function ($qurey) use($request){
            $qurey->where('store_id', $request->store_id);
        })
        ->when(!is_numeric($request->store_id), function ($query) use ($request) {
            $query->whereHas('store', function ($q) use ($request) {
                $q->where('slug', $request->store_id);
            });
        })
        ->active()->type($type)->latest()->paginate($limit, ['*'], 'page', $offset);
        $data=[
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
        $data['products'] = Helpers::product_data_formatting($data['products'] , true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_popular_basic_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id')??null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $limit = $request['limit']??25;
        $offset = $request['offset']??1;

        $items = ProductLogic::get_popular_basic_products($zone_id, $limit, $offset, $type, $request['store_id'], $request['category_id'], $min,$max,$product_id);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_products(Request $request)
    {
        $data_type = $request->query('data_type', 'all');
        $delivery_type = $request->query('delivery_type');
        
        // Get latitude/longitude from query parameters first, then fallback to headers
        $longitude = $request->query('longitude') ?? $request->header('longitude');
        $latitude = $request->query('latitude') ?? $request->header('latitude');
        
        // Get module_id from query parameter or header
        $module_id = (int) ($request->query('module_id') ?? $request->header('moduleId') ?? config('module.current_module_data')['id'] ?? null);
        
        // If delivery_type is 30_minute, use the thirty-minute delivery method
        if ($delivery_type === '30_minute' && $latitude && $longitude && $module_id) {
            return $this->get_thirty_minute_delivery_items($request);
        }

        $zone_id = $request->header('zoneId');
        
        // If zoneId is not provided but latitude/longitude are, try to find zone
        if (!$zone_id && $latitude && $longitude) {
            try {
                $zone = Zone::whereContains('coordinates', new Point($latitude, $longitude, POINT_SRID))->first();
                if ($zone) {
                    $zone_id = json_encode([$zone->id]);
                }
            } catch (\Exception $e) {
                // If zone detection fails, continue without zone_id
            }
        }
        
        // If still no zone_id, use empty array
        // Note: This will only work if module has all_zone_service enabled
        // Otherwise, it will return no items (which is expected behavior)
        if (!$zone_id) {
            $zone_id = json_encode([]);
        }
        
        $type = $request->query('type', 'all');
        $filter = $request->query('filter', '');
        $filter = $filter?(is_array($filter)?$filter:str_getcsv(trim($filter, "[]"), ',')):'';
        $category_ids = $request->query('category_ids', '');

        // Common parameters for all product types
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $rating_count = $request->query('rating_count');
        $product_id = $request->query('product_id');

        switch ($data_type) {
            case 'searched':
                return $this->get_searched_products($request);
                break;
            case 'discounted':
                $items = ProductLogic::discounted_products($zone_id, $limit, $offset, $type, $category_ids, $filter, $min_price, $max_price, $rating_count);
                break;
            case 'new':
                $items = ProductLogic::get_new_products($zone_id, $type, $min_price, $max_price, $product_id, $limit, $offset, $filter, $rating_count);
                break;
            case 'category':
                $validator = Validator::make($request->all(), [
                    'category_ids' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => Helpers::error_processor($validator)], 403);
                }

                $items = CategoryLogic::category_products($category_ids, $zone_id, $limit, $offset, $type, $filter, $min_price, $max_price, $rating_count);
                break;
            default:
            $items =  [
                'total_size' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'products' => [],
                'categories' => [],
            ];
        }

        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        
        // Format items in the requested format
        $formatted_items = [];
        foreach ($items['products'] as $item) {
            // Get estimated delivery time from store's delivery_time
            $estimated_delivery_time = 30; // Default
            
            // Try to get store_id from item
            $store_id = null;
            if (isset($item->store_id)) {
                $store_id = $item->store_id;
            } elseif (isset($item->store) && isset($item->store->id)) {
                $store_id = $item->store->id;
            }
            
            if ($store_id) {
                // Fetch store to get delivery_time
                $store = Store::find($store_id);
                if ($store && $store->delivery_time) {
                    $delivery_time_str = $store->delivery_time;
                    if (preg_match('/(\d+)\s*(?:hours?|hrs?)/i', $delivery_time_str, $matches)) {
                        $estimated_delivery_time = (int)$matches[1] * 60;
                    } elseif (preg_match('/(\d+)\s*(?:min|minutes?)/i', $delivery_time_str, $matches)) {
                        $estimated_delivery_time = (int)$matches[1];
                    }
                }
            }
            
            $formatted_items[] = [
                'id' => $item->id,
                'name' => $item->name,
                'estimated_delivery_time' => (int) $estimated_delivery_time,
                'eta_label' => "ETA {$estimated_delivery_time} mins"
            ];
        }

        return response()->json([
            'items' => $formatted_items
        ], 200);
    }



    public function getGenericNameList(){
        $names= GenericName::select(['generic_name'])->pluck('generic_name');
        return response()->json($names, 200);
    }
    public function getAllergyNameList(){
        $names= Allergy::select(['allergy'])->pluck('allergy');
        return response()->json($names, 200);
    }
    public function getNutritionNameList(){
        $names= Nutrition::select(['nutrition'])->pluck('nutrition');
        return response()->json($names, 200);
    }

    public function get_thirty_minute_delivery_items(Request $request)
    {
        // Read from query parameters first, then fallback to request body
        $latitude = $request->query('latitude') ?? $request->input('latitude');
        $longitude = $request->query('longitude') ?? $request->input('longitude');
        $limit = $request->query('limit') ?? $request->input('limit', 20);
        $offset = $request->query('offset') ?? $request->input('offset', 0);
        
        $validator = Validator::make([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'offset' => $offset,
            'limit' => $limit,
        ], [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        $limit = (int) $limit;
        $offset = (int) $offset;
        // Get module_id from query parameter (preferred) or header (fallback)
        $module_id = (int) ($request->query('module_id') ?? $request->header('moduleId') ?? config('module.current_module_data')['id'] ?? null);
        
        if (!$module_id) {
            $errors = [];
            array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.module_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        // Get stores within 30-minute delivery range (typically 5km radius)
        $max_distance = 5000; // 5km in meters
        $max_delivery_time = 30; // 30 minutes

        $items = Item::where('items.status', 1)
            ->where('items.is_approved', 1)
            ->whereHas('store', function($query) use ($latitude, $longitude, $max_distance, $module_id) {
                $query->where('module_id', $module_id)
                    ->where('active', 1)
                    ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
                    ->whereRaw("CASE 
                        WHEN delivery_time IS NULL THEN 0
                        WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                        WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                        ELSE 0
                    END <= 30");
            })
            ->with(['store' => function($query) use ($latitude, $longitude) {
                $query->selectRaw('stores.*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude]);
            }])
            ->selectRaw('items.*, 
                CASE 
                    WHEN stores.delivery_time IS NULL THEN 30
                    WHEN stores.delivery_time LIKE "%hours%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(stores.delivery_time, "-", 1), " ", 1) AS UNSIGNED) * 60
                    WHEN stores.delivery_time LIKE "%min%" OR stores.delivery_time LIKE "%minute%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(stores.delivery_time, "-", 1), " ", 1) AS UNSIGNED)
                    ELSE 30
                END as estimated_delivery_time')
            ->join('stores', 'items.store_id', '=', 'stores.id')
            ->orderBy('estimated_delivery_time', 'asc')
            ->orderByRaw('ST_Distance_Sphere(point(stores.longitude, stores.latitude), point(?, ?))', [$longitude, $latitude])
            ->skip(($offset - 1) * $limit)
            ->take($limit)
            ->get();

        $total_size = Item::where('items.status', 1)
            ->where('items.is_approved', 1)
            ->whereHas('store', function($query) use ($latitude, $longitude, $max_distance, $module_id) {
                $query->where('module_id', $module_id)
                    ->where('active', 1)
                    ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
                    ->whereRaw("CASE 
                        WHEN delivery_time IS NULL THEN 0
                        WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                        WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                        ELSE 0
                    END <= 30");
            })
            ->count();

        // Format items with 30-minute delivery data
        $formatted_items = [];
        foreach ($items as $item) {
            $estimated_time = $item->estimated_delivery_time ?? 30;
            $distance = $item->store->distance ?? 0;
            
            $formatted_item = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image_full_url' => $item->image_full_url,
                'price' => (float) $item->price,
                'discount' => (float) $item->discount,
                'discount_type' => $item->discount_type ?? 'percent',
                'store_id' => $item->store_id,
                'store_name' => $item->store->name ?? '',
                'category_id' => $item->category_id,
                'avg_rating' => (float) ($item->avg_rating ?? 0),
                'rating_count' => (int) ($item->rating_count ?? 0),
                'estimated_delivery_time' => (int) $estimated_time,
                'eta_label' => "ETA {$estimated_time} mins",
                'tags' => $item->tags->pluck('tag')->toArray() ?? [],
                'variations' => json_decode($item->variations ?? '[]', true),
                'add_ons' => json_decode($item->add_ons ?? '[]', true),
                'stock' => (int) ($item->stock ?? 0),
                'available_time_starts' => $item->available_time_starts ?? '08:00',
                'available_time_ends' => $item->available_time_ends ?? '22:00',
            ];
            
            $formatted_items[] = $formatted_item;
        }

        return response()->json([
            'items' => $formatted_items,
            'total_size' => $total_size,
            'limit' => $limit,
            'offset' => $offset
        ], 200);
    }

    /**
     * Get paginated product listing with filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list_products(Request $request)
    {
        // Validate pagination parameters
        $validator = Validator::make($request->all(), [
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:1',
            'category_id' => 'integer',
            'store_id' => 'integer',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'type' => 'in:all,veg,non_veg',
            'sort_by' => 'in:latest,popular,price_asc,price_desc,rating,discounted',
            'rating_count' => 'numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Get zone_id from header or try to derive from lat/lng
        $zone_id = $request->header('zoneId');
        
        // Try to get zone from latitude/longitude if zone_id is not provided
        if (!$zone_id) {
            $latitude = $request->query('latitude') ?? $request->header('latitude');
            $longitude = $request->query('longitude') ?? $request->header('longitude');
            
            if ($latitude && $longitude) {
                try {
                    // Find zone(s) that contain the given coordinates
                    // Order by area (smallest first) to get most specific zone first
                    $zones = Zone::whereContains('coordinates', new Point($latitude, $longitude, POINT_SRID))
                        ->where('status', 1)
                        ->selectRaw('id, ABS(ST_Area(coordinates)) as area')
                        ->orderBy('area', 'asc')
                        ->latest()
                        ->get();
                    
                    if ($zones && count($zones) > 0) {
                        // Get zone IDs as array (can be multiple if zones overlap)
                        $zoneIds = $zones->pluck('id')->toArray();
                        $zone_id = json_encode($zoneIds);
                    }
                } catch (\Exception $e) {
                    // If zone detection fails, continue without zone_id
                    // This allows the API to work without zone filtering
                }
            }
        }

        // Get pagination parameters
        $limit = $request->query('limit', 20);
        $offset = $request->query('offset', 1);
        
        // Get filter parameters
        $category_id = $request->query('category_id');
        $store_id = $request->query('store_id');
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $type = $request->query('type', 'all');
        $sort_by = $request->query('sort_by', 'latest');
        $rating_count = $request->query('rating_count');

        // Build query
        $query = Item::active()->type($type)
            ->with(['store', 'category'])
            ->when(config('module.current_module_data') && isset(config('module.current_module_data')['id']), function($query) {
                $moduleId = config('module.current_module_data')['id'];
                $query->module($moduleId);
            })
            ->when($category_id, function($query) use ($category_id) {
                $query->whereHas('category', function($q) use ($category_id) {
                    $q->where('id', $category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->when($store_id, function($query) use ($store_id) {
                $query->where('store_id', $store_id);
            })
            ->when($min_price && $max_price, function($query) use ($min_price, $max_price) {
                $query->whereBetween('price', [$min_price, $max_price]);
            })
            ->when($min_price && !$max_price, function($query) use ($min_price) {
                $query->where('price', '>=', $min_price);
            })
            ->when($max_price && !$min_price, function($query) use ($max_price) {
                $query->where('price', '<=', $max_price);
            })
            ->when($rating_count, function($query) use ($rating_count) {
                $query->where('avg_rating', '>=', $rating_count);
            });

        // Apply zone filter if zone_id is provided
        if ($zone_id) {
            $query->whereHas('module.zones', function($query) use ($zone_id) {
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function($query) use ($zone_id) {
                $query->whereIn('zone_id', json_decode($zone_id, true))
                    ->when(config('module.current_module_data') && isset(config('module.current_module_data')['id']), function($query) {
                        $moduleId = config('module.current_module_data')['id'];
                        $query->where('module_id', $moduleId)
                            ->whereHas('zone.modules', function($query) use ($moduleId) {
                                $query->where('modules.id', $moduleId);
                            });
                    });
            });
        }

        // Apply sorting
        switch ($sort_by) {
            case 'popular':
                $query->popular();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('avg_rating', 'desc');
                break;
            case 'discounted':
                $query->where('discount', '>', 0)->orderBy('discount', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Execute pagination
        $paginator = $query->paginate($limit, ['*'], 'page', $offset);
        
        // Format products
        $products = Helpers::product_data_formatting($paginator->items(), true, false, app()->getLocale());

        // Build response
        $data = [
            'total_size' => $paginator->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'products' => $products,
        ];

        return response()->json($data, 200);
    }
}
