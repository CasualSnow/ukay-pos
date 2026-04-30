<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#111827', // Charcoal/Dark Gray
                        secondary: '#4B5563', // Gray-600
                        background: '#F9FAFB', // Off-white
                        accent: '#52796F', // Muted Green
                        'accent-hover': '#354F52',
                        border: '#E5E7EB',
                        surface: '#FFFFFF'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F9FAFB; color: #111827; }
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar Styles - Minimalist Aesthetic */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }

        .card-shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        
        .transition-soft {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-background dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
