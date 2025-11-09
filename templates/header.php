<?php
// templates/header.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asisten Mahasiswa</title>
    
    <link href="./dist/output.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

    <style>
        html, body { font-family: 'Inter', sans-serif; height: 100%; scroll-behavior: smooth; }
        #chat-window::-webkit-scrollbar { width: 0; background: transparent; }
        .typing-indicator span {
            height: 8px; width: 8px; float: left; margin: 0 1px;
            background-color: #9E9E9E; display: block; border-radius: 50%;
            opacity: 0.4; animation: typing 1s infinite;
        }
        .typing-indicator span:nth-of-type(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-of-type(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0% { opacity: 0.4; transform: translateY(0); }
            25% { opacity: 1; transform: translateY(-3px); }
            50% { opacity: 0.4; transform: translateY(0); }
            100% { opacity: 0.4; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-white flex items-center justify-center transition-colors duration-300">

<!-- ...hapus seluruh elemen switch mode malam/siang... -->

