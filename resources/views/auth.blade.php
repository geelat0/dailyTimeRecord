<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-color: #f7fafc;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box; /* Prevent content overflow */
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 100px; /* Adjust logo size */
            height: auto;
            border-radius: 12px; /* Rounded logo edges */
        }
        h3 {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4a5568;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            margin-bottom: 16px;
            padding: 10px;
            background: #fed7d7;
            color: #c53030;
            border-radius: 8px;
        }
        label {
            display: block;
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 0 auto;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            background: rgb(249, 115, 22);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background: rgb(234, 88, 12);
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: #4a5568;
            margin-top: 16px;
        }
        .footer a {
            color: rgb(249, 115, 22);
            text-decoration: none;
            border-radius: 4px;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <img src="{{ config('app.url') }}/bear-logo-dswd.png" style="width:60px">
            <img src="{{ config('app.url') }}/DSWD_FULL_TEXT.png" style="width:100px">
        </div>
        
        <h3>EMPOWEREX Login</h3>
        
        {{-- @if (session('error'))
            <div class="error">
                {{ session('error') }}
            </div> --}}
        {{-- @endif --}}

        <form method="POST" action="">
            @csrf

            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required autofocus>
                {{-- @error('email')
                    <span class="error"></span>
                @enderror --}}
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                {{-- @error('password')
                    <span class="error"></span> --}}
                {{-- @enderror --}}
            </div>

            <button type="submit">Login </button>
        </form>
    </div>
</body>
</html>
