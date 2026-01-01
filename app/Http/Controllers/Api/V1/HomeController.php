<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DataSetting;
use App\Models\Banner;
use App\Models\Item;
use App\Models\Zone;
use App\Scopes\ZoneScope;
use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class HomeController extends Controller
{

    public function terms_and_conditions(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('terms_and_conditions',$current_language);
        return response()->json($data);
    }

    public function about_us(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('about_us',$current_language);
        return response()->json($data);
    }

    public function privacy_policy(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('privacy_policy',$current_language);
        return response()->json($data);
    }

    public function refund_policy(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('refund_policy',$current_language);
        return response()->json($data);
    }

    public function shipping_policy(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('shipping_policy',$current_language);
        return response()->json($data);
    }

    public function cancelation(Request $request)
    {
        $current_language = $request->header('X-localization') ?? 'en';
        $data = self::get_settings_localization('cancellation_policy',$current_language);
        return response()->json($data);
    }

    public static function get_settings_localization($name,$lang)
    {
        $data = DataSetting::withoutGlobalScope('translate')->with(['translations' => function ($query) use ($lang) {
            return $query->where('locale', $lang);
        }])->where(['key' => $name])->first();
        if($data && count($data->translations)>0){
            $data = $data->translations[0]['value'];
        }else{
            $data = $data ? $data->value: '';
        }
        return $data;
    }

    public function dashboard(Request $request)
    {
        try {
            // Get zone_id from header or detect from latitude/longitude
            $zone_id = $request->header('zoneId');
            
            // If zone_id not provided, try to detect from latitude/longitude
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
            
            $limit = (int)$request->query('limit', 10); // Default limit for products
            $offset = (int)$request->query('offset', 1); // Default offset for products
            
            $sections = [];
            
            // Section 1: Banners (Top Offers)
            $bannerQuery = Banner::withoutGlobalScope(ZoneScope::class)
                ->with('storage')
                ->active()
                ->where('type', 'default');
            
            if($zone_id) {
                $zoneIds = is_array($zone_id) ? $zone_id : json_decode($zone_id, true);
                if($zoneIds && is_array($zoneIds) && count($zoneIds) > 0) {
                    $bannerQuery->whereIn('zone_id', $zoneIds);
                }
            }
            
            $moduleData = config('module.current_module_data');
            if($moduleData && isset($moduleData['id'])) {
                $bannerQuery->where('module_id', $moduleData['id']);
            }
            
            // Order by id (priority column may not exist in all installations)
            $banners = $bannerQuery->orderBy('id', 'desc')->limit(10)->get();
            
            $bannerData = [];
            foreach($banners as $banner) {
                $imageUrl = $banner->image_full_url ?? $banner->image;
                if($imageUrl) {
                    $bannerData[] = [
                        'image' => $imageUrl
                    ];
                }
            }
            
            $sections[] = [
                'id' => '1',
                'type' => 'banner',
                'title' => 'Top Offers',
                'position' => 1,
                'data' => $bannerData
            ];
            
            // Section 2: Featured Products
            $featuredQuery = Item::active()->where('recommended', 1);
            
            if($zone_id) {
                $zoneIds = is_array($zone_id) ? $zone_id : json_decode($zone_id, true);
                if($zoneIds && is_array($zoneIds) && count($zoneIds) > 0) {
                    $featuredQuery->whereHas('store', function($q) use ($zoneIds) {
                        $q->whereIn('zone_id', $zoneIds);
                    });
                }
            }
            
            if($moduleData && isset($moduleData['id'])) {
                $featuredQuery->module($moduleData['id'])
                    ->whereHas('store', function($q) use ($moduleData) {
                        $q->where('module_id', $moduleData['id']);
                    });
            }
            
            $featuredProducts = $featuredQuery->orderBy('order_count', 'desc')->limit($limit)->get();
            
            $featuredData = [];
            foreach($featuredProducts as $product) {
                if($product) {
                    $featuredData[] = [
                        'id' => (string)($product->id ?? ''),
                        'name' => $product->name ?? '',
                        'price' => (float)($product->price ?? 0),
                        'discount' => (float)($product->discount ?? 0),
                        'image' => $product->image_full_url ?? $product->image ?? '',
                        'rating' => (float)($product->avg_rating ?? 0),
                        'store_id' => $product->store_id ?? null
                    ];
                }
            }
            
            $totalFeaturedQuery = Item::active()->where('recommended', 1);
            
            if($zone_id) {
                $zoneIds = is_array($zone_id) ? $zone_id : json_decode($zone_id, true);
                if($zoneIds && is_array($zoneIds) && count($zoneIds) > 0) {
                    $totalFeaturedQuery->whereHas('store', function($q) use ($zoneIds) {
                        $q->whereIn('zone_id', $zoneIds);
                    });
                }
            }
            
            $totalFeatured = $totalFeaturedQuery->count();
            
            $sections[] = [
                'id' => '2',
                'type' => 'products',
                'title' => 'Featured Products',
                'position' => 2,
                'pagination' => [
                    'page' => $offset,
                    'hasMore' => ($offset * $limit) < $totalFeatured
                ],
                'data' => $featuredData
            ];
            
            // Section 3: New Arrivals
            $newProductsQuery = Item::active();
            
            if($zone_id) {
                $zoneIds = is_array($zone_id) ? $zone_id : json_decode($zone_id, true);
                if($zoneIds && is_array($zoneIds) && count($zoneIds) > 0) {
                    $newProductsQuery->whereHas('store', function($q) use ($zoneIds) {
                        $q->whereIn('zone_id', $zoneIds);
                    });
                }
            }
            
            if($moduleData && isset($moduleData['id'])) {
                $newProductsQuery->module($moduleData['id'])
                    ->whereHas('store', function($q) use ($moduleData) {
                        $q->where('module_id', $moduleData['id']);
                    });
            }
            
            $newProducts = $newProductsQuery->orderBy('created_at', 'desc')->limit($limit)->get();
            
            $newArrivalsData = [];
            foreach($newProducts as $product) {
                if($product) {
                    $newArrivalsData[] = [
                        'id' => (string)($product->id ?? ''),
                        'name' => $product->name ?? '',
                        'price' => (float)($product->price ?? 0),
                        'discount' => (float)($product->discount ?? 0),
                        'image' => $product->image_full_url ?? $product->image ?? '',
                        'rating' => (float)($product->avg_rating ?? 0),
                        'store_id' => $product->store_id ?? null
                    ];
                }
            }
            
            $totalNewQuery = Item::active();
            
            if($zone_id) {
                $zoneIds = is_array($zone_id) ? $zone_id : json_decode($zone_id, true);
                if($zoneIds && is_array($zoneIds) && count($zoneIds) > 0) {
                    $totalNewQuery->whereHas('store', function($q) use ($zoneIds) {
                        $q->whereIn('zone_id', $zoneIds);
                    });
                }
            }
            
            $totalNew = $totalNewQuery->count();
            
            $sections[] = [
                'id' => '3',
                'type' => 'products',
                'title' => 'New Arrivals',
                'position' => 3,
                'pagination' => [
                    'page' => $offset,
                    'hasMore' => ($offset * $limit) < $totalNew
                ],
                'data' => $newArrivalsData
            ];
            
            return response()->json([
                'sections' => $sections
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching dashboard data',
                'message' => $e->getMessage(),
                'sections' => []
            ], 500);
        }
    }
}
