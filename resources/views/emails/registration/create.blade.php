<!DOCTYPE html>
<html>
<head>
    <title>Client Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Optional simple styling -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            padding: 40px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #1e40af;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Complete Your Registration</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="error">
            <ul style="padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('client.registration.store', $token) }}">
        @csrf

        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <label>Email</label>
        <input type="email" name="email" value="{{ $email }}" readonly>

        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required>

        <label>Address</label>
        <input type="text" name="address" value="{{ old('address') }}">

        

        <button type="submit">Submit Registration</button>
    </form>
</div>

</body>
</html>