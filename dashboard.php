<?php
require 'db.php';

// Handle file upload
if (isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];

    $uploadDir = 'uploads/';
    $filePath = $uploadDir . basename($filename);

    if (move_uploaded_file($fileTmp, $filePath)) {
        $conn->query("INSERT INTO files (filename, size, uploaded_at) VALUES ('$filename', $fileSize, NOW())");
    }
}

// Handle soft delete
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $type = $_POST['type'];
    $table = $type === 'file' ? 'files' : 'posts';
    $conn->query("UPDATE $table SET deleted_at = NOW() WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch files
$files = $conn->query("SELECT * FROM files WHERE deleted_at IS NULL ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>File Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
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
            outfit: ['Outfit', 'sans-serif'],
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-900 text-white min-h-screen font-outfit">

  <!-- Navbar -->
  <header class="bg-[#2c2f48] px-6 py-4 shadow-md flex justify-between items-center sticky top-0 z-50">
    <div class="text-xl font-bold text-white">📁 PeerConnect Files</div>
    <nav>
      <ul class="flex gap-6 text-md font-medium text-white">
        <li><a href="AAindex.php" class="text-blue-400 font-semibold">Home</a></li>
        <li><a href="about.php" class="hover:text-indigo-300 transition">About</a></li>
        <li><a href="#" class="hover:text-indigo-300 transition">Review</a></li>
        <li><a href="discussion.php" class="hover:text-indigo-300 transition">Discussion Area</a></li>
        <li><a href="dashboard.php" class="hover:text-indigo-300 transition">Upload</a></li>
        <li>
          <a href="login1.php"
             class="bg-indigo-600 mb-12 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition">
            Login
          </a>
        </li>
      </ul>
    </nav>
  </header>

  <!-- Upload Section -->
  <section class="w-full max-w-3xl mx-auto mt-16 mb-12 p-8 bg-gradient-to-br from-[#1e1e2f] to-[#2a2a40] rounded-2xl shadow-[0_0_25px_#6366f188] border border-indigo-500/30 backdrop-blur-md relative overflow-hidden">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-indigo-400 flex items-center gap-2">📤 Upload Your Files</h2>
      <a href="recyclebin.php" class="text-sm font-semibold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition-all">
         Recycle Bin
      </a>
    </div>
    
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
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
            <p class="font-medium"><?= htmlspecialchars($file['filename']) ?> (<?= round($file['size'] / 1024, 2) ?> KB)</p>
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

  <script>
    const btn = document.getElementById('viewMoreBtn');
    if (btn) {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.file-item.hidden').forEach(el => el.classList.remove('hidden'));
        btn.style.display = 'none';
      });
    }
  </script>

</body>
</html>
