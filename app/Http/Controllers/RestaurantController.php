<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Experience;
use App\Models\ExperiencePhoto;
use App\Http\Resources\RestaurantCollection;
use DB;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;

class RestaurantController extends Controller
{
    //

    public function index(Restaurant $restaurant)
    {

        return new RestaurantCollection($restaurant->latest()->get());
    }

    public function get(Restaurant $restaurant, Request $request){
        $place_id = $request->place_id;

        $record = $restaurant->where('place_id','=',$place_id)->first();
        if ($record){
            return $record;
        }else{
            return response()->json([
                "message" => "record not found"
            ], 404);
        }

    }

    public function exist(Restaurant $restaurant, Request $request){
        $name = $request->name;
        $address = $request->address;
        $record = $restaurant->where('name', '=', $name)
            ->where('address', '=', $address)
            ->get();
        if ($record){
            return $record;
        }else{
            return response()->json([
                "message" => "record not found"
            ], 404);
        }
    }

    public function create(Request $request){
        $restaurant = new Restaurant;
        $restaurant->name = $request->name;
        $restaurant->address = $request->address;
        $restaurant->created_at = Carbon::now();
        $restaurant->created_by = $request->user_id;
        $restaurant->save();

        return response()->json([
            "message" => "restaurant record created"
        ], 201);

    }



    public function update(Request $request){

        $restaurant = Restaurant::where('place_id', '=',$request->place_id)->get();

        if ($restaurant->count() > 0){
            return $restaurant->where('place_id', '=',$request->place_id)->first()->fill($request->all())->save();
        }else{
            return Restaurant::create($request->all());
        }


    }

    public function get_qi_ultimate(Request $request){
        return DB::table('experiences')
            ->leftJoin('users','users.id','=','experiences.created_by')
            ->select('users.username', 'users.name', DB::raw('sum(points) as highest_points'))
            ->where('place_id','=',$request->place_id)
            ->groupBy('created_by')
            ->orderBy('highest_points', 'desc')
            ->first();

            //DB::Raw('SELECT max(sum(points)) as highest_points, created_by FROM experiences where place_id='.$request->place_id." GROUP BY created_by");
    }

    public function get_experience(Request $request){


        if ($request->experience_id){
            $experience = Experience::leftJoin('users','users.id','=','experiences.created_by')
                ->where('experiences.id', '=',$request->experience_id);

        }else{
            $experience = Experience::where('experiences.place_id', '=',$request->place_id)
                ->leftJoin('users','users.id','=','experiences.created_by')
                ->where('experiences.created_by','=', $request->created_by);
            if ($request->created_at != 'null') {
                $experience->where(DB::raw('DATE(experiences.created_at)'),'=', DB::raw('DATE(\''.$request->created_at.'\')'));
            }
        }

        $experience->leftJoin(DB::Raw('(SELECT sum(points) as total_points, place_id, created_by from experiences group by experiences.place_id, created_by) as user_points'), function($join) {
            $join->on('user_points.created_by','=','experiences.created_by');
            $join->on('user_points.place_id','=','experiences.place_id');
        });

        $experience->select('experiences.*', 'users.username as creator_name', 'user_points.total_points');
        $records = $experience->get();

        if ($experience->count() > 1) {
            return $records;
        }else if ($experience->count() == 1){
            return $records[0];
        }else{
            return null;
        }
    }

