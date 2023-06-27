<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseResourceModel;

class ResourceController extends Controller {

    private $response;
    private $validationRules;

    public function __construct() {
        $this->response = [
            'success' => TRUE,
            'resources' => [],
            'errors' => [],
            'total' => 0,
        ];

        $this->validationRules = [
            'name' => [
                'required',
            ],
            'type' => [
                'required',
            ],
            'url' => [
                'required_if:type,link',
            ],
            'file' => [
                'required_if:type,file',
            ],
        ];

    }

    public function index(Request $request) {
        $resource = new Resource();

        $limit = $startFrom = 0;
        $isFilterApplied = FALSE;
        if ($request->page && $request->per_page) {
            $startFrom = ($request->page == 0) ? ($request->page * $request->per_page) : ($request->page - 1) * $request->per_page;
            $limit = $request->per_page;
            $isFilterApplied = TRUE;
        }

        if ($request->search) {
            $resource = $resource->where('name', 'like', '%' . $request->search . '%');
            $isFilterApplied = TRUE;
        }

        if (isset($request->status) && $request->status != -1) {
            $resource = $resource->where('status', $request->status);
            $isFilterApplied = TRUE;
        }

        if ($request->courses) {
            $resource = $resource->whereHas('courses', function($query) use ($request) {
                $query->where('course_id', $request->courses);
            });
            $isFilterApplied = TRUE;
        }

        if ($limit) {
            $resource->skip($startFrom);
            $resource->take($limit);
            $isFilterApplied = TRUE;
        }

        if ($isFilterApplied === FALSE) {
              $resource->orderby('tbl_resources.name','asc');
            $this->response['resources'] = $resource->get();
        } else {
              $resource->orderby('tbl_resources.name','asc');
            $this->response['resources'] = $resource->paginate($request->per_page);
        }
        $this->response['total'] = $resource->count();

        return response()->json($this->response);

    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), $this->validationRules);

        if ($validator->fails()) {
            $this->response['success'] = FALSE;
            $messages = [];
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $this->response['errors'] = implode(',', $message);

            return response()->json($this->response, 422);
        }

        try {
            DB::beginTransaction();

            if ($request->has('id')) {
                $resource = Resource::find($request->id);
            } else {
                $resource = new Resource();
            }

            $resource->name = $request->name;
            $resource->type = $request->type;
            $resource->available_after_course_completion = $request->availableAfterCourseCompletion == 'true' ? 1 : 0;

            if ($request->type == 'file') {
                if ($request->hasFile('file')) {
                    $file = $request->file;
                    $fileExtension = $file->getClientOriginalExtension(); // getting image extension
                    $fileName = time() . '.' . $fileExtension;
                    $file->move('images/course/resources', $fileName);
                    $resource->url = URL::to('images/course/resources/' . $fileName);
                    $resource->file_name = $file->getClientOriginalName();
                }
            } else {
                if ($request->has('url')) {
                    $resource->url = $request->url;
                }
            }

            $resource->save();

            $resource = $resource->fresh();

            $this->response['resources'] = $resource;
            DB::commit();

            return response()->json($this->response);
        } catch (\Exception $exception) {
            DB::rollback();
            $this->response['success'] = FALSE;
            $this->response['errors'] = $exception->getMessage();

            return response()->json([$this->response], 422);
        }

    }

    public function resourceCourses($resource) {
        return response()->json([
            'success' => TRUE,
            'errors' => [],
            'resourceCourses' => Resource::find($resource)->courses,
        ]);
    }

    public function updateStatus(Request $request, $id) {
        $resource = Resource::find($id);
        $resource->status = $request->status ? 1 : 0;
        $resource->save();
        $this->response['resources'] = $resource;

        return response()->json($this->response, 200);
    }
      public function deleteResources($id){
       
         CourseResourceModel::where('resource_id' ,$id)->delete();
         DB::table('tbl_resources')->where('id',$id)->delete();

           return response()->json('Resource deleted Sucessfully.', 200);
    }
}
