<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { addIcons, OhVueIcon } from 'oh-vue-icons';
import { BiArrowRight } from 'oh-vue-icons/icons';
import { Head } from '@inertiajs/vue3';

addIcons(BiArrowRight);

export default {
    components: {
        // eslint-disable-next-line vue/no-reserved-component-names
        Head,
        AuthenticatedLayout,
        'v-icon': OhVueIcon,
    },

    props: {
        users: {
            type: Object,
            required: true,
        },
        headOfDepartmentUsers: {
            type: Object,
            required: true,
        },
    },

    methods: {
        switchUserRoleToHeadOfDepartment(user) {
            this.$inertia.patch(
                route('hod-functions.switchUserRoleToHeadOfDepartment', {
                    user: user,
                }),
            );
        },

        switchUserRoleToUser(user) {
            this.$inertia.patch(
                route('hod-functions.switchUserRoleToUser', {
                    user: user,
                }),
            );
        },
    },
};
</script>

<template>
    <Head title="Управление отделом" />
    <AuthenticatedLayout>
        <div class="pl-52 pr-52 pt-20">
            <div
                class="rounded-md border-gray-300/90 bg-neutral-200/70 shadow-lg"
            >
                <div
                    class="flex items-center justify-center rounded-t-md border border-gray-400 bg-gradient-to-br from-amber-200 to-amber-400 p-0.5 pb-0.5 text-sm"
                >
                    Управление отделом
                </div>
                <div
                    class="rounded-b-md border border-t-0 border-gray-400 bg-neutral-200 pb-16 pl-16 pr-16 pt-10"
                >
                    <div
                        class="min-h-[10rem] w-fit rounded border border-gray-400/50 bg-gradient-to-br from-amber-400/30 to-amber-500/40 p-1 text-center text-sm"
                    >
                        <div
                            class="border-b border-blue-400/50 p-1 text-center text-sm"
                        >
                            Делегирование обязанностей
                        </div>
                        <div class="mt-4 grid grid-cols-2 pb-4">
                            <div
                                class="border-r-2 border-dotted border-gray-400 p-2"
                            >
                                <div class="text-center text-xs font-semibold">
                                    Сотрудники
                                </div>
                                <div
                                    class="mt-6 flex flex-col justify-between gap-3 text-sm"
                                >
                                    <div
                                        v-for="user in users"
                                        :key="user.id"
                                        class="flex gap-2"
                                    >
                                        <div
                                            :class="[
                                                user.id ===
                                                this.$page.props.auth.user.id
                                                    ? 'select-none rounded-md border border-neutral-400 bg-gray-300 p-1 text-neutral-700'
                                                    : 'select-none rounded-md border border-neutral-400 bg-amber-200/80 p-1 text-neutral-700',
                                            ]"
                                        >
                                            {{ user.name }}
                                        </div>

                                        <button
                                            :disabled="
                                                user.id ===
                                                this.$page.props.auth.user.id
                                            "
                                            class="flex cursor-pointer select-none items-center justify-center transition-all duration-[50ms] active:scale-90 disabled:pointer-events-none"
                                            @click="
                                                switchUserRoleToHeadOfDepartment(
                                                    user,
                                                )
                                            "
                                        >
                                            <v-icon
                                                :class="[
                                                    user.id ===
                                                    this.$page.props.auth.user
                                                        .id
                                                        ? 'w-[2rem] rounded-md border border-neutral-500/50 bg-gradient-to-br from-gray-300 to-gray-500 shadow-md'
                                                        : 'w-[2rem] rounded-md border border-neutral-500/50 bg-gradient-to-br from-amber-300 to-amber-500 shadow-md',
                                                ]"
                                                fill="#000"
                                                height="25"
                                                name="bi-arrow-right"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-2">
                                <div class="text-center text-xs font-semibold">
                                    Руководители
                                </div>
                                <div
                                    class="mt-6 flex flex-col justify-between gap-2 text-sm"
                                >
                                    <div
                                        v-for="user in headOfDepartmentUsers"
                                        :key="user.id"
                                        class="flex gap-2"
                                    >
                                        <button
                                            :disabled="
                                                user.id ===
                                                this.$page.props.auth.user.id
                                            "
                                            class="flex cursor-pointer select-none items-center justify-center transition-all duration-[50ms] active:scale-90 disabled:pointer-events-none"
                                            @click="switchUserRoleToUser(user)"
                                        >
                                            <v-icon
                                                :class="[
                                                    user.id ===
                                                    this.$page.props.auth.user
                                                        .id
                                                        ? 'w-[2rem] rounded-md border border-neutral-500/50 bg-gradient-to-br from-gray-300 to-gray-500 shadow-md'
                                                        : 'w-[2rem] rounded-md border border-neutral-500/50 bg-gradient-to-br from-amber-300 to-amber-500 shadow-md',
                                                ]"
                                                fill="#000"
                                                flip="horizontal"
                                                height="25"
                                                name="bi-arrow-right"
                                            />
                                        </button>
                                        <div
                                            :class="[
                                                user.id ===
                                                this.$page.props.auth.user.id
                                                    ? 'select-none rounded-md border border-neutral-400 bg-gray-300 p-1 text-neutral-700'
                                                    : 'select-none rounded-md border border-neutral-400 bg-amber-200/80 p-1 text-neutral-700',
                                            ]"
                                        >
                                            {{ user.name }}
                                        </div>
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
