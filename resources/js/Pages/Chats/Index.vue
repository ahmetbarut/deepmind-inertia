<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { GoogleGenerativeAI } from "@google/generative-ai";
import { computed, type PropType } from 'vue';
import VueMarkdown from 'vue-markdown-render'
import MessageComponent from '@/Components/MessageComponent.vue';
import NavLink from '@/Components/NavLink.vue';

const API_KEY = "AIzaSyDGCME4x2cF7WHwjLj_YvHggW3HzUGo7gw";
const generativeAI = new GoogleGenerativeAI(API_KEY);
const model = generativeAI.getGenerativeModel({ model: "gemini-pro" });

const proccessing = ref<Boolean>(false)
const repsonsePrompt = ref<String>('')
const prompt = ref<String>('');

const props = defineProps({
    chats: {
        type: Array as PropType<Array<{
            id: number,
            name: string,
            lastMessage: string,
            date: string,
        }>>,
        required: true
    }
});

const storePrompt = async () => {
};

const sendPrompt = async () => {
    proccessing.value = true
    storePrompt();
    const result = await model.generateContentStream(prompt.value.toString());
    for await (const chunk of result.stream) {
        proccessing.value = false
        const chunkText = chunk.text();
        repsonsePrompt.value += chunkText;
    }
}

</script>

<template>
    <AuthenticatedLayout>
        <div class="w-full flex min-h-screen bg-red-50">
            <div class="w-1/4">
                <ul class="w-full flex flex-col leading-9">
                    <li class="border h-12 hover:bg-gray-400/30 flex pl-3 hover:cursor-pointer flex-col justify-center"
                        v-for="chat in chats">
                        <NavLink :href="route('chats.show', chat.id)" :active="route().current('chats.show')">
                            {{ chat.name }}
                        </NavLink>
                    </li>
                </ul>
            </div>
            <div class="w-full">
                <div class="h-[600px] border w-11/12 relative">
                    <!-- <MessageComponent v-for="chat in chats" :type="chat.type"/> -->
                    <div class="w-full p-2 flex flex-row absolute bottom-0">
                        <input placeholder="Write a prompt.." v-model="prompt" type="text"
                            class="border w-full p-3 focus:border-none" name="" id="">
                        <button class="p-2 bg-blue-500 text-white" @click="sendPrompt">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
