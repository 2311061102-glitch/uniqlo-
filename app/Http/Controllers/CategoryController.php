<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * GET /danh-muc — hiện toàn bộ danh mục cấp cao nhất (không phải danh mục con).
     */
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return view('categories.index', compact('categories'));
    }
}
