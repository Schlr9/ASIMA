// File: app.js (Versi Final + Hide/Show Chat Input Saat Tes)

// --- VARIABEL GLOBAL ---
let currentUser = null;
let conversations = [];
let activeConversationId = null;
let testQuestions = [];
let categories = [];
let currentCategoryIndex = 0;
let userAnswers = {};

// Konfigurasi library 'marked' untuk format Markdown
marked.setOptions({
    highlight: function(code, lang) {
        const language = hljs.getLanguage(lang) ? lang : 'plaintext';
        return hljs.highlight(code, { language }).value;
    }
});

// --- FUNGSI UTAMA APLIKASI ---

async function initializeChat() {
    await loadConversations();
}


// --- HANDLE USER INPUT ---
function handleUserInput() {
    const userInputEl = document.getElementById('user-input');
    const messageText = userInputEl.value.trim();
    if (messageText === '' || !activeConversationId) return;

    const conversation = conversations.find(c => c.id === activeConversationId);
    if (!conversation) return;

    const isNewChat = conversation.messages.length <= 1;

    addMessage({ text: messageText, sender: 'user' });
    userInputEl.value = '';

    if (isNewChat) {
        // gunakan pertanyaan user sebagai judul percakapan
        conversation.title = sanitizeConversationTitle(messageText);
        if (typeof saveActiveConversation === 'function') saveActiveConversation();
        renderChatList(); // pastikan daftar chat langsung diperbarui
        // teruskan ke flow normal (ambil respon umum)
        getGeneralBotResponse(messageText);
    } else {
        getGeneralBotResponse(messageText);
    }
}

// --- MEMULAI CHAT BARU ---
async function startNewChat() {
    const welcomeMessage = `Halo ${currentUser.nama_lengkap}! Saya Asisten Mahasiswa virtual. Apa yang bisa saya bantu hari ini?`;
    const newConversation = {
        id: 'chat_' + Date.now(),
        title: 'Percakapan Baru',
        messages: [{ text: welcomeMessage, sender: 'bot' }]
    };

    conversations.unshift(newConversation);
    await saveActiveConversation(); 
    switchConversation(newConversation.id); 
}

// --- GANTI CONVERSATION ---
function switchConversation(id) {
    activeConversationId = id;
    localStorage.setItem('activeConversationId', activeConversationId);
    renderChatList();
    displayActiveConversation();
}

// --- TAMBAH PESAN ---
function addMessage(messageObject) {
    if (!activeConversationId) return;
    const conversation = conversations.find(c => c.id === activeConversationId);
    if (!conversation) return;
    
    if (typeof messageObject !== 'object' || messageObject === null) {
        console.error("addMessage expects an object, but received:", messageObject);
        return;
    }

    conversation.messages.push(messageObject);
    renderMessage(messageObject);
    scrollToBottom();
    saveActiveConversation();
}

// --- HAPUS CONVERSATION ---
async function deleteConversation(id) {
    if (!id || !confirm("Apakah Anda yakin ingin menghapus percakapan ini?")) return;
    try {
        await fetch('config/api.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'delete_conversation', conversationId: id })
        });
        conversations = conversations.filter(c => c.id !== id);
        if (id === activeConversationId) {
            if (conversations.length > 0) {
                switchConversation(conversations[0].id);
            } else {
                await startNewChat();
            }
        }
        renderChatList();
    } catch (error) {
        console.error("Gagal menghapus percakapan:", error);
    }
}

// --- INTERAKSI DENGAN BACKEND ---
async function getGeneralBotResponse(text) {
    showTypingIndicator();
    clearSuggestionArea();
    try {
        const conversation = conversations.find(c => c.id === activeConversationId);
        const response = await fetch('config/api.php', {
            method: 'POST',
            body: JSON.stringify({ 
                action: 'ask_general', 
                question: text,
                history: conversation.messages.filter(m => m.type !== 'test_result')
            })
        });
        const data = await response.json();
        hideTypingIndicator();
        if (data.reply) addMessage({ text: data.reply, sender: 'bot' });
    } catch (error) {
        hideTypingIndicator();
        addMessage({ text: "Maaf, terjadi kesalahan teknis.", sender: 'bot' });
    } finally {
        showMainMenu();
    }
}

/**
 * Hapus awalan "Tentu" dan semua tanda bintang dari judul,
 * trim, collapse whitespace, dan batasi panjang.
 */
function sanitizeConversationTitle(rawTitle) {
    if (!rawTitle) return 'Percakapan Baru';
    let t = String(rawTitle);
    // hapus awalan "Tentu", "Tentu," "Tentu:" (case-insensitive)
    t = t.replace(/^\s*tentu[\s,:\-]*/i, '');
    // hapus semua asterisk markdown
    t = t.replace(/\*+/g, '');
    // collapse whitespace dan trim
    t = t.replace(/\s+/g, ' ').trim();
    if (t.length === 0) return 'Percakapan Baru';
    // batasi panjang judul
    return t.length > 60 ? t.slice(0, 60).trim() + '...' : t;
}

