<!DOCTYPE html>
<html lang="en">
<head>
  <title>Tour Management Edit</title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

  <div class="container">
  <h2 class="text-center">Tour Management Edit</h2>
  <form method="POST" action="{{ url('tourUpdate',$editTours->id) }}" >
   
  <table class="table table-striped">

    <tbody>
      <tr>
        <th>Name</th><td><input type="text" class="form-control" name="tName" value="{{ $editTours->name }}"></td>
      </tr>
      <tr>
      <th>URL</th><td><input type="text" class="form-control" name="tVimeoId" value="{{ $editTours->vimeo_video_id}}"></td>
      </tr>
      <tr>
      <th>Description</th><td><input type="text" class="form-control" name="tDescript" value="{{ $editTours->discription }}"></td>
      </tr>
      <tr>
      <th>Type</th><td>
      <select name="tType">      
        <option value="Public" <?php echo ($editTours->type == 'Public')?'selected':''; ?>>Public</option>  
        <option value="Private" <?php echo ($editTours->type == 'Private')?'selected':''; ?>>Private</option>    
      </select>
        </td>
        


      </tr>
      <tr>
      <th>Tour Status</th><td>
      <select name="tstatus">
       <option value="True" <?php echo ($editTours->status == 'True')?'selected':''; ?>>Active</option>  
      <option value="False" <?php echo ($editTours->status == 'False')?'selected':''; ?>>Inactive</option>    
        </select>
     </td>
      
      </tr>

    </tbody>
  </table>
  <button type="submit" class="btn btn-success">Submit</button>
    <a class="btn btn-danger" href="{{ url('tour') }}">Cancel</a>
  </form>
</div>

</body>
</html>
