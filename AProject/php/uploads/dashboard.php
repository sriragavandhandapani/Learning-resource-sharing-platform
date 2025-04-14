<?php
require 'db.php';

// Handle file upload
if (isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];

    // Move the uploaded file to the server's directory
    $uploadDir = 'uploads/';
    $filePath = $uploadDir . basename($filename);
    if (move_uploaded_file($fileTmp, $filePath)) {
        // Insert file information into the database
        $conn->query("INSERT INTO files (filename, size, uploaded_at) VALUES ('$filename', $fileSize, NOW())");
    }
}

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

<body class="bg-gray-900 text-white min-h-screen p-6 relative">

  <!-- Header -->
  <header class="flex items-center justify-between mb-10">
    <h1 class="text-4xl font-bold font-outfit text-primary">📁 File Upload & Sharing</h1>
    <div class="flex gap-2">
      <a href="recyclebin.php" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">♻️ Recycle Bin</a>
      <a href="login.html" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">Signup</a>
    </div>
  </header>

  <!-- Upload Section -->
  <section class="w-full max-w-3xl mx-auto mb-12 p-8 bg-gradient-to-br from-[#1e1e2f] to-[#2a2a40] rounded-2xl shadow-[0_0_25px_#6366f188] border border-indigo-500/30 backdrop-blur-md relative overflow-hidden">
    <h2 class="text-3xl font-bold font-outfit text-indigo-400 mb-6 flex items-center gap-2">📤 Upload Your Files</h2>
    
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

  <!-- Script -->
  <script>
    const btn = document.getElementById('viewMoreBtn');
    if (btn) {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.file-item.hidden').forEach(el => el.classList.remove('hidden'));
        btn.style.display = 'none';
      });
    }
  </script>

<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>
