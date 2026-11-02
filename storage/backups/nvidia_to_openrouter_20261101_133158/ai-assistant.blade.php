<!-- WebotApp Floating AI Copilot & Voice Assistant -->
<div id="aiAssistantContainer" class="fixed bottom-6 right-6 z-[9999] select-none">

    <!-- Floating Trigger Launcher Button (Small Round Icon) -->
    <div class="relative group">
        <!-- Ping Glow Animation -->
        <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5 z-10 pointer-events-none">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-slate-900"></span>
        </span>

        <!-- Small Round Button -->
        <button id="aiLauncherBtn" onclick="toggleAiChat()" title="AI Copilot & Voice Assistant"
            class="w-12 h-12 flex items-center justify-center bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700 hover:from-emerald-400 hover:to-teal-500 text-white rounded-full shadow-xl hover:shadow-emerald-600/40 transition-all duration-300 transform hover:scale-110 active:scale-95 focus:outline-none border border-emerald-400/50 cursor-pointer">
            <i id="launcherIcon" class="fa-solid fa-wand-magic-sparkles text-lg text-white transition-transform duration-300"></i>
        </button>

        <!-- Hover Tooltip -->
        <div class="absolute right-14 top-1/2 -translate-y-1/2 px-2.5 py-1 bg-slate-900/95 text-white text-[11px] font-bold rounded-lg shadow-xl border border-slate-800 whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity duration-200">
            AI Copilot
        </div>
    </div>

    <!-- Chat Modal Window -->
    <div id="aiChatModal" class="hidden absolute bottom-16 right-0 w-[92vw] sm:w-[420px] h-[590px] max-h-[82vh] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300 transform origin-bottom-right z-[10000]">
        
        <!-- Header -->
        <div class="px-4 py-3.5 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white flex items-center justify-between border-b border-slate-800/80">
            <div class="flex items-center gap-3">
                <div class="relative flex items-center justify-center w-9 h-9 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md">
                    <i class="fa-solid fa-robot text-white text-base"></i>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 border border-slate-900"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h4 class="text-xs font-bold text-white tracking-tight">WebotApp AI Assistant</h4>
                        <span class="text-[9px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.2 rounded-full">NVIDIA NIM</span>
                    </div>
                    <p id="aiVoiceStatusText" class="text-[10px] text-slate-400">Voice & Autonomous Accounting Engine</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <!-- Voice Mode Toggle (Audio Output) -->
                <button type="button" id="aiVoiceToggleBtn" onclick="toggleSpeechOutput()" title="Toggle Voice Responses"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 transition">
                    <i id="aiVoiceIcon" class="fa-solid fa-volume-high text-xs text-emerald-400"></i>
                </button>

                <!-- Clear Chat -->
                <button type="button" onclick="clearAiHistory()" title="Clear Chat History"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-arrow-rotate-right text-xs"></i>
                </button>

                <!-- Close Modal -->
                <button type="button" onclick="toggleAiChat()" title="Close Assistant"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Quick Suggestion Chips Carousel -->
        <div class="px-3 py-2 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-100 dark:border-slate-800 flex items-center gap-1.5 overflow-x-auto no-scrollbar text-[11px]">
            <button onclick="sendQuickPrompt('create a bill of rs 10000 for website designing services to mr rampal')" 
                class="whitespace-nowrap px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-600 transition shadow-2xs">
                Bill ₹10k to Mr. Rampal
            </button>
            <button onclick="sendQuickPrompt('check party pending balance')" 
                class="whitespace-nowrap px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-600 transition shadow-2xs">
                Check Pending Balances
            </button>
            <button onclick="sendQuickPrompt('what is our profit and loss')" 
                class="whitespace-nowrap px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-600 transition shadow-2xs">
                Profit & Loss
            </button>
            <button onclick="sendQuickPrompt('check bank balances')" 
                class="whitespace-nowrap px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-600 transition shadow-2xs">
                Bank Balances
            </button>
            <button onclick="sendQuickPrompt('check taxes')" 
                class="whitespace-nowrap px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:border-emerald-500 hover:text-emerald-600 transition shadow-2xs">
                Taxes
            </button>
        </div>

        <!-- Chat Messages Feed -->
        <div id="aiChatFeed" class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs">
            <!-- Initial Greeting -->
            <div class="flex items-start gap-2.5">
                <div class="flex-shrink-0 w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    <i class="fa-solid fa-sparkles text-[10px]"></i>
                </div>
                <div class="max-w-[85%] bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none p-3 shadow-xs space-y-2 leading-relaxed">
                    <p class="font-medium">
                        Hello! I am your <strong>WebotApp Accounting AI</strong>. I can create bills, issue customer invoices, add vendors, check pending balances, review banking, and provide direct links.
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        <em>Try typing or speaking:</em> "Create a bill of ₹10,000 for website designing services to Mr. Rampal" or click the microphone to talk!
                    </p>
                </div>
            </div>
        </div>

        <!-- Listening / Voice Indicator Banner (hidden by default) -->
        <div id="aiVoiceListeningBanner" class="hidden px-4 py-2 bg-emerald-50 dark:bg-emerald-950/50 border-t border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300 animate-pulse">
            <div class="flex items-center gap-2 font-semibold">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
                </span>
                <span>Listening to your voice... Speak now</span>
            </div>
            <button type="button" onclick="stopVoiceRecognition()" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 underline">Cancel</button>
        </div>

        <!-- Input Bar -->
        <div class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <form id="aiChatForm" onsubmit="handleAiFormSubmit(event)" class="flex items-center gap-2">
                
                <!-- Voice Microphone Button -->
                <button type="button" id="aiMicBtn" onclick="toggleVoiceRecognition()" title="Click to speak (Voice Recognition)"
                    class="w-9 h-9 rounded-2xl flex-shrink-0 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 text-slate-600 dark:text-slate-300 hover:text-emerald-600 border border-slate-200 dark:border-slate-700 transition shadow-2xs">
                    <i id="aiMicIcon" class="fa-solid fa-microphone text-sm"></i>
                </button>

                <!-- Text Input -->
                <div class="relative flex-1">
                    <input type="text" id="aiUserInput" autocomplete="off" placeholder="Ask AI or speak your request..."
                        class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition shadow-inner">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="aiSendBtn"
                    class="w-9 h-9 rounded-2xl flex-shrink-0 flex items-center justify-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-md shadow-emerald-600/20 transition duration-200 transform active:scale-95">
                    <i class="fa-solid fa-paper-plane text-xs text-white"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar styling for AI Feed */
