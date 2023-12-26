<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { GoogleGenerativeAI } from "@google/generative-ai";
import { computed, type PropType, ref } from 'vue';
import MessageComponent from '@/Components/MessageComponent.vue';
import { useForm } from 'laravel-precognition-vue-inertia';
import hljs from 'highlight.js';
import { onUpdated } from 'vue';
import { onMounted } from 'vue';
import 'highlight.js/styles/dark.css';
import CopyButtonPlugin from 'highlightjs-copy';
import 'highlightjs-copy/styles/highlightjs-copy.css';

const props = defineProps({
    chats: {
        type: Array as PropType<Array<{
            id: number,
            name: string,
            user_id: Number,
            created_at: string,
            updated_at: string,
        }>>,
        required: true
    },
    chat: {
        type: Object as PropType<{
            id: number,
            name: string,
            user_id: Number,
            created_at: string,
            updated_at: string,
            items: Array<{
                id: number,
                chat_id: number,
                user_id: number,
                type: string,
                message: string,
                created_at: string,
                updated_at: string,
            }>
        }>,
        required: true
    },
    auth: {
        type: Object as PropType<{
            user: Object,
        }>,
        required: true
    },
    apiKey: {
        type: String as PropType<string>,
        required: true
    }
});

const API_KEY = props.apiKey;
const generativeAI = new GoogleGenerativeAI(API_KEY);
const model = generativeAI.getGenerativeModel({ model: "gemini-pro" });

const proccessing = ref<Boolean>(false)
const prompt = ref<String>('');

const sendPrompt = async () => {
    if (prompt.value.length === 0) {
        return;
    }

    const p = prompt.value.toString().trim();
    prompt.value = '';

    proccessing.value = true
    const chatItems = props.chat.items;

    const history = props.chat.items.map((item, index) => {
        return {
            role: item.type === 'prompt' ? 'user' : 'model',
            parts: item.message,
        }
    });

    const chat = model.startChat({
        history: history,
    });
    const chatItem = {
        id: chatItems.length + 1,
        chat_id: props.chat.id,
        user_id: props.chat.user_id,
        type: 'prompt',
        message: p,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
    }

    props.chat.items.push(chatItem);

    const repsonseChatItem = {
        id: chatItems.length + 1,
        chat_id: props.chat.id,
        user_id: props.chat.user_id,
        type: 'response',
        message: "",
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
    }

    props.chat.items.push(repsonseChatItem);

    const lastChatItem = props.chat.items[props.chat.items.length - 1];

    const result = await chat.sendMessageStream(p);

    for await (const chunk of result.stream) {
        const chunkText = chunk.text();
        lastChatItem.message += chunkText;
    }
    await storePrompt(p, lastChatItem.message);
}

const storePrompt = async (prompt: String, response: String) => {
    console.log(prompt, response);

    const form = useForm('post', '/dashboard/store-chat-items/' + props.chat.id, {
        prompt: prompt,
        response: response,
    });

    form.submit({
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            proccessing.value = false;
        },
    });
};

onMounted(() => {
    hljs.addPlugin(new CopyButtonPlugin({
        hook: (text, el) => {
            el.setAttribute('aria-label', 'Copy');
        },
        callback: (el) => {
            el.setAttribute('aria-label', 'Kopyalandı!');
        },
    }));

    const codeBlocks = document.querySelectorAll('pre code');
    hljs.configure({
        languages: ['javascript', 'typescript', 'php', 'python', 'java', 'csharp', 'go', 'rust', 'ruby', 'dart', 'kotlin', 'swift', 'scala', 'elixir', 'clojure', 'haskell', 'lua', 'r', 'sql', 'shell', 'plaintext'],
    });

    codeBlocks.forEach((block) => {
        hljs.highlightBlock(block);
    });
});

onUpdated(() => {
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach((block) => {
        hljs.highlightBlock(block);
    });
});
</script>

<template>
    <AuthenticatedLayout :chats="chats">
        <div class="w-full flex justify-center">
            <div class="h-screen w-4/5 relative overflow-y-scroll">
                <div class="flex flex-col gap-4">
                    <MessageComponent :user="auth.user" v-for="item in chat.items" :type="item.type"
                        :message="item.message" />
                </div>
                <div class="w-full p-2 bottom-0 sticky">
                    <div class="flex flex-row relative">
                        <input v-model="prompt" type="text"
                            class="border w-full p-3 text-white focus:border-0 bg-[#343540] rounded-lg"
                            :disabled="proccessing.valueOf() === true"
                            :placeholder="proccessing.valueOf() === true ? 'Generating...' : 'Write a prompt..'">
                        <button
                            class="p-2 bg-transparent border border-gray-500 text-white absolute h-full right-0 border-r-0 border-t-0 border-b-0 hover:bg-gray-500/50 top-0 translate-x--3"
                            :disabled="proccessing.valueOf() === true" @click="sendPrompt">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
