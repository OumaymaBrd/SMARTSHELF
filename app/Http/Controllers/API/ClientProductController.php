<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientProductController extends Controller
{
    /**
     * Liste des produits disponibles par rayon
     */
    public function getProductsByCategory(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'current_price' => $product->current_price,
                    'is_on_promotion' => $product->is_on_promotion,
                    'stock_quantity' => $product->stock_quantity,
                    'category_name' => $product->category->name
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'category' => $category->name,
                'products' => $products
            ]
        ]);
    }

    /**
     * Recherche de produits par nom ou catégorie
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $query = Product::where('stock_quantity', '>', 0)
            ->with('category');

        // Recherche par nom - utiliser $request->input('query') au lieu de $request->query
        $query->where('name', 'like', '%' . $request->input('query') . '%');

        // Filtrer par catégorie si spécifié
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'current_price' => $product->current_price,
                'is_on_promotion' => $product->is_on_promotion,
                'promotion_price' => $product->when($product->is_on_promotion, function ($query) {
                    return $query->promotion_price;
                }),
                'promotion_end_date' => $product->when($product->is_on_promotion, function ($query) {
                    return $query->promotion_end_date;
                }),
                'stock_quantity' => $product->stock_quantity,
                'category_name' => $product->category->name
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }
    /**
     * Produits populaires et en promotion par rayon
     */
    public function getPromotionalProducts(Category $category = null)
    {
        $query = Product::where('stock_quantity', '>', 0)
            ->with('category');

        if ($category) {
            $query->where('category_id', $category->id);
        }

        // Obtenir les produits en promotion
        $today = Carbon::today();
        $promotionalProducts = (clone $query)
            ->whereNotNull('promotion_price')
            ->whereDate('promotion_start_date', '<=', $today)
            ->whereDate('promotion_end_date', '>=', $today)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'original_price' => $product->price,
                    'promotion_price' => $product->promotion_price,
                    'stock_quantity' => $product->stock_quantity,
                    'category_name' => $product->category->name,
                    'promotion_end_date' => $product->promotion_end_date
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'category' => $category ? $category->name : 'Tous les rayons',
                'promotional_products' => $promotionalProducts
            ]
        ]);
    }

    /**
     * Liste de tous les rayons disponibles
     */
    public function getCategories()
    {
        $categories = Category::whereHas('products', function ($query) {
            $query->where('stock_quantity', '>', 0);
        })->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}