<?php
require 'db.php';

// Handle deletion (soft delete)
if (isset($_POST['delete'])) {
  $id = (int)$_POST['id'];
  $type = $_POST['type'];
  $table = $type === 'file' ? 'files' : 'posts';
  $conn->query("UPDATE $table SET deleted_at = NOW() WHERE id = $id");
  header("Location: dashboard.php");
  exit();
}

// Fetch files
$files = $conn->query("SELECT * FROM files WHERE deleted_at IS NULL ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#6366f1',
            secondary: '#ec4899',
          },
          fontFamily: {
            japan: ['"Shippori Mincho B1"', 'serif'],
          }
        }
      }
    }
  </script>
  <style>
    body { cursor: none; }
    .cursor-dot {
      position: fixed;
      top: 0; left: 0;
      width: 30px; height: 30px;
      background-color: #f9dec9;
      border-radius: 70%;
      pointer-events: none;
      transform: translate(-50%, -50%);
      z-index: 9999;
      transition: transform 0.05s ease-out, background-color 0.2s ease;
    }
    .cursor-dot::after {
      content: ''; display: block;
      width: 8px; height: 8px;
      margin: auto;
      background-color: #000;
      border-radius: 50%;
      position: relative;
      top: 50%; transform: translateY(-50%);
    }
    .cursor-dot.transparent { background-color: transparent; }
  </style>
</head>

<body class="bg-gray-900 text-white min-h-screen p-6">

  <!-- Cursor -->
  <div class="cursor-dot" id="cursor-dot"></div>

  <!-- Header -->
  <header class="flex items-center justify-between mb-10">
    <h1 class="text-4xl font-bold font-japan text-primary">📁 File Upload & Sharing</h1>
    <div class="flex gap-2">
      <a href="recyclebin.php" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">♻️ Recycle Bin</a>
      <a href="login.html" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">Signup</a>
    </div>
  </header>

  <!-- Upload Section (Improved) -->
  <section class="w-full max-w-3xl mx-auto mb-12 p-8 bg-gradient-to-br from-[#1e1e2f] to-[#2a2a40] rounded-2xl shadow-[0_0_25px_#6366f188] border border-indigo-500/30 backdrop-blur-md relative overflow-hidden">
    <div class="absolute top-0 right-0 text-6xl text-indigo-400/10 pointer-events-none animate-pulse"></div>
    <h2 class="text-3xl font-bold font-japan text-indigo-400 mb-6 flex items-center gap-2">📤 Upload Your Files</h2>
    
    <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-5">
      <div>
        <label class="block mb-2 font-semibold text-gray-300">Choose File</label>
        <input type="file" name="file" required
               class="w-full p-3 rounded-lg bg-gray-800 text-white border border-gray-700 file:bg-indigo-600 file:text-white file:rounded file:px-4 file:py-2 file:border-none hover:file:bg-indigo-700 transition-all"/>
      </div>
      <button type="submit"
              class="w-full py-3 text-lg font-semibold bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-lg shadow-indigo-500/30 transition transform hover:scale-105">
        🚀 Upload Now
      </button>
    </form>
  </section>

  <!-- File List -->
  <section class="bg-gray-800 p-6 rounded-xl shadow-lg max-w-4xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6 text-indigo-300">📁 Uploaded Files</h2>
    <ul class="space-y-4" id="file-list">
      <?php $count = 0; while ($file = $files->fetch_assoc()): $count++; $hidden = $count > 4 ? 'hidden' : ''; ?>
        <li class="bg-gray-700 p-4 rounded-lg file-item <?= $hidden ?> flex justify-between items-start">
          <div>
            <p class="font-medium"><?= $file['filename'] ?> (<?= round($file['size'] / 1024, 2) ?> KB)</p>
            <a href="download.php?file=<?= $file['id'] ?>" class="text-blue-400 underline">Download</a>
          </div>
          <form method="POST">
            <input type="hidden" name="id" value="<?= $file['id'] ?>">
            <input type="hidden" name="type" value="file">
            <button name="delete" class="ml-4 text-red-500 hover:text-red-700">🗑</button>
          </form>
        </li>
      <?php endwhile; ?>
    </ul>
    <?php if ($count > 4): ?>
      <button id="viewMoreBtn" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition">⬇️ View More</button>
    <?php endif; ?>
  </section>

  <!-- Scripts -->
  <script>
    const btn = document.getElementById('viewMoreBtn');
    if (btn) {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.file-item.hidden').forEach(el => el.classList.remove('hidden'));
        btn.style.display = 'none';
      });
    }

    // Cursor follow
    const cursor = document.getElementById("cursor-dot");
    document.addEventListener("mousemove", (e) => {
      cursor.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
    });

    // Transparent cursor over text & UI
    const transparentTags = ['A', 'BUTTON', 'INPUT', 'TEXTAREA', 'LABEL', 'P', 'H1', 'H2', 'H3', 'SPAN'];
    document.addEventListener("mouseover", (e) => {
      if (transparentTags.includes(e.target.tagName)) {
        cursor.classList.add("transparent");
      }
    });
    document.addEventListener("mouseout", () => {
      cursor.classList.remove("transparent");
    });
  </script>

</body>
</html>
