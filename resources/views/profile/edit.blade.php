<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil de cuenta') }}
        </h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-3 px-4 sm:px-6 lg:px-8">
            <x-profile-card>
                <div class="mx-auto max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-profile-card>

            <x-profile-card>
                <div class="mx-auto max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-profile-card>

            <x-profile-card>
                <div class="mx-auto max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-profile-card>
        </div>
    </div>
</x-app-layout>
