<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockStatsController extends Controller
{
    public function index()
    {
        try {
            // Produits avec stock bas
            $lowStockProducts = Product::where('stock_quantity', '<=', DB::raw('alert_threshold'))
                ->with('category')
                ->get();

            // Statistiques générales
            $stats = [
                'low_stock_products' => $lowStockProducts->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock_quantity' => $product->stock_quantity,
                        'alert_threshold' => $product->alert_threshold,
                        'category' => $product->category->name
                    ];
                }),
                'total_products' => Product::count(),
                'total_categories' => Category::count(),
                'stock_value' => number_format(Product::sum(DB::raw('price * stock_quantity')), 2),
                'products_by_category' => Category::withCount('products')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'category_name' => $category->name,
                            'products_count' => $category->products_count
                        ];
                    }),
                'critical_stock_alerts' => $lowStockProducts->count()
            ];

            return response()->json([
                'status' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}