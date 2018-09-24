<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Index Page</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
  </head>
  <body>
    <div class="container">
    <br />
    @if (\Session::has('result'))
      <div class="alert alert-success">
        <p>{{ \Session::get('result') }}</p>
      </div><br />
     @endif
    <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Company</th>
      </tr>
    </thead>
    <tbody>
      
      @foreach($users as $car)
      <tr>
        <td>{{$car->id}}</td>
        <td>{{$car->first_name}}</td>
        
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  </body>
</html>