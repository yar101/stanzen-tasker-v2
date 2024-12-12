<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, WhenVisible } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TasksTableRow from '@/Components/Tasks/TasksTableRow.vue';
import { addIcons, OhVueIcon } from 'oh-vue-icons';
import VueDatePicker from '@vuepic/vue-datepicker';
import Cookies from 'js-cookie';
import { ref } from 'vue';
import {
    BiBarChartLineFill,
    BiCurrencyExchange,
    FaCommentAlt,
    GiProgression,
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
    GiProgression,
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
            type: Array,
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
            type: Array,
            required: true,
        },
        currentUserRole: String,
        currentUserDepartment: Object,
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
        if (
            this.currentUserRole === 'head-of-department' ||
            this.currentUserRole === 'admin'
        ) {
            this.selectAllUsersInFilter();
        }
        document.addEventListener('mousedown', this.closeFilters);

        // Загрузка сохранённых статусов из куков при монтировании компонента
        const savedStatuses = Cookies.get('selectedStatuses');
        if (savedStatuses) {
            this.selectedStatuses = JSON.parse(savedStatuses);
        }
    },

    beforeUnmount() {
        document.removeEventListener('mousedown', this.closeFilters);
    },

    watch: {
        selectedStatuses: {
            handler(newValue) {
                Cookies.set('selectedStatuses', JSON.stringify(newValue), {
                    expires: 36500,
                });
            },
            deep: true,
        },
    },

    computed: {
        // Список контрагентов, отфильтрованный по запросу
        filteredContractors() {
            return this.contractors.filter((contractor) =>
                contractor.name
                    .toLowerCase()
                    .includes(this.contractorSearchTerm.toLowerCase()),
            );
        },
        // Имя выбранного контрагента для отображения
        selectedContractorName() {
            return this.selectedContractor ? this.selectedContractor.name : '';
        },

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
            isContractorSelectOpen: false, // Статус дропдауна
            contractorSearchTerm: '', // Текущий поисковый запрос
            selectedContractor: ref(null), // Выбранный контрагент

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
            selectedStatuses: [],

            filterByUser: false,
            selectedUsers: [this.$page.props.auth.user.id],
        };
    },

    methods: {
        toggleDropdown() {
            this.isContractorSelectOpen = !this.isContractorSelectOpen;
        },

        selectContractor(contractor) {
            this.selectedContractor = contractor;
            this.selectedTask.contractor = contractor;
            this.form.contractor = contractor.id; // Сохраняем ID выбранного контрагента
            this.isContractorSelectOpen = false; // Закрываем дропдаун
        },

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
            const contractorSelect = this.$refs.contractorSelect;

            if (contractorSelect && !contractorSelect.contains(event.target)) {
                this.isContractorSelectOpen = false;
            }

            if (statusModal && !statusModal.contains(event.target)) {
                this.filterByStatuses = false;
            }

            if (userModal && !userModal.contains(event.target)) {
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
            this.form.cost = 0;
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
            this.selectedTask.contractor = { ...task.contractor };
            this.form.contractor = task.contractor.id;
            this.form.cost = 0;
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
            this.isContractorSelectOpen = false;
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
                    preserveScroll: true,
                },
            );
        },

        getUserName(userId) {
            const user = this.users.find((user) => user.id === userId);
            if (userId === 1) return 'admin';
            return user ? user.name : '';
        },

        getContractorName(contractorId) {
            const contractor = this.contractors.find(
                (contractor) => contractor.id === contractorId,
            );
            if (contractorId === 1) return 'Без контрагента';
            return contractor ? contractor.name : '';
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
            class="mx-auto h-full overflow-x-auto overflow-y-scroll px-5 pb-5 pt-5"
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
                                    getStatusName(status.id) === 'CLOSED'
                                        ? 'border-amber-400 bg-neutral-800 text-amber-200 hover:bg-neutral-900 hover:text-amber-300'
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
            <div class="mx-auto w-full overflow-hidden rounded-md shadow-lg">
                <table
                    v-if="tasks.length > 0"
                    class="w-full table-auto overflow-scroll bg-white shadow-lg"
                >
                    <thead
                        class="border-b border-blue-200 bg-neutral-300/50"
                    >
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
                                <v-icon
                                    name="bi-bar-chart-line-fill"
                                    scale="1.7"
                                />
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
                                v-show="
                                    currentUserDepartment.name ===
                                    'Оборудование'
                                "
                                class="text-center text-sm font-medium text-gray-700"
                            >
                                <v-icon name="gi-progression" scale="1.5" />
                            </th>

                            <th
                                v-show="
                                    currentUserDepartment.name !==
                                    'Оборудование'
                                "
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
                    <tbody class="divide-y divide-gray-200">
                        <template v-for="task in filteredTasks" :key="task.id">
                            <WhenVisible always as="span" data="task">
                                <template #fallback>
                                    <tr>
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
                                    :currentUserDepartment="
                                        currentUserDepartment
                                    "
                                    :errors="errors"
                                    :statuses="statuses"
                                    :task="task"
                                    :users="users"
                                    @open-edit-modal="openEditModal"
                                    @open-create-subtask-modal="
                                        openCreateSubtaskModal
                                    "
                                />
                                <template
                                    v-if="
                                        task.subtasks &&
                                        task.subtasks.length > 0
                                    "
                                >
                                    <TasksTableRow
                                        v-for="subtask in task.subtasks"
                                        :key="subtask.id"
                                        :comments="subtask.comments"
                                        :contractors="contractors"
                                        :currentUserDepartment="
                                            currentUserDepartment
                                        "
                                        :errors="errors"
                                        :statuses="statuses"
                                        :task="subtask"
                                        :users="users"
                                        @open-edit-modal="openEditModal"
                                    />
                                </template>
                            </WhenVisible>
                        </template>
                    </tbody>
                </table>
            </div>

            <div
                v-if="filteredTasks.length === 0 || tasks.length === 0"
                class="mx-auto mb-10 mt-10 flex h-[20rem] w-[40rem] flex-col items-center justify-evenly rounded-md border-2 border-dotted border-gray-300 bg-neutral-200/50 px-4 py-2 text-center text-xl text-red-400 shadow-xl"
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
                        <div
                            class="z-10 flex w-full items-center justify-center"
                        >
                            <div class="group relative w-full">
                                <button
                                    class="inline-flex w-full justify-between rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-0"
                                    @click.prevent="toggleDropdown"
                                >
                                    <span>{{
                                        selectedContractorName ||
                                        'Выберите контрагента'
                                    }}</span>
                                    <svg
                                        aria-hidden="true"
                                        class="-mr-1 ml-2 h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            clip-rule="evenodd"
                                            d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                            fill-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div
                                    v-show="isContractorSelectOpen"
                                    ref="contractorSelect"
                                    class="absolute left-0 right-0 mt-2 max-h-48 w-full overflow-y-auto rounded-md border border-gray-300 bg-gray-100 pr-1 shadow-lg"
                                >
                                    <div class="sticky top-0 bg-gray-100 p-1">
                                        <div class="backdrop-blur">
                                            <!-- Поле поиска -->
                                            <input
                                                v-model="contractorSearchTerm"
                                                autocomplete="off"
                                                class="h-[2rem] w-full rounded-md border border-gray-300 bg-gray-100 px-2 text-sm text-gray-800 outline-none ring-0 transition-all duration-100 ease-in-out focus:shadow-xl focus:shadow-blue-100/50"
                                                placeholder="Поиск..."
                                                type="text"
                                            />
                                        </div>
                                    </div>
                                    <!-- Список контрагентов -->
                                    <span
                                        v-for="contractor in filteredContractors"
                                        :key="contractor.id"
                                        class="m-1 block cursor-pointer rounded-md px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 active:bg-blue-100"
                                        @click="selectContractor(contractor)"
                                    >
                                        {{ contractor.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

                <div
                    v-show="currentUserDepartment.name !== 'Оборудование'"
                    class="mb-4"
                >
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Стоимость
                    </InputLabel>
                    <div class="flex gap-2">
                        <TextInput
                            v-model="form.cost"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                            @click="$event.target.value = null"
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
                        <div
                            class="z-10 flex w-full items-center justify-center"
                        >
                            <div class="group relative w-full">
                                <button
                                    class="inline-flex w-full justify-between rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-0 disabled:bg-neutral-200"
                                    disabled
                                    @click.prevent="toggleDropdown"
                                >
                                    <span>{{
                                        selectedTask.contractor.name ||
                                        selectedContractorName ||
                                        'Выберите контрагента'
                                    }}</span>
                                    <svg
                                        aria-hidden="true"
                                        class="-mr-1 ml-2 h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            clip-rule="evenodd"
                                            d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                            fill-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div
                                    v-show="isContractorSelectOpen"
                                    ref="contractorSelect"
                                    class="absolute left-0 right-0 mt-2 max-h-48 w-full overflow-y-auto rounded-md border border-gray-300 bg-gray-100 pr-1 shadow-lg"
                                >
                                    <div class="sticky top-0 bg-gray-100 p-1">
                                        <div class="backdrop-blur">
                                            <!-- Поле поиска -->
                                            <input
                                                v-model="contractorSearchTerm"
                                                autocomplete="off"
                                                class="h-[2rem] w-full rounded-md border border-gray-300 bg-gray-100 px-2 text-sm text-gray-800 outline-none ring-0 transition-all duration-100 ease-in-out focus:shadow-xl focus:shadow-blue-100/50"
                                                placeholder="Поиск..."
                                                type="text"
                                            />
                                        </div>
                                    </div>
                                    <!-- Список контрагентов -->
                                    <span
                                        v-for="contractor in filteredContractors"
                                        :key="contractor.id"
                                        class="m-1 block cursor-pointer rounded-md px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 active:bg-blue-100"
                                        @click="selectContractor(contractor)"
                                    >
                                        {{ contractor.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

                <div
                    v-show="currentUserDepartment.name !== 'Оборудование'"
                    class="mb-4"
                >
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Стоимость
                    </InputLabel>
                    <div class="flex gap-2">
                        <TextInput
                            v-model="form.cost"
                            class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0"
                            @click="$event.target.value = null"
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
                        <div
                            class="z-10 flex w-full items-center justify-center"
                        >
                            <div class="group relative w-full">
                                <button
                                    :disabled="form.is_subtask"
                                    class="inline-flex w-full justify-between rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-0 disabled:bg-neutral-200"
                                    @click.prevent="toggleDropdown"
                                >
                                    <span>{{
                                        selectedTask.contractor.name
                                    }}</span>
                                    <svg
                                        aria-hidden="true"
                                        class="-mr-1 ml-2 h-5 w-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            clip-rule="evenodd"
                                            d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                            fill-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div
                                    v-show="isContractorSelectOpen"
                                    ref="contractorSelect"
                                    class="absolute left-0 right-0 mt-2 max-h-48 w-full overflow-y-auto rounded-md border border-gray-300 bg-gray-100 pr-1 shadow-lg"
                                >
                                    <div class="sticky top-0 bg-gray-100 p-1">
                                        <div class="backdrop-blur">
                                            <!-- Поле поиска -->
                                            <input
                                                v-model="contractorSearchTerm"
                                                autocomplete="off"
                                                class="h-[2rem] w-full rounded-md border border-gray-300 bg-gray-100 px-2 text-sm text-gray-800 outline-none ring-0 transition-all duration-100 ease-in-out focus:shadow-xl focus:shadow-blue-100/50"
                                                placeholder="Поиск..."
                                                type="text"
                                            />
                                        </div>
                                    </div>
                                    <!-- Список контрагентов -->
                                    <span
                                        v-for="contractor in filteredContractors"
                                        :key="contractor.id"
                                        class="m-1 block cursor-pointer rounded-md px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 active:bg-blue-100"
                                        @click="selectContractor(contractor)"
                                    >
                                        {{ contractor.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError :message="errors.contractor" class="mt-2" />
                </div>

                <div
                    v-show="currentUserDepartment.name !== 'Оборудование'"
                    class="mb-4"
                >
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
