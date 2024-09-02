<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
public function index(Request $request){
    
}

    public function store(Request $request)
    {
    
      $validator =Validator::make($request->all(),[
            'title' => 'required|string|max:255|unique:categories,title',
           'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
           'status' => ['required',Rule::in([0,1])],
      ]);
      if($validator->fails()){
        return Response()->json(['status'=>false ,'message'=>$validator->errors()], 422);
      }
      $imageName = null;
    
      try{
        DB::beginTransaction();
      $data=new Category();
       $data->title=$request->title;
       if ($request->hasFile('thumbnail')) {
        $imageName = time() . '.' . $request->thumbnail->extension();
        $request->thumbnail->move(public_path('category/images'), $imageName);
        $data->thumbnail=$imageName;
        $imageUrl = url('category/images/' . $imageName);
    }
       $data->status=$request->status;
      $data->save();
      DB::commit();
      return response()->json(['status'=>true,'message'=>'Category created successfully','Category'=>['id'=>$data->id,
    'title'=>$data->title,'status'=>$data->status,'thumbnail'=>$imageUrl,]],200);
    }catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Product not found'], 404);

    }catch (Exception $e) {
        
        DB::rollBack();
     return response()->json(['status'=>false,'message'=>'Please try again - '.$e->getMessage()]);
    }
}

    
  
    public function show(Request $request)
    {
        try{
            $perPage=$request->input('per_page', 10);
        $datas=Category::paginate($perPage);
        $datasWithImageUrls = $datas->map(function ($data) {
            $data->image_url = url('category/images/' . $data->thumbnail);
            return $data; 
          });
     return response()->json(['status'=>true,'message'=>'success','data'=>$datasWithImageUrls],200);
     
       }catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Product not found'], 404);

    }catch(\Exception $e){
          
          return response()->json(['status'=>false,'message'=>"failed request - ".$e->getMessage()],500);
       }
    }
    public function edite(Request $request,$id)
    {
        try{
        $data=Category::where('id',$id)->first();
        $imageUrl = url('category/images/' . $data->thumbnail);
     return response()->json(['status'=>true,'message'=>'success','data'=>['id'=>$data->id,
    'title'=>$data->title,'status'=>$data->status,'thumbnail'=>$imageUrl,]],200);
       }catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Product not found'], 404);

    }catch(\Exception $e){
          
          return response()->json(['status'=>false,'message'=>"failed request - ".$e->getMessage()],500);
       }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {  
        $data=Category::where('id',$id)->first();
        if(!$data){
            return response()->return(['status'=>false,'message'=>'Data not found?'],404);
        }

        $validator =Validator::make($request->all(),[
            'title' => [
                'required',
                Rule::unique('categories')->ignore($id),],
           'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
           'status' => ['required',Rule::in([0,1])],
      ]);
      if($validator->fails()){
        return Response()->json(['status'=>false ,'message'=>$validator->errors()], 422);
      }
      $imageName = null;
      try{
        DB::beginTransaction();
       $data->title=$request->title;
       if ($request->hasFile('thumbnail')) {
        if ($data->thumbnail) {
           $directory = "category/images/";
           $file = public_path($directory . $data->thumbnail);            
            if (file_exists($file)) {
                unlink($file) ; 
             }  
        }
        $imageName = time() . '.' . $request->thumbnail->extension();
        $request->thumbnail->move(public_path('category/images'), $imageName);
        $data->thumbnail=$imageName;
        }else{
            $data->thumbnail=$data->thumbnail;
        }
       $data->status=$request->status;
      $data->save();
      DB::commit();
      return response()->json(['status'=>true,'message'=>'Category updated successfully'],200);
    }catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Product not found'], 404);

    }catch (Exception $e) {
        
        DB::rollBack();
     return response()->json(['status'=>false,'message'=>'Please try again - '.$e->getMessage()]);
    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request ,$id)
    {
        try{
        $category=Category::where('id',$id)->first();
        if(!$category){
            return response()->json(['status'=>false,'message'=>'Category not Found?'],401);
        }
        if ($category->thumbnail) {
            $directory = "category/images/";
           $file = public_path($directory . $category->thumbnail);
            if (file_exists($file)) {
                unlink($file) ;  
            }             
        }
        $category->delete();
        return response()->json(['status'=>true,'message'=>'Category delete successfuly'],200);
        }catch (Exception $e) {
         return response()->json(['status'=>false,'message'=>'Please try again'.$e->getMessage()],500);
        }
    }
    public function status(Request $request, $id){
        try{
            
            $validator=Validator::make($request->all(),[
                'status' => ['required',Rule::in([0,1])],
            ]);
            if($validator->fails()){
                return response()->json(['status'=>false,'message'=>$validator->errors()],422);
            }
            DB::beginTransaction();
            $data=Category::find($id);
            if(!$data){
                return response()->json(['status'=>false,'message'=>'Category not found?'],404);
            }
            
            $data->status=$request->status;
            $data->save();
            DB::commit();
            return response()->json(['status'=>true,'message'=>'Status Updated','data'=>$data->status],200);
        }catch (Exception $e) {
            return response()->json(['status'=>false,'message'=>'Please try again'.$e->getMessage()],500);
           }
    }
}
