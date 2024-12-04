<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, WhenVisible } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TasksTableRow from '@/Components/Tasks/TasksTableRow.vue';
import { addIcons, OhVueIcon } from 'oh-vue-icons';
import VueDatePicker from '@vuepic/vue-datepicker';
import {
    BiBarChartLineFill,
    BiCurrencyExchange,
    FaCommentAlt,
    IoPersonSharp,
    MdDownloadingRound,
    MdErroroutlineRound,
} from 'oh-vue-icons/icons';

addIcons(
    IoPersonSharp,
    BiCurrencyExchange,
    FaCommentAlt,
    BiBarChartLineFill,
    MdErroroutlineRound,
    MdDownloadingRound,
);
export default {
    components: {
        WhenVisible,
        TasksTableRow,
        InputLabel,
        TextInput,
        InputError,
        AuthenticatedLayout,
        VueDatePicker,
        'v-icon': OhVueIcon,
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
        currentUserRole: String,
    },

    setup() {
        const format = (date) => {
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            return `${day}.${month}.${year}`;
        };

        return { format };
    },

    mounted() {
        if (this.currentUserRole === 'head-of-department') {
            this.selectAllUsersInFilter();
        }
        document.addEventListener('mousedown', this.closeFilters);
    },

    beforeUnmount() {
        document.removeEventListener('mousedown', this.closeFilters);
    },

    computed: {
        filteredTasks() {
            // Создаём глубокую копию задач с вложенными подзадачами
            let filteredTasks = this.tasks.map((task) => ({
                ...task,
                subtasks:
                    task.subtasks?.map((subtask) => ({ ...subtask })) || [],
            }));

            // Фильтрация по статусам
            if (this.selectedStatuses.length > 0) {
                filteredTasks = filteredTasks
                    .map((task) => ({
                        ...task,
                        subtasks: task.subtasks.filter((subtask) =>
                            this.selectedStatuses.includes(subtask.status),
                        ),
                    }))
                    .filter(
                        (task) =>
                            this.selectedStatuses.includes(task.status) ||
                            task.subtasks.length > 0,
                    );
            }

            // Фильтрация по пользователям
            if (this.selectedUsers.length > 0) {
                filteredTasks = filteredTasks
                    .map((task) => ({
                        ...task,
                        subtasks: task.subtasks.filter((subtask) =>
                            this.selectedUsers.includes(subtask.manager),
                        ),
                    }))
                    .filter(
                        (task) =>
                            this.selectedUsers.includes(task.manager) ||
                            task.subtasks.length > 0,
                    );
            }

            // Фильтрация по текстовому запросу
            const query = this.searchQuery.toLowerCase();
            filteredTasks = filteredTasks
                .map((task) => ({
                    ...task,
                    subtasks: task.subtasks.filter((subtask) => {
                        return (
                            subtask.title?.toLowerCase().includes(query) ||
                            subtask.description
                                ?.toLowerCase()
                                .includes(query) ||
                            subtask.contractor?.name
                                ?.toLowerCase()
                                .includes(query) ||
                            subtask.id?.toString().toLowerCase().includes(query)
                        );
                    }),
                }))
                .filter((task) => {
                    const matchesTask =
                        task.title?.toLowerCase().includes(query) ||
                        '' ||
                        task.description?.toLowerCase().includes(query) ||
                        '' ||
                        task.contractor?.name?.toLowerCase().includes(query) ||
                        '' ||
                        task.id?.toString().toLowerCase().includes(query);

                    return matchesTask || task.subtasks.length > 0;
                });

            return filteredTasks;
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
                cost: 0,
                currency: 'RUB',
                deadline_end: new Date(),
                priority: 'III',
            }),

            searchQuery: '',

            filterByStatuses: false,
            selectedStatuses: [1, 2, 3, 5],

            filterByUser: false,
            selectedUsers: [this.$page.props.auth.user.id],
        };
    },

    methods: {
        isToday(date) {
            if (!date) return false;
            const today = new Date();
            const targetDate = new Date(date);
            return (
                today.getFullYear() === targetDate.getFullYear() &&
                today.getMonth() === targetDate.getMonth() &&
                today.getDate() === targetDate.getDate()
            );
        },

        isDeadlineApproaching(deadline) {
            if (!deadline) return false;
            const deadlineDate = new Date(deadline);
            const today = new Date();
            const twoDaysLater = today.setDate(today.getDate() + 2); // Add 2 days to today's date

            return deadlineDate <= twoDaysLater;
        },

        closeFilters(event) {
            const statusModal = this.$refs.filterByStatusesModal;
            const userModal = this.$refs.filterByUserModal;

            if (
                statusModal &&
                !statusModal.contains(event.target) &&
                userModal &&
                !userModal.contains(event.target)
            ) {
                this.filterByStatuses = false;
                this.filterByUser = false;
            }
        },

        selectAllUsersInFilter() {
            this.selectedUsers = this.users.map((user) => user.id);
        },

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
            this.form.contractor = task.contractor.id;
            if (task.is_subtask) {
                this.form.contractor = task.contractor;
            }
            this.form.cost = task.cost;
            this.form.manager = task.manager;
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
            this.form.manager = this.$page.props.auth.user.id;
            this.form.currency = 'RUB';
            this.form.priority = 'III';
            this.isCreateModalOpen = true;
        },

        openCreateSubtaskModal(task) {
            this.selectedTask = { ...task };
            this.form.parent_task = task.id;
            this.form.title = task.title;
            this.form.description = '';
            this.form.contractor = task.contractor.id;
            this.form.cost = null;
            this.form.manager = task.manager;
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

        getUserName(userId) {
            const user = this.users.find((user) => user.id === userId);
            if (userId === 1) return 'admin';
            return user ? user.name : '';
        },
    },
};
</script>

