<script lang="ts" setup>
import { computed, type PropType } from 'vue';
import VueMarkdown from 'vue-markdown-render'

const props = defineProps({
    message: {
        type: String as PropType<string>,
        required: true
    },
    type: {
        type: String as PropType<string>,
        required: true,
        validator: (value: string) => {
            return ['response', 'prompt'].includes(value);
        }
    },
    user : {
        type: Object as PropType<{
            id: number,
            name: string,
            email: string,
            email_verified_at: string,
            created_at: string,
            updated_at: string,
        }>,
        required: true
    }
});

const copyContent = () => {
    const el = document.createElement('textarea');
    el.value = props.message;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
}

</script>

<template>
    <div class="max-h-screen overflow-y-scroll p-3 text-white">
        <div class="flex flex-col" v-if="type == 'prompt'">
            <h1 class="text-2xl font-bold">
                {{ user.name }}
            </h1>
            <div class="p-2">
                {{ message }}
            </div>
        </div>
        <div class="flex flex-col" v-else>
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold">
                    Deepmind
                </h1>
                <div>
                    <button @click="copyContent" class="p-2 bg-blue-500 text-white">
                        Copy
                    </button>
                </div>
            </div>
            <vue-markdown class="p-2" :source="message" :options="{ breaks: true, typographer: true }" />
        </div>
    </div>
</template>