#aiChatFeed::-webkit-scrollbar {
    width: 5px;
}
#aiChatFeed::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 9999px;
}
.dark #aiChatFeed::-webkit-scrollbar-thumb {
    background-color: #334155;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script>
    let aiChatHistory = [];
    let isVoiceOutputEnabled = localStorage.getItem('ai_voice_output') !== 'false';
    let recognition = null;
    let isListening = false;

    // Initialize Voice Recognition if supported by browser
    function initSpeechRecognition() {
        window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if ('SpeechRecognition' in window) {
            recognition = new window.SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-IN'; // Indian English accent recognition for Rs / Rupees / party names

            recognition.onstart = function() {
                isListening = true;
                updateMicUI(true);
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                document.getElementById('aiUserInput').value = transcript;
                updateMicUI(false);
                // Auto send once speech is complete
                sendAiMessage(transcript);
            };

            recognition.onerror = function(event) {
                console.warn('Speech recognition error:', event.error);
                updateMicUI(false);
            };

            recognition.onend = function() {
                isListening = false;
                updateMicUI(false);
            };
        } else {
            console.log('Web Speech API is not supported in this browser.');
        }
    }

    function toggleVoiceRecognition() {
        if (!recognition) {
            initSpeechRecognition();
        }
        if (!recognition) {
            alert('Voice speech recognition is not supported in your browser. Please try Chrome, Edge, or Safari.');
            return;
        }

        if (isListening) {
            recognition.stop();
        } else {
            // Stop any ongoing speech synthesis first
            if (window.speechSynthesis) {
                window.speechSynthesis.cancel();
            }
            try {
                recognition.start();
            } catch (e) {
                console.error(e);
            }
        }
    }

    function stopVoiceRecognition() {
        if (recognition && isListening) {
            recognition.stop();
        }
    }

    function updateMicUI(listening) {
        const micBtn = document.getElementById('aiMicBtn');
        const micIcon = document.getElementById('aiMicIcon');
        const banner = document.getElementById('aiVoiceListeningBanner');

        if (listening) {
            micBtn.classList.add('bg-red-500', 'text-white', 'animate-pulse');
            micBtn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            micIcon.className = 'fa-solid fa-microphone-lines text-sm';
            if (banner) banner.classList.remove('hidden');
        } else {
            micBtn.classList.remove('bg-red-500', 'text-white', 'animate-pulse');
            micBtn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            micIcon.className = 'fa-solid fa-microphone text-sm';
            if (banner) banner.classList.add('hidden');
        }
    }

    function toggleSpeechOutput() {
        isVoiceOutputEnabled = !isVoiceOutputEnabled;
        localStorage.setItem('ai_voice_output', isVoiceOutputEnabled);
        const icon = document.getElementById('aiVoiceIcon');
        if (isVoiceOutputEnabled) {
            icon.className = 'fa-solid fa-volume-high text-xs text-emerald-400';
            speakText("Voice mode enabled.");
        } else {
            icon.className = 'fa-solid fa-volume-xmark text-xs text-slate-400';
            if (window.speechSynthesis) window.speechSynthesis.cancel();
        }
    }

    function speakText(text) {
        if (!isVoiceOutputEnabled || !('speechSynthesis' in window)) return;
        
        // Strip markdown links, formatting, and emojis for clean speech
        let cleanText = stripEmojisClient(text)
            .replace(/\[([^\]]+)\]\([^\)]+\)/g, '$1') // remove links
            .replace(/[*_#`]/g, '') // remove formatting symbols
            .replace(/₹/g, 'Rupees ')
            .replace(/Rs\.?/gi, 'Rupees ');

        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.rate = 1.0;
        utterance.pitch = 1.0;

        // Try to pick an English voice
        const voices = window.speechSynthesis.getVoices();
        const preferred = voices.find(v => v.lang.includes('en-IN') || v.lang.includes('en-US') || v.lang.includes('en-GB'));
        if (preferred) utterance.voice = preferred;

        window.speechSynthesis.speak(utterance);
    }

    function toggleAiChat() {
        const modal = document.getElementById('aiChatModal');
        const icon = document.getElementById('launcherIcon');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            icon.className = 'fa-solid fa-xmark text-sm text-white';
            setTimeout(() => {
                document.getElementById('aiUserInput').focus();
            }, 100);
        } else {
            modal.classList.add('hidden');
            icon.className = 'fa-solid fa-wand-magic-sparkles text-sm text-white';
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            stopVoiceRecognition();
        }
    }

    function sendQuickPrompt(text) {
        document.getElementById('aiUserInput').value = text;
        sendAiMessage(text);
    }

    function handleAiFormSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('aiUserInput');
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        sendAiMessage(msg);
    }

    function appendUserMessage(text) {
        const feed = document.getElementById('aiChatFeed');
        const div = document.createElement('div');
        div.className = 'flex items-end justify-end gap-2';
        div.innerHTML = `
            <div class="max-w-[85%] bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl rounded-br-none px-3.5 py-2.5 text-xs shadow-sm leading-relaxed">
                ${escapeHtml(text)}
            </div>
        `;
        feed.appendChild(div);
        feed.scrollTop = feed.scrollHeight;
    }

    function appendLoadingBubble() {
        const feed = document.getElementById('aiChatFeed');
        const div = document.createElement('div');
        div.id = 'aiTypingIndicator';
        div.className = 'flex items-start gap-2.5';
        div.innerHTML = `
            <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                <i class="fa-solid fa-robot text-xs"></i>
            </div>
            <div class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl rounded-tl-none px-4 py-3 flex items-center gap-1.5 shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-bounce [animation-delay:0.4s]"></span>
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 ml-1.5">Processing accounting action...</span>
            </div>
        `;
        feed.appendChild(div);
        feed.scrollTop = feed.scrollHeight;
    }

    function removeLoadingBubble() {
        const ind = document.getElementById('aiTypingIndicator');
        if (ind) ind.remove();
    }

    function appendAssistantMessage(replyText, cardData) {
        const feed = document.getElementById('aiChatFeed');
        const div = document.createElement('div');
        div.className = 'flex items-start gap-2.5';

        // Format markdown links and bolding
        let formattedText = formatMarkdown(replyText);

        let cardHtml = '';
        if (cardData) {
            if (cardData.type === 'invoice') {
                cardHtml = `
                    <div class="mt-2.5 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Sales Invoice Created</span>
                            <span class="text-xs font-black text-emerald-800 dark:text-emerald-300">${cardData.amount}</span>
                        </div>
                        <p class="text-slate-800 dark:text-slate-200 font-bold">${cardData.party}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">${cardData.item}</p>
                        <div class="flex items-center gap-2 pt-1 border-t border-emerald-100 dark:border-emerald-900/60">
                            <a href="${cardData.url}" class="flex-1 text-center py-1 px-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-[11px] transition shadow-xs">View Invoice</a>
                            <a href="${cardData.print_url}" target="_blank" class="py-1 px-2.5 bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-lg font-semibold text-[11px] transition">Print / PDF</a>
                        </div>
                    </div>
                `;
            } else if (cardData.type === 'bill') {
                cardHtml = `
                    <div class="mt-2.5 p-3 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Vendor Bill Created</span>
                            <span class="text-xs font-black text-blue-800 dark:text-blue-300">${cardData.amount}</span>
                        </div>
                        <p class="text-slate-800 dark:text-slate-200 font-bold">${cardData.party}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">${cardData.item}</p>
                        <div class="flex items-center gap-2 pt-1 border-t border-blue-100 dark:border-blue-900/60">
                            <a href="${cardData.url}" class="flex-1 text-center py-1 px-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold text-[11px] transition shadow-xs">View Bill</a>
                        </div>
                    </div>
                `;
            }
        }

        div.innerHTML = `
            <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm flex-shrink-0">
                <i class="fa-solid fa-sparkles text-[10px]"></i>
            </div>
            <div class="max-w-[85%] bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none p-3 shadow-xs space-y-2 leading-relaxed">
                <div>${formattedText}</div>
                ${cardHtml}
            </div>
        `;
        feed.appendChild(div);
        feed.scrollTop = feed.scrollHeight;

        // Verbal speech playback if enabled
        speakText(replyText);
    }

    async function sendAiMessage(messageText) {
        appendUserMessage(messageText);
        appendLoadingBubble();

        aiChatHistory.push({ role: 'user', content: messageText });

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route("ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: messageText,
                    history: aiChatHistory
                })
            });

            removeLoadingBubble();

            const data = await res.json();
            if (data.success) {
                aiChatHistory.push({ role: 'assistant', content: data.reply });
                appendAssistantMessage(data.reply, data.action_card);
            } else {
                appendAssistantMessage(data.message || 'Error communicating with AI assistant.');
            }
        } catch (err) {
            removeLoadingBubble();
            console.error(err);
            appendAssistantMessage('Connection error. Please check server or network.');
        }
    }

    function clearAiHistory() {
        aiChatHistory = [];
        const feed = document.getElementById('aiChatFeed');
        feed.innerHTML = `
            <div class="flex items-start gap-2.5">
                <div class="flex-shrink-0 w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    <i class="fa-solid fa-sparkles text-[10px]"></i>
                </div>
                <div class="max-w-[85%] bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none p-3 shadow-xs space-y-2 leading-relaxed">
                    <p class="font-medium">
                        Chat reset! How can I assist with your accounts, bills, or invoices today?
                    </p>
                </div>
            </div>
        `;
    }

    function stripEmojisClient(str) {
        if (!str) return '';
        return str.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{2300}-\u{23FF}\u{2B50}\u{FE0F}\u{200D}]/gu, '').trim();
    }

    function formatMarkdown(text) {
        if (!text) return '';
        let clean = stripEmojisClient(text);
        let escaped = escapeHtml(clean);
        
        // Bold: **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Italics: *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Links: [text](url)
        escaped = escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-emerald-600 dark:text-emerald-400 font-bold underline hover:text-emerald-700">$1</a>');
        
        // Linebreaks
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    function escapeHtml(string) {
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return String(string).replace(/[&<>"']/g, function (s) {
            return entityMap[s];
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initSpeechRecognition();
        const icon = document.getElementById('aiVoiceIcon');
        if (icon) {
            icon.className = isVoiceOutputEnabled ? 'fa-solid fa-volume-high text-xs text-emerald-400' : 'fa-solid fa-volume-xmark text-xs text-slate-400';
        }
    });
</script>
