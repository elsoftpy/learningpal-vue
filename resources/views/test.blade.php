<!DOCTYPE html>
<html>
<head>
    <title>Tailwind Test</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="bg-red-500 text-white p-8 text-center">
        <h1 class="text-4xl font-bold">Tailwind Test</h1>
        <p class="text-xl mt-4">If this is red with white text, Tailwind is working!</p>
        
        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="bg-blue-500 p-4 rounded-sm">Blue</div>
            <div class="bg-green-500 p-4 rounded-sm">Green</div>
            <div class="bg-yellow-500 p-4 rounded-sm">Yellow</div>
        </div>
        
        <div class="mt-8 flex space-x-4 justify-center">
            <button class="bg-purple-600 text-white px-6 py-2 rounded-lg shadow-sm">Button 1</button>
            <button class="bg-pink-600 text-white px-6 py-2 rounded-lg shadow-sm">Button 2</button>
        </div>
    </div>
</body>
</html>