<template>
    <Head title="Задачи" />

    <AuthenticatedLayout>
        <template #nav-buttons>
            <div :class="tasks.length === 0 ? 'hidden' : ''" class="">
                <button
                    class="m-2 rounded bg-green-500 px-3 py-1 text-sm text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                    @click="openCreateModal"
                >
                    Создать задачу
                </button>
            </div>
        </template>

        <div
            class="mx-auto h-screen overflow-x-auto overflow-y-scroll px-5 pb-5 pt-5"
        >
            <!--            Фильтры-->
            <div
                :class="tasks.length === 0 ? 'hidden' : ''"
                class="mb-5 flex items-center justify-start gap-5 text-sm"
            >
                <!--                По статусу-->
                <div class="">
                    <button
                        class="rounded-md bg-amber-300/80 px-3 py-1 text-sm font-medium shadow-md transition-all duration-100 ease-in-out hover:bg-amber-400/80 active:translate-y-[3px] active:ring-0"
                        @click="filterByStatuses = !filterByStatuses"
                    >
                        Фильтр по статусу
                    </button>
                    <div
                        ref="filterByStatusesModal"
                        :class="
                            filterByStatuses
                                ? 'block translate-x-0'
                                : 'translate-x-[-1000px]'
                        "
                        class="absolute mt-2 flex w-fit flex-col gap-2 rounded-md border border-blue-500 bg-blue-600/30 px-3 py-3 shadow-md backdrop-blur-lg transition-all duration-200"
                    >
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="w-full"
                        >
                            <label
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
                                class="flex select-none items-center gap-1 rounded border bg-opacity-90 p-1 text-sm font-semibold transition-all duration-100 ease-in-out hover:shadow-md"
                            >
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

                <!--                Фильтр по пользователю-->
                <div class="">
                    <button
                        class="rounded-md bg-amber-300/80 px-3 py-1 text-sm font-medium shadow-md transition-all duration-100 ease-in-out hover:bg-amber-400/80 active:translate-y-[3px] active:ring-0"
                        @click.stop="filterByUser = !filterByUser"
                    >
                        Фильтр по исполнителю
                    </button>
                    <div
                        ref="filterByUserModal"
                        :class="
                            filterByUser
                                ? 'block translate-x-0'
                                : 'translate-x-[-1000px]'
                        "
                        class="absolute mt-2 flex w-fit flex-col gap-2 rounded-md border border-blue-500 bg-blue-600/30 px-3 py-3 shadow-md backdrop-blur-lg transition-all duration-200"
                    >
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="w-full"
                        >
                            <label
                                :class="[
                                    selectedUsers.includes(user.id)
                                        ? 'border-amber-500 bg-amber-200 text-amber-900 hover:bg-amber-300 hover:text-amber-900'
                                        : 'border-amber-300 bg-gray-100/60 text-gray-900 hover:bg-amber-200 hover:text-amber-900',
                                ]"
                                class="flex select-none items-center gap-1 rounded border p-1 text-sm font-medium transition-all duration-100 ease-in-out hover:shadow-md"
                            >
                                <input
                                    v-model="selectedUsers"
                                    :value="user.id"
                                    class="h-[14px] w-[13px] border border-gray-400 bg-neutral-100 text-blue-600 checked:bg-blue-500 hover:checked:bg-blue-600 focus:ring-0 focus:checked:bg-blue-500"
                                    type="checkbox"
                                />
                                {{ user.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <!--                Поиск-->
                <div class="flex w-[20rem] items-center">
                    <div
                        class="group flex h-8 w-6 items-center justify-center rounded-l-md bg-blue-200"
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
                                    d="M11 6C13.7614 6 16 8.23858 16 11M16.6588 16.6549L21 21M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z"
                                    stroke="#1c71d8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.488"
                                ></path>
                            </g>
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        class="h-8 w-full rounded-r-md border border-blue-200 px-2 text-sm text-gray-700 outline-none transition-all focus:border-none focus:bg-blue-200 focus:shadow-xl focus:ring-0"
                        placeholder="Поиск..."
                        type="text"
                    />
                </div>
            </div>
            <!-- Таблица -->
            <table
                v-if="tasks.length > 0"
                class="w-full table-auto overflow-scroll rounded-md border-none border-transparent bg-white shadow-lg"
            >
                <thead class="bg-neutral-200">
                    <tr>
                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            ID
                        </th>
                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Статус
                        </th>

                        <th class="text-gray-700">
                            <v-icon name="bi-bar-chart-line-fill" scale="1.7" />
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            <v-icon name="io-person-sharp" scale="1.5" />
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Контрагент
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Тема
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Описание
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Дата начала
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            Дедлайн
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            <v-icon name="bi-currency-exchange" scale="2" />
                        </th>

                        <th
                            class="text-center text-sm font-medium text-gray-700"
                        >
                            <v-icon name="fa-comment-alt" scale="1.5" />
                        </th>

                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template v-for="task in filteredTasks" :key="task.id">
                        <WhenVisible always data="task">
                            <template #fallback>
                                <tr
                                    class="group transition-all duration-100 ease-in-out [&>td]:border-s-2 [&>td]:border-dotted [&>td]:border-gray-500"
                                >
                                    <td class="" colspan="12">
                                        Задача {{ task.id }}
                                        загружается
                                        <v-icon
                                            class="text-blue-500"
                                            name="md-downloading-round"
                                        />
                                    </td>
                                </tr>
                            </template>
                            <TasksTableRow
                                v-if="!task.is_subtask"
                                :comments="task.comments"
                                :contractors="contractors"
                                :errors="errors"
                                :statuses="statuses"
                                :task="task"
                                :users="users"
                                @open-edit-modal="openEditModal"
                                @open-create-subtask-modal="
                                    openCreateSubtaskModal
                                "
                                @update-status="updateStatus"
                                @update-deadline="updateDeadline"
                            />
                        </WhenVisible>
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
                v-if="filteredTasks.length === 0 || tasks.length === 0"
                class="mx-auto mb-10 mt-10 flex h-[20rem] w-[40rem] flex-col items-center justify-evenly rounded-md border-2 border-dotted border-gray-300 bg-neutral-200/50 px-4 py-2 text-center text-xl text-gray-500 text-red-400 shadow-xl"
            >
                Задачи не найдены
                <v-icon name="md-erroroutline-round" scale="5" />
                <button
                    class="text-md m-2 w-[200px] rounded bg-green-500 px-3 py-1 text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                    @click="openCreateModal"
                >
                    Создать задачу
                </button>
            </div>
        </div>

        <!-- Модальное окно создания задачи -->
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

                <div v-if="currentUserRole !== 'user'" class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Менеджер
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.manager"
                            :disabled="currentUserRole === 'user'"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
                        >
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <InputError :message="errors.manager" class="mt-2" />
                </div>

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
                    <InputError :message="errors.currency" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Дедлайн
                        <span v-if="isToday(form.deadline_end)"
                            >установлен на
                            <span
                                class="rounded border border-red-700 bg-red-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >сегодняшний день</span
                            ></span
                        >
                        <span
                            v-else-if="isDeadlineApproaching(form.deadline_end)"
                            ><b
                                class="rounded border border-red-700 bg-red-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >скоро наступит</b
                            ></span
                        >
                        <span v-else
                            ><b
                                class="rounded border border-green-700 bg-green-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >установлен более чем на 2 дня</b
                            ></span
                        >
                    </InputLabel>
                    <div class="mt-2">
                        <VueDatePicker
                            v-model="form.deadline_end"
                            :action-row="{ showNow: true, showPreview: false }"
                            :class="
                                isDeadlineApproaching(form.deadline_end)
                                    ? 'box-border rounded-md border-[2px] border-red-400 shadow-lg shadow-red-500/50'
                                    : 'box-border'
                            "
                            :clearable="false"
                            :enable-time-picker="false"
                            :format="format"
                            :min-date="new Date()"
                            cancel-text="Отмена"
                            locale="ru"
                            now-button-label="Сегодня"
                            select-text="Подтвердить"
                        />
                    </div>
                    <InputError :message="errors.deadline_end" class="mt-2" />
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

                <div v-if="currentUserRole !== 'user'" class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Менеджер
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.manager"
                            :disabled="currentUserRole === 'user'"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
                        >
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

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
                        Дедлайн
                        <span v-if="isToday(form.deadline_end)"
                            >установлен на
                            <span
                                class="rounded border border-red-700 bg-red-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >сегодняшний день</span
                            ></span
                        >
                        <span
                            v-else-if="isDeadlineApproaching(form.deadline_end)"
                            ><b
                                class="rounded border border-red-700 bg-red-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >скоро наступит</b
                            ></span
                        >
                        <span v-else
                            ><b
                                class="rounded border border-green-700 bg-green-100 px-2 py-0.5 font-semibold text-neutral-800"
                                >установлен более чем на 2 дня</b
                            ></span
                        >
                    </InputLabel>
                    <div class="mt-2">
                        <VueDatePicker
                            v-model="form.deadline_end"
                            :action-row="{ showNow: true, showPreview: false }"
                            :class="
                                isDeadlineApproaching(form.deadline_end)
                                    ? 'box-border rounded-md border-[2px] border-red-400 shadow-lg shadow-red-500/50'
                                    : 'box-border'
                            "
                            :clearable="false"
                            :enable-time-picker="false"
                            :format="format"
                            :min-date="new Date()"
                            cancel-text="Отмена"
                            locale="ru"
                            now-button-label="Сегодня"
                            select-text="Подтвердить"
                        />
                    </div>
                    <InputError :message="errors.deadline_end" class="mt-2" />
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

                <div v-if="currentUserRole !== 'user'" class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Менеджер
                    </InputLabel>
                    <div class="flex gap-2">
                        <select
                            v-model="form.manager"
                            :disabled="currentUserRole === 'user'"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
                        >
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <InputError :message="errors.manager" class="mt-2" />
                </div>

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
