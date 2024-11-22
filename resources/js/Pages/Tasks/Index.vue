<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
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
        errors: {
            type: Object,
            default: () => ({}),
        },
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
            selectedContractor: null,
            newTaskForm: {
                title: '',
                description: '',
                contractor: '',
                cost: '',
                currency: '',
                parent_task: null,
            },
        };
    },

    methods: {
        openEditModal(task) {
            this.selectedContractor = { ...task }; // Копируем объект
            this.isEditModalOpen = true;
        },
        openCreateModal() {
            // Сбрасываем форму
            this.newTaskForm.title = '';
            this.newTaskForm.description = '';
            this.newTaskForm.contractor = 1;
            this.newTaskForm.cost = '';
            this.newTaskForm.currency = 'RUB';
            this.isCreateModalOpen = true;
        },
        closeModals() {
            this.isEditModalOpen = false;
            this.isCreateModalOpen = false;
            this.selectedContractor = null;
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
            this.$inertia.post(route('tasks.store'), this.newTaskForm, {
                onSuccess: () => {
                    this.closeModals();
                },
            });
        },
    },
};
</script>

<template>
    <Head title="Задачи" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-[100rem] overflow-x-hidden rounded-md pb-5 pt-5"
        >
            <!-- Таблица -->
            <table
                v-if="tasks.length > 0"
                class="min-w-[100rem] divide-y divide-gray-200 rounded-md border border-gray-300 shadow-md"
            >
                <thead class="bg-neutral-200">
                    <tr>
                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            Статус
                        </th>

                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
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

                        <th class="w-0">
                            <button
                                class="m-2 rounded bg-green-500 px-3 py-1 text-sm text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                                @click="openCreateModal"
                            >
                                СОЗДАТЬ
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="task in tasks"
                        :key="task.id"
                        class="group transition-all duration-100 ease-in-out hover:bg-gray-100"
                    >
                        <td class="max-w-[5rem] text-sm text-gray-900">
                            <div
                                :class="[
                                    getStatusName(task.status) === 'NOT STARTED'
                                        ? 'bg-gray-200 text-gray-700'
                                        : '',
                                    getStatusName(task.status) === 'ONGOING'
                                        ? 'bg-blue-200 text-blue-700'
                                        : '',
                                    getStatusName(task.status) === 'ON HOLD'
                                        ? 'bg-yellow-200 text-yellow-700'
                                        : '',
                                    getStatusName(task.status) === 'DELAY'
                                        ? 'bg-red-200 text-red-700'
                                        : '',
                                    getStatusName(task.status) === 'DONE'
                                        ? 'bg-green-200 text-green-700'
                                        : '',
                                ]"
                                class="ml-5 w-fit rounded-md px-2 py-1 font-semibold"
                            >
                                {{ getStatusName(task.status) }}
                            </div>
                        </td>

                        <td
                            class="flex max-w-[10rem] justify-center px-4 py-2 text-sm"
                        >
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
                                class="flex min-h-8 w-[30px] items-center justify-center overflow-x-scroll rounded-md px-2 py-1 font-semibold"
                            >
                                {{ task.priority }}
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
                                class="flex min-h-8 items-center justify-center overflow-x-scroll"
                            >
                                {{ task.cost + ' ' + task.currency }}
                            </div>
                        </td>

                        <td class="text-center">
                            comments in progress...
                        </td>

                        <td></td>
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
                        v-model="newTaskForm.title"
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
                        v-model="newTaskForm.description"
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
                            v-model="newTaskForm.contractor"
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
                            v-model="newTaskForm.cost"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                            type="text"
                        />
                        <select
                            v-model="newTaskForm.currency"
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
    </AuthenticatedLayout>
</template>
