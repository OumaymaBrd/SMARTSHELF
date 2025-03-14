<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Recherche par nom
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtrer par catégorie
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrer par disponibilité de stock
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock_quantity', '>', 0);
        }

        // Filtrer par promotion
        if ($request->has('on_promotion') && $request->on_promotion) {
            $today = Carbon::today()->format('Y-m-d');
            $query->whereNotNull('promotion_price')
                  ->whereDate('promotion_start_date', '<=', $today)
                  ->whereDate('promotion_end_date', '>=', $today);
        }

        $products = $query->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'promotion_price' => 'nullable|numeric|min:0',
            'promotion_start_date' => 'nullable|date|required_with:promotion_price',
            'promotion_end_date' => 'nullable|date|required_with:promotion_price|after_or_equal:promotion_start_date'
        ]);

        $product = Product::create($validated);

        if ($product->isLowStock()) {
            $message = "Attention : Le produit {$product->name} a un stock bas ({$product->stock_quantity} unités)";
        }

        return response()->json([
            'status' => true,
            'message' => 'Produit créé avec succès',
            'data' => $product->load('category'),
            'alert' => $product->isLowStock() ? $message : null
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'data' => $product->load('category')
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'promotion_price' => 'nullable|numeric|min:0',
            'promotion_start_date' => 'nullable|date|required_with:promotion_price',
            'promotion_end_date' => 'nullable|date|required_with:promotion_price|after_or_equal:promotion_start_date'
        ]);

        // Vérifier si la promotion est terminée
        if ($product->is_on_promotion && 
            (!$request->filled('promotion_price') || 
             Carbon::parse($request->promotion_end_date)->isPast())) {
            $product->endPromotion();
        }

        $product->update($validated);

        if ($product->isLowStock()) {
            $message = "Attention : Le produit {$product->name} a un stock bas ({$product->stock_quantity} unités)";
        }

        return response()->json([
            'status' => true,
            'message' => 'Produit mis à jour avec succès',
            'data' => $product->fresh()->load('category'),
            'alert' => $product->isLowStock() ? $message : null
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }

    /**
     * Ajouter une promotion à un produit
     */
    public function addPromotion(Request $request, Product $product)
    {
        $validated = $request->validate([
            'promotion_price' => 'required|numeric|min:0|lt:' . $product->price,
            'promotion_start_date' => 'required|date',
            'promotion_end_date' => 'required|date|after_or_equal:promotion_start_date'
        ]);

        // Si une promotion est déjà en cours, l'ajouter à l'historique
        if ($product->is_on_promotion) {
            $product->endPromotion();
        }

        $product->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Promotion ajoutée avec succès',
            'data' => $product->fresh()
        ]);
    }

    /**
     * Terminer une promotion
     */
    public function endPromotion(Product $product)
    {
        if (!$product->is_on_promotion) {
            return response()->json([
                'status' => false,
                'message' => 'Ce produit n\'a pas de promotion en cours'
            ], 400);
        }

        $product->endPromotion();

        return response()->json([
            'status' => true,
            'message' => 'Promotion terminée avec succès',
            'data' => $product->fresh()
        ]);
    }

    /**
     * Rechercher des produits par nom
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $products = Product::where('name', 'like', '%' . $request->query . '%')
            ->with('category')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    /**
     * Obtenir les produits disponibles par catégorie
     */
    public function getAvailableByCategory(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->where('stock_quantity', '>', 0)
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'category' => $category->name,
                'products' => $products
            ]
        ]);
    }
}