<?php

namespace App\Http\Controllers;


use App\Models\Sections;
use App\Models\Food;
use App\Traits\SendResponse;
use App\Traits\Pagination;
use App\Models\Resturant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SectionsController extends Controller
{
    use SendResponse,Pagination;

    public function addSection(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'name'=>'required',
            'resturant_id'=>'required|exists:resturants,id',
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
        $request = $request->json()->all();
         $validator = Validator::make($request,[
            "name" => 'required|exists:resturants,name',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية احضار الاقسام',$validator->errors(),[]);
        }
        $resturant = Resturant::where('name', $request["name"])->first();
        $sections = Sections::where('resturant_id', $resturant->id)->get();
        return $this->send_response(200,'تم جلب الاقسام بنجاح',[], $sections);
    }

    public function clientManagemenEditSection(Request $request){
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
    public function clientManagementSections(){
        $sections=Sections::where('user_id',auth()->user()->id);
        if (isset($_GET['query'])) {
            $columns = Schema::getColumnListing('sections');
            foreach ($columns as $column) {
                $sections->orWhere($column, 'LIKE', '%' . $_GET['query'] . '%');
            }
        }
         if(isset($_GET)){
            foreach($_GET as $key => $value){
                if($key == 'skip' || $key=='limit' || $key=='query' || $key=='filter'){
                    continue;
                }else{
                    $sort = $value == 'true' ? 'desc' : 'asc';
                    $sections->orderBy($key,$sort);
                }
            }
        }
         if (!isset($_GET['skip']))
            $_GET['skip'] = 0;
        if (!isset($_GET['limit']))
            $_GET['limit'] = 10;
        $res = $this->paging($sections,  $_GET['skip'],  $_GET['limit']);
        return $this->send_response(200,'تم جلب الاقسام لصاحب المطعم',[],  $res["model"], null, $res["count"]);
    }
    public function deleteSections(Request $request){
        $request = $request->json()->all();
        $validator = Validator::make($request,[
            'id'=>'required|exists:sections,id',
        ]);
        if($validator->fails()){
            return $this->send_response(400,'فشلة عملية حذف قسم',$validator->errors(),[]);
        }
        $foods=Food::where('section_id',$request['id'])->get();
        foreach($foods as $food){
            $food->delete();
        }
        $section = Sections::find($request['id'])->delete();
        return $this->send_response(200,'تم حذف القسم بنجاح',[], $section);
    }
}
