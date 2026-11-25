<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — SPK Seleksi Jalur Prestasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="mb-2"><i class="bi bi-diagram-3" style="font-size:2.2rem;color:#2563eb"></i></div>
                <h5 class="fw-bold mb-1">SPK Seleksi Jalur Prestasi</h5>
                <div class="text-secondary small">Metode AHP + Weighted Product</div>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kata Sandi</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
            </form>
            <div class="text-center text-secondary small mt-3">
                Akun awal: admin@spk.test / admin123 (ubah setelah instalasi)
            </div>
        </div>
    </div>
</div>
</body>
</html>
