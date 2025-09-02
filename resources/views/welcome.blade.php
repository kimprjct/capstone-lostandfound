<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Welcome to SuPehDah</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://unpkg.com/heroicons@2.0.16/dist/heroicons.min.js"></script>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #E8C0D8, #DEB1A8, #C2AA6A);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-gray-800">
  <div class="bg-white max-w-xl w-full p-8 rounded-3xl shadow-2xl border-l-8 border-[#3D7256]">
    <div class="flex flex-col items-center text-center">
      <!-- Logo -->
      <img src="/images/pet-logo.png" alt="SuPehDah Logo" class="w-24 h-24 mb-4 rounded-full border-4 border-[#C2AA6A] shadow-md" />

      <!-- Heading -->
      <h1 class="text-4xl font-bold text-[#1C5B38] mb-2">
        Welcome to <span class="text-[#DEB1A8]">SuPehDah</span>
      </h1>

      <!-- Subtext -->
      <p class="text-gray-700 mb-6">
        Your one-stop app to schedule vet appointments. Fast, reliable, and paw-sitively perfect for your pets!
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-wrap justify-center gap-4 w-full">
       <a href="/login" class="... transition-all duration-300 hover:scale-105">Login</a>

        <a href="/register" class="bg-white border-2 border-[#3D7256] text-[#3D7256] px-6 py-2 rounded-xl hover:bg-[#F0F8F5] transition-all duration-200">
          <i class="fas fa-user-plus mr-2"></i>Register
        </a>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-sm text-gray-400">
      &copy; {{ date('Y') }} SuPehDah. All rights reserved.
    </div>
  </div>
</body>
</html>
