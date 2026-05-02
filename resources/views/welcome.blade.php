<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario SaaS - Control total de tu negocio</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    <script>
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
</head>
<body class="bg-zinc-50 text-zinc-800 antialiased dark:bg-zinc-900 dark:text-zinc-100 selection:bg-blue-600 selection:text-white flex flex-col min-h-screen transition-colors duration-300">

    <header class="bg-white dark:bg-zinc-800 shadow-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0 flex items-center">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="ml-2 text-xl font-bold text-zinc-900 dark:text-white">InventarioSaaS</span>
                </div>

                <nav class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-zinc-600 hover:text-blue-600 dark:text-zinc-300 dark:hover:text-blue-400 transition">
                                Ir al Panel (Dashboard)
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                Acceder a mi cuenta
                            </a>
                        @endauth
                    @endif
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 text-center">
            <h1 class="text-4xl tracking-tight font-extrabold text-zinc-900 dark:text-white sm:text-5xl md:text-6xl">
                <span class="block">Gestiona tu inventario con</span>
                <span class="block text-blue-600 dark:text-blue-400">precisión milimétrica</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-zinc-500 dark:text-zinc-400 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Nuestra plataforma te ofrece control total sobre tus sucursales, productos, entradas y salidas. Una solución diseñada exclusivamente para empresas que buscan crecer sin perder el control.
            </p>
            <div class="mt-10 flex justify-center gap-4">
                <a href="#contacto" class="px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg transition shadow-lg">
                    Contactar a Ventas
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 py-16 sm:py-24 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-blue-100 text-blue-600 dark:bg-zinc-900/50 dark:text-blue-400 mx-auto mb-4 border dark:border-zinc-700">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Multisucursal</h3>
                        <p class="mt-2 text-base text-zinc-500 dark:text-zinc-400">Administra el stock de múltiples tiendas o almacenes desde un solo panel centralizado.</p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-blue-100 text-blue-600 dark:bg-zinc-900/50 dark:text-blue-400 mx-auto mb-4 border dark:border-zinc-700">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Reportes en Tiempo Real</h3>
                        <p class="mt-2 text-base text-zinc-500 dark:text-zinc-400">Toma decisiones informadas con análisis detallados de movimientos, compras y ventas.</p>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-blue-100 text-blue-600 dark:bg-zinc-900/50 dark:text-blue-400 mx-auto mb-4 border dark:border-zinc-700">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Máxima Seguridad</h3>
                        <p class="mt-2 text-base text-zinc-500 dark:text-zinc-400">Datos encriptados y aislamiento total de la información de tu empresa. Nadie más tiene acceso a tus números.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer id="contacto" class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-base text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} InventarioSaaS. Todos los derechos reservados.
                </p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    ¿Interesado en implementar nuestra solución en tu empresa? Envíanos un correo a <a href="mailto:ventas@tudominio.com" class="text-blue-600 hover:underline dark:text-blue-400">ventas@tudominio.com</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>