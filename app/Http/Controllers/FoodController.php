<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Traits\SendResponse;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Traits\Pagination;

class FoodController extends Controller
{
    use SendResponse,Pagination,UploadImage;

    public function addFood(Request $request){
        $request =$request->json()->all();
        $validator = Validator::make($request, [
            'section_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            "image" => 'required'
        ],[
           'name.required' => 'اسم الطعام مطلوب',
           'price.required' => 'سعر الطعام مطلوب',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية انشاء الطعام',$validator->errors()->all());
        }
        $food= Food::create([
            'section_id'=>$request['section_id'],
            'name'=>$request['name'],
            'price'=>$request['price'],
            'image'=>$this->uploadIamge($request['image'], '/image/image_food/'),
            'description'=>$request['description'],
            'user_id'=>auth()->user()->id
        ]);
        return $this->send_response(200,'تمت عملية انشاء الطعام بنجاح',[],Food::find($food->id));
    }
    public function getFoods(Request $request){
        $request= $request->json()->all();
        $validator = Validator::make($request, [
            'section_id' => 'required|exists:sections,id',
        ]);

        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية احضار الطعام',$validator->errors(),[]);
        }

        $foods= Food::where('section_id', $request['section_id']);
         if(isset($_GET)){
            foreach($_GET as $key => $value){
                if($key == 'skip' || $key=='limit' || $key=='query' || $key=='filter'){
                    continue;
                }else{
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $resturant->orderBy($key,$sort);
                }
            }
        }
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 6;
        $res = $this->paging($foods,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب الطعام بنجاح',[], $res["model"], null, $res["count"]);
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
    public function clientManagementFoods(){
        $foods=Food::where('user_id',auth()->user()->id);
        if(isset($_GET)){
            foreach($_GET as $key => $value){
                if($key == 'skip' || $key=='limit' || $key=='query' || $key=='filter'){
                    continue;
                }else{
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $foods->orderBy($key,$sort);
                }
            }
        }
         if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 6;
        $res = $this->paging($foods,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب الاقسام لصاحب المطعم',[],  $res["model"], null, $res["count"]);
    }
    public function clientManagementDeleteFoods(Request $request){
        $request= $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:food,id',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية حذف الطعام',$validator->errors()->all());
        }
        $food=Food::find($request['id'])->delete();
        return $this->send_response(200,'تم حذف الطعام بنجاح',[],$food);
    }
}
