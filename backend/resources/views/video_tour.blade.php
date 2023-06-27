<!DOCTYPE html>
<html>
  <head>
    <title>Tour Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
  <link rel="stylesheet" href="http://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <style>
  .disabled {
    pointer-events: none;
    cursor: default;
    opacity: 0.4;
  }
  .player .vp-controls .play-bar {
    display: none !important;
  }
    </style>
   </head>
  <body>
  
    <div class="container">
    <h2 class="text-center"> Tours Management</h2>
   
    <div class="text-right" style="padding-bottom: 12px;">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal"><i class="fa fa-map-marker" aria-hidden="true"></i> Add Tour</button>
     </div>
    
      <!--Table-->
<table class="table table-hover table-fixed" id="tourTable">

<!--Table head-->
<thead>
  <tr>
    <th>SL No</th>
    <th>Thumbnail</th>
    <th>Name</th>
    <th>Video </th>
    <th>URL</th>
    <th>Type</th>
    <th>Tour Status</th>
    <th>Action</th>
  </tr>
</thead>
<!--Table head-->

<!--Table body-->
<tbody>
@php
$postervalue ="https://i.vimeocdn.com/video/";
$postervalue1 = ".webp?mw=700&amp;mh=392&amp;q=70";
$urlvalues = "https://lms.homeoftraining.com/#/tour?id=";
@endphp
@if($i=1)
@endif

@foreach($tourvideo as $tourvideos)
  <tr>
    <!-- <th scope="row">{{$tourvideos->id}}</th> -->

    <th scope="row">{{$i++}}</th>
    <td>
    <iframe class="text-center" style=" width: 180px; height: 100px;" src="https://player.vimeo.com/video/{{$tourvideos->vimeo_video_id}}" width="150" height="150" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>
    <!-- <iframe src="{{$tourvideos->vimeo_video_id}}.jpg" width="150" height="150" frameborder="0" title="Tour" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> -->
    <!-- <video
								id="my-video"
								class="video-js vjs-default-skin vjs-big-play-centered"
								controls
								preload="auto"
								width="180"
								height="100"
								poster="{{$postervalue}}{{$tourvideos->vimeo_video_id}}{{$postervalue1}}"
								data-setup="{}"
								>
						
								
                </video> -->
                
                <!-- <iframe class="text-center" style="position: absolute;" src="'https://player.vimeo.com/video/' + tour.videoUrl" width="320" height="240" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> -->
 
    </td>

    <td>{{$tourvideos->name}}</td>
    <td>{{$tourvideos->vimeo_video_id}}</td>
    <td>{{$urlvalues}}{{$tourvideos->tour_id}}</td>
    <td>{{$tourvideos->type}}</td>
    @if($tourvideos->status =='True')         
      <td>Active</td>         
@else
      <td>Inactive </td>        
@endif
    <td>
    <input type="hidden" value="{{$urlvalues}}{{$tourvideos->vimeo_video_id}}" id="tUrl_{{$tourvideos->id}}" class="tUrl">
    <form action="" method="POST">
    <a href="{{ url('editTour',$tourvideos->id)}}" title="Edit"><i class="far fa-edit"></i></a>
    <a href="{{ url('destroy',$tourvideos->id) }}" title="delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
    @if($tourvideos->status =='True')
    <a href="#" title="copylink" class="btn-copy" data-copy="{{$urlvalues}}{{$tourvideos->vimeo_video_id}}" onclick='copyclipboard("{{$urlvalues}}{{$tourvideos->tour_id}}")'><i class='far fa-copy'></i></a>
    @else
    <a href="#" class="disabled" onclick="return false"><i class='far fa-copy'></i></a>
@endif
</form>
    

    </td>
    </tr>
  @endforeach
   
</tbody>
<!--Table body-->
</table>
<!-- <input type="text" name="myInput" id="myInput" value="https://lms.train321.com/#/tour?id=256059498"> -->
<!--Table-->
</div>

<!-- user  form model -->
<div class="modal" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Add Tour Video</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <form method="post" action="{{ url('tourStore') }}">
        
<div class="form-group">
 
            <div class="form-group">
            <label for="usr">Name:</label>
            <input type="text" class="form-control" id="tName" name="tName" placeholer="Enter Name" require>
            </div>

            <div class="form-group">
            <label for="usr">Vimeo Video ID:</label>
            <input type="text" class="form-control" id="vimeoid" name="vimeoid" placeholer="Enter Vimeo Video ID" require>
            </div>

            <div class="form-group">
            <label for="comment">Description:</label>
          <input type="text" class="form-control" id="tDescription" name="tDescription" placeholer="Enter Description" require>
            </div>

            <div class="form-group">
            <label for="usr">Type:</label>
             <select name="tType" required="required">
    <option value="Public">Public</option>
    <option value="Private">Private</option>
        </select>
            </div>

            <div class="form-group">
            <label for="comment">Status:</label>
            <select name="tStatus" required="required">
    <option value="True">Active</option>
    <option value="False">Inactive </option>
        </select>
            </div>
</div>
        
        <!-- Modal footer -->
        <div class="modal-footer">
        <button type="submit" class="btn btn-success text-center">Submit</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        </div>
      
    </form>    
      </div>
    </div>
  </div>
  
</div>

<!-- stop model -->


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="http://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src='https://cdn.jsdelivr.net/clipboard.js/1.5.10/clipboard.min.js'></script>
<script src="{{URL::asset('/js/script.js')}}"></script>
<script> 	
//for tatatable
 $(document).ready( function () {
     $('#tourTable').DataTable();
 } );

 </script>

 <script>
  function copyclipboard(url) {
    $("body").append('<input id="copyURL" type="text" value="" />');
        $("#copyURL").val(url).select();
        document.execCommand("copy");
        $("#copyURL").remove();  
  }
</script>



 
  </body>
</html>