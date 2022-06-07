<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resturant;
use App\Models\Orders;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UsersController extends Controller
{
    use SendResponse;

    public function addUser(Request $request){
        $request= $request->json()->all();
        $validator= Validator::make($request,[
            'name'=>'required',
            'user_name'=>'required|unique:users,user_name',
            'phone_number'=>'required|unique:users,phone_number',
            'password'=>'required'
        ],[
            'name.required'=>'حقل الاسم مطلوب',
            'user_name.required'=>' اسم المستخدم مطلوب',
            'user_name.unique'=>'اسم المستحدم موجود مسبقا',
            'phone_number.required'=>'رقم الهاتف مطلوب',
            'phone_number.unique'=>'رقم الهاتف موجود مسبقا',
            'password.required'=>'كلمة المرور مطلوبة'
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشل عملية تسجيل الدخول',$validator->errors(),[]);
        }

        $user = User::create([
            'name'=> $request['name'],
            'user_name'=>$request['user_name'],
            'phone_number'=>$request['phone_number'],
            'password'=>bcrypt($request['password'])
        ]);
        $token= $user->createToken('resturant')->accessToken;
        return $this->send_response(200,'تم اضافة الحساب بنجاح',[], User::find($user->id),$token);
    }
    public function login(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'user_name'=>'required',
            'password'=>'required'
        ],[
            'user_name.required'=>'اسم المستخدم مطلوب',
            'password.required'=>'كلمة المرور مطلوبة'
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشل عملية تسجيل الدخول',$validator->errors(),[]);
        }
        if(auth()->attempt(array('user_name'=> $request['user_name'], 'password'=> $request['password']))){
            $user =auth()->user();
            $token= $user->createToken('resturant')->accessToken;
            return $this->send_response(200,'تم تسجيل الدخول بنجاح',[], $user, $token);
        }else{
            return $this->send_response(400, 'هناك مشكلة تحقق من تطابق المدخلات', null, null, null);
        }
    }
    public function statisticss(){
        $restaurants_active = Resturant::where('status_resturant',1)->count();
        $restaurants_inactive = Resturant::where('status_resturant',0)->count();
        $restaurants_block = Resturant::where('status_resturant',2)->count();
        $users = User::all()->count();
        $orders = Orders::count();
        $statisticss = [];
        array_push($statisticss,$restaurants_active,$restaurants_inactive,$restaurants_block,$users,$orders);
        return $this->send_response(200,'عدد المستخدمين',[],$statisticss);
    }

}
