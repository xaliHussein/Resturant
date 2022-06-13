<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel("notification_socket.{user_id}",function($user,$user_id){
    return (int) auth()->user()->id  === (int) $user_id;
});
Broadcast::channel("resturant_admin_socket.{user_id}",function($user,$user_id){
    return (int) auth()->user()->id  === (int) $user_id;
});

Broadcast::channel("resturant_socket",function(){
    return true;
});
Broadcast::channel("resturant_status_socket",function(){
    return true;
});
