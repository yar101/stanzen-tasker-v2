<script>
import { useForm } from '@inertiajs/vue3';
import TaskCommentsModal from '@/Components/Tasks/TaskCommentsModal.vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { addIcons, OhVueIcon } from 'oh-vue-icons';
import { BiArrowUpSquareFill } from 'oh-vue-icons/icons';

addIcons(BiArrowUpSquareFill);
export default {
    components: {
        TaskCommentsModal,
        VueDatePicker,
        'v-icon': OhVueIcon,
    },
    inheritAttrs: false,
    props: {
        task: {
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
        errors: {
            type: Object,
        },
        comments: {
            type: Array,
            required: true,
        },
        users: {
            type: Array,
            required: true,
        },
        currentUserDepartment: {
            type: Object,
            required: true,
        },
    },

    emits: ['open-edit-modal', 'open-create-subtask-modal'],

    setup() {
        const format = (date) => {
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            return `${day}.${month}.${year}`;
        };

        return { format };
    },

    data() {
        return {
            // userStatuses: this.statuses.filter(
            //     (status) => status.name !== 'CLOSED',
            // ),
            isCommentsModalOpen: false,
            form: useForm({
                task_id: this.task.id,
                created_by: this.$page.props.auth.user.id,
                content: '',
            }),
            isShowPopup: false,
        };
    },

    methods: {
        isDeadlineApproaching(deadline) {
            if (!deadline) return false;
            const deadlineDate = new Date(deadline);
            const today = new Date();
            const twoDaysLater = today.setDate(today.getDate() + 2); // Add 2 days to today's date

            return deadlineDate <= twoDaysLater;
        },

        openEditModal() {
            this.$emit('open-edit-modal', this.task);
        },

        openCreateSubtaskModal() {
            this.$emit('open-create-subtask-modal', this.task);
        },

        closeCommentsModal() {
            this.isCommentsModalOpen = false;
        },

        updateStatus(task) {
            this.$inertia.patch(
                route('tasks.updateStatus', {
                    task,
                    status: task.status,
                }),
                {},
                {
                    preserveScroll: true,
                },
            );
        },

        updateDeadline(task) {
            const formattedDate = new Date(task.deadline_end)
                .toISOString()
                .split('T')[0];
            this.$inertia.patch(
                route('tasks.updateDeadline', {
                    task,
                    deadline_end: formattedDate,
                }),
                {},
                {
                    preserveScroll: true,
                },
            );
        },

        formatProgress(progressValue) {
            if (progressValue > 100) {
                this.task.progress = 100;
            }

            if (progressValue < 0) {
                this.task.progress = 0;
            }
        },

        updateProgress(task) {
            // this.formatProgress(task.progress);
            this.$inertia.patch(
                route('tasks.updateProgress', {
                    task,
                    progress: task.progress,
                }),
                {},
                {
                    preserveScroll: true,
                },
            );
        },

        updateProgressByButton(task, target) {
            const button = target.closest('button'); // Находим кнопку, ближайшую к месту клика

            if (button.classList.contains('progress-plus')) {
                task.progress += 10;
                this.formatProgress(task.progress);
                this.updateProgress(task);
            } else if (button.classList.contains('progress-minus')) {
                task.progress -= 10;
                this.formatProgress(task.progress);
                this.updateProgress(task);
            }
        },

        splitUserName(fullUserName) {
            const words = fullUserName.trim().split(' ');
            const initials = words
                .map((word) => word[0].toUpperCase())
                .join('');

            return initials;
        },

        getStatusName(statusId) {
            const status = this.statuses.find(
                (status) => status.id === statusId,
            );
            return status ? status.name : '';
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
    <tr
        :class="this.task.is_subtask === 0 ? 'bg-indigo-50' : ''"
        class="group transition-all duration-100 ease-in-out [&>td]:border-s-2 [&>td]:border-dotted [&>td]:border-gray-500/25"
    >
        <td
            :class="this.task.is_subtask === 1 ? 'invisible' : ''"
            class="w-fit !border-s-0 px-0.5 text-center text-xs text-gray-900"
        >
            <span class="">
                {{ this.task.id }}
            </span>
        </td>

        <td
            :class="this.task.is_subtask === 1 ? '!border-s-0' : ''"
            class="text-sm text-gray-900"
        >
            <div
                :class="this.task.is_subtask === 1 ? 'ml-[5rem]' : ''"
                class="flex w-fit max-w-[10rem] items-start gap-1"
            >
                <select
                    v-model="this.task.status"
                    :class="[
                        getStatusName(this.task.status) === 'NOT STARTED'
                            ? 'border-gray-500 bg-gray-200 text-gray-800 hover:bg-gray-300 hover:text-gray-900'
                            : '',
                        getStatusName(this.task.status) === 'ONGOING'
                            ? 'border-blue-500 bg-blue-200 text-blue-800 hover:bg-blue-300 hover:text-blue-900'
                            : '',
                        getStatusName(this.task.status) === 'ON HOLD'
                            ? 'border-yellow-500 bg-yellow-200 text-yellow-800 hover:bg-yellow-300 hover:text-yellow-900'
                            : '',
                        getStatusName(this.task.status) === 'DELAY'
                            ? 'border-red-500/70 bg-red-200 text-red-800 hover:bg-red-300 hover:text-red-900'
                            : '',
                        getStatusName(this.task.status) === 'DONE'
                            ? 'border-green-500 bg-green-200 text-green-800 hover:bg-green-300 hover:text-green-900'
                            : '',
                        getStatusName(this.task.status) === 'CLOSED'
                            ? 'border-amber-400 bg-neutral-800 text-amber-200 hover:bg-neutral-900 hover:text-amber-300'
                            : '',
                    ]"
                    class="ml-[0.5rem] rounded-md text-sm transition-colors focus:ring-0"
                    @change="updateStatus(this.task)"
                    :disabled="this.$page.props.auth.user.id !== this.task.manager"
                >
                    <option
                        v-for="status in this.statuses"
                        :key="status.id"
                        :hidden="
                            status.id === 6 &&
                            this.$page.props.auth.user.role.name === 'user'
                        "
                        :value="status.id"
                    >
                        {{ getStatusName(status.id) }}
                    </option>
                </select>
            </div>
        </td>

        <td class="text-sm">
            <div class="flex items-center justify-center">
                <span
                    :class="[
                        this.task.priority === 'I'
                            ? 'border border-red-500/70 bg-red-200 text-red-800'
                            : '',
                        this.task.priority === 'II'
                            ? 'border border-yellow-500 bg-yellow-200 text-yellow-800'
                            : '',
                        this.task.priority === 'III'
                            ? 'border border-green-500 bg-green-200 text-green-800'
                            : '',
                    ]"
                    class="flex h-[25px] w-[25px] items-center justify-center rounded text-center font-semibold"
                >
                    {{ this.task.priority }}
                </span>
            </div>
        </td>

        <td
            class="w-[2rem] text-sm text-gray-900"
            @mouseleave="isShowPopup = false"
        >
            <div
                class="flex h-[5rem] min-h-8 cursor-pointer flex-col items-center justify-center overflow-x-hidden overflow-y-hidden rounded-md text-center hover:bg-indigo-100"
                @click="isShowPopup = !isShowPopup"
            >
                <div
                    class="select-none transition-all duration-100 ease-in-out"
                >
                    {{
                        task.manager
                            ? splitUserName(getUserName(task.manager))
                            : ''
                    }}
                </div>
            </div>
            <!-- Всплывающее окно -->
            <div
                :class="
                    isShowPopup === true
                        ? 'translate-y-0 opacity-100'
                        : 'invisible translate-y-[-80px] opacity-0'
                "
                class="absolute select-none rounded border border-indigo-300 bg-indigo-200 px-3 py-1 text-sm text-black shadow-lg transition-all duration-100 ease-in-out"
            >
                {{ getUserName(task.manager) }}
            </div>
        </td>

        <td class="max-w-[15rem] break-words text-center text-sm text-gray-900">
            {{ task.contractor.name }}
        </td>

        <td
            class="max-w-[15rem] break-words px-2 text-center text-sm text-gray-900"
        >
            {{ task.title }}
        </td>

        <td
            class="max-w-[15rem] break-words p-1 text-center text-sm text-gray-900"
        >
            {{ task.description }}
        </td>

        <td>
            <div class="text-center text-sm text-gray-900">
                {{
                    new Date(this.task.deadline_start).toLocaleDateString(
                        'ru-RU',
                        {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                        },
                    )
                }}
            </div>
        </td>

        <td class="w-[190px] min-w-[190px] max-w-[11.5rem] px-5">
            <div class="flex items-center justify-center text-sm text-gray-900">
                <VueDatePicker
                    v-model="task.deadline_end"
                    :action-row="{ showNow: true, showPreview: false }"
                    :class="
                        isDeadlineApproaching(task.deadline_end)
                            ? 'box-border rounded-md border-[2px] border-red-400 shadow-lg shadow-red-500/50'
                            : ''
                    "
                    :clearable="false"
                    :enable-time-picker="false"
                    :format="format"
                    :min-date="new Date()"
                    cancel-text="Отмена"
                    class=""
                    locale="ru"
                    now-button-label="Сегодня"
                    select-text="Подтвердить"
                    @change="updateDeadline(task)"
                />
                <!--                {{-->
                <!--                    new Date(this.task.deadline_end).toLocaleDateString(-->
                <!--                        'ru-RU',-->
                <!--                        {-->
                <!--                            day: '2-digit',-->
                <!--                            month: '2-digit',-->
                <!--                            year: 'numeric',-->
                <!--                        },-->
                <!--                    )-->
                <!--                }}-->
                <!--                <input-->
                <!--                    v-model="this.task.deadline_end"-->
                <!--                                    :class="-->
                <!--                                        isDeadlineApproaching(task.deadline_end)-->
                <!--                                            ? 'bg-red-400 text-black shadow-md shadow-red-300 ring-1 ring-neutral-600/50 focus:bg-red-500'-->
                <!--                                            : ''-->
                <!--                                    "-->
                <!--                    class="rounded-md border-none text-sm ring-1 ring-neutral-500/50 transition-all duration-200 ease-in-out hover:shadow-md focus:bg-blue-100"-->
                <!--                    type="date"-->
                <!--                    @blur="updateDeadline(this.task)"-->
                <!--                />-->
            </div>
        </td>

        <td
            v-show="currentUserDepartment.name === 'Оборудование'"
            class="w-[5.5rem] px-1 text-center text-sm"
        >
            <div class="flex w-fit items-center gap-1">
                <input
                    v-model="this.task.progress"
                    :class="[
                        this.task.progress >= 0 && this.task.progress <= 25
                            ? 'border border-orange-500 bg-orange-500/70 text-neutral-900'
                            : '',
                        this.task.progress > 25 && this.task.progress <= 50
                            ? 'border border-amber-600 bg-amber-300 text-neutral-900'
                            : '',
                        this.task.progress > 50 && this.task.progress <= 85
                            ? 'border border-blue-600 bg-blue-500/70 text-neutral-900'
                            : '',
                        this.task.progress > 85 && this.task.progress <= 100
                            ? 'border border-green-600 bg-green-500/70 text-neutral-900'
                            : '',
                    ]"
                    class="mt-0 w-full rounded border-gray-300 text-center text-sm font-semibold shadow-sm transition-all focus:border-blue-500 focus:ring-0"
                    disabled
                    @update:model-value="updateProgress(this.task)"
                />
                <div class="flex-col items-center justify-center">
                    <div class="mb-1 flex items-center justify-center">
                        <button
                            class="progress-plus"
                            @click="
                                updateProgressByButton(this.task, $event.target)
                            "
                        >
                            <v-icon
                                class="rounded bg-green-700/70 shadow transition-all active:translate-y-[-2px] active:bg-green-500"
                                fill="#fff"
                                name="bi-arrow-up-square-fill"
                                scale="1.2"
                            />
                        </button>
                    </div>

                    <div class="flex items-center justify-center">
                        <button
                            class="progress-minus"
                            @click="
                                updateProgressByButton(this.task, $event.target)
                            "
                        >
                            <v-icon
                                class="rotate-180 rounded bg-red-700/70 shadow transition-all active:translate-y-[2px] active:bg-red-500"
                                fill="#fff"
                                name="bi-arrow-up-square-fill"
                                scale="1.2"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </td>

        <td
            v-show="currentUserDepartment.name !== 'Оборудование'"
            class="text-sm text-gray-900"
        >
            <div class="flex flex-col items-center justify-center">
                <div class="text-center">
                    {{ this.task.cost }}
                </div>
                <div class="text-center">
                    {{ this.task.currency }}
                </div>
            </div>
        </td>

        <td class="max-w-[20rem]">
            <!--                  Last comment-->
            <template v-if="task.comments && task.comments.length > 0">
                <div
                    v-if="task.comments.length > 1"
                    class="ml-1 mt-1 w-fit cursor-pointer rounded border border-blue-500 bg-blue-200 px-2 text-end text-sm font-medium text-blue-900 transition-all duration-100 ease-in-out hover:bg-blue-300"
                    @click="isCommentsModalOpen = true"
                >
                    {{ task.comments.length }}
                </div>
                <div
                    class="m-1 mt-2 cursor-pointer overflow-y-auto rounded-md border border-transparent p-1 text-sm text-neutral-900 transition-all duration-100 ease-in-out hover:translate-y-[-3px] hover:border-blue-600 hover:bg-blue-400/50 hover:shadow-md"
                    @click="isCommentsModalOpen = true"
                >
                    <!--                    <div-->
                    <!--                        class="flex justify-between rounded-md text-xs text-gray-900"-->
                    <!--                    >-->
                    <!--                        <div-->
                    <!--                            class="rounded-tl-md border-r border-gray-300 bg-amber-200 p-1"-->
                    <!--                        >-->
                    <!--                            {{ getUserName(task.comments[0].created_by) }}-->
                    <!--                        </div>-->
                    <!--                        <div class="p-1">-->
                    <!--                            {{-->
                    <!--                                new Date(-->
                    <!--                                    task.comments[0].created_at,-->
                    <!--                                ).toLocaleDateString('ru-RU', {-->
                    <!--                                    day: '2-digit',-->
                    <!--                                    month: '2-digit',-->
                    <!--                                    year: 'numeric',-->
                    <!--                                    hour: '2-digit',-->
                    <!--                                    minute: '2-digit',-->
                    <!--                                })-->
                    <!--                            }}-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <div
                        class="cursor-pointer p-1 text-start font-medium"
                        @click="isCommentsModalOpen = true"
                    >
                        {{ task.comments[0].content }}
                    </div>
                </div>
            </template>
        </td>

        <!--        Buttons-->
        <td class="">
            <div class="flex items-center justify-center gap-1">
                <!--                            Edit button-->
                <button
                    v-if="
                        this.$page.props.auth.user.id === task.manager ||
                        this.$page.props.auth.user.role.name !== 'user' ||
                        this.$page.props.auth.user.id === task.created_by
                    "
                    class="w-8 rounded-md border border-amber-300 bg-amber-100 p-1 transition-all duration-100 hover:bg-amber-200 hover:shadow-md"
                    @click="openEditModal(task)"
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
                                d="M11 4H7.2C6.0799 4 5.51984 4 5.09202 4.21799C4.71569 4.40974 4.40973 4.7157 4.21799 5.09202C4 5.51985 4 6.0799 4 7.2V16.8C4 17.9201 4 18.4802 4.21799 18.908C4.40973 19.2843 4.71569 19.5903 5.09202 19.782C5.51984 20 6.0799 20 7.2 20H16.8C17.9201 20 18.4802 20 18.908 19.782C19.2843 19.5903 19.5903 19.2843 19.782 18.908C20 18.4802 20 17.9201 20 16.8V12.5M15.5 5.5L18.3284 8.32843M10.7627 10.2373L17.411 3.58902C18.192 2.80797 19.4584 2.80797 20.2394 3.58902C21.0205 4.37007 21.0205 5.6364 20.2394 6.41745L13.3774 13.2794C12.6158 14.0411 12.235 14.4219 11.8012 14.7247C11.4162 14.9936 11.0009 15.2162 10.564 15.3882C10.0717 15.582 9.54378 15.6885 8.48793 15.9016L8 16L8.04745 15.6678C8.21536 14.4925 8.29932 13.9048 8.49029 13.3561C8.65975 12.8692 8.89125 12.4063 9.17906 11.9786C9.50341 11.4966 9.92319 11.0768 10.7627 10.2373Z"
                                stroke="#eba800"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                            ></path>
                        </g>
                    </svg>
                </button>
                <!--                            CommentButton-->

                <button
                    class="w-8 rounded-md border border-blue-300 bg-blue-100 p-1 transition-all duration-100 hover:bg-blue-200 hover:shadow-md"
                    @click="isCommentsModalOpen = true"
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
                                d="M10 9H17M10 13H17M7 9H7.01M7 13H7.01M21 20L17.6757 18.3378C17.4237 18.2118 17.2977 18.1488 17.1656 18.1044C17.0484 18.065 16.9277 18.0365 16.8052 18.0193C16.6672 18 16.5263 18 16.2446 18H6.2C5.07989 18 4.51984 18 4.09202 17.782C3.71569 17.5903 3.40973 17.2843 3.21799 16.908C3 16.4802 3 15.9201 3 14.8V7.2C3 6.07989 3 5.51984 3.21799 5.09202C3.40973 4.71569 3.71569 4.40973 4.09202 4.21799C4.51984 4 5.0799 4 6.2 4H17.8C18.9201 4 19.4802 4 19.908 4.21799C20.2843 4.40973 20.5903 4.71569 20.782 5.09202C21 5.51984 21 6.0799 21 7.2V20Z"
                                stroke="#1c71d8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                            ></path>
                        </g>
                    </svg>
                </button>

                <!--                                Subtask button-->

                <button
                    v-if="this.task.is_subtask === 0"
                    class="w-8 rounded-md border border-green-300 bg-green-100 p-1 transition-all duration-100 hover:bg-green-200 hover:shadow-md"
                    @click="openCreateSubtaskModal(this.task)"
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
                                d="M11 18C11 18.9319 11 19.3978 11.1522 19.7654C11.3552 20.2554 11.7446 20.6448 12.2346 20.8478C12.6022 21 13.0681 21 14 21H18C18.9319 21 19.3978 21 19.7654 20.8478C20.2554 20.6448 20.6448 20.2554 20.8478 19.7654C21 19.3978 21 18.9319 21 18C21 17.0681 21 16.6022 20.8478 16.2346C20.6448 15.7446 20.2554 15.3552 19.7654 15.1522C19.3978 15 18.9319 15 18 15H14C13.0681 15 12.6022 15 12.2346 15.1522C11.7446 15.3552 11.3552 15.7446 11.1522 16.2346C11 16.6022 11 17.0681 11 18ZM11 18H9.2C8.07989 18 7.51984 18 7.09202 17.782C6.71569 17.5903 6.40973 17.2843 6.21799 16.908C6 16.4802 6 15.9201 6 14.8V9M6 9H18C18.9319 9 19.3978 9 19.7654 8.84776C20.2554 8.64477 20.6448 8.25542 20.8478 7.76537C21 7.39782 21 6.93188 21 6C21 5.06812 21 4.60218 20.8478 4.23463C20.6448 3.74458 20.2554 3.35523 19.7654 3.15224C19.3978 3 18.9319 3 18 3H6C5.06812 3 4.60218 3 4.23463 3.15224C3.74458 3.35523 3.35523 3.74458 3.15224 4.23463C3 4.60218 3 5.06812 3 6C3 6.93188 3 7.39782 3.15224 7.76537C3.35523 8.25542 3.74458 8.64477 4.23463 8.84776C4.60218 9 5.06812 9 6 9Z"
                                stroke="#26a269"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                            ></path>
                        </g>
                    </svg>
                </button>
            </div>
        </td>
        <TaskCommentsModal
            :errors="errors"
            :isCommentsModalOpen="isCommentsModalOpen"
            :task="this.task"
            :users="users"
            @closeCommentModal="closeCommentsModal"
            @getUserName="getUserName"
        />
    </tr>
</template>
