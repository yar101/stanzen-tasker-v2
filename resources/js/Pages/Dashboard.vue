<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        AuthenticatedLayout,
        // eslint-disable-next-line vue/no-reserved-component-names
        Head,
    },

    props: {
        open_tasks: {
            type: Number,
            required: true,
        },

        not_started_tasks: {
            type: Number,
            required: true,
        },

        on_hold_tasks: {
            type: Number,
            required: true,
        },
    },
};
</script>

<template>
    <Head title="Дашборд" />

    <AuthenticatedLayout>
        <!--        <template #header>-->
        <!--            <h2 class="text-xl font-semibold leading-tight text-gray-800">-->
        <!--                Dashboard-->
        <!--            </h2>-->
        <!--        </template>-->

        <div class="py-12">
            <div class="mx-auto max-w-[90rem] sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="">
                            <div class="mb-10 text-xl font-semibold">
                                Состояние задач
                            </div>
                            <div class="flex gap-2">
                                <!--Не начато-->
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="flex w-[7rem] flex-col rounded-md border border-neutral-300/50 bg-neutral-200 shadow-neutral-200"
                                    >
                                        <span
                                            class="rounded-t-md bg-neutral-300/50 px-2"
                                            >Не начато</span
                                        >
                                        <span
                                            class="flex items-center justify-end px-2 py-2 text-xl font-semibold text-neutral-900"
                                            >{{ not_started_tasks }}</span
                                        >
                                    </div>
                                </div>

                                <!--Открытые-->
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="flex w-[7rem] flex-col rounded-md border border-blue-300/50 bg-blue-200 shadow-blue-200"
                                    >
                                        <span
                                            class="rounded-t-md bg-blue-300/50 px-2"
                                            >В работе</span
                                        >
                                        <span
                                            class="flex items-center justify-end px-2 py-2 text-xl font-semibold text-blue-900"
                                            >{{ open_tasks }}</span
                                        >
                                    </div>
                                </div>

                                <!--Отложены-->
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="flex min-w-[7rem] max-w-fit flex-col rounded-md border border-amber-300/50 bg-amber-200 shadow-amber-200"
                                    >
                                        <span
                                            class="rounded-t-md bg-amber-300/50 px-2"
                                            >На удержании</span
                                        >
                                        <span
                                            class="flex items-center justify-end px-2 py-2 text-xl font-semibold text-amber-900"
                                            >{{ on_hold_tasks }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
