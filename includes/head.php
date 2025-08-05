<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'slate': {
            950: '#0a0a0a'
          }
        }
      }
    }
  }
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
  body {
    font-family: 'Inter', sans-serif;
  }

  .gradient-text {
    background: linear-gradient(135deg, #ffffff, #a1a1aa);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .glass {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  /* Premium Achievement Cards */
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Smooth scrolling for mobile */
  @media (max-width: 768px) {
    .achievement-grid {
      scroll-behavior: smooth;
    }
  }

  /* Custom backdrop blur for better browser support */
  @supports not (backdrop-filter: blur(10px)) {
    .glass {
      background: rgba(15, 23, 42, 0.8);
    }
  }
</style>