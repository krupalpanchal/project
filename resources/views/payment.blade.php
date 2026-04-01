<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Checkout Integration</title>
    @vite('resources/css/app.css') <!-- Laravel 11 uses Vite for assets -->
</head>

<body class="bg-gray-100">

    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Stripe Payment</h2>

            @if (session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('payment.checkout') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Enter Amount (USD)</label>
                    <input type="number" name="amount" step="0.01" min="1"
                        class="w-full border p-2 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Name</label>
                    <input type="text" name="name" class="w-full border p-2 rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Address</label>
                    <input type="text" name="address" class="w-full border p-2 rounded-md" required>
                </div>
                <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white
                font-semibold rounded-md hover:bg-blue-700 transition">
                    Checkout with Stripe
                </button>
            </form>
        </div>
    </div>

</body>

</html>
