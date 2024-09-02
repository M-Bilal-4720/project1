<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        try{
           $validator=Validator::make($request->all(),[
            'name'=>'required|string|max:255|unique:users,name',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|required|min:8',
           ]);
           DB::beginTransaction();
           if($validator->fails()){
            DB::rollBack();
            return response()->json(['status'=>false,'message'=>$validator->errors()],404);
           }
            $user=new User();
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=Hash::make($request->password);
            $user->save();
            DB::commit();
            return response()->json(['status'=>true,'messaage'=>'Created successfully','user'=>$user]);
           
        }catch(Exception $e){
            DB::rollBack();
            return response()->json(['status'=>false,'message'=>'Please try again - '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function login(Request $request)
    {
        try {
            $validator=Validator::make($request->all(),[
                'email'=>'required|email',
                'password'=>'required|string',
            ]);
            if($validator->fails()){
                return response()->json(['status'=>false,'message'=>$validator->errors()],422);
            }
            DB::beginTransaction();
            $data=User::where('email',$request->email)->first();
            if(!$data){
                DB::rollBack();
                return response()->json(['status'=>false,'message'=>'This email address not found?'],401);
            }
            if (!Hash::check($request->password, $data->password)) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Incorrect password.'], 401);
            }
               
                $token=$data->createToken('user')->accessToken;
            
            DB::commit();
            return response()->json(['status'=>true,'Message'=>'Login Success_','Token'=>$token],200);
        } catch (\Exception $e) {
            DB::rollBack();
        return response()->json(['status'=>false,'Message'=>'Please try again_'.$e->getMessage()],500);    
        }
    }

   
    public function logout(Request $request)
    {
       try{
        $user=Auth::guard("user")->user();

        $user->token()->revoke();
        return response()->json(['status'=>true,'message'=>'logout successfully'],200);
       }catch(\Exception $e){
        return response()->json(['status'=>false,'message'=>'Try again'.$e->getMessage],500);
       }
    }
    public function profile(Request $request)
    {
        try{
            $id=Auth::guard('user')->user()->id;
            $data=User::where('id',$id)->first();
            if(!$data->image){
                $imageUrl='';
            }else{$imageUrl = url('profile/images/' . $data->image);
            }
            return response()->json(['status'=>true,'message'=>'User profile updated','seller'=>['name'=>$data->name,
            'email'=>$data->email,'image'=>$imageUrl]],200);
        }catch(\Exception $e){
            return response()->json(['status'=>true,'message'=>'Please try again'.$e->getMessage()],500);
        }
    }
    public function update(Request $request){
        $id=Auth::guard('user')->user()->id;
        $validator=Validator::make($request->all(),[
            'name'=>[
                'required',
                Rule::unique('users')->ignore($id)],
            'email'=>['required',Rule::unique('users')->ignore($id)],
            'password'=>'required|required|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if(!$validator){
            return response()->json(['status'=>false,'message'=>$validator->errors()],401);
        }
        $data=User::where('id',$id)->first();
        if(!$data){
            return response()->json(['status'=>false,'message'=>'User not found'],402);
        }
        $imageName = null;
        try{
          DB::beginTransaction();
         
         if ($request->hasFile('image')) {
          if ($data->image) {
             $directory = "profile/images/";
             $file = public_path($directory . $data->image);            
              if (file_exists($file)) {
                  unlink($file) ; 
               }  
          }
          $imageName = time() . '.' . $request->image->extension();
          $request->image->move(public_path('profile/images'), $imageName);
          $data->image=$imageName;
          }
            $data->name=$request->name;
            $data->email=$request->email;
            $data->password=Hash::make($request->password);
            $data->save();
            DB::commit();
            if(!$data->image){
                $imageUrl='';
            }else{$imageUrl = url('profile/images/' . $data->image);
            }
            return response()->json(['status'=>true,'message'=>'User profile updated','seller'=>['name'=>$data->name,
            'email'=>$data->email,'image'=>$imageUrl]],200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>false,'message'=>'please Try again_ '.$e->getMessage()],500);
        }
    }
}
