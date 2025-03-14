<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = Category::create($validated);
        
        return response()->json([
            'status' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'status' => true,
            'data' => $category->load('products')
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category->update($validated);
        
        return response()->json([
            'status' => true,
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }


    
}