async function getInitialBotResponseWithTitle(text) {
    showTypingIndicator();
    clearSuggestionArea();
    const conversation = conversations.find(c => c.id === activeConversationId);
    try {
        const response = await fetch('config/api.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'ask_initial', question: text })
        });
        const data = await response.json();
        hideTypingIndicator();

        // contoh: ambil kandidat judul dari data.title atau dari reply pertama
        const rawTitle = data.title || (data.reply ? data.reply.split('\n')[0] : null) || text;
        const title = sanitizeConversationTitle(rawTitle);

        // buat percakapan / set title menggunakan title yang sudah disanitasi
        if (conversation) {
            conversation.title = title;
        }

        if (data.reply) {
            addMessage({ text: data.reply, sender: 'bot' });
        }
    } catch (error) {
        hideTypingIndicator();
        addMessage({ text: "Maaf, terjadi kesalahan teknis.", sender: 'bot' });
    } finally {
        showMainMenu();
    }
}

// --- ALUR TES BAKAT ---
async function startTest() {
    addMessage({ text: "Baik, mari kita mulai Tes Bakat Karir...", sender: 'user' });
    clearSuggestionArea();
    showTypingIndicator();
    try {
        const response = await fetch('config/api.php?action=get_test_questions');
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        
        testQuestions = data;
        hideTypingIndicator();

        if (testQuestions && testQuestions.length > 0) {
            userAnswers = {};
            categories = [...new Set(testQuestions.map(q => q.kategori))];
            currentCategoryIndex = 0;
            displayCurrentQuestion();
        } else {
            addMessage({ text: "Gagal memuat soal tes.", sender: 'bot' });
            showMainMenu();
        }
    } catch (error) {
        hideTypingIndicator();
        addMessage({ text: `Gagal mengambil soal: ${error.message}`, sender: 'bot' });
    }
}

function handleAnswer() {
    const selectedOptions = document.querySelectorAll('input[name="test_question"]:checked');
    selectedOptions.forEach(checkbox => { userAnswers[checkbox.value] = 1; });
    currentCategoryIndex++;
    document.querySelectorAll('.test-question-bubble').forEach(b => b.remove());
    if (currentCategoryIndex < categories.length) {
        displayCurrentQuestion();
    } else {
        submitTest();
    }
}

async function submitTest() {
    addMessage({ text: "Terima kasih! Menganalisis jawaban Anda...", sender: 'bot' });
    clearSuggestionArea();
    showTypingIndicator();
    hideProgressBar();
    try {
        const response = await fetch('config/api.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'submit_test', answers: userAnswers })
        });
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        
        hideTypingIndicator();

        const combinedRecommendation = (data.recommendation_system || '') + (data.recommendation_ai || '');
        const finalResult = { scores: data.scores, recommendation: combinedRecommendation };

        // ✅ FIX UTAMA: Buat objek pesan dengan tipe 'test_result'
        const messageObject = {
            type: 'test_result',
            content: finalResult,
            sender: 'bot' // Tambahkan sender agar konsisten
        };
        addMessage(messageObject);

    } catch (error) {
        hideTypingIndicator();
        addMessage({ text: `Gagal mengirim jawaban: ${error.message}`, sender: 'bot' });
    } finally {
        showChatInput();
        showMainMenu();
        scrollToBottom();
    }
}


// --- DOM CONTENT LOADED ---
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const chatContainer = document.getElementById('chat-container');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const npm = document.getElementById('npm').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'Memproses...';
            errorMessage.classList.remove('hidden', 'text-red-500');
            
            fetch('config/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ npm, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) window.location.reload();
                else {
                    errorMessage.textContent = data.message;
                    errorMessage.classList.add('text-red-500');
                }
            })
            .catch(() => {
                errorMessage.textContent = 'Gagal terhubung ke server login.';
                errorMessage.classList.add('text-red-500');
            });
        });
    }

    if (chatContainer) {
        currentUser = { nama_lengkap: chatContainer.dataset.userNama };
        initializeChat();

        document.getElementById('send-button').addEventListener('click', handleUserInput);
        document.getElementById('user-input').addEventListener('keydown', (e) => { 
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleUserInput(); }
        });
        document.getElementById('new-chat-btn').addEventListener('click', startNewChat);

        const deleteChatBtn = document.getElementById('delete-chat-btn');
        if (deleteChatBtn) deleteChatBtn.addEventListener('click', () => deleteConversation(activeConversationId));

        const openChatListBtn = document.getElementById('open-chat-list-btn');
        const closeChatListBtn = document.getElementById('close-chat-list-btn');
        const chatListModal = document.getElementById('chat-list-modal');
        if (openChatListBtn) openChatListBtn.addEventListener('click', () => chatListModal.classList.remove('hidden'));
        if (closeChatListBtn) closeChatListBtn.addEventListener('click', () => chatListModal.classList.add('hidden'));
        if (chatListModal) chatListModal.addEventListener('click', (e) => { 
            if (e.target === chatListModal) chatListModal.classList.add('hidden'); 
        });

        const userMenuButton = document.getElementById('user-menu-button');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        if (userMenuButton) {
            userMenuButton.addEventListener('click', (event) => {
                event.stopPropagation();
                userMenuDropdown.classList.toggle('hidden');
            });
            window.addEventListener('click', function(e) {
                if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden') && !userMenuButton.contains(e.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }
    }
});