    public function get_experience_restaurant(Request $request){

        $experience = Experience::where('experiences.place_id', '=',$request->place_id)
            ->leftJoin("restaurants","restaurants.place_id","=","experiences.place_id")
            ->leftJoin(DB::Raw('(SELECT ANY_VALUE(id) as id, experiences_id FROM experiences_photos group by experiences_id) as experiences_photos'), function($join){
                $join->on('experiences_photos.experiences_id','=','experiences.id');
            })
            ->leftJoin(DB::Raw('(SELECT sum(points) as total_points, place_id, created_by from experiences group by experiences.place_id, created_by) as user_points'), function($join) {
                $join->on('user_points.created_by','=','experiences.created_by');
                $join->on('user_points.place_id','=','experiences.place_id');
            })
            ->leftJoin('users','users.id','=','experiences.created_by')
            ->select('restaurants.name', 'restaurants.place_id', 'experiences.id', 'experiences.rating', 'experiences.comment','experiences.points','user_points.total_points',
                'experiences.updated_at', 'experiences.updated_at', 'experiences_photos.id as experiences_photos_id', 'users.name as creator_name')
            ->orderby('experiences.created_at','desc');


        if ($request->user_id != 'null'){
            $experience->where('experiences.created_by','=',$request->user_id);
        }

        return $experience->get();

    }


