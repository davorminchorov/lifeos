<div
    x-data="aiAssistant()"
    x-init="init()"
    class="fixed bottom-6 right-6 z-50"
>
    {{-- Floating trigger button --}}
    <button
        @click="toggle()"
        :class="open ? 'bg-[color:var(--color-danger-600)] hover:bg-[color:var(--color-danger-700)]' : 'bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]'"
        class="w-14 h-14 rounded-full shadow-lg text-white flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)]"
        :title="open ? 'Close assistant' : 'Ask your Life Assistant'"
    >
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
        </svg>
        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chat panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="absolute bottom-16 right-0 w-96 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-2xl shadow-2xl border border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex flex-col overflow-hidden"
        style="height: 520px; display: none;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-[color:var(--color-accent-500)] text-white">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                </svg>
                <span class="font-semibold text-sm">Life Assistant</span>
            </div>
            <button
                @click="clearHistory()"
                title="Clear conversation"
                class="text-white/70 hover:text-white text-xs transition-colors"
            >
                Clear
            </button>
        </div>

        {{-- Messages --}}
        <div
            x-ref="messages"
            class="flex-1 overflow-y-auto px-4 py-3 space-y-3 text-sm"
        >
            {{-- Welcome message --}}
            <template x-if="messages.length === 0">
                <div class="text-center py-6 text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    <p class="font-medium">Your Life OS Assistant</p>
                    <p class="text-xs mt-1 opacity-70">Ask me anything about your finances, investments, subscriptions, and more.</p>
                </div>
            </template>

            {{-- Suggested prompts (shown when no messages) --}}
            <template x-if="messages.length === 0">
                <div class="space-y-2">
                    <template x-for="prompt in suggestedPrompts" :key="prompt">
                        <button
                            @click="sendSuggestion(prompt)"
                            class="w-full text-left text-xs px-3 py-2 rounded-lg bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors border border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]"
                            x-text="prompt"
                        ></button>
                    </template>
                </div>
            </template>

            {{-- Chat messages --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="msg.role === 'user'
                            ? 'bg-[color:var(--color-accent-500)] text-white rounded-2xl rounded-tr-sm max-w-[80%] px-3 py-2'
                            : 'bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-800)] dark:text-[color:var(--color-dark-700)] rounded-2xl rounded-tl-sm max-w-[90%] px-3 py-2'"
                        class="text-xs leading-relaxed"
                        x-html="msg.role === 'assistant' ? formatMarkdown(msg.content) : escapeHtml(msg.content)"
                    ></div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <template x-if="loading">
                <div class="flex justify-start">
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-2xl rounded-tl-sm px-4 py-3">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 rounded-full bg-[color:var(--color-accent-400)] animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 rounded-full bg-[color:var(--color-accent-400)] animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 rounded-full bg-[color:var(--color-accent-400)] animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-3 py-2">
            <form @submit.prevent="send()" class="flex items-end space-x-2">
                <textarea
                    x-ref="input"
                    x-model="input"
                    @keydown.enter.prevent="!$event.shiftKey && send()"
                    :disabled="loading"
                    rows="1"
                    placeholder="Ask anything... (Enter to send)"
                    class="flex-1 resize-none text-xs rounded-xl border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-white dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-800)] dark:text-[color:var(--color-dark-700)] px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[color:var(--color-accent-500)] focus:border-transparent disabled:opacity-50 transition-colors"
                    style="max-height: 80px;"
                    @input="autoResize($el)"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="shrink-0 w-8 h-8 rounded-xl bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] disabled:bg-[color:var(--color-primary-300)] text-white flex items-center justify-center transition-colors"
                >
                    <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function aiAssistant() {
    return {
        open: false,
        loading: false,
        input: '',
        messages: [],
        conversationId: null,
        suggestedPrompts: [
            'What\'s my financial health this month?',
            'Any subscriptions I should reconsider?',
            'Show me my investment portfolio performance',
            'What upcoming bills or renewals need attention?',
        ],

        init() {
            // Nothing needed on init
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.input?.focus());
            }
        },

        async send() {
            const message = this.input.trim();
            if (!message || this.loading) return;

            this.messages.push({ role: 'user', content: message });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('{{ route('assistant.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message,
                        conversation_id: this.conversationId,
                    }),
                });

                const data = await response.json();

                if (data.error) {
                    this.messages.push({ role: 'assistant', content: '⚠️ ' + data.error });
                } else {
                    this.messages.push({ role: 'assistant', content: data.reply });
                    if (data.conversation_id) {
                        this.conversationId = data.conversation_id;
                    }
                }
            } catch (e) {
                this.messages.push({ role: 'assistant', content: '⚠️ Could not reach the assistant. Please try again.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        sendSuggestion(prompt) {
            this.input = prompt;
            this.send();
        },

        async clearHistory() {
            await fetch('{{ route('assistant.clear') }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            this.messages = [];
            this.conversationId = null;
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 80) + 'px';
        },

        escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        formatMarkdown(text) {
            // Minimal markdown: bold, bullet lists, line breaks
            return this.escapeHtml(text)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/^[-•]\s(.+)$/gm, '<li class="ml-3">$1</li>')
                .replace(/(<li.*<\/li>\n?)+/g, '<ul class="list-disc space-y-0.5 my-1">$&</ul>')
                .replace(/\n/g, '<br>');
        },
    };
}
</script>
