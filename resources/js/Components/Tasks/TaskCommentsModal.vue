<script>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

export default {
    components: { InputError },

    props: {
        isCommentsModalOpen: Boolean,
        task: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            required: true,
        },
        users: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            form: useForm({
                task_id: this.task.id,
                created_by: this.$page.props.auth.user.id,
                content: '',
            }),
        };
    },

    emits: ['getUserName', 'closeCommentModal'],

    methods: {
        getUserName(userId) {
            const user = this.users.find((user) => user.id === userId);
            return user ? user.name : '';
        },
        closeCommentModal() {
            this.$emit('closeCommentModal');
        },
        store() {
            this.form.post(route('comments.store'), {
                onSuccess: () => {
                    this.closeCommentModal();
                    this.form.reset('content');
                },
            });
        },
    },
};
</script>

<template>
    <div
        v-if="isCommentsModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
    >
        <div class="w-[50rem] rounded-lg bg-white p-4 shadow-md">
            <h2 class="mb-4 text-lg font-medium text-gray-800">Комментарии</h2>

            <div
                class="flex max-h-[30rem] flex-col gap-4 overflow-auto rounded-lg border border-gray-300 bg-neutral-50 p-4 shadow-inner"
            >
                <div v-for="comment in task.comments" :key="comment.id">
                    <div
                        :class="
                            comment.created_by === $page.props.auth.user.id
                                ? 'flex justify-end'
                                : 'flex justify-start'
                        "
                    >
                        <div class="flex w-[25rem] flex-col">
                            <div
                                :class="
                                    comment.created_by ===
                                    $page.props.auth.user.id
                                        ? 'justify-end'
                                        : 'justify-between'
                                "
                                class="flex items-center px-2 text-xs text-gray-500"
                            >
                                <div
                                    v-if="
                                        comment.created_by !==
                                        $page.props.auth.user.id
                                    "
                                >
                                    {{ getUserName(comment.created_by) }}
                                </div>
                                <div>
                                    {{
                                        new Date(
                                            comment.created_at,
                                        ).toLocaleDateString('ru-Ru', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    }}
                                </div>
                            </div>
                            <div
                                :class="
                                    comment.created_by ===
                                    $page.props.auth.user.id
                                        ? 'rounded-md border border-gray-200 bg-gray-100 p-2 text-sm text-neutral-700 shadow-md'
                                        : 'rounded-md border border-blue-200 bg-blue-100 p-2 text-sm text-neutral-700 shadow-md'
                                "
                            >
                                {{ comment.content }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <textarea
                    v-model="form.content"
                    class="mt-1 h-32 w-full resize-none rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                    type="textarea"
                />
                <InputError :message="errors.content" class="mt-2" />
            </div>
            <div class="flex justify-between gap-2">
                <button
                    class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                    type="button"
                    @click="closeCommentModal"
                >
                    Отмена
                </button>
                <button
                    class="flex w-[10rem] items-center justify-between overflow-hidden rounded border border-blue-300 bg-blue-100 px-1 text-sm text-white transition-all duration-200 hover:bg-blue-200 hover:shadow-md active:translate-y-[3px]"
                    type="button"
                    @click="store"
                >
                    <span class="font-semibold text-blue-600"
                        >Опубликовать</span
                    >
                    <svg
                        fill="none"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g
                            id="SVGRepo_tracerCarrier"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        ></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M20.7639 12H10.0556M3 8.00003H5.5M4 12H5.5M4.5 16H5.5M9.96153 12.4896L9.07002 15.4486C8.73252 16.5688 8.56376 17.1289 8.70734 17.4633C8.83199 17.7537 9.08656 17.9681 9.39391 18.0415C9.74792 18.1261 10.2711 17.8645 11.3175 17.3413L19.1378 13.4311C20.059 12.9705 20.5197 12.7402 20.6675 12.4285C20.7961 12.1573 20.7961 11.8427 20.6675 11.5715C20.5197 11.2598 20.059 11.0295 19.1378 10.5689L11.3068 6.65342C10.2633 6.13168 9.74156 5.87081 9.38789 5.95502C9.0808 6.02815 8.82627 6.24198 8.70128 6.53184C8.55731 6.86569 8.72427 7.42461 9.05819 8.54246L9.96261 11.5701C10.0137 11.7411 10.0392 11.8266 10.0493 11.9137C10.0583 11.991 10.0582 12.069 10.049 12.1463C10.0387 12.2334 10.013 12.3188 9.96153 12.4896Z"
                                stroke="#1c71d8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.2"
                            ></path>
                        </g>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
