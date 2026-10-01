<x-guest-layout>
    <div class="min-h-screen w-full flex flex-col lg:flex-row m-0 p-0 overflow-hidden">
        
        <!-- PANEL IZQUIERDO: Branding en tonos verdes -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-800 via-emerald-900 to-green-950 p-12 flex-col justify-between relative text-white">
            
            <!-- PARTE SUPERIOR: Logotipo con mayor tamaño -->
            <div class="relative z-10 flex items-center">
                <img src="{{ asset('img/logo2.png') }}" alt="Plásticos Fénix Logo" class="h-28 w-auto object-contain drop-shadow-md">
            </div>

            <!-- CENTRO: Bienvenida corporativa -->
            <div class="relative z-10 my-auto max-w-lg">
                <h1 class="text-4xl font-extrabold tracking-tight mb-4 text-white">
                    ¡Bienvenido al sistema de Control de Calidad! 👋
                </h1>
                <p class="text-emerald-200 text-base leading-relaxed">
                    Controla las inspecciones de calidad, gramajes y lotes de planta con máxima precisión. Automatiza tus procesos y mantén el estándar industrial al día.
                </p>
            </div>

            <!-- FOOTER -->
            <div class="relative z-10 text-xs text-emerald-400/80">
                © {{ date('Y') }} Plásticos Fénix - Sistema de Calidad. Todos los derechos reservados.
            </div>
        </div>

        <!-- PANEL DERECHO: Formulario de Acceso -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 bg-white min-h-screen lg:min-h-0">
            <div class="w-full max-w-md space-y-6">
                
                <div>
                    <h3 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">
                        Bienvenido de nuevo
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ingresa tus credenciales para acceder al sistema de calidad.
                    </p>
                </div>

                <!-- Estado de Sesión -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Formulario -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Usuario -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Nombre de Usuario</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username"
                            class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                        <x-input-error :messages="$errors->get('username')" class="mt-2 text-red-600 text-xs" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-xs" />
                    </div>

                    <!-- Recuérdame -->
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        <label for="remember_me" class="ml-2 text-sm text-gray-600">Recordar este equipo</label>
                    </div>

                    <!-- Botón de Ingreso -->
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition-colors">
                            Ingresar al Sistema
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-guest-layout>