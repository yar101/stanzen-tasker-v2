<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TasksTableRow from '@/Components/Tasks/TasksTableRow.vue';

export default {
    components: {
        TasksTableRow,
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
        users: {
            type: Object,
            required: true,
        },
    },

    computed: {
        filteredTasks() {
            // Фильтрация задач на основе выбранного статуса
            if (this.selectedStatuses.length > 0) {
                return this.tasks.filter((task) =>
                    this.selectedStatuses.includes(task.status),
                );
            }
            return this.tasks; // Если фильтр не выбран, возвращаем все задачи
        },
    },

    data() {
        return {
            isEditModalOpen: false,
            isCreateModalOpen: false,
            isCreateSubtaskModalOpen: false,
            selectedTask: null,
            form: useForm({
                parent_task: null,
                title: '',
                description: '',
                contractor: 1,
                cost: 0.0,
                currency: 'RUB',
            }),
            selectedStatuses: [2, 3, 4],
            // eslint-disable-next-line vue/no-dupe-keys
            // tasks: this.tasks,
            filterByStatuses: false,
        };
    },

    methods: {
        getStatusName(statusId) {
            const status = this.statuses.find(
                (status) => status.id === statusId,
            );
            return status ? status.name : '';
        },
        openEditModal(task) {
            this.selectedTask = { ...task }; // Копируем объект
            this.form.parent_task = task.parent_task;
            this.form.title = String(task.title);
            this.form.description = task.description;
            this.form.contractor = task.contractor;
            this.form.cost = task.cost;
            this.form.currency = task.currency;
            this.form.priority = task.priority;
            this.form.is_subtask = task.is_subtask;
            this.isEditModalOpen = true;
        },
        openCreateModal() {
            // Сбрасываем форму
            this.form.parent_task = null;
            this.form.title = '';
            this.form.description = '';
            this.form.contractor = 1;
            this.form.cost = '';
            this.form.currency = 'RUB';
            this.form.priority = 'III';
            this.isCreateModalOpen = true;
        },

        openCreateSubtaskModal(task) {
            this.selectedTask = { ...task };
            this.form.parent_task = task.id;
            this.form.title = task.title;
            this.form.description = '';
            this.form.contractor = task.contractor;
            this.form.cost = task.cost;
            this.form.currency = task.currency;
            this.form.priority = task.priority;
            this.isCreateSubtaskModalOpen = true;
        },

        closeModals() {
            this.isEditModalOpen = false;
            this.isCreateModalOpen = false;
            this.selectedTask = null;
            this.isCreateSubtaskModalOpen = false;
            this.form.clearErrors();
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

        updateDeadline(task) {
            this.$inertia.patch(
                route('tasks.updateDeadline', {
                    task,
                    deadline_end: task.deadline_end,
                }),
            );
        },
    },
};
</script>

<template>
    <Head title="Задачи" />

    <AuthenticatedLayout>
        <template #nav-buttons>
            <div class="">
                <button
                    class="m-2 rounded bg-green-500 px-3 py-1 text-sm text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                    @click="openCreateModal"
                >
                    Создать задачу
                </button>
            </div>
        </template>

        <div class="mx-auto overflow-x-auto overflow-y-scroll px-5 pb-5 pt-5">
            <!-- Фильтр по статусу -->
            <!--            <select v-model="selectedStatuses" multiple @change="filterTasks">-->
            <!--                <option value="">Все</option>-->
            <!--                <option-->
            <!--                    v-for="status in statuses"-->
            <!--                    :key="status.id"-->
            <!--                    :value="status.id"-->
            <!--                >-->
            <!--                    {{ status.name }}-->
            <!--                </option>-->
            <!--            </select>-->

            <div class="flex gap-5">
                <div class="mb-5">
                    <button
                        class="rounded-md bg-blue-300 px-3 py-1 text-sm shadow-md transition-all duration-200 ease-in-out hover:bg-blue-400 active:translate-y-[3px] active:ring-0"
                        @click="filterByStatuses = !filterByStatuses"
                    >
                        Фильтр по статусу
                    </button>
                    <!-- Фильтр по статусам с чекбоксами -->
                    <div
                        :class="
                            filterByStatuses
                                ? 'block translate-x-0'
                                : 'translate-x-[-3000px]'
                        "
                        class="absolute ml-2 mt-2 w-fit rounded-md border border-blue-500 bg-blue-600/30 px-3 py-1 shadow-md backdrop-blur-lg transition-all duration-200"
                    >
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            :class="[
                                getStatusName(status.id) === 'NOT STARTED'
                                    ? 'border-gray-500 bg-gray-200 text-gray-800 hover:bg-gray-300 hover:text-gray-900'
                                    : '',
                                getStatusName(status.id) === 'ONGOING'
                                    ? 'border-blue-500 bg-blue-200 text-blue-800 hover:bg-blue-300 hover:text-blue-900'
                                    : '',
                                getStatusName(status.id) === 'ON HOLD'
                                    ? 'border-yellow-500 bg-yellow-200 text-yellow-800 hover:bg-yellow-300 hover:text-yellow-900'
                                    : '',
                                getStatusName(status.id) === 'DELAY'
                                    ? 'border-red-500/70 bg-red-200 text-red-800 hover:bg-red-300 hover:text-red-900'
                                    : '',
                                getStatusName(status.id) === 'DONE'
                                    ? 'border-green-500 bg-green-200 text-green-800 hover:bg-green-300 hover:text-green-900'
                                    : '',
                            ]"
                            class="mb-1 rounded border bg-opacity-90 p-1 text-sm shadow-sm"
                        >
                            <label>
                                <input
                                    v-model="selectedStatuses"
                                    :value="status.id"
                                    class="h-[14px] w-[13px] border border-gray-400 bg-neutral-100 text-blue-600 focus:ring-0"
                                    type="checkbox"
                                />
                                {{ status.name }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Таблица -->
            <table
                v-if="tasks.length > 0"
                class="w-full table-auto rounded-md border border-gray-300 shadow-md"
            >
                <thead class="border-b border-gray-300 bg-neutral-200">
                    <tr>
                        <th
                            class="px-4 py-2 text-center text-sm font-medium text-gray-700"
                        >
                            ID
                        </th>
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

                        <th class=""></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template v-for="task in filteredTasks" :key="task.id">
                        <TasksTableRow
                            v-if="!task.is_subtask"
                            :comments="task.comments"
                            :contractors="contractors"
                            :errors="errors"
                            :statuses="statuses"
                            :task="task"
                            :users="users"
                            @open-edit-modal="openEditModal"
                            @open-create-subtask-modal="openCreateSubtaskModal"
                            @update-status="updateStatus"
                            @update-deadline="updateDeadline"
                        />
                        <template
                            v-if="task.subtasks && task.subtasks.length > 0"
                        >
                            <TasksTableRow
                                v-for="subtask in task.subtasks"
                                :key="subtask.id"
                                :comments="subtask.comments"
                                :contractors="contractors"
                                :errors="errors"
                                :statuses="statuses"
                                :task="subtask"
                                :users="users"
                                @open-edit-modal="openEditModal"
                                @update-status="updateStatus"
                                @update-deadline="updateDeadline"
                            />
                            <tr class="h-2.5 border-none bg-gray-100"></tr>
                        </template>
                    </template>
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
                    <InputError :message="errors.priority" class="mt-2" />
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

        <!--        Модальное окно создания ПОДЗАДАЧИ-->

        <div
            v-if="isCreateSubtaskModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <form
                class="w-[30rem] rounded-md bg-white p-5 shadow-lg"
                @submit.prevent="store"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-800">
                    Добавить подзадачу
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
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
                            disabled
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
                    <InputError :message="errors.priority" class="mt-2" />
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
                    <span v-if="this.form.is_subtask">
                        Редактирование подзадачи
                    </span>
                    <span v-else>Редактирование задачи</span>
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
                            :disabled="selectedTask.contractor !== 1"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
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
                    <InputError :message="errors.priority" class="mt-2" />
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
