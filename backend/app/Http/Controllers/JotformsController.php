<?php

namespace App\Http\Controllers;

use App\Models\Jotforms;
use Illuminate\Http\Request;

class JotformsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Jotforms  $jotforms
     * @return \Illuminate\Http\Response
     */
    public function show(Jotforms $jotforms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Jotforms  $jotforms
     * @return \Illuminate\Http\Response
     */
    public function edit(Jotforms $jotforms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Jotforms  $jotforms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jotforms $jotforms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Jotforms  $jotforms
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jotforms $jotforms)
    {
        //
    }

    public function getjotforms(Request $request) {
        //$company_id = $request->get('company_id');
        //$employee_id = $request->get('employee_id');

        if (!empty($request->company_id)) {
            $data = Jotforms::where('company_id', 'like', '%"'.$request->company_id.'"%')->orderBy('formlink', 'Asc')->get();
            return response()->json($data, 200);
        } else if (!empty($request->employee_id)) {
            $data = Jotforms::where('employee_id', 'like', '%"'.$request->employee_id.'"%')->orderBy('formlink', 'Asc')->get();
            return response()->json($data, 200);
        }else{
            $data = Jotforms::orderBy('formlink', 'Asc')->get();
            return response()->json($data, 200);
        }
    }

    public function addnewLink(Request $request) {

        //$randNo = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8);
        $tourUp = new Jotforms();
        $tourUp->formlink = $request->get('formlink');
        //$tourUp->id = $randNo;
        //$tourUp->vimeo_video_id = $request->get('video');
        //$tourUp->discription = $request->get('video_description');
       // $tourUp->type = $request->get('role');
       // $tourUp->status = $request->get('status');


        if ($tourUp->save()) {
            return response()->json([], 200);
        } else {
            return response()->json([], 500);
        }
    
    }
    public function getFormLinkbyid($id) {
        $getTour = Jotforms::where('id', $id)->first();
        return response()->json($getTour, 200);
    }

    public function updateForm(Request $request, $id) {
        $tourUp = Jotforms::find($id);
        $tourUp->formlink = $request->get('formlink');
        $tourUp->save();
        return response()->json([], 200);

    }
    public function deleteform(Request $request) {
        $form_id = $request->form_id;
        if (empty($form_id)) {
            return response()->json(['form' => 'Invalid id no video found.!'], 422);
        }
        Jotforms::where('id', $form_id)->delete();

        return response()->json(['Form' => 'Form Deleted Successfully.!'], 200);
    }
}
