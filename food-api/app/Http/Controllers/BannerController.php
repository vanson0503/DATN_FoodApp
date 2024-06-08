<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function getAllBanner(){
        return response()->json(Banner::all());
    }

    public function addBanner(Request $request){
        // Kiểm tra xem request có chứa file hình ảnh không
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/banner_images');
            $imageUrl = Storage::url($imagePath);
            $imageName = basename($imagePath);
            
            $banner = new Banner();
            $banner->img_url = $imageName;
            $banner->save();

            return response()->json(['message' => 'Banner added successfully'], 201);
        } else {
            return response()->json(['error' => 'Image not provided'], 400);
        }
    }

    public function deleteBanner($id){
        $banner = Banner::find($id);
        
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }
        
        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully'], 200);
    }
}
