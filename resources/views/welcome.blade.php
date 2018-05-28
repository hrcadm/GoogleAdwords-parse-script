<!DOCTYPE html>
<html>
<head>
    <title>test</title>
</head>
<body>

    <form method="POST" action="{{ action('TestController@test') }}" enctype="multipart/form-data">

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <label>JSON</label><br>
        <input type="file" name="jsonData"><br>

        <input type="submit" name="Submit">

    </form>

</body>
</html>