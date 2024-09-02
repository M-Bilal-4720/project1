<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function store(Request $request){
      
            $validator=Validator::make($request->all(),[
                'title' => 'required|string|max:255|unique:categories,title',
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'discription'=>'required|string',
                'status'=>['required', Rule::in([0,1])],
            ]);
            if(!$validator){
                return response()->json(['status'=>false,'message'=>$validator->errors()],402);
            }
            $imageName = null;
    
            try{
              DB::beginTransaction();
            $data=new Brand();
             $data->title=$request->title;
             if ($request->hasFile('thumbnail')) {
              $imageName = time() . '.' . $request->thumbnail->extension();
              $request->thumbnail->move(public_path('brand/images'), $imageName);
              $data->thumbnail=$imageName;
              $imageUrl = url('brand/images/' . $imageName);
          }
             $data->description=$request->description;
             $data->status=$request->status;
            $data->save();
            DB::commit();
            return response()->json(['status'=>true,'message'=>'Category created successfully','Category'=>['id'=>$data->id,
            'title'=>$data->title,'status'=>$data->status,'thumbnail'=>$imageUrl,'description'=>$data->description]],200);
        } catch (\Exception $e) {
            return response()->json(['status'=>false,'message'=>'please try later_'.$e->getMessage()],500);
        }
    }
   public function show(Request $request){
    try {
        $perpage=$request->input('per_page');
        $datas=Brand::paginate($perPage);
        $datasWithImageUrls = $datas->map(function ($data) {
            $data->image_url = url('category/images/' . $data->thumbnail);
            return $data; 
          });
       if(!$datas){
        return response()->json(['status'=>false,'message'=>'Brands Not found?'],402);
       }
        
          return response()->json(['status'=>true,'message'=>'Brand','brand'=>$datasWithImageUrls],200);
    } catch (\Exception $e) {
        return response()->json(['status'=>true,'mesage'=>'try again_  '.$e->getMessage()],500);
    }
        

   }

}
