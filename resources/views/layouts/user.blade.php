<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Mini Store</title>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/layouts/user.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    @if (!isset($hideHeaderFooter))
        <x-header />
    @endif

    @yield('content')

    @if (!isset($hideHeaderFooter))
        <x-footer />
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/js/notifications/toast-handler.js', 'resources/js/notifications/modal-handler.js', 'resources/js/components/modal-custom.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast('success', '{{ session('success') }}');
            @endif

            @if (session('error'))
                showToast('error', '{{ session('error') }}');
            @endif

            @if (session('warning'))
                showToast('warning', '{{ session('warning') }}');
            @endif
        });

        // AJAX toast
        function handleAjaxResponse(data) {
            if (data.success) {
                showToast("success", data.message);
            } else {
                showToast("error", data.message);
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
