<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('id');
        $show_produuct = $request->input('show_produuct');

        if($id)
        {
            $category = ProductCategory::with(['products'])->find($id);

            if($category){
                return ResponseFormatter::success(
                    $category,
                    'Data category berhasil diambil'
                );
            }
            else {
                return ResponseFormatter::error(
                    null,
                    'Data category gagal diambil bro'
                );
            }
        }

        $category = ProductCategory::query();

        if($name){
            $category->where('name', 'like', '%' . $name . '%');
        }

        if($show_produuct) {
            $category->with('products');
        }

        return ResponseFormatter::success(
            $category->paginate($limit),
            'Yeay data List category behasil di ambil'
        );
    }
}