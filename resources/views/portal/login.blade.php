<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Login</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: Arial, Helvetica, sans-serif; font-size: 13px; background: #f0f0f0; }
.login-wrap {
    display: flex; align-items: center; justify-content: center; height: 100%;
}
.login-box {
    background: #fff; width: 340px; border-radius: 3px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15); overflow: hidden;
}
.login-header {
    background: linear-gradient(to bottom, #e84040, #c43030);
    color: #fff; padding: 20px 20px 16px;
    text-align: center;
}
.login-header h1 { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
.login-header p  { font-size: 11px; opacity: 0.8; margin-top: 4px; }
.login-body { padding: 20px; }
.field { margin-bottom: 14px; }
.field label { display: block; font-size: 11px; color: #555; margin-bottom: 4px; font-weight: bold; }
.field input {
    width: 100%; border: 1px solid #ccc; padding: 7px 10px;
    font-size: 13px; outline: none; height: 34px;
}
.field input:focus { border-color: #e84040; }
.error-msg { color: #c00; font-size: 11px; margin-top: 4px; }
.btn-login {
    display: block; width: 100%; height: 38px;
    background: #e84040; color: #fff; border: none;
    font-size: 13px; font-weight: bold; letter-spacing: 1px; cursor: pointer;
    margin-top: 6px;
}
.btn-login:hover { background: #c43030; }
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="login-header">
            <h1>CUSTOMER PORTAL</h1>
            <p>Sign in to view your account</p>
        </div>
        <div class="login-body">
            <form method="POST" action="{{ route('portal.login.post') }}">
                @csrf
                <div class="field">
                    <label>EMAIL</label>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                    @error('email')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>PASSWORD</label>
                    <input type="password" name="password" autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login">LOGIN</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
