<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Orders;
use App\Models\Food_Orders;
use App\Traits\SendResponse;
use App\Traits\Pagination;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    use SendResponse ,Pagination;

    public function random_code()
    {
        $code = substr(str_shuffle("0123456789"), 0, 6);
        $get = Orders::where('code_order', $code)->first();
        if ($get) {
            return $this->random_code();
        } else {
            return $code;
        }
    }

    public function addOrder(Request $request)
    {
        $request=$request->json()->all();
        $validator = Validator::make($request, [
            'resturant_id' => 'required',
            'foods_id.*.id' => 'required|exists:food,id',
            'foods_id.*.quantity' => 'required|Numeric',
            'address'=>'required|min:3|max:60',
        ]);

        if ($validator->fails()) {
            return $this->send_response(400, 'فشلة عملية انشاء طلب',$validator->errors()->all());
        }

        $total_price=0;
        $data=[];
        $data=[
            'resturant_id' => $request['resturant_id'],
            'user_id' => auth()->user()->id,
            'name_clint'=> auth()->user()->name,
            'phone_number'=> auth()->user()->phone_number,
            'address'=>$request['address'],
            'code_order'=> $this->random_code(),
            'order_status'=>'قيد المراجعه',
            'payment_type'=> 'كاش'
        ];

        if (array_key_exists('note', $request)) {
            $data['note'] = $request['note'];
        }

        $foods_id=[];
        foreach ($request['foods_id'] as $food_id) {
            $food=Food::find($food_id['id']);
            array_push($foods_id, $food_id['id']);
            $total_price+= $food->price * $food_id['quantity'];
        }
        $data['total_price']=$total_price;
        $orders= Orders::create($data);

        foreach ($foods_id as $key => $food_id) {
            Food_Orders::create([
                'food_id'=>$food_id,
                'order_id'=>$orders->id,
                'quantity'=>$request['foods_id'][$key]['quantity']
            ]);
        }
        return $this->send_response(200, 'تمت عملية انشاء طلب بنجاح',[],Orders::find($orders->id));
    }

    public function getOrders(Request $request){
        if(isset($_GET['order_id'])){
            $foods = Food_Orders::where('order_id',$_GET['order_id']);
            if(isset($_GET)){
                foreach($_GET as $key => $value){
                    if($key == 'skip' || $key == 'limit' || $key == 'query' || $key == 'order_id'){
                        continue;
                    }else{
                        $sort = $value == 'true' ? 'ace' : 'desc';
                        $foods->orderBy($key, $sort);
                    }
                }
            }

            if(!isset($_GET['skip'])){
                $_GET['skip'] = 0;
            }
            if(!isset($_GET['limit'])){
                $_GET['limit'] = 10;
            }
            $response = $this->paging($foods,$_GET['skip'],$_GET['limit']);
            return $this->send_response(200, 'تمت عملية جلب البيانات بنجاح',[],$response['model'],null,$response['count']);
        }

        $orders = Orders::where('user_id',auth()->user()->id);
        if(isset($_GET)){
            foreach($_GET as $key => $value){
                if($key == 'skip' || $key == 'limit' || $key == 'query' || $key =='filter'){
                    continue;
                }else{
                    $sort = $value == 'true' ? 'ace' : 'desc';
                    $orders->orderBy($key, $sort);
                }
            }
        }
        if(!isset($_GET['skip'])){
            $_GET['skip'] = 0;
        }
        if(!isset($_GET['limit'])){
            $_GET['limit'] = 10;
        }
        $response = $this->paging($orders,$_GET['skip'],$_GET['limit']);
        return $this->send_response(200, 'تمت عملية جلب البيانات بنجاح',[],$response['model'],null,$response['count']);
    }
}