    public function get_user_top_qi(Request $request){


        if ($request->type == "restaurant"){
            return Restaurant::leftJoin(DB::Raw('(SELECT place_id, created_by, sum(points) as total_points, max(created_at) as latest_created_at FROM experiences group by created_by, place_id) as restaurant_topqi'), function($join){
                    $join->on('restaurant_topqi.place_id','=','restaurants.place_id');
                })
                ->leftJoin('users','users.id','=','restaurant_topqi.created_by')
                ->where('restaurant_topqi.created_by','=',$request->user_id)
                ->select('users.name as creator_name', 'restaurants.name as restaurant_name', 'restaurant_topqi.latest_created_at', 'restaurant_topqi.total_points')
                ->orderby('restaurant_topqi.total_points','desc')
                ->limit(5)
                ->get();

        }else if ($request->type == "local"){


            return DB::select(DB::Raw('SELECT users.name as creator_name, restaurant_topcity.city as city_name, restaurant_topcity.latest_created_at, restaurant_topcity.total_points FROM 
                (SELECT city, restaurant_topqi.created_by, max(created_at) as latest_created_at, sum(total_points) as total_points FROM
                    (SELECT place_id, created_by, sum(points) as total_points, max(created_at) as latest_created_at FROM experiences group by created_by, place_id) as restaurant_topqi 
                        LEFT JOIN restaurants ON (restaurants.place_id=restaurant_topqi.place_id) group by restaurant_topqi.created_by, restaurants.city) as restaurant_topcity, 
                users WHERE users.id=restaurant_topcity.created_by AND restaurant_topcity.created_by='.$request->user_id.' order by restaurant_topcity.total_points desc'));

        }else{
            return Experience::where('experiences.created_by', '=',$request->user_id)
                ->leftJoin("restaurants","restaurants.place_id","=","experiences.place_id")
                ->leftJoin(DB::Raw('(SELECT ANY_VALUE(id) as id, experiences_id FROM experiences_photos group by experiences_id) as experiences_photos'), function($join){
                    $join->on('experiences_photos.experiences_id','=','experiences.id');
                })
                ->leftJoin('users','users.id','=','experiences.created_by')
                ->select('restaurants.name', 'restaurants.place_id', 'experiences.id', 'experiences.rating', 'experiences.comment','experiences.points',
                    'experiences.updated_at', 'experiences.updated_at', 'users.name as creator_name')
                ->orderby('experiences.created_at','desc')
                ->get();

            return [];
        }


    }


    public function get_experience_user(Request $request){

        return Experience::where('experiences.created_by', '=',$request->user_id)
            ->leftJoin("restaurants","restaurants.place_id","=","experiences.place_id")
            ->leftJoin(DB::Raw('(SELECT ANY_VALUE(id) as id, experiences_id FROM experiences_photos group by experiences_id) as experiences_photos'), function($join){
                $join->on('experiences_photos.experiences_id','=','experiences.id');
            })
            ->leftJoin('users','users.id','=','experiences.created_by')
            ->select('restaurants.name', 'restaurants.place_id', 'experiences.id', 'experiences.rating', 'experiences.comment','experiences.points',
                'experiences.updated_at', 'experiences.updated_at', 'experiences_photos.id as experiences_photos_id', 'users.name as creator_name')
            ->orderby('experiences.created_at','desc')
            ->get();

    }

    public function get_restaurant_bound(Request $request){

        $payLoad = json_decode($request->getContent(), true);
        $restaurants = Restaurant::where('lat','<', $payLoad['Coordinate']['NE']['lat'])
            ->where('lat','>', $payLoad['Coordinate']['SW']['lat'])
            ->where('lng','<', $payLoad['Coordinate']['NE']['lng'])
            ->where('lng','>', $payLoad['Coordinate']['SW']['lng'])
            ->leftJoin(DB::raw('(SELECT ROUND(AVG(rating), 1) as rating, place_id, count(place_id) as num_experience FROM experiences group by place_id) as experiences'), function($join){
                $join->on('experiences.place_id','=','restaurants.place_id');
            })
            ->select('restaurants.*', 'experiences.rating', 'experiences.num_experience')
            ->get();

        return $restaurants;
    }


    public function get_experience_photo(Request $request){

        $experiences_photo = ExperiencePhoto::where('experiences_id', '=',$request->experiences_id)->orderby('created_at')
            ->get();

        if ($experiences_photo->count() > 0){
            return $experiences_photo;
        }else{
            return null;
        }
    }


    public function delete_experience_photo(Request $request){
        $experiences_photos = ExperiencePhoto::find($request->experiences_photos_id);

        $filename_path = storage_path()."/app/".$experiences_photos->file_path;
        if (is_file($filename_path)){
            unlink($filename_path);

        }

        $filename_path_small = storage_path()."/app/".$experiences_photos->file_path_small;
        if (is_file($filename_path_small)){
            unlink($filename_path_small);

        }

        ExperiencePhoto::where('id', $request->experiences_photos_id)->delete();

        return response()->json([
            "message" => "experience photo deleted"
        ], 201);

    }

    public function display_experience_photo(Request $request){
        $experiences_photos_id = request('experiences_photos_id');
        $small = request('small');
        $experiences_photos = ExperiencePhoto::find($experiences_photos_id);

        if ($small){
            $filename_path = storage_path()."/app/".$experiences_photos->file_path_small;

        }else{
            $filename_path = storage_path()."/app/".$experiences_photos->file_path;

        }
        if (is_file($filename_path))
        {
            $arr_temp_path = explode("/", $experiences_photos->file_path);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$arr_temp_path[1].'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename_path));
            readfile($filename_path);

        }
        return NULL;
    }


    public function update_experience(Request $request){


        if ($request->id){
            $experience = Experience::where('id', $request->id)->first();
            $experience->fill($request->all())->save();

            return $experience;


        }else{
            return Experience::create($request->all());
        }


    }


    public function done_experience(Request $request){


        if ($request->experience_id){

            $experience = Experience::where('id', $request->experience_id)->update(["done"=> 1]);
            return $experience;

        }


    }

    public function update_experience_photo(Request $request){
        $experience_id = request('experience_id');
        $place_id = request('place_id');
        $created_at = request('created_at');
        $created_by = request('created_by');

        $path = $request->file('file')->store('experience_photos');
        $path_small = $request->file('file')->store('experience_photos_small');
        $filepath_full = storage_path()."/app/".$path;
        $filepath_full_small = storage_path()."/app/".$path_small;

        $data = getimagesize($filepath_full);
        $width = $data[0];
        $height = $data[1];

        $image_resize = Image::make($filepath_full);
        $image_resize->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save($filepath_full_small);

        $arrInsert = array(
            "experiences_id"=>$experience_id,
            "place_id"=>$place_id,
            "file_path"=>$path,
            "file_path_small"=>$path_small,
            "width"=>$width,
            "height"=>$height,
            "created_at"=>$created_at,
            "created_by"=>$created_by);


        DB::table("experiences_photos")->insert($arrInsert);
        $experiences_photos_id = DB::getPdo()->lastInsertId();
        return ExperiencePhoto::find($experiences_photos_id);
    }



}
