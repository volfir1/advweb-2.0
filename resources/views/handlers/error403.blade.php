<<<<<<< HEAD
@php
    $previousUrl = url()->previous();
@endphp

=======
>>>>>>> origin/master
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Error 403 - Access Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/error403.css') }}">
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="text-content">
                <header>
                    <h1>BakeToGo</h1>
                    <h2>Pastries</h2>
                </header>
                <main>
                    <h3>Oh No!</h3>
                    <p>It seems like you do not have access to this page.</p>
                </main>
                <footer>
                    <button id="returnButton" data-previous-url="{{ $previousUrl }}">RETURN</button>
                </footer>
            </div>
            <div class="illustration">
                <svg width="200" height="200" viewBox="0 0 200 200" aria-hidden="true" role="img">
                    <circle cx="100" cy="100" r="80" fill="#FFD700" class="pastry"/>
                    <rect x="85" y="40" width="30" height="60" fill="#F9584A" class="candle"/>
                    <circle cx="100" cy="40" r="15" fill="#F9584A" class="flame"/>
                    <text x="70" y="140" font-size="24" fill="#F9584A" class="lock-text">LOCKED</text>
                </svg>
            </div>
        </div>
    </div>

    <!-- Popup Container -->
    <div id="popupContainer" class="popup-container">
        <div class="popup-content">
            <p id="popupMessage"></p>
        </div>
    </div>

    <script src="{{ asset('js/error403.js') }}"></script>
=======
    <title>403 Error</title>
    <link rel="stylesheet" href="../../css/error403.css"> 
</head>
<body>
    <div class="error-403-container">
        <img src="{{ asset('images/403.svg') }}" alt="403 Image">
        <button class="return-button">Return to Admin</button>
    </div>

    {{-- Optional: Load scripts if needed --}}
    {{-- <script src="{{ asset('js/custom.js') }}"></script> --}}
>>>>>>> origin/master
</body>
</html>
