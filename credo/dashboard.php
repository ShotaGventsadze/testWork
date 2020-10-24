<?php
    session_start();
    if (!$_SESSION['USER_DATA']) {
        header("Location: index.html");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>TestWork</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link rel="stylesheet" href="css/style.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <div class="jumbotron text-center">
        <h1>სატესტო დავალება</h1>
        <br>
    </div>

    <div class="container">
        <div class="row">
            <div class="profileBlock col-sm-12">
                <h3>პირადი კაბინეტი</h3>
                <div class="row">
                    <div class="col-md-6 personalData">
                        <form class="registerForm">
                            <div class="form-group">
                                <label for="user">მომხმარებელი:</label>
                                <input type="text" class="form-control" id="user" value="<?=$_SESSION['USER_DATA']['user']?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="fullname">სახელი გვარი:</label>
                                <input type="text" class="form-control" id="fullname"value="<?=$_SESSION['USER_DATA']['fullname']?>" >
                            </div>
                            <div class="form-group">
                                <label for="avatar">ავატარი:</label>
                                <input type="file" class="form-control" id="avatar" onchange="convertToBase64(this);">
                                <input type="hidden" id="base64img">
                            </div>
                            <div class="form-group">
                                <button type="button" id="editProfileBtn" class="btn btn-info">ინფომაციის ცვლილება</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 personalData">
                        <form class="registerForm">
                            <div class="form-group">
                                <label for="user">ავატარი:</label>
                                <img src="uploads/<?=$_SESSION['USER_DATA']['img']?>" alt="" width="60%">
                            </div>
                        </form>

                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
<script src="js/app.js"></script>
</body>
</html>


