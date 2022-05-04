<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SendResponse;
use App\Traits\Pagination;
use App\Traits\UploadImage;
use App\Models\Resturant;
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
                "image" => 'required'
            ]);
            if($validator->fails()){
                return $this->send_response(400,'فشلة عملية انشاء مطعم',$validator->errors(),[]);
            }

            $resturant = Resturant::create([
                'name'=>$request['name'],
                'location'=>$request['location'],
                'status'=>false,
                'resturants_status'=>false,
                'image'=> $this->uploadIamge($request['image'], '/image/logo_resturant/'),
                'user_id'=>auth()->user()->id
            ]);
            return $this->send_response(200,'تم انشاء المطعم بنجاح',[], $resturant);
        }else{
            return $this->send_response(400,'لايمكنك انشاء مطعمين','لايمكنك انشاء مطعمين',null);
        }
    }
    public function getResturants(){
        $resturant = Resturant::where('resturants_status',true);
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
        return $this->send_response(200,'تم جلب المطعم',[],  $res["model"], null, $res["count"]);
    }

    public function getInactiveRestaurants(){
        $request = request()->json()->all();
        $resturant = Resturant::where('resturants_status',false);
         if (isset($_GET['query'])) {
            $columns = Schema::getColumnListing('resturant');
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
    // تغير حالة مطعم
    public function restaurantStatusChanged(Request $request){
        $request = request()->json()->all();
        $validator = Validator::make($request, [
            "id" => 'required|exists:resturants,id',
        ]);
         if ($validator->fails()) {
            return $this->send_response(400, 'خطأ بالمدخلات', $validator->errors(), []);
        }
        $resturant = Resturant::find($request['id']);
         $resturant->update([
            'resturants_status' => !$resturant->resturants_status,
        ]);
        return $this->send_response(200, 'تم تغيير حالة المستخدم بنجاح', [], Resturant::find($resturant->id));

    }
}
