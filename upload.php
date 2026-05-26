<?php
// Database
$host = "localhost";
$user = "root";
$password = "";
$database = "security_system";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create images folder
if (!is_dir("images")) {
    mkdir("images", 0777, true);
}

$message = "";

// When form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Distance
    $distance = $_POST['distance'] ?? 0;

    // Check image
    if (!isset($_FILES['image'])) {
        $message = "No image selected";
    } else {

        $imageTmp = $_FILES['image']['tmp_name'];

        $imageName = "images/" . time() . "_" .
            basename($_FILES['image']['name']);

        // Save image
        if (move_uploaded_file($imageTmp, $imageName)) {

            // Save database
            $sql = "INSERT INTO Intrusion_events
            (image_path, Distance_Detected, Event_Status)
            VALUES
            ('$imageName', '$distance', 'Intrusion Detected')";

            if ($conn->query($sql) === TRUE) {

                // Create mail object
                $mail = new PHPMailer(true);

                try {

                    // SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;

                    // Gmail
                    $mail->Username =
                        'uwamungukalisa1@gmail.com';

                    // Gmail App Password
                    $mail->Password =
                        'PUT_YOUR_APP_PASSWORD_HERE';

                    $mail->SMTPSecure =
                        PHPMailer::ENCRYPTION_STARTTLS;

                    $mail->Port = 587;

                    // Sender
                    $mail->setFrom(
                        'uwamungukalisa1@gmail.com',
                        'ESP32 Security System'
                    );

                    // Receiver
                    $mail->addAddress(
                        'mBrigitte451@gmail.com'
                    );

                    // Email
                    $mail->isHTML(true);

                    $mail->Subject =
                        'Intrusion Detected';

                    $serverIP = "http://localhost/";

                    $mail->Body = "
                        <h2>Security Alert</h2>

                        <p>
                        <b>Distance:</b>
                        {$distance} cm
                        </p>

                        <p>
                        <b>Time:</b>
                        " . date("Y-m-d H:i:s") . "
                        </p>

                        <img src='{$serverIP}{$imageName}'
                        width='300'>
                    ";

                    // Send email
                    $mail->send();

                    $message =
                        "SUCCESS + EMAIL SENT";

                } catch (Exception $e) {

                    $message =
                        "Image saved but email failed";
                }

            } else {

                $message =
                    "Database Error";
            }

        } else {

            $message =
                "Failed to upload image";
        }
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html>
<head>

    <title>ESP32 Security Upload</title>

    <style>

        body{
            background:#f0f2f5;
            font-family:Arial;
        }

        .container{
            width:400px;
            background:white;
            margin:60px auto;
            padding:30px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.2);
        }

        h2{
            text-align:center;
            color:#333;
        }

        input{
            width:100%;
            padding:12px;
            margin-top:10px;
            margin-bottom:15px;
        }

        button{
            width:100%;
            padding:12px;
            background:#007bff;
            color:white;
            border:none;
            cursor:pointer;
            font-size:16px;
        }

        button:hover{
            background:#0056b3;
        }

        .message{
            text-align:center;
            color:green;
            margin-bottom:15px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Upload Security Image</h2>

    <div class="message">
        <?php echo $message; ?>
    </div>

    <form method="POST"
          enctype="multipart/form-data">

        <input type="number"
               name="distance"
               placeholder="Enter Distance">

        <input type="file"
               name="image"
               required>

        <button type="submit">
            Upload Image
        </button>

    </form>

</div>

</body>
</html>