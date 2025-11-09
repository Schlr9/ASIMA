<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem</title>
    <link href="../dist/output.css" rel="stylesheet">
</head>
<body id="main-body" class="bg-gray-800 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm p-8 bg-gray-800 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-center text-white mb-2">Asisten Mahasiswa</h2>
    <h2 class="text-2xl font-bold text-center text-blue-600 mb-2">UNIVERSITAS MANDIRI</h2>
        <p class="text-center text-white mb-6">Masuk untuk memulai</p>
        
        <form id="login-form">
            <div class="mb-4">
                <!-- DIPERBAIKI: Menggunakan 'username' agar konsisten dengan PHP -->
                <label for="npm" class="block text-white text-sm font-bold mb-2">NPM</label>
                <input type="text" id="npm" name="npm" class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-white text-sm font-bold mb-2">Password</label>
                <!-- DIPERBAIKI: Menambahkan atribut 'name' -->
                <input type="password" id="password" name="password" class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <!-- DIPERBAIKI: Tombol login ID diperbaiki dari 'login-buttoon' menjadi 'login-button' -->
            <button id="login-button" type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors">Masuk</button>
        </form>

    <!-- Elemen untuk pesan error/sukses login -->
    <div id="error-message" class="mt-4 text-center text-sm font-semibold"></div>
        
        <!-- DIPERBAIKI: Menambahkan elemen debug yang hilang dari HTML -->
        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            if (!loginForm) return;

            async function handleLoginSubmit(event) {
                event.preventDefault();

                const loginButton = document.getElementById('login-button');
                const errorMessageDiv = document.getElementById('error-message');

                loginButton.disabled = true;
                loginButton.textContent = 'Memproses...';
                errorMessageDiv.textContent = '';
                errorMessageDiv.classList.remove('text-red-500', 'text-green-500');

                const formData = new FormData(loginForm);
                const data = Object.fromEntries(formData.entries());
                
                let responseText = '';

                try {
                    // Pastikan path ke login.php sudah benar
                    const response = await fetch('config/login.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    responseText = await response.text();

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        throw new Error("Server tidak merespons dengan JSON. Ini adalah error PHP.");
                    }

                    if (!response.ok) {
                        throw new Error(result.message || `Terjadi error HTTP: ${response.status}`);
                    }

                    if (result.success) {
                        errorMessageDiv.textContent = 'Login berhasil! Mengarahkan...';
                        errorMessageDiv.classList.remove('text-red-500');
                        errorMessageDiv.classList.add('text-green-500');
                        setTimeout(() => { window.location.href = 'index.php'; }, 800);
                    } else {
                        errorMessageDiv.textContent = result.message || 'Terjadi kesalahan yang tidak diketahui.';
                        errorMessageDiv.classList.remove('text-green-500');
                        errorMessageDiv.classList.add('text-red-500');
                    }

                } catch (error) {
                    console.error('Login Error:', error);
                    errorMessageDiv.textContent = error.message;
                    errorMessageDiv.classList.remove('text-green-500');
                    errorMessageDiv.classList.add('text-red-500');
                } finally {
                    if(loginButton) {
                        loginButton.disabled = false;
                        loginButton.textContent = 'Masuk';
                    }
                }
            }

            loginForm.addEventListener('submit', handleLoginSubmit);
        });
    </script>
</body>
</html>