<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\notifications;
use App\Models\User;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Support\Facades\Validator;

class NotificationsController extends Controller
{
    use SendResponse ,Pagination;
    public function getNotifications(){
        $notifications = notifications::where('to_user',auth()->user()->id);
        if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 8;
        $res = $this->paging($notifications->orderBy("created_at", "DESC"),  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب الاشعارات',[],  $res["model"], null, $res["count"]);
    }

    public function seenNotification(Request $request){
        $request=$request->json()->all();
           $validator = Validator::make($request, [
            'notification.*.id' => 'required|exists:notifications,id',
        ]);

         if ($validator->fails()) {
            return $this->send_response(400, 'فشلة العملية', $validator->errors(), []);
        }
        $notifications=[];
        foreach ($request['notification'] as $notify) {
            $notification=notifications::find($notify['id']);
            $notification->update([
                "seen"=>true
            ]);
            array_push($notifications, $notification);
        }
        return $this->send_response(200,'تم تحديث الاشعارات',[], $notifications);
    }
}
