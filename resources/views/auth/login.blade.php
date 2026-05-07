<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PayRoll Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3f6e 0%, #2B5797 60%, #3a7bd5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #2B5797, #3a7bd5);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: #fff;
            margin: 0 auto 1rem;
        }
        .form-control:focus {
            border-color: #2B5797;
            box-shadow: 0 0 0 .2rem rgba(43,87,151,.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #2B5797, #3a7bd5);
            border: none; color: #fff;
            padding: .75rem;
            font-size: 1rem; font-weight: 600;
            border-radius: 8px;
            transition: opacity .2s;
        }
        .btn-login:hover { opacity: .9; color: #fff; }
        .input-group-text { background: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
        .form-control:not(:focus) { border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo"><i class="bi bi-cash-stack"></i></div>
        <h4 class="text-center fw-bold mb-1">PayRoll Manager</h4>
        <p class="text-center text-muted small mb-4">Sign in to your account</p>

        @if($errors->any())
        <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="you@company.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="passwordField"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-secondary border-start-0"
                            onclick="togglePassword()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <hr class="my-4">
        <div class="text-center text-muted" style="font-size:.75rem">
            <i class="bi bi-shield-lock me-1"></i>
            Secure Role-Based Access Control
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon  = document.getElementById('eyeIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>