<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SendResponse;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Models\Resturant;
use App\Models\User;
use App\Models\Sections;
use App\Models\notifications;
use App\Events\NotificationSocket;
use App\Events\AdminSocket;
use App\Events\ResturantSocket;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;


class ResturantController extends Controller
{
    use SendResponse,UploadImage,Pagination;
    public function addResturant(Request $request){
        $request = $request->json()->all();
        if(empty(auth()->user()->resturant)){
            $validator = Validator::make($request,[
                'name'=>'required:unique:resturants,name|min:3|max:15',
                'location'=>'required',
                'complement_location'=>'required',
                'minimum_order'=>'required',
                'food_type'=>'required',
                "image" => 'required'
            ]);
            if($validator->fails()){
                return $this->send_response(400,'فشلة عملية انشاء مطعم',$validator->errors(),[]);
            }

            $resturant = Resturant::create([
                'name'=>$request['name'],
                'location'=>$request['location'],
                'status'=>false,
                'status_resturant'=>0,
                'complement_location'=>$request['complement_location'],
                'minimum_order'=>$request['minimum_order'],
                'food_type'=>$request['food_type'],
                'image'=> $this->uploadIamge($request['image'], '/image/logo_resturant/'),
                'user_id'=>auth()->user()->id
            ]);
            $user=User::where('user_type',0)->first();
            $notification_user = notifications::create([
                'title' => 'طلبك قيد المراجعه',
                'body' => 'سوف يتم مراجعة طلبك خلال 24 ساعة',
                'color' => 'orange darken-2',
                'icon' => 'folder-clock',
                'link'=>null,
                'to_user' =>  auth()->user()->id,
                'from_user' =>$user->id,
            ]);

            $notification_admin = notifications::create([
                'title' => 'لديك طلب جديد',
                'body' => 'لديك طلب جديد',
                'color' => 'orange darken-2',
                'icon' => 'folder-multiple',
                'link'=>null,
                'to_user' =>  $user->id,
                'from_user' =>$resturant->user_id,
            ]);
            broadcast(new NotificationSocket($notification_user,auth()->user()->id));
            broadcast(new NotificationSocket($notification_admin,$user->id));
            broadcast(new AdminSocket($resturant,$user->id));
            return $this->send_response(200,'تم انشاء المطعم بنجاح',[], $resturant);
        }else{
            return $this->send_response(400,'لايمكنك انشاء مطعمين','لايمكنك انشاء مطعمين',null);
        }
    }
    public function getResturants(){
        $resturant = Resturant::where('status_resturant',1);
        if (isset($_GET['query'])) {
            $columns = Schema::getColumnListing('resturants');
            $resturant->whereHas('user', function ($query) {
                $query->where('name', 'like', '%' . $_GET['query'] . '%');
            });
            foreach ($columns as $column) {
                $resturant->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
                error_log($column);
            }
        }
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
            $_GET['limit'] = 10;
        $res = $this->paging($resturant,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب المطعم',[],  $res["model"], null, $res["count"]);
    }

    public function getInactiveRestaurants(){
        $resturant = Resturant::where('status_resturant',0);
         if (isset($_GET['query'])) {
            $columns = Schema::getColumnListing('resturants');
            foreach ($columns as $column) {
                $resturant->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
            }
        }
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
            $_GET['limit'] = 10;
        $res = $this->paging($resturant,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب المطاعم الغير مفعله',[],  $res["model"], null, $res["count"]);
    }
    public function getBannedRestaurants(){
        $resturant = Resturant::where('status_resturant',2);
         if (isset($_GET['query'])) {
            $columns = Schema::getColumnListing('resturants');
            foreach ($columns as $column) {
                $resturant->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
            }
        }
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
            $_GET['limit'] = 10;
        $res = $this->paging($resturant,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب المطاعم المحظوره',[],  $res["model"], null, $res["count"]);
    }
    // تغير حالة مطعم الى تفعيل
    public function restaurantStatusActivate(Request $request){
        $request = request()->json()->all();
        $validator = Validator::make($request, [
            "id" => 'required|exists:resturants,id',
        ]);
         if ($validator->fails()) {
            return $this->send_response(400, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $resturant = Resturant::find($request['id']);
         $resturant->update([
            'status_resturant' => 1,
        ]);

        $user=User::where('user_type',0)->first();
            $notification = notifications::create([
                'title' => 'تم الموافقة على طلبك',
                'body' => 'تم الموافقة على طلبك يمكنك الان استقبال طلبات الزبائن',
                'color' => 'green darken-1',
                'icon' => 'check-circle',
                'link'=>null,
                'to_user' =>  $resturant->user_id,
                'from_user' =>$user->id,
            ]);
            broadcast(new NotificationSocket($notification,$resturant->user_id));
            broadcast(new ResturantSocket($resturant));
        return $this->send_response(200, 'تم تغيير حالة المستخدم بنجاح', [], Resturant::find($resturant->id));

    }
    // تغير حالة مطعم الى الحظر
    public function restaurantStatusShutdown(Request $request){
        $request = request()->json()->all();
        $validator = Validator::make($request, [
            "id" => 'required|exists:resturants,id',
        ]);
         if ($validator->fails()) {
            return $this->send_response(400, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $resturant = Resturant::find($request['id']);
         $resturant->update([
            'status_resturant' => 2,
        ]);

         $user=User::where('user_type',0)->first();
            $notification = notifications::create([
                'title' => 'تم ايقاف حسابك',
                'body' => 'عذرا لقد  تم ايقاف حسابك بسبب عدم اتباعك لشروط الاستخدام',
                'color' => 'red darken-1',
                'icon' => 'minus-circle',
                'link'=>null,
                'to_user' =>  $resturant->user_id,
                'from_user' =>$user->id,
            ]);
            broadcast(new NotificationSocket($notification,$resturant->user_id));
            broadcast(new ResturantSocket($resturant));
        return $this->send_response(200, 'تم تغيير حالة المستخدم بنجاح', [], Resturant::find($resturant->id));
    }
    public function clientManagementResturant(){
        $client_resturant =Resturant::where('user_id',auth()->user()->id)->get();
        return $this->send_response(200,'تم جلب مطعم العميل',[],  $client_resturant);
    }
    public function clientManagementResturantStatus(Request $request){
        $request = request()->json()->all();
        $validator = Validator::make($request, [
            "id" => 'required|exists:resturants,id',
        ]);
         if($validator->fails()) {
            return $this->send_response(400, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $sections = Sections::where('resturant_id', $request["id"])->get();
        if(!empty($sections)){
            $resturant = Resturant::find($request['id']);
            $resturant->update([
                'status' => !$resturant->status,
            ]);
            return $this->send_response(200, 'تم تغيير حالة المستخدم بنجاح', [], Resturant::find($resturant->id));
        }else{
            return $this->send_response(400, 'فشلة العملية', 'يجب اضافة قسم واحد كحد ادنى للمطعم', []);
        }
    }
    public function clientManagemenEditRestaurant(Request $request){
        $request = request()->json()->all();
        $validator = Validator::make($request,[
                "id" => 'required|exists:resturants,id',
                'name'=>'required:min:3|max:15|unique:resturants,name,'.$request['id'],
                'location'=>'required',
                'complement_location'=>'required',
                'minimum_order'=>'required',
                'food_type'=>'required',
            ],[
                'name.required'=>'ادخلت اسم مطعم موجود بالفعل',
            ]);
            if($validator->fails()){
                return $this->send_response(400,'فشلة عملية تعديل مطعم',$validator->errors(),[]);
            }

            $resturant = Resturant::find($request['id']);
            $resturant->update([
                'name'=>$request['name'],
                'location'=>$request['location'],
                'complement_location'=>$request['complement_location'],
                'minimum_order'=>$request['minimum_order'],
                'food_type'=>$request['food_type'],
            ]);
            if(isset($request['image'])){
                $resturant->update([
                    'image'=> $this->uploadIamge($request['image'], '/image/logo_resturant/'),
                ]);
            }
            return $this->send_response(200,'تم تعديل مطعم بنجاح',[],Resturant::find($resturant->id));
    }
}
