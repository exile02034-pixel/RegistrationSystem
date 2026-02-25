<!DOCTYPE html>
<html>
<head>
    <title>Registration Submitted</title>
</head>
<body>
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <h1>Thank you for registering!</h1>
    <p>Your registration has been successfully submitted.</p>
</body>
</html>