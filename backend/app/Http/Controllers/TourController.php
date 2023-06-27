<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TourVideo;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    // construct use for authorise

// public function __construct()
    // {
    
    //     $this->middleware("auth");
    
    // }


//use for demo video
   public function demoVideo()
   {
    $demovideo =  DB::table('tbl_tour_video')->where('type','=','Public')->orderby('order','asc')->get();
    // echo "checkvideo content here";
    // echo "$demovideo";
    // die;
    // echo json_encode(array('success' => $demovideo));
    // die;
    // dd(json_decode($demovideo));
    return response()
    ->json($demovideo, 200);
   }

   public function index()
   {
       $tourvideo = DB::table('tbl_tour_video')->get();
      return view('video_tour')->with('tourvideo', $tourvideo);
     
   }

   public function editTour($id)
   {   
    
       $editTours = TourVideo::find($id);
       return view('touredit')->with('editTours', $editTours);

   }

   public function tourUpdate(Request $request,$id)
   {
       $tourUp = TourVideo::find($id);
       $tourUp->name =  $request->get('tName');
       $tourUp->vimeo_video_id = $request->get('tVimeoId');
       $tourUp->discription = $request->get('tDescript');
       $tourUp->type = $request->get('tType');
       $tourUp->status = $request->get('tstatus');
       $tourUp->save(); 
       return redirect('/tour');

   }

   // Delete
   public function destroy($id)
   {
       // delete
       $shark = TourVideo::find($id);
       $shark->delete();
      // Session::flash('message', 'Successfully deleted the shark!');
       return redirect('/tour');
   }

public function tourStore(Request $request)
   {
       $randNo = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"),0,8);
       $tourUp  = new TourVideo;
       $tourUp->name =  $request->get('tName');
       $tourUp->tour_id =  $randNo; 
       $tourUp->vimeo_video_id = $request->get('vimeoid');
       $tourUp->discription = $request->get('tDescription');
       $tourUp->type = $request->get('tType');
       $tourUp->status = $request->get('tStatus');
       $tourUp->save(); 
       
       return redirect('/tour');
       // ->with(['tour_add_success' => 'Created successfully!']);

   }

    public function getTourData($id){
    $getTour = TourVideo::where('tour_id',$id)->first();
    return $getTour;
    }

}

