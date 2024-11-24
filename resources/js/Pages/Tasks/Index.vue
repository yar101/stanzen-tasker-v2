<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

export default {
    components: {
        InputLabel,
        TextInput,
        InputError,
        AuthenticatedLayout,
        // eslint-disable-next-line vue/no-reserved-component-names
        Head,
    },
    props: {
        errors: Object,
        tasks: {
            type: Object,
            required: true,
        },
        statuses: {
            type: Object,
            required: true,
        },
        contractors: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            isEditModalOpen: false,
            isCreateModalOpen: false,
            selectedTask: null,
            form: useForm({
                title: '',
                description: '',
                contractor: 1,
                cost: 0.0,
                currency: 'RUB',
            }),
        };
    },

    methods: {
        openEditModal(task) {
            this.selectedTask = { ...task }; // Копируем объект
            this.form.title = String(task.title);
            this.form.description = task.description;
            this.form.contractor = task.contractor;
            this.form.cost = task.cost;
            this.form.currency = task.currency;
            this.form.priority = task.priority;
            this.isEditModalOpen = true;
        },
        openCreateModal() {
            // Сбрасываем форму
            this.form.title = '';
            this.form.description = '';
            this.form.contractor = 1;
            this.form.cost = '';
            this.form.currency = 'RUB';
            this.form.priority = 'III';
            this.isCreateModalOpen = true;
        },
        closeModals() {
            this.isEditModalOpen = false;
            this.isCreateModalOpen = false;
            this.selectedTask = null;
            this.form.clearErrors();
        },

        getContractorName(contractorId) {
            const contractor = this.contractors.find(
                (contractor) => contractor.id === contractorId,
            );
            return contractor ? contractor.name : '';
        },

        getStatusName(statusId) {
            const status = this.statuses.find(
                (status) => status.id === statusId,
            );
            return status ? status.name : '';
        },

        store() {
            this.$inertia.post(route('tasks.store'), this.form, {
                onSuccess: () => {
                    this.closeModals();
                },
            });
        },

        update() {
            this.$inertia.patch(
                route('tasks.update', this.selectedTask.id),
                this.form,
                {
                    onSuccess: () => {
                        this.closeModals();
                    },
                },
            );
        },

        updateStatus(task) {
            this.$inertia.patch(
                route('tasks.updateStatus', {
                    task,
                    status: task.status,
                }),
            );
        },
    },
};
</script>

