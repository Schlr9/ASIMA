<?php
// templates/chat_view.php
$user = $_SESSION['user'];
?>
<div id="chat-container" class="flex flex-col h-full w-full md:max-w-2xl md:h-[90%] md:rounded-2xl bg-gray-900 shadow-xl"
    data-user-nama="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
    
  <header class="bg-blue-900 text-white p-4 flex items-center justify-between shadow-md rounded-t-none md:rounded-t-2xl">
        <button id="open-chat-list-btn" title="Lihat Percakapan" class="p-2 rounded-full hover:bg-blue-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </button>
        
        <div class="relative">
            <button id="user-menu-button" class="flex items-center gap-2 p-2 rounded-lg hover:bg-blue-800 transition-colors focus:outline-none">
                <span class="font-semibold"><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
                <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div id="user-menu-dropdown" class="z-50 hidden absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg  border dark:border-gray-600">
                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="user-menu-button">
                    <li>
                        <a href="templates/profil.php" class="flex items-center gap-3 px-4 py-2 hover:text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Profil Saya</span>
                        </a>
                    </li>
                    <li>
                        <a href="./config/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');" title="Logout" class="flex items-center gap-3 px-4 py-2 hover:text-red-600">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main id="chat-window" class="flex-grow p-4 overflow-y-auto bg-gray-900">
        </main>

    <div id="suggestion-area" class="p-2 flex flex-wrap gap-2 border-t border-gray-700 bg-gray-900">
        </div>

    <!-- wrapper untuk dikontrol oleh JS (show/hide) -->
    <div id="chat-input-container" class="p-2 border-t border-gray-700 bg-gray-900">
        <footer class="w-full">
            <div class="flex items-center gap-2 w-full">
                <input type="text" id="user-input" placeholder="Ketik pesan..." class="bg-gray-800 flex-1 min-w-0 border rounded-full py-2 px-4 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                <button id="send-button" class="bg-blue-600 text-white rounded-full w-10 h-10 ml-2 flex items-center justify-center hover:bg-blue-800 flex-shrink-0" disabled>
                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                 </button>
             </div>
         </footer>
    </div>
    <div>
        <footer class="w-full p-2 text-center text-xs text-gray-500">
            &copy; 2025 ASIMA. All rights reserved.
    </div>
</div>



<div id="chat-list-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex justify-center items-center">
    <div id="chat-list-container" class="w-full max-w-md bg-gray-800 flex flex-col h-full max-h-[80%] rounded-2xl shadow-xl border border-gray-700">
        <header class="p-4 bg-blue-900 border-b border-blue-800 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-white">Percakapan</h2>
            <div class="flex items-center gap-2">
                <button id="new-chat-btn" title="Mulai Chat Baru" class="p-2 rounded-lg hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 5v14m-7-7h14"/></svg>
                </button>
                <button id="close-chat-list-btn" title="Tutup" class="p-2 rounded-lg hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </header>

        <nav id="chat-list-items" class="flex-grow overflow-y-auto p-2">
            </nav>

        </div>
</div>

