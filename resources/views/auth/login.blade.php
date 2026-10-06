<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Petugas - Sistem Rekap Berita Kemitraan Diskominfo Kab. Bogor</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #004aad;
            --primary-dark: #072a63;
            --primary-deep: #051d45;
            --primary-light: #e8f2fe;
            --accent-yellow: #ffbf00;
            --accent-yellow-dark: #d97706;
            --accent-green: #10b981;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --surface: #ffffff;
            --radius-md: 12px;
            --radius-lg: 18px;
            --shadow: 0 10px 30px -5px rgba(7, 42, 99, 0.12), 0 4px 6px -2px rgba(7, 42, 99, 0.05);
            --shadow-lg: 0 20px 40px -10px rgba(7, 42, 99, 0.22);
            --font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Background decorative shapes */
        .bg-decor {
            display: none;
        }
        .bg-decor-1 {
            width: 450px;
            height: 450px;
            background: #ffbf00;
            top: -120px;
            left: -100px;
        }
        .bg-decor-2 {
            width: 500px;
            height: 500px;
            background: #00d2ff;
            bottom: -150px;
            right: -100px;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 38px 32px 32px 32px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .header-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .brand-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .brand-badge {
            background: var(--accent-yellow);
            color: #000;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 3px 7px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        .alert-error {
            background-color: var(--danger-light);
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.95rem;
            transition: color 0.2s;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 42px;
            font-family: inherit;
            font-size: 0.92rem;
            color: var(--text-dark);
            background-color: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 74, 173, 0.12);
        }

        .form-control:focus + .input-icon,
        .input-group:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-pwd {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        .toggle-pwd:hover {
            color: var(--primary);
        }

        .field-error {
            color: var(--danger);
            font-size: 0.78rem;
            font-weight: 500;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background-color: var(--primary);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 74, 173, 0.2);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .demo-box {
            margin-top: 24px;
            padding: 12px 14px;
            background: #f1f5f9;
            border-radius: var(--radius-md);
            border: 1px dashed #cbd5e1;
            font-size: 0.8rem;
            color: #475569;
        }

        .demo-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .btn-fill-demo {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--primary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-fill-demo:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body>

    <!-- Decorative ambient glows -->
    <div class="bg-decor bg-decor-1"></div>
    <div class="bg-decor bg-decor-2"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Brand Header -->
            <div class="header-brand">
                <div class="logo-wrap">
                    <img src="{{ asset('assets/logo_baru.png') }}" alt="Logo Diskominfo Kab. Bogor" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,74,173,0.25));">
                </div>
                <div class="brand-name">
                    DISKOMINFO <span class="brand-badge">KAB. BOGOR</span>
                </div>
                <div class="brand-subtitle">
                    Sistem Rekapitulasi Berita Kemitraan
                </div>
            </div>

            <!-- Session & Error Alerts -->
            @if (session('success'))
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->has('email') && !old('email'))
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @elseif ($errors->has('email') && old('email'))
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Petugas</label>
                    <div class="input-group">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@diskominfo.bogorkab.go.id" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                        <button type="button" class="toggle-pwd" id="togglePasswordBtn" title="Tampilkan kata sandi">
                            <i class="fa-regular fa-eye" id="pwdEyeIcon"></i>
                        </button>
                    </div>
                    @if ($errors->has('password'))
                        <div class="field-error">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                <!-- Remember Me -->
                <div class="form-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="accent-color: var(--primary);">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Masuk ke Sistem</span>
                </button>

                <!-- Demo Credentials Box -->
                <div class="demo-box">
                    <div class="demo-box-header">
                        <span><i class="fa-solid fa-key" style="color: var(--accent-yellow-dark);"></i> Akun Petugas Default:</span>
                        <button type="button" class="btn-fill-demo" id="btnFillDemo">Gunakan Akun</button>
                    </div>
                    <div><strong>Email:</strong> admin@diskominfo.bogorkab.go.id</div>
                    <div><strong>Password:</strong> diskominfo123</div>
                </div>
            </form>
        </div>

        <div class="login-footer">
            &copy; {{ date('Y') }} Dinas Komunikasi dan Informatika Kabupaten Bogor<br>
            Bidang Kemitraan & Promosi Media
        </div>
    </div>

    <script>
        // Toggle Show / Hide Password
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const pwdInput = document.getElementById('password');
        const eyeIcon = document.getElementById('pwdEyeIcon');

        toggleBtn.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Quick fill demo credentials
        const fillDemoBtn = document.getElementById('btnFillDemo');
        if (fillDemoBtn) {
            fillDemoBtn.addEventListener('click', function() {
                document.getElementById('email').value = 'admin@diskominfo.bogorkab.go.id';
                document.getElementById('password').value = 'diskominfo123';
            });
        }
    </script>
</body>
</html>
