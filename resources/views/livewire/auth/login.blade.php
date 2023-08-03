<!DOCTYPE html>
<HTML class="h-full bg-gray-50" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head/>
</head>
<body class="h-full">
<div>
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <x-logo fill="#000000" class="h-24"/>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="mt-6">
                    @section('login-form')
                        <form action="{{ route('login') }}" method="POST" class="space-y-6">
                            @csrf
                            <x-input id="email" value="{{ request()->get('email') }}" :label="__('Email')" name="email" type="email" required autofocus/>
                            <x-inputs.password  value="{{ request()->get('password') }}" :label="__('Password')" id="password" name="password" required/>
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <a href="#"
                                       class="font-medium text-indigo-600 hover:text-indigo-500"> {{ __('Reset password') }}</a>
                                </div>
                            </div>
                            <x-button primary class="w-full" :label="__('Login')" type="submit"></x-button>
                        </form>
                    @show
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</HTML>
