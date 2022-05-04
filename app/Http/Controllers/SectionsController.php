<?php

namespace App\Http\Controllers;


use App\Models\Sections;
use App\Traits\SendResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SectionsController extends Controller
{
    use SendResponse;

    public function addSection(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'name'=>'required',
            'resturant_id'=>'required',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية انشاء قسم',$validator->errors(),[]);
        }
        $section = Sections::create([
            'name'=>$request['name'],
            'resturant_id'=>$request['resturant_id'],
            'user_id'=>auth()->user()->id
        ]);
        return $this->send_response(200,'تم انشاء القسم بنجاح',[], $section);
    }
    public function getSections(Request $request){
        $sections = Sections::where('user_id', auth()->user()->id)->get();
        return $this->send_response(200,'تم جلب الاقسام بنجاح',[], $sections);
    }

    public function editSection(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'id'=>'required|exists:sections,id',
            'name'=>'required',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية تعديل قسم',$validator->errors(),[]);
        }

        $section = Sections::find($request['id'])->update([
            'name'=>$request['name'],
        ]);
        return $this->send_response(200,'تم تعديل القسم بنجاح',[], Sections::find($request['id']));

    }
}
