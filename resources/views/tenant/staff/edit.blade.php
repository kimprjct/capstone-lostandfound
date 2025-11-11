@extends('layouts.tenantApp')

@section('title', 'Edit Staff Member')

@section('page-title', 'Edit Staff Member')

@section('content')
<div class="mb-6">
    <a href="{{ route('tenant.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-arrow-left mr-2"></i> {{ __('Back to Staff List') }}
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.staff.update', $user ? $user->id : 0) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('First Name') }}</label>
                <input id="first_name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="first_name" value="{{ old('first_name', $user ? $user->first_name : '') }}" required autofocus />
            </div>

            <!-- Middle Name -->
            <div>
                <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Middle Name') }}</label>
                <input id="middle_name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="middle_name" value="{{ old('middle_name', $user ? $user->middle_name : '') }}" />
            </div>

            <!-- Last Name -->
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Last Name') }}</label>
                <input id="last_name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="last_name" value="{{ old('last_name', $user ? $user->last_name : '') }}" required />
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                <input id="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="email" name="email" value="{{ old('email', $user ? $user->email : '') }}" required />
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone Number') }}</label>
                <input id="phone_number" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="phone_number" value="{{ old('phone_number', $user ? $user->phone_number : '') }}" required />
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                <input id="address" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="text" name="address" value="{{ old('address', $user ? $user->address : '') }}" required />
            </div>

            <!-- Current Password (only if changing password) -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current Password') }}</label>
                <input id="current_password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="password" name="current_password" autocomplete="current-password" />
                <p class="text-sm text-gray-500 mt-1">Enter your current password to confirm changes</p>
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Password') }}</label>
                <input id="password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="password" name="password" autocomplete="new-password" />
                <p class="text-sm text-gray-500 mt-1">Leave blank to keep current password</p>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm New Password') }}</label>
                <input id="password_confirmation" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" type="password" name="password_confirmation" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('tenant.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Cancel') }}
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-save mr-2"></i> {{ __('Update Account') }}
            </button>
        </div>
    </form>
</div>
@endsection
