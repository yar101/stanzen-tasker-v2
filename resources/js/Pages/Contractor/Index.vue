<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, WhenVisible } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { OhVueIcon } from 'oh-vue-icons';

export default {
    // eslint-disable-next-line vue/no-reserved-component-names
    components: {
        'v-icon': OhVueIcon,
        WhenVisible,
        TextInput,
        InputLabel,
        InputError,
        AuthenticatedLayout,
        Head,
    },
    props: {
        contractors: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
        currentUserRole: String,
    },
    data() {
        return {
            isEditModalOpen: false,
            isCreateModalOpen: false,
            selectedContractor: null,
            newContractorForm: {
                name: '',
            },
            searchQuery: '',
        };
    },

    computed: {
        filteredContractors() {
            // Фильтруем по имени и описанию
            const query = this.searchQuery.toLowerCase();
            return this.contractors.filter((contractor) => {
                return contractor.name.toLowerCase().includes(query);
                // ||
                // contractor.description.toLowerCase().includes(query)
            });
        },
    },

    methods: {
        openEditModal(contractor) {
            this.selectedContractor = { ...contractor }; // Копируем объект
            this.isEditModalOpen = true;
        },
        openCreateModal() {
            this.newContractorForm.name = ''; // Сбрасываем форму
            this.isCreateModalOpen = true;
        },
        closeModals() {
            this.isEditModalOpen = false;
            this.isCreateModalOpen = false;
            this.selectedContractor = null;
        },
        store() {
            this.$inertia.post(
                route('contractors.store'),
                this.newContractorForm,
                {
                    onSuccess: () => {
                        this.closeModals();
                    },
                },
            );
        },
        update() {
            this.$inertia.put(
                route('contractors.update', this.selectedContractor.id),
                this.selectedContractor,
                {
                    onSuccess: () => {
                        this.closeModals();
                    },
                },
            );
        },
        destroy(id, name) {
            if (
                confirm(
                    'Вы действительно хотите удалить контрагента `' +
                        name +
                        '`?',
                )
            ) {
                this.$inertia.delete(route('contractors.destroy', id));
                this.closeModals();
            }
        },
    },
};
</script>

<template>
    <Head title="Контрагенты" />
    <AuthenticatedLayout>
        <div class="mx-auto w-fit overflow-hidden rounded-md pb-5 pt-5">
            <!-- Таблица -->
            <table
                class="m-5 min-w-[38rem] divide-y divide-gray-200 overflow-hidden rounded-md border border-none border-transparent shadow-lg"
            >
                <thead class="bg-neutral-200">
                    <tr>
                        <th
                            class="flex items-center justify-between px-4 py-2 text-start text-sm font-medium text-gray-700"
                        >
                            <span class="font-bold">Список контрагентов</span>

                            <!--                            Поиск-->
                            <div>
                                <input
                                    v-model="searchQuery"
                                    class="mr-10 h-6 rounded border border-gray-400 px-2 text-sm text-gray-700 outline-none transition-all focus:translate-y-[-3px] focus:shadow-xl focus:ring-0"
                                    placeholder="Поиск..."
                                    type="text"
                                />
                            </div>
                            <button
                                class="rounded bg-green-500 px-3 py-1 text-sm text-white transition-all duration-100 hover:bg-green-500/90 hover:shadow-md active:translate-y-[3px] active:shadow-inner active:ring-0"
                                @click="openCreateModal"
                            >
                                Добавить
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template
                        v-for="contractor in contractors"
                        :key="contractor.id"
                    >
                        <WhenVisible as="span" data="contractors">
                            <template #fallback>
                                <tr>
                                    <td class="" colspan="2">
                                        Контрагент {{ contractor.id }}
                                        загружается
                                        <v-icon
                                            class="text-blue-500"
                                            name="md-downloading-round"
                                        />
                                    </td>
                                </tr>
                            </template>

                            <tr class="group hover:bg-gray-100">
                                <td
                                    class="flex items-center justify-between px-4 py-2 text-sm text-gray-900"
                                >
                                    <span>{{ contractor.name }}</span>
                                    <button
                                        class="translate-x-10 rounded bg-blue-500 px-3 py-1 text-sm text-white opacity-0 transition-all hover:bg-blue-600 hover:shadow-md group-hover:translate-x-0 group-hover:opacity-100 group-hover:shadow-md"
                                        @click="openEditModal(contractor)"
                                    >
                                        Редактировать
                                    </button>
                                </td>
                            </tr>
                        </WhenVisible>
                    </template>
                    <tr v-if="filteredContractors.length === 0">
                        <td class="px-4 py-2 text-center text-sm text-gray-500">
                            Контрагенты не найдены
                        </td>
                    </tr>
                </tbody>
            </table>
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
                    Добавить контрагента
                </h2>
                <div class="mb-4">
                    <InputLabel class="block text-sm font-medium text-gray-700">
                        Имя
                    </InputLabel>
                    <TextInput
                        v-model="newContractorForm.name"
                        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        type="text"
                    />
                    <InputError :message="errors.name" class="mt-2" />
                </div>
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

        <!-- Модальное окно редактирования -->
        <div
            v-if="isEditModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <form
                class="w-[30rem] rounded-md bg-white p-5 shadow-lg"
                @submit.prevent="update"
            >
                <h2 class="mb-4 text-lg font-medium text-gray-800">
                    Редактировать контрагента
                </h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Имя
                    </label>
                    <input
                        v-model="selectedContractor.name"
                        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                        type="text"
                    />
                    <InputError :message="errors.name" class="mt-2" />
                </div>
                <div class="flex justify-between gap-2">
                    <!--                    <div class="">-->
                    <!--                        <button-->
                    <!--                            class="rounded bg-red-500 px-4 py-2 text-sm hover:bg-red-600"-->
                    <!--                            type="button"-->
                    <!--                            @click="-->
                    <!--                                destroy(-->
                    <!--                                    selectedContractor.id,-->
                    <!--                                    selectedContractor.name,-->
                    <!--                                )-->
                    <!--                            "-->
                    <!--                        >-->
                    <!--                            Удалить-->
                    <!--                        </button>-->
                    <!--                    </div>-->
                    <div class="flex gap-2">
                        <button
                            class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                            type="button"
                            @click="closeModals"
                        >
                            Отмена
                        </button>
                    </div>
                    <div>
                        <button
                            class="rounded bg-blue-500 px-4 py-2 text-sm text-white hover:bg-blue-600"
                            type="submit"
                        >
                            Сохранить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
