<?php
require 'db.php';

// Handle new post submission
if (isset($_POST['add_post'])) {
  $title = $conn->real_escape_string($_POST['post_title']);
  $link = $conn->real_escape_string($_POST['post_link']);
  $conn->query("INSERT INTO posts (title, link) VALUES ('$title', '$link')");
  header("Location: dashboard.php");
  exit();
}

// Handle deletion (soft delete)
if (isset($_POST['delete'])) {
  $id = (int)$_POST['id'];
  $type = $_POST['type'];
  $table = $type === 'post' ? 'posts' : 'files';
  $conn->query("UPDATE $table SET deleted_at = NOW() WHERE id = $id");
  header("Location: dashboard.php");
  exit();
}

// Fetch posts and files
$posts = $conn->query("SELECT * FROM posts WHERE deleted_at IS NULL ORDER BY posted_at DESC");
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
</head>
<body class="bg-gray-900 text-white min-h-screen p-6">
  <header class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-bold">Dashboard</h1>
    <div class="flex gap-2">
      <a href="recyclebin.php" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">♻️ Recycle Bin</a>
      <a href="index.html" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">Logout</a>
    </div>
  </header>

  <!-- Compact Upload Form -->
<form action="upload.php" method="POST" enctype="multipart/form-data"
      class="mb-6 relative group bg-gradient-to-br from-[#1e1e2f] to-[#2a2a40] p-6 rounded-xl shadow-md shadow-indigo-500/10 border border-indigo-600/20 backdrop-blur-sm transition-all duration-300 hover:shadow-indigo-500/30 w-full max-w-md">

  <h3 class="text-2xl font-japan text-indigo-400 mb-3 flex items-center gap-2">
    📤 Upload File
  </h3>

  <label class="block mb-1 text-sm font-semibold text-gray-300">Choose File</label>

  <input type="file" name="file" required
         class="mb-3 block w-full text-sm text-white bg-gray-800 border border-gray-700 p-2 rounded-md file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all"/>

  <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-4 rounded-md text-sm transition-all duration-200 shadow shadow-indigo-500/20 hover:shadow-indigo-500/30">
    Upload
  </button>
</form>


  <div class="grid md:grid-cols-2 gap-8">
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg">
      <h2 class="text-xl font-semibold mb-2">📢 Recent Posts</h2>
      <form action="" method="POST" class="mb-6 bg-gradient-to-br from-[#1f1f1f] to-[#2c2c3c] border border-indigo-500/40 rounded-2xl p-5 shadow-[0_0_20px_#6366f144] backdrop-blur-md relative overflow-hidden">
        <div class="absolute top-0 right-0 text-3xl opacity-20 pointer-events-none animate-pulse">🌸</div>
        <h3 class="text-2xl font-semibold text-indigo-400 font-japan mb-4">✒️ Share Your Wisdom</h3>
        <input type="text" name="post_title" placeholder="Enter a cool title..." required class="mb-3 w-full p-2 rounded bg-[#3f3f52] text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        <input type="url" name="post_link" placeholder="https://..." required class="mb-4 w-full p-2 rounded bg-[#3f3f52] text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        <button type="submit" name="add_post" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition-all duration-300 transform hover:scale-[1.02] shadow-md shadow-indigo-500/30">
          ➕ Post Now
        </button>
      </form>
      <ul class="space-y-4">
        <?php while ($post = $posts->fetch_assoc()): ?>
          <li class="bg-gray-700 p-4 rounded-lg hover:bg-gray-600 transition flex justify-between items-start">
            <a href="<?= $post['link'] ?>" target="_blank" class="block">
              <h3 class="font-bold text-lg text-primary"><?= $post['title'] ?></h3>
              <p class="text-sm text-gray-300"><?= date("M j, Y", strtotime($post['posted_at'])) ?></p>
            </a>
            <form method="POST">
              <input type="hidden" name="id" value="<?= $post['id'] ?>">
              <input type="hidden" name="type" value="post">
              <button name="delete" class="ml-4 text-red-500 hover:text-red-700">🗑</button>
            </form>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <div class="bg-gray-800 p-6 rounded-xl shadow-lg">
      <h2 class="text-xl font-semibold mb-4">📁 Uploaded Files</h2>
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
        <button id="viewMoreBtn" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition">⬇️ View More</button>
      <?php endif; ?>
    </div>
  </div>

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
