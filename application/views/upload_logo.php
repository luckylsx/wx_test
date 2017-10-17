<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="<?php echo 'http://wx-test.lylucky.com/index.php/MemberCard/upload' ?>" method="post" enctype="multipart/form-data">
        <h3>上传卡券logo</h3>
        选择图片：<input type="file" name="logo">
        <br>
        <input type="submit" value="制作卡券">
    </form>
</body>
</html>