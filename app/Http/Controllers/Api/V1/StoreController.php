<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\StoreLogic;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Store;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreController extends Controller
{
    public function get_stores(Request $request, $filter_data="all")
    {
        $type = $request->query('type', 'all');
        $store_type = $request->query('store_type', 'all');
        $delivery_type = $request->query('delivery_type');
        $zone_id = $request->header('zoneId');
        // Get latitude/longitude from query parameters first, then fallback to headers
        $longitude = $request->query('longitude') ?? $request->header('longitude');
        $latitude = $request->query('latitude') ?? $request->header('latitude');
        
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
        // Otherwise, it will return no stores (which is expected behavior)
        if (!$zone_id) {
            $zone_id = json_encode([]);
        }
        
        // If delivery_type is 30_minute, filter stores directly instead of using StoreLogic
        if ($delivery_type === '30_minute' && $latitude && $longitude) {
            $max_distance = 5000; // 5km in meters
            $stores_query = Store::where('active', 1)
                ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
                ->whereRaw("CASE 
                    WHEN delivery_time IS NULL THEN 0
                    WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                    WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                    ELSE 0
                END <= 30")
                ->selectRaw('stores.*, 
                    ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) / 1000 as distance,
                    CASE 
                        WHEN delivery_time IS NULL THEN 30
                        WHEN delivery_time LIKE "%hours%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, "-", 1), " ", 1) AS UNSIGNED) * 60
                        WHEN delivery_time LIKE "%min%" OR delivery_time LIKE "%minute%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, "-", 1), " ", 1) AS UNSIGNED)
                        ELSE 30
                    END as estimated_delivery_time', [$longitude, $latitude])
                ->orderBy('distance', 'asc');
            
            $limit = (int) ($request->query('limit') ?? 20);
            $offset = (int) ($request->query('offset') ?? 0);
            
            $total_size = $stores_query->count();
            $stores_list = $stores_query->skip($offset)->take($limit)->get();
            
            $stores = [
                'total_size' => $total_size,
                'limit' => $limit,
                'offset' => $offset,
                'stores' => $stores_list
            ];
        } else {
            $stores = StoreLogic::get_stores( $zone_id, $filter_data, $type, $store_type, $request['limit'], $request['offset'], $request->query('featured'),$longitude,$latitude);
        }
        
        // Format stores in the requested format
        $max_30_minute_delivery_distance = 5; // 5km
        $formatted_stores = [];
        
        foreach ($stores['stores'] as $store) {
            // Get distance
            $distance = 0;
            if (isset($store->distance)) {
                // If delivery_type is 30_minute, distance is already in km, otherwise it's in meters
                if ($delivery_type === '30_minute') {
                    $distance = (float) $store->distance;
                } else {
                    $distance = $store->distance / 1000; // Convert from meters to km
                }
            } elseif ($latitude && $longitude && isset($store->latitude) && isset($store->longitude)) {
                // Fallback: calculate distance if not already calculated
                $distance = DB::selectOne("SELECT ST_Distance_Sphere(point(?, ?), point(?, ?)) / 1000 as distance", 
                    [$longitude, $latitude, $store->longitude, $store->latitude])->distance ?? 0;
            }
            
            // Get estimated delivery time
            $estimated_delivery_time = 30; // Default
            if (isset($store->estimated_delivery_time)) {
                // If delivery_type is 30_minute, estimated_delivery_time is already calculated
                $estimated_delivery_time = (int) $store->estimated_delivery_time;
            } elseif (isset($store->min_delivery_time) && $store->min_delivery_time != 9999) {
                $estimated_delivery_time = (int) $store->min_delivery_time;
            } elseif (isset($store->delivery_time) && $store->delivery_time) {
                // Fallback: parse delivery_time string
                $delivery_time_str = $store->delivery_time;
                if (preg_match('/(\d+)\s*(?:hours?|hrs?)/i', $delivery_time_str, $matches)) {
                    $estimated_delivery_time = (int)$matches[1] * 60;
                } elseif (preg_match('/(\d+)\s*(?:min|minutes?)/i', $delivery_time_str, $matches)) {
                    $estimated_delivery_time = (int)$matches[1];
                }
            }
            
            // Check if store supports 30-minute delivery
            // Supports if: distance <= 5km AND estimated_delivery_time <= 30 minutes
            $supports_30_minute_delivery = ($distance <= $max_30_minute_delivery_distance && $estimated_delivery_time <= 30);
            
            $formatted_stores[] = [
                'id' => $store->id,
                'name' => $store->name,
                'distance' => round($distance, 1),
                'supports_30_minute_delivery' => $supports_30_minute_delivery,
                'estimated_delivery_time' => (int) $estimated_delivery_time,
                'max_30_minute_delivery_distance' => $max_30_minute_delivery_distance
            ];
        }

        return response()->json([
            'stores' => $formatted_stores
        ], 200);
    }

    public function get_latest_stores(Request $request, $filter_data="all")
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
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $stores = StoreLogic::get_latest_stores($zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        return response()->json($stores, 200);
    }

    public function get_popular_stores(Request $request)
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
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $stores = StoreLogic::get_popular_stores($zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        return response()->json($stores, 200);
    }

    public function get_discounted_stores(Request $request)
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
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $stores = StoreLogic::get_discounted_stores($zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        return response()->json($stores, 200);
    }

    public function get_top_rated_stores(Request $request)
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
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $stores = StoreLogic::get_top_rated_stores($zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        usort($stores['stores'], function ($a, $b) {
            $key = 'avg_rating';
            return $b[$key] - $a[$key];
        });

        return response()->json($stores, 200);
    }

    public function get_popular_store_items($id)
    {
        $items = Item::
        when(is_numeric($id),function ($qurey) use($id){
            $qurey->where('store_id', $id);
        })
        ->when(!is_numeric($id), function ($query) use ($id) {
            $query->whereHas('store', function ($q) use ($id) {
                $q->where('slug', $id);
            });
        })
        ->active()->popular()->limit(10)->get();
        $items = Helpers::product_data_formatting($items, true, true, app()->getLocale());

        return response()->json($items, 200);
    }

    public function get_details(Request $request,$id)
    {
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $store = StoreLogic::get_store_details($id,$longitude,$latitude);
        if($store)
        {
            $category_ids = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw('categories.position as positions, IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
            ->where('items.store_id', $store->id)
            ->where('categories.status',1)
            ->groupBy('categories','positions')
            ->get();

            $store = Helpers::store_data_formatting($store);
            $store['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
            $store['category_details'] = Category::whereIn('id',$store['category_ids'])->get();
            $store['price_range']  = Item::withoutGlobalScopes()->where('store_id', $store->id)
            ->select(DB::raw('MIN(price) AS min_price, MAX(price) AS max_price'))
            ->get(['min_price','max_price'])->toArray();
        }
        return response()->json($store, 200);
    }

    public function get_searched_stores(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request->query('type', 'all');

        $zone_id= $request->header('zoneId');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $stores = StoreLogic::search_stores($request['name'], $zone_id, $request->category_id,$request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);
        return response()->json($stores, 200);
    }

    public function reviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $id = $request['store_id'];


        $reviews = Review::with(['customer', 'item'])
        ->whereHas('item', function($query)use($id){
            return $query->where('store_id', $id);
        })
        ->active()->latest()->get();

        $storage = [];
        foreach ($reviews as $temp) {
            $temp['attachment'] = json_decode($temp['attachment']);
            $temp['item_name'] = null;
            $temp['item_image'] = null;
            $temp['customer_name'] = null;
            // $temp->item=null;
            if($temp->item)
            {
                $temp['item_name'] = $temp->item->name;
                $temp['item_image'] = $temp->item->image;
                $temp['item_image_full_url'] = $temp->item->image_full_url;
                if(count($temp->item->translations)>0)
                {
                    $translate = array_column($temp->item->translations->toArray(), 'value', 'key');
                    $temp['item_name'] = $translate['name'];
                }
                unset($temp->item);
                $temp['item'] = Helpers::product_data_formatting($temp->item, false, false, app()->getLocale());
            }
            if($temp->customer)
            {
                $temp['customer_name'] = $temp->customer->f_name.' '.$temp->customer->l_name;
            }

            unset($temp['customer']);
            array_push($storage, $temp);
        }

        return response()->json($storage, 200);
    }


    public function get_recommended_stores(Request $request){


        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $type = $request->query('type', 'all');
        $zone_id= $request->header('zoneId');
        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;
        $stores = StoreLogic::get_recommended_stores($zone_id, $request['limit'], $request['offset'], $type,$longitude,$latitude);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);

        return response()->json($stores, 200);
    }

    public function get_combined_data(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]];
            return response()->json(['errors' => $errors], 403);
        }

        $zone_id = $request->header('zoneId');
        $data_type = $request->query('data_type', 'all');
        $type = $request->query('type', 'all');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);
        $longitude =  $request->header('longitude') ?? 0;
        $latitude =  $request->header('latitude') ?? 0;
        $filter = $request->query('filter', '');
        $filter = $filter?(is_array($filter)?$filter:str_getcsv(trim($filter, "[]"), ',')):'';
        $rating_count = $request->query('rating_count');

        switch ($data_type) {
            case 'searched':
                $validator = Validator::make($request->all(), ['name' => 'required']);
                if ($validator->fails()) {
                    return response()->json(['errors' => Helpers::error_processor($validator)], 403);
                }
                $name = $request->input('name');

                $paginator = StoreLogic::search_stores($name, $zone_id, $request->category_id, $limit, $offset, $type, $longitude, $latitude, $filter, $rating_count);
                break;

            case 'discounted':

                $paginator = StoreLogic::get_discounted_stores($zone_id, $limit, $offset, $type, $longitude, $latitude, $filter, $rating_count);
                break;

            case 'category':
                $validator = Validator::make($request->all(), [
                    'category_ids' => 'required|array',
                    'category_ids.*' => 'integer'
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => Helpers::error_processor($validator)], 403);
                }

                $category_ids = $request->input('category_ids');

                $paginator = CategoryLogic::category_stores($category_ids, $zone_id, $limit, $offset, $type, $longitude, $latitude, $filter, $rating_count);
                break;

            default:
                $filter_data = $request->query('filter_data', 'all');
                $store_type = $request->query('store_type', 'all');
                $featured = $request->query('featured');
                $paginator = StoreLogic::get_stores($zone_id, $filter_data, $type, $store_type, $limit, $offset, $featured, $longitude, $latitude, $filter, $rating_count);
                break;
        }

        $paginator['stores'] = Helpers::store_data_formatting($paginator['stores'], true);
        return response()->json($paginator, 200);
    }

    public function get_top_offer_near_me(Request $request)
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
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');

        $stores = StoreLogic::get_top_offer_near_me(zone_id:$zone_id, limit:$request['limit'], offset: $request['offset'], type: $type, longitude:$longitude,latitude: $latitude,
                    name:$request->name, sort: $request->sort_by ,halal: $request->halal);
        $stores['stores'] = Helpers::store_data_formatting($stores['stores'], true);


        return response()->json($stores, 200);
    }

    public function get_thirty_minute_delivery_stores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $latitude = (float) $request->latitude;
        $longitude = (float) $request->longitude;
        $limit = (int) $request->limit;
        $offset = (int) $request->offset;
        // Get module_id from header (preferred) or query parameter (fallback for browser testing)
        $module_id = (int) ($request->header('moduleId') ?? $request->query('module_id') ?? config('module.current_module_data')['id'] ?? null);
        
        if (!$module_id) {
            $errors = [];
            array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.module_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        // Get stores within 30-minute delivery range (typically 5km radius)
        $max_distance = 5000; // 5km in meters

        $stores = Store::where('module_id', $module_id)
            ->where('active', 1)
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
            ->whereRaw("CASE 
                WHEN delivery_time IS NULL THEN 0
                WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                ELSE 0
            END <= 30")
            ->selectRaw('stores.*, 
                ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) / 1000 as distance,
                CASE 
                    WHEN delivery_time IS NULL THEN 30
                    WHEN delivery_time LIKE "%hours%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, "-", 1), " ", 1) AS UNSIGNED) * 60
                    WHEN delivery_time LIKE "%min%" OR delivery_time LIKE "%minute%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, "-", 1), " ", 1) AS UNSIGNED)
                    ELSE 30
                END as estimated_delivery_time,
                5.0 as max_30_minute_delivery_distance', [$longitude, $latitude])
            ->orderBy('estimated_delivery_time', 'asc')
            ->orderBy('distance', 'asc')
            ->skip(($offset - 1) * $limit)
            ->take($limit)
            ->get();

        $total_size = Store::where('module_id', $module_id)
            ->where('active', 1)
            ->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [$longitude, $latitude, $max_distance])
            ->whereRaw("CASE 
                WHEN delivery_time IS NULL THEN 0
                WHEN delivery_time LIKE '%hours%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED) * 60
                WHEN delivery_time LIKE '%min%' OR delivery_time LIKE '%minute%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', 1), ' ', 1) AS UNSIGNED)
                ELSE 0
            END <= 30")
            ->count();

        // Format stores with 30-minute delivery data
        $formatted_stores = [];
        foreach ($stores as $store) {
            $estimated_time = $store->estimated_delivery_time ?? 30;
            $distance = $store->distance ?? 0;
            
            $formatted_store = [
                'id' => $store->id,
                'name' => $store->name,
                'image' => $store->logo ? Helpers::get_full_url('store', $store->logo, 'public', 'logo') : null,
                'address' => $store->address,
                'latitude' => (string) $store->latitude,
                'longitude' => (string) $store->longitude,
                'distance' => round($distance, 1),
                'avg_rating' => (float) ($store->rating ?? 0),
                'rating_count' => (int) ($store->reviews_count ?? 0),
                'estimated_delivery_time' => (int) $estimated_time,
                'max_30_minute_delivery_distance' => 5.0,
                'delivery_charge' => (float) ($store->minimum_shipping_charge ?? 0),
                'minimum_order' => (float) ($store->minimum_order ?? 0),
                'schedule_order' => (bool) ($store->schedule_order ?? false),
                'open' => (bool) ($store->active ?? false),
                'active' => (bool) ($store->active ?? false),
            ];
            
            $formatted_stores[] = $formatted_store;
        }

        return response()->json([
            'stores' => $formatted_stores,
            'total_size' => $total_size,
            'limit' => $limit,
            'offset' => $offset
        ], 200);
    }

}
