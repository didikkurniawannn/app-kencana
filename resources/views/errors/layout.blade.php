<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Kencana</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#14797b',
                        secondary: '#0d9488',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        body {
            background: radial-gradient(circle at top right, #f0fdfa, #ffffff);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(20, 121, 123, 0.15);
        }

        .float-animation {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .btn-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(20, 121, 123, 0.3);
        }
    </style>
</head>
<body class="flex items-center justify-center p-6 text-slate-800">

    <div class="max-w-xl w-full text-center">
        <!-- Icon/Illustration Space -->
        <div class="mb-8 flex justify-center">
            <div class="relative">
                <div class="absolute -inset-4 bg-primary/20 blur-2xl rounded-full"></div>
                <div class="relative float-animation">
                    @yield('icon')
                </div>
            </div>
        </div>

        <div class="glass-card rounded-[2.5rem] p-10 md:p-14 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary/5 rounded-full"></div>
            
            <h1 class="text-7xl md:text-8xl font-bold bg-clip-text text-transparent bg-gradient-to-br from-primary to-emerald-600 mb-4">
                @yield('code')
            </h1>
            
            <h2 class="text-2xl md:text-3xl font-semibold mb-6 text-slate-900">
                @yield('message')
            </h2>
            
            <p class="text-slate-500 leading-relaxed mb-10 text-lg">
                @yield('description')
                <br>
                <span class="mt-4 block font-medium text-primary/80">
                    Silakan hubungi Admin di <span class="font-bold">082130314252</span> untuk bantuan teknis.
                </span>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.history.back()" class="btn-hover px-8 py-4 bg-slate-900 text-white rounded-2xl font-semibold flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </button>
                
                <a href="https://wa.me/6282130314252" target="_blank" class="btn-hover px-8 py-4 bg-emerald-500 text-white rounded-2xl font-semibold flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                    </svg>
                    Bantuan Admin
                </a>
            </div>
        </div>
        
        <p class="mt-8 text-slate-400 text-sm font-medium">
            &copy; {{ date('Y') }} Kencana - Aplikasi Pengelolaan Anggaran. All Rights Reserved.
        </p>
    </div>

</body>
</html>
