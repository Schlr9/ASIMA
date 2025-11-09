// js/ui.js
// VERSI FINAL - STABIL UNTUK TES BAKAT

// --- FUNGSI PENYIMPANAN & PEMUATAN ---

async function saveActiveConversation() {
    if (!activeConversationId) return;
    const conversation = conversations.find(c => c.id === activeConversationId);
    if (!conversation) return;

    try {
        await fetch('config/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_conversation',
                conversation: conversation
            })
        });
    } catch (error) {
        console.error("Failed to save conversation:", error);
    }
}

async function loadConversations() {
    try {
        const response = await fetch('config/api.php?action=load_conversations');
        const data = await response.json();
        if (data.error) throw new Error(data.error);

        conversations = data;
        const lastActiveId = localStorage.getItem('activeConversationId');
        const lastConversationExists = conversations.some(c => c.id === lastActiveId);

        if (lastActiveId && lastConversationExists) {
            activeConversationId = lastActiveId;
        } else if (conversations.length > 0) {
            activeConversationId = conversations[0].id;
        } else {
            await startNewChat();
            return;
        }
        localStorage.setItem('activeConversationId', activeConversationId);
    } catch (error) {
        console.error("Gagal memuat percakapan:", error);
        await startNewChat();
        return;
    }

    renderChatList();
    displayActiveConversation();
}


// --- FUNGSI TAMPILAN LIST CHAT ---

