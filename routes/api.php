<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\StockStatsController;
use App\Http\Controllers\API\ClientProductController;


// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées pour tous les utilisateurs authentifiés
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Routes pour les administrateurs uniquement
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json([
                'status' => true,
                'message' => 'Bienvenue dans le tableau de bord admin'
            ]);
        });
    });
    
    // Routes pour les clients uniquement
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', function () {
            return response()->json([
                'status' => true,
                'message' => 'Bienvenue dans le tableau de bord client'
            ]);
        });
    });
});

// Routes protégées pour l'administrateur
// Routes protégées pour l'administrateur
// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
//     // Routes pour les rayons
//     Route::apiResource('categories', CategoryController::class);
    
//     // Routes pour les produits
//     Route::apiResource('products', ProductController::class);
    
//     // Route pour les statistiques
//     Route::get('stats/stock', [StockStatsController::class, 'index']);

// });

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes pour les rayons
    Route::apiResource('categories', CategoryController::class);
    
    // Routes pour les produits
    Route::apiResource('products', ProductController::class);
    
    // Routes pour les produits par rayon
    Route::apiResource('categories.products', CategoryProductController::class);
    
    // Route pour déplacer un produit vers un autre rayon
    Route::post('categories/{category}/products/{product}/move', [CategoryProductController::class, 'moveToCategory']);
    
    // Routes pour les promotions
    Route::post('products/{product}/promotion', [ProductController::class, 'addPromotion']);
    Route::delete('products/{product}/promotion', [ProductController::class, 'endPromotion']);
    
    // Route pour rechercher des produits
    Route::get('products/search', [ProductController::class, 'search']);
    
    // Route pour obtenir les produits disponibles par catégorie
    Route::get('categories/{category}/available-products', [ProductController::class, 'getAvailableByCategory']);
    
    // Route pour les statistiques
    Route::get('stats/stock', [StockStatsController::class, 'index']);
});


Route::apiResource('products', ProductController::class);

Route::prefix('client')->group(function () {
    // Liste des rayons disponibles
    Route::get('categories', [ClientProductController::class, 'getCategories']);
    
    // Produits disponibles par rayon
    Route::get('categories/{category}/products', [ClientProductController::class, 'getProductsByCategory']);
    
    // Recherche de produits
    Route::get('products/search', [ClientProductController::class, 'search']);
    
    // Produits en promotion (tous les rayons)
    Route::get('products/promotional', [ClientProductController::class, 'getPromotionalProducts']);
    
    // Produits en promotion par rayon
    Route::get('categories/{category}/promotional', [ClientProductController::class, 'getPromotionalProducts']);
});