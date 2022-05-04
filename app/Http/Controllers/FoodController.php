<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Traits\SendResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use SendResponse;

    public function addFood(Request $request){
        $request =$request->json()->all();
        $validator = Validator::make($request, [
            'section_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            // 'image' => 'required|mimes:jpeg,png,jpg,svg|max:2048',
        ],[
           'name.required' => 'اسم الطعام مطلوب',
           'price.required' => 'سعر الطعام مطلوب',
        //    'image.required' => 'الصور مطلوبة الحد الادنى لحجم الصورة 2 ميجا',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية انشاء الطعام',$validator->errors()->all());
        }
        $food= Food::create([
            'section_id'=>$request['section_id'],
            'name'=>$request['name'],
            'price'=>$request['price'],
            // 'image'=>$request['image'],
            'description'=>$request['description'],
            'user_id'=>auth()->user()->id
        ]);
        return $this->send_response(200,'تمت عملية انشاء الطعام بنجاح',[],$food);
    }
    public function getFoods(Request $request){
        $request= $request->json()->all();
        $foods= Food::where('section_id', $request['section_id'])->get();
        return $this->send_response(200,'تم جلب الطعام بنجاح',[],$foods);
    }
    public function updateFood(Request $request){
        $request= $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required',
            'name' => 'required',
            'price' => 'required',
            // 'image' => 'required|mimes:jpeg,png,jpg,svg|max:2048',
        ],[
           'name.required' => 'اسم الطعام مطلوب',
           'price.required' => 'سعر الطعام مطلوب',
            //    'image.required' => 'الصور مطلوبة الحد الادنى لحجم الصورة 2 ميجا',
        ]);

        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية تعديل الطعام', $validator->errors()->all());
        }
        $food= Food::find($request['id']);
        if(auth()->user()->id == $food->user_id){
            $food->update([
            'name'=>$request['name'],
            'price'=>$request['price'],
            'description'=>$request['description'],
            // 'image'=>$request['image'],
        ]);
            return $this->send_response(200,'تم تعديل الطعام بنجاح',[],$food);
        }else{
            return $this->send_response(400,'فشلة عملية تعديل الطعام','ليس لديك هذه الصلاحية',[]);
        }

    }
}
