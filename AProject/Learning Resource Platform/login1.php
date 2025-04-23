<?php
session_start();
$conn = new mysqli("localhost", "root", "", "auth_system");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user"] = $email;
            header("Location: AAindex.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en" class="dark transition-all duration-300">
<head>
  <meta charset="UTF-8">
  <title>Login - PeerConnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
  <style>
    @keyframes spin-slow {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .animate-spin-slow {
      animation: spin-slow 20s linear infinite;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white">

  <!-- Navbar -->
  <header class="bg-[#2c2f48] px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-lg">
    <div class="text-xl font-bold text-white">PeerConnect</div>
    <nav>
      <a href="AAindex.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
        &larr; Back to Home
      </a>
    </nav>
  </header>

  <!-- Login Form with Gozzy Effect -->
  <div class="flex justify-center items-center min-h-screen px-4">
    <div class="relative w-full max-w-md p-10 rounded-3xl bg-gray-900 border border-white/20 overflow-hidden z-10">

      <!-- Gozzy animated border -->
      <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-gray-900 via-gray-800 to-black animate-spin-slow opacity-20 blur-2xl"></div>

      <div class="relative z-10 space-y-6">
        <h2 class="text-3xl font-bold text-center text-white drop-shadow">Log in</h2>

        <?php if (!empty($error)) : ?>
          <div class="bg-red-600 text-white p-3 rounded-lg text-center">
            <?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white">Email</label>
            <input type="email" name="email" required
              class="w-full p-3 border border-white/30 bg-black/40 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <div class="mb-6">
            <label class="block text-sm font-semibold text-white">Password</label>
            <input type="password" name="password" required
              class="w-full p-3 border border-white/30 bg-black/40 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold hover:scale-105 transition-all shadow-md">
            Log in
          </button>
        </form>

        <p class="text-center text-sm text-white/80">
          Don't have an account?
          <a href="login.php" class="text-indigo-400 font-semibold hover:underline">Sign up</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>
