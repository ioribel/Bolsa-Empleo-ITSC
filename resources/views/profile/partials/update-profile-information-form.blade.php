<section>
    <header>
        <h2 class="text-[15px] font-medium text-itsc-slate-900">
            {{ __('Información de la cuenta') }}
        </h2>

        <p class="mt-1 text-sm text-itsc-slate-500">
            {{ __('Actualiza tu nombre y dirección de correo electrónico.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
            <p class="mt-1 text-sm text-itsc-slate-500">
                Si cambias el correo, te pediremos la contraseña actual y enviaremos un enlace de confirmación.
            </p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu dirección de correo electrónico no está verificada.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Reenviar el correo de verificación') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p role="status" aria-live="polite" class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Se envió un nuevo enlace de verificación a tu correo electrónico.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="current_password" value="Contraseña actual (requerida para cambiar el correo)" />
            <x-password-input id="current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        @if ($user->pending_email)
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p>
                    Está pendiente confirmar <strong>{{ $user->pending_email }}</strong>.
                    Tu correo actual seguirá activo hasta que confirmes el enlace.
                </p>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button class="itsc-primary min-h-10 rounded-lg px-3.5 py-2 text-[13px]">{{ __('Guardar cambios') }}</x-primary-button>

            @if (session('status') === 'email-change-requested')
                <p role="status" aria-live="polite" class="text-sm text-green-700">
                    Enviamos un enlace al nuevo correo. Tu correo actual no cambiará hasta que lo confirmes.
                </p>
            @elseif (session('status') === 'email-updated')
                <p role="status" aria-live="polite" class="text-sm text-green-700">
                    El correo fue confirmado y actualizado correctamente.
                </p>
            @elseif (session('status') === 'profile-updated')
                <p
                    role="status"
                    aria-live="polite"
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Cambios guardados') }}</p>
            @endif
        </div>
    </form>

    @if ($user->pending_email)
        <form method="post" action="{{ route('profile.email-change.resend') }}" class="mt-4 space-y-3">
            @csrf
            <div>
                <x-input-label for="resend_current_password" value="Contraseña actual para reenviar" />
                <x-password-input id="resend_current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error class="mt-2" :messages="$errors->getBag('emailChangeResend')->get('current_password')" />
            </div>
            <x-primary-button>Reenviar enlace de confirmación</x-primary-button>
        </form>
    @endif
</section>
