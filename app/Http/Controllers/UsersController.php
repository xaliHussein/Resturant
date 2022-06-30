<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Orders;
use Twilio\Rest\Client;
use App\Models\Resturant;
use App\Models\Favorite;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class UsersController extends Controller
{
    use SendResponse;
    public function sendCode($phone_number)
    {
        $random_code= substr(str_shuffle("0123456789"), 0, 6);
        try {
            $account_sid = env('TWILIO_SID');
            $auth_token = env('TWILIO_TOKEN');
            $twilio_number = env('TWILIO_FROM');

            $client = new Client($account_sid, $auth_token);
            $client->messages->create($phone_number, [
                'from' => $twilio_number,
                'body' => $random_code
            ]);
            return $random_code;

        } catch (\Exception $e) {
            return $this->send_response(400,'فشل عملية',$e->getMessage(),[]);
        }
    }
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
        $random_code= $this->sendCode($request['phone_number']);
        $user = User::create([
            'name'=> $request['name'],
            'user_name'=>$request['user_name'],
            'check_number'=>$random_code,
            'phone_number'=>$request['phone_number'],
            'password'=>bcrypt($request['password'])
        ]);
        // $token= $user->createToken('resturant')->accessToken;
        return $this->send_response(200,'تم اضافة الحساب بنجاح',[], User::find($user->id));
    }
    public function verifyAuthentication(Request $request){
        $request= $request->json()->all();
        $validator= Validator::make($request,[
            'id'=>'required|exists:users,id',
            'otp'=>'required|min:6|max:6',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشل عملية ',$validator->errors(),[]);
        }
         $user = User::find($request['id']);
        if($request['otp'] == $user->check_number){
            $token= $user->createToken('resturant')->accessToken;
            return $this->send_response(200,'تم التحقق بنجاح',[],$user,$token);
        }else{
            return $this->send_response(400,'فشلة العملية',[],[]);
        }
    }
    public function sendCodeAgain(Request $request){
        $request= $request->json()->all();
        $validator= Validator::make($request,[
            'id'=>'required|exists:users,id',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشل عملية ',$validator->errors(),[]);
        }
        $user = User::find($request['id']);
        $random_code= $this->sendCode($user->phone_number);
        $user->update([
            'check_number'=>$random_code
        ]);
        return $this->send_response(200,'تم ارسال الكود بنجاح',[],[]);
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
    public function getNumberPhone(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'id'=>'required|exists:users,id'
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشل عملية ',$validator->errors(),[]);
        }
        $user = User::find($request['id']);
        return $this->send_response(200,'تم جلب بيانات المستخدم',[], $user);
    }

    public function getFavorite(){
        $user=auth()->user()->id;
        $get_favorite=Favorite::where('user_id',$user)->get();
        return $this->send_response(200,'تم جلب المفضلة',[],$get_favorite);
    }

}
