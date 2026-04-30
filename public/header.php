<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-soft-neutral { background-color: #F8F9FA; }
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 22px;
            height: 22px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 6px;
            border: 3px solid #f1f1f1;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #1f2937;
        }

        /* Override scrollbar-thin if it exists */
        .scrollbar-thin::-webkit-scrollbar {
            width: 22px !important;
            height: 22px !important;
        }

        /* Dark mode scrollbar */
        .dark ::-webkit-scrollbar-track {
            background: #111827;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #9ca3af;
            border: 3px solid #111827;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #f3f4f6;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #9ca3af !important;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1A1A1A',
                        secondary: '#4A4A4A',
                        accent: '#E5E7EB',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-soft-neutral dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