<template>
    <Head title="Задачи" />

    <AuthenticatedLayout>
        <div
            class="mx-auto h-[80rem] overflow-x-auto overflow-y-scroll px-5 pb-5 pt-5"
        >
            <!-- Таблица -->
            <table
                v-if="tasks.length > 0"
                class="w-full table-auto divide-y divide-gray-200 rounded-md border border-gray-300 shadow-md"
            >
                <thead class="bg-neutral-200">
                    <tr>
                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Статус
                        </th>

                        <th
                            class="w-0 px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Приоритет
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Контрагент
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Тема
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Описание
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Дата начала
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Дедлайн
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Стоимость
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Последний комментарий
                        </th>

                        <th class="">
                            <!--                            <button-->
                            <!--                                class="m-2 rounded bg-green-500 px-3 py-1 text-sm text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"-->
                            <!--                                @click="openCreateModal"-->
                            <!--                            >-->
                            <!--                                СОЗДАТЬ-->
                            <!--                            </button>-->
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="task in tasks"
                        :key="task.id"
                        class="group transition-all duration-100 ease-in-out hover:bg-gray-100"
                    >
                        <td class="max-w-[6rem] text-sm text-gray-900">
                            <!--                            <div-->
                            <!--                                :class="[-->
                            <!--                                                                getStatusName(task.status) === 'NOT STARTED'-->
                            <!--                                                                    ? 'bg-gray-200 text-gray-700'-->
                            <!--                                                                    : '',-->
                            <!--                                                                getStatusName(task.status) === 'ONGOING'-->
                            <!--                                                                    ? 'bg-blue-200 text-blue-700'-->
                            <!--                                                                    : '',-->
                            <!--                                                                getStatusName(task.status) === 'ON HOLD'-->
                            <!--                                                                    ? 'bg-yellow-200 text-yellow-700'-->
                            <!--                                                                    : '',-->
                            <!--                                                                getStatusName(task.status) === 'DELAY'-->
                            <!--                                                                    ? 'bg-red-200 text-red-700'-->
                            <!--                                                                    : '',-->
                            <!--                                                                getStatusName(task.status) === 'DONE'-->
                            <!--                                                                    ? 'bg-green-200 text-green-700'-->
                            <!--                                                                    : '',-->
                            <!--                                ]"-->
                            <!--                                class="ml-5 w-[7rem] rounded-md px-2 py-1 text-center font-semibold"-->
                            <!--                            >-->
                            <!--                                {{ getStatusName(task.status) }}-->
                            <!--                            </div>-->

                            <div class="flex items-center justify-center">
                                <select
                                    v-model="task.status"
                                    :class="[
                                        getStatusName(task.status) ===
                                        'NOT STARTED'
                                            ? 'border-gray-500 bg-gray-200 text-gray-800 hover:bg-gray-300 hover:text-gray-900'
                                            : '',
                                        getStatusName(task.status) === 'ONGOING'
                                            ? 'border-blue-500 bg-blue-200 text-blue-800 hover:bg-blue-300 hover:text-blue-900'
                                            : '',
                                        getStatusName(task.status) === 'ON HOLD'
                                            ? 'border-yellow-500 bg-yellow-200 text-yellow-800 hover:bg-yellow-300 hover:text-yellow-900'
                                            : '',
                                        getStatusName(task.status) === 'DELAY'
                                            ? 'border-red-500/70 bg-red-200 text-red-800 hover:bg-red-300 hover:text-red-900'
                                            : '',
                                        getStatusName(task.status) === 'DONE'
                                            ? 'border-green-500 bg-green-200 text-green-800 hover:bg-green-300 hover:text-green-900'
                                            : '',
                                    ]"
                                    class="rounded-lg text-sm transition-all focus:ring-0"
                                    @change="updateStatus(task)"
                                >
                                    <option
                                        v-for="status in statuses"
                                        :key="status.id"
                                        :value="status.id"
                                    >
                                        {{ getStatusName(status.id) }}
                                    </option>
                                </select>
                            </div>
                        </td>

                        <td class="px-4 py-2 text-sm">
                            <div class="flex items-center justify-center">
                                <div
                                    :class="[
                                        task.priority === 'I'
                                            ? 'bg-red-200 text-red-700'
                                            : '',
                                        task.priority === 'II'
                                            ? 'bg-yellow-200 text-yellow-700'
                                            : '',
                                        task.priority === 'III'
                                            ? 'bg-blue-200 text-blue-700'
                                            : '',
                                    ]"
                                    class="w-[25px] rounded-md p-1 text-center font-semibold"
                                >
                                    {{ task.priority }}
                                </div>
                            </div>
                        </td>

                        <td
                            class="max-w-[9rem] px-4 py-2 text-sm text-gray-900"
                        >
                            <div
                                class="flex min-h-8 w-fit items-center overflow-x-scroll"
                            >
                                {{ getContractorName(task.contractor) }}
                            </div>
                        </td>

                        <td
                            class="max-w-[15rem] px-4 py-2 text-sm text-gray-900"
                        >
                            <div
                                class="flex min-h-8 items-center overflow-x-scroll"
                            >
                                {{ task.title }}
                            </div>
                        </td>

                        <td
                            class="max-w-[25rem] px-4 py-2 text-sm text-gray-900"
                        >
                            <div
                                class="flex min-h-8 items-center overflow-x-scroll text-center"
                            >
                                {{ task.description }}
                            </div>
                        </td>

                        <td>
                            <div class="text-center text-sm text-gray-900">
                                {{
                                    new Date(
                                        task.deadline_start,
                                    ).toLocaleDateString('ru-RU', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                    })
                                }}
                            </div>
                        </td>

                        <td>
                            <div class="text-center text-sm text-gray-900">
                                {{
                                    new Date(
                                        task.deadline_end,
                                    ).toLocaleDateString('ru-RU', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                    })
                                }}
                            </div>
                        </td>

                        <td class="px-4 py-2 text-sm text-gray-900">
                            <div
                                class="flex min-h-8 flex-col items-center justify-center overflow-x-scroll"
                            >
                                <div class="text-center">
                                    {{ task.cost }}
                                </div>
                                <div class="text-center">
                                    {{ task.currency }}
                                </div>
                            </div>
                        </td>

                        <td class="text-center">comments in progress...</td>

                        <td>
                            <div
                                class="flex min-h-8 items-center justify-center gap-1"
                            >
                                <!--                            Edit button-->
                                <button
                                    class="w-8 rounded-md border border-amber-300 bg-amber-100 p-1 transition-all duration-100 hover:bg-amber-200 hover:shadow-md"
                                    @click="openEditModal(task)"
                                >
                                    <svg
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <g
                                            id="SVGRepo_bgCarrier"
                                            stroke-width="0"
                                        ></g>
                                        <g
                                            id="SVGRepo_tracerCarrier"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path
                                                d="M7.5 2.75C7.5 2.33579 7.16421 2 6.75 2C6.33579 2 6 2.33579 6 2.75V5.75C6 6.60889 6.61875 7.32327 7.43481 7.47169L6.34113 10.4403C6.08209 11.1434 6.12117 11.9217 6.44935 12.5954L10.6068 21.129C10.8664 21.6619 11.4072 22 12 22C12.5927 22 13.1336 21.6619 13.3932 21.129L17.5034 12.6923C17.8691 11.9415 17.8739 11.0653 17.5165 10.3106L16.1851 7.5H16.25C17.2165 7.5 18 6.7165 18 5.75V2.75C18 2.33579 17.6642 2 17.25 2C16.8358 2 16.5 2.33579 16.5 2.75V5.75C16.5 5.88807 16.3881 6 16.25 6H7.75C7.61193 6 7.5 5.88807 7.5 5.75V2.75ZM14.5254 7.5L16.1609 10.9527C16.3234 11.2958 16.3212 11.6941 16.1549 12.0353L12.75 19.0243V12.2993C13.1984 12.04 13.5 11.5552 13.5 11C13.5 10.1716 12.8284 9.5 12 9.5C11.1716 9.5 10.5 10.1716 10.5 11C10.5 11.5552 10.8016 12.04 11.25 12.2993V19.0244L7.79783 11.9384C7.64866 11.6322 7.6309 11.2784 7.74864 10.9588L9.02294 7.5H14.5254Z"
                                                fill="#e5a50a"
                                            ></path>
                                        </g>
                                    </svg>
                                </button>

                                <!--                            CommentButton-->

                                <button
                                    class="w-8 rounded-md border border-blue-300 bg-blue-100 p-1 transition-all duration-100 hover:bg-blue-200 hover:shadow-md"
                                >
                                    <svg
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <g
                                            id="SVGRepo_bgCarrier"
                                            stroke-width="0"
                                        ></g>
                                        <g
                                            id="SVGRepo_tracerCarrier"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></g>
                                        <g id="SVGRepo_iconCarrier">
                                            <path
                                                d="M10 9H17M10 13H17M7 9H7.01M7 13H7.01M21 20L17.6757 18.3378C17.4237 18.2118 17.2977 18.1488 17.1656 18.1044C17.0484 18.065 16.9277 18.0365 16.8052 18.0193C16.6672 18 16.5263 18 16.2446 18H6.2C5.07989 18 4.51984 18 4.09202 17.782C3.71569 17.5903 3.40973 17.2843 3.21799 16.908C3 16.4802 3 15.9201 3 14.8V7.2C3 6.07989 3 5.51984 3.21799 5.09202C3.40973 4.71569 3.71569 4.40973 4.09202 4.21799C4.51984 4 5.0799 4 6.2 4H17.8C18.9201 4 19.4802 4 19.908 4.21799C20.2843 4.40973 20.5903 4.71569 20.782 5.09202C21 5.51984 21 6.0799 21 7.2V20Z"
                                                stroke="#1c71d8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                            ></path>
                                        </g>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="Object.keys(tasks).length === 0"
                class="w-full px-4 py-2 text-center text-2xl text-gray-500"
            >
                Задачи не найдены
                <br />
                <button
                    class="m-2 w-[200px] rounded bg-green-500 px-3 py-1 text-[22px] text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                    @click="openCreateModal"
                >
                    СОЗДАТЬ
                </button>
            </div>
        </div>

        <!-- Модальное окно создания -->
        <div
            v-if="isCreateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <form
                class="w-[30rem] rounded-md bg-white p-5 shadow-lg"
                @submit.prevent="store"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-800">
                    Добавить задачу
                </h2>

                <!--                Поля формы-->

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Тема
                    </InputLabel>
                    <TextInput
                        v-model="form.title"
                        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        type="text"
                    />
                    <InputError :message="errors.title" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Описание
                    </InputLabel>
                    <textarea
                        v-model="form.description"
                        class="mt-1 h-20 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        type="textarea"
                    />
                    <InputError :message="errors.description" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Контрагент
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.contractor"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option
                                v-for="contractor in contractors"
                                :key="contractor.id"
                                :value="contractor.id"
                            >
                                {{ contractor.name }}
                            </option>
                        </select>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Стоимость
                    </InputLabel>
                    <div class="flex gap-2">
                        <TextInput
                            v-model="form.cost"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                            type="text"
                        />
                        <select
                            v-model="form.currency"
                            class="mt-1 w-fit rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option selected value="RUB">RUB</option>
                            <option value="CNY">CNY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="LYR">LYR</option>
                        </select>
                    </div>
                    <InputError :message="errors.cost" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Приоритет
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.priority"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option selected value="III">III</option>
                            <option value="II">II</option>
                            <option value="I">I</option>
                        </select>
                    </div>
                    <InputError :message="errors.cost" class="mt-2" />
                </div>

                <!--                Кнопки формы-->
                <div class="flex justify-end gap-2">
                    <button
                        class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                        type="button"
                        @click="closeModals"
                    >
                        Отмена
                    </button>
                    <button
                        class="rounded bg-green-500 px-4 py-2 text-sm text-white hover:bg-green-600"
                        type="submit"
                    >
                        Добавить
                    </button>
                </div>
            </form>
        </div>

        <!--        Модальное окно редактирования-->
        <div
            v-if="isEditModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <form
                class="w-[30rem] rounded-md bg-white p-5 shadow-lg"
                @submit.prevent="update"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-800">
                    Редактирование задачи
                </h2>

                <!--                Поля формы-->

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Тема
                    </InputLabel>
                    <TextInput
                        v-model="form.title"
                        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        type="text"
                    />
                    <InputError :message="errors.title" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Описание
                    </InputLabel>
                    <textarea
                        v-model="form.description"
                        class="mt-1 h-20 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        type="textarea"
                    />
                    <InputError :message="errors.description" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Контрагент
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.contractor"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option
                                v-for="contractor in contractors"
                                :key="contractor.id"
                                :value="contractor.id"
                            >
                                {{ contractor.name }}
                            </option>
                        </select>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Стоимость
                    </InputLabel>
                    <div class="flex gap-2">
                        <TextInput
                            v-model="form.cost"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                            type="text"
                        />
                        <select
                            v-model="form.currency"
                            class="mt-1 w-fit rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option selected value="RUB">RUB</option>
                            <option value="CNY">CNY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="LYR">LYR</option>
                        </select>
                    </div>
                    <InputError :message="errors.cost" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Приоритет
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.priority"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                        >
                            <option selected value="III">III</option>
                            <option value="II">II</option>
                            <option value="I">I</option>
                        </select>
                    </div>
                    <InputError :message="errors.cost" class="mt-2" />
                </div>

                <!--                Кнопки формы-->
                <div class="flex justify-end gap-2">
                    <button
                        class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                        type="button"
                        @click="closeModals"
                    >
                        Отмена
                    </button>
                    <button
                        class="rounded bg-green-500 px-4 py-2 text-sm text-white hover:bg-green-600"
                        type="submit"
                    >
                        Обновить
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