function renderChatList() {
    const container = document.getElementById('chat-list-items');
    if (!container) return;
    container.innerHTML = '';

    conversations.forEach(conv => {
        const itemWrapper = document.createElement('div');
        itemWrapper.className = 'flex items-center justify-between group';

        const link = document.createElement('a');
        link.href = '#';
        link.className = `block flex-grow p-3 my-1 rounded-lg text-white truncate ${conv.id === activeConversationId ? 'bg-blue-600' : 'hover:bg-gray-700'}`;
        link.textContent = conv.title || 'Percakapan Baru';
        link.onclick = (e) => {
            e.preventDefault();
            switchConversation(conv.id);
            document.getElementById('chat-list-modal').classList.add('hidden');
        };

        const deleteButton = document.createElement('button');
        deleteButton.className = 'p-2 rounded-lg text-gray-400 hover:bg-red-800 hover:text-white ml-2 transition-opacity opacity-100 md:opacity-0 group-hover:md:opacity-100';
        deleteButton.title = 'Hapus Percakapan Ini';
        deleteButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`;
        deleteButton.onclick = (e) => {
            e.stopPropagation();
            deleteConversation(conv.id);
        };

        itemWrapper.appendChild(link);
        itemWrapper.appendChild(deleteButton);
        container.appendChild(itemWrapper);
    });
}

function displayActiveConversation() {
    const chatWindow = document.getElementById('chat-window');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    chatWindow.innerHTML = '';

    if (activeConversationId) {
        const conversation = conversations.find(c => c.id === activeConversationId);
        if (conversation) {
            conversation.messages.forEach(msg => renderMessage(msg));
            userInput.disabled = false;
            sendButton.disabled = false;
            showMainMenu();

            setTimeout(() => {
                scrollToBottom();
            }, 100);
        }
    } else {
        userInput.disabled = false;
        sendButton.disabled = false;
        clearSuggestionArea();
    }
}


// --- FUNGSI CHAT MESSAGE ---

function renderMessage(message) {
    const chatWindow = document.getElementById('chat-window');
    if (!chatWindow) return;

    if (message.type === 'test_result') {
        displayFullResults(message.content);
    } else {
        const msgContainer = document.createElement('div');
        msgContainer.className = `flex mb-4 ${message.sender === 'user' ? 'justify-end' : 'justify-start'}`;

        const msgBubble = document.createElement('div');
        if (message.sender === 'user') {
            msgBubble.className = 'max-w-lg rounded-2xl p-3 shadow-sm bg-blue-600 text-white';
            msgBubble.innerHTML = (message.text || '').replace(/\n/g, '<br>');
        } else {
            msgBubble.className = 'max-w-lg rounded-2xl p-3 shadow-sm bg-gray-800 text-white prose dark:prose-invert';
            msgBubble.innerHTML = marked.parse(message.text || '');
        }

        msgContainer.appendChild(msgBubble);
        chatWindow.appendChild(msgContainer);
    }
}


// --- FUNGSI UI BANTUAN ---

function showChatInput() {
    const footer = document.querySelector('#chat-container footer');
    if (footer) {
        footer.classList.remove('opacity-0', 'pointer-events-none');
        footer.classList.add('flex', 'items-center', 'gap-2');
        footer.style.display = '';
        footer.style.opacity = '1';
        footer.style.transition = 'opacity 0.2s ease-in-out';
    }

    const userInput = document.getElementById('user-input');
    if (userInput) {
        userInput.style.height = '44px';
        userInput.style.minHeight = '44px';
        userInput.style.fontSize = '14px';
    }
}

function showMainMenu() {
    const buttons = [
        { text: '🧭 Tes Bakat Karir', action: startTest, style: 'w-flex'}
    ];
    updateSuggestionArea(buttons);
}

function updateSuggestionArea(buttons) {
    const suggestionArea = document.getElementById('suggestion-area');
    if (!suggestionArea) return;
    suggestionArea.innerHTML = '';
    buttons.forEach(btnInfo => {
        const button = document.createElement('button');
        const primaryStyle = 'bg-blue-600 hover:bg-blue-700 text-white';
        const secondaryStyle = 'w-full bg-gray-800 hover:bg-blue-800 text-white';
        button.className = `font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors text-sm ${btnInfo.style === 'primary' ? primaryStyle : secondaryStyle}`;
        button.textContent = btnInfo.text;
        button.onclick = btnInfo.action;
        suggestionArea.appendChild(button);
    });
}

function clearSuggestionArea() {
    const suggestionArea = document.getElementById('suggestion-area');
    if (suggestionArea) suggestionArea.innerHTML = '';
}

function scrollToBottom() {
    const chatWindow = document.getElementById('chat-window');
    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
}


// --- FUNGSI TES BAKAT ---

function displayCurrentQuestion() {
    // Jangan sembunyikan footer total agar ukuran tidak berubah
    const footer = document.querySelector('#chat-container footer');
    if (footer) {
        footer.classList.add('opacity-0', 'pointer-events-none');
        footer.style.display = ''; // tetap ikut layout asli
    }

    clearSuggestionArea();
    updateProgressBar();

    const currentCategoryName = categories[currentCategoryIndex];
    const questionsForCategory = testQuestions.filter(q => q.kategori === currentCategoryName);

    const questionContainer = document.createElement('div');
    questionContainer.className = 'flex mb-4 justify-start test-question-bubble';

    const questionBubble = document.createElement('div');
    questionBubble.className = 'max-w-lg w-full rounded-2xl p-4 md:p-6 shadow-sm bg-gray-800 text-white';

    let inputHtml = `<h3 class="text-lg font-bold mb-1 capitalize">${currentCategoryName.replace('_', '-')}</h3>`;
    inputHtml += `<p class="text-sm text-gray-400 mb-4">Pilih semua pernyataan yang sesuai dengan diri Anda.</p>`;
    inputHtml += '<div class="space-y-3 mt-3">';
    questionsForCategory.forEach(q => {
        inputHtml += `
            <div class="flex items-start">
                <input type="checkbox" id="q_${q.id}" name="test_question" value="${q.id}" class="mt-1 mr-3 h-4 w-4 text-blue-600 border-gray-500 rounded focus:ring-blue-500">
                <label for="q_${q.id}" class="text-gray-300">${q.pernyataan}</label>
            </div>`;
    });
    inputHtml += '</div>';

    questionBubble.innerHTML = inputHtml;
    questionContainer.appendChild(questionBubble);
    document.getElementById('chat-window').appendChild(questionContainer);

    updateSuggestionArea([{ text: 'Lanjut →', action: handleAnswer, style: 'seconddary flex' }]);
    scrollToBottom();
}


function displayFullResults(data) {
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');
    if (userInput) userInput.disabled = false;
    if (sendButton) sendButton.disabled = false;

    const chatWindow = document.getElementById('chat-window');
    const resultContainer = document.createElement('div');
    resultContainer.className = 'flex mb-4 justify-start';

    const resultBubble = document.createElement('div');
    resultBubble.className = 'max-w-lg w-full rounded-2xl p-4 shadow-sm bg-gray-800 text-white';

    if (data.scores && Object.keys(data.scores).length > 0) {
        const chartContainer = document.createElement('div');
        chartContainer.className = 'mb-4 relative w-full h-64 md:h-72';

        const title = document.createElement('h3');
        title.className = 'text-lg font-bold mb-2 text-center';
        title.innerHTML = '📊 Grafik Profil Bakat';

        const canvas = document.createElement('canvas');
        chartContainer.appendChild(title);
        chartContainer.appendChild(canvas);
        resultBubble.appendChild(chartContainer);

        requestAnimationFrame(() => {
            if (typeof renderRadarChart === 'function') {
                renderRadarChart(canvas, data.scores);
            } else {
                console.error('renderRadarChart tidak ditemukan!');
            }
        });
    }

    if (data.recommendation && data.recommendation.trim() !== '') {
        const textElement = document.createElement('div');
        textElement.className = 'mt-4 text-sm';
        const cleanRecommendation = data.recommendation.replace(/^```html\s*\n/, '').replace(/\n```$/, '');
        textElement.innerHTML = cleanRecommendation;
        resultBubble.appendChild(textElement);
    }

    resultContainer.appendChild(resultBubble);
    chatWindow.appendChild(resultContainer);
    chatWindow.scrollTop = chatWindow.scrollHeight;
}


function updateProgressBar() {
    const progressContainer = document.getElementById('test-progress-container');
    const progressBar = document.getElementById('test-progress-bar');
    if (!progressContainer || !progressBar) return;
    const totalQuestions = testQuestions.length;
    const progress = totalQuestions > 0 ? ((currentQuestionIndex) / totalQuestions) * 100 : 0;
    progressContainer.classList.remove('hidden');
    progressBar.style.width = progress + '%';
}

function hideProgressBar() {
    const progressContainer = document.getElementById('test-progress-container');
    if (progressContainer) progressContainer.classList.add('hidden');
}

function showTypingIndicator() {
    const chatWindow = document.getElementById('chat-window');
    if (!chatWindow || document.getElementById('typing-indicator')) return;
    const typingContainer = document.createElement('div');
    typingContainer.id = 'typing-indicator';
    typingContainer.className = 'flex mb-4 justify-start';
    typingContainer.innerHTML = `<div class="bg-gray-800 rounded-2xl p-3 flex items-center"><div class="typing-indicator"><span></span><span></span><span></span></div></div>`;
    chatWindow.appendChild(typingContainer);
    scrollToBottom();
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
}

window.chartInstance = null;
