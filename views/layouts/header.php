<?php
/**
 * Layout Header
 */
$base_url = $base_url ?? '';
?>
<!DOCTYPE html>
<html lang="en" <?php 
    $theme = $_SESSION['theme'] ?? 'light';
    if (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true') {
        $theme = 'dark';
    }
    if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark') {
        $theme = 'dark';
    }
    if ($theme === 'dark') echo 'class="dark"'; 
?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (typeof localStorage !== 'undefined' && localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#111827',
                            dark: '#F9FAFB'
                        },
                        secondary: {
                            DEFAULT: '#4B5563',
                            dark: '#D1D5DB'
                        },
                        background: {
                            DEFAULT: '#F9FAFB',
                            dark: '#111827'
                        },
                        surface: {
                            DEFAULT: '#FFFFFF',
                            dark: '#1F2937'
                        },
                        border: {
                            DEFAULT: '#E5E7EB',
                            dark: '#374151'
                        },
                        accent: {
                            DEFAULT: '#52796F',
                            dark: '#52796F'
                        },
                        'accent-hover': {
                            DEFAULT: '#354F52',
                            dark: '#6B8F7D'
                        }
                    },
                    transitionTimingFunction: {
                        'soft': 'cubic-bezier(0.4, 0, 0.2, 1)'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar Styles - Minimalist Aesthetic */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
        .dark ::-webkit-scrollbar-thumb { background: #4B5563; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #6B7280; }

        :root {
            --color-background: #F9FAFB;
            --color-surface: #FFFFFF;
            --color-primary: #111827;
            --color-secondary: #4B5563;
            --color-border: #E5E7EB;
            --color-accent: #52796F;
            --color-accent-hover: #354F52;
        }

        .dark {
            --color-background: #111827;
            --color-surface: #1F2937;
            --color-primary: #F9FAFB;
            --color-secondary: #D1D5DB;
            --color-border: #374151;
            --color-accent: #52796F;
            --color-accent-hover: #6B8F7D;
        }

        html, body {
            background-color: var(--color-background);
            color: var(--color-primary);
        }

        .bg-background { background-color: var(--color-background) !important; }
        .bg-surface { background-color: var(--color-surface) !important; }
        .text-primary { color: var(--color-primary) !important; }
        .text-secondary { color: var(--color-secondary) !important; }
        .border-border { border-color: var(--color-border) !important; }
        .bg-accent { background-color: var(--color-accent) !important; }
        .bg-accent-hover { background-color: var(--color-accent-hover) !important; }
        .text-accent { color: var(--color-accent) !important; }
        .dark .bg-white { background-color: var(--color-surface) !important; }
        .dark .text-black { color: var(--color-primary) !important; }

        .card-shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        
        .transition-soft {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-background dark:bg-surface text-primary dark:text-secondary transition-soft">
