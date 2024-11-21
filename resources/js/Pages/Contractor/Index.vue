<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps(['contractors']);

// Локальное состояние для модальных окон
const isEditModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const selectedContractor = ref(null);
const newContractor = ref({ name: '' }); // Начальное состояние для нового контрагента

// Открытие модального окна редактирования
function openEditModal(contractor) {
    selectedContractor.value = { ...contractor };
    isEditModalOpen.value = true;
}

// Открытие модального окна создания
function openCreateModal() {
    newContractor.value = { name: '' }; // Сброс формы
    isCreateModalOpen.value = true;
}

// Закрытие модальных окон
function closeModals() {
    isEditModalOpen.value = false;
    isCreateModalOpen.value = false;
    selectedContractor.value = null;
}

// Сохранение нового контрагента
function saveNewContractor() {
    console.log('Создаём контрагента:', newContractor.value);
    // Здесь отправьте данные на сервер (например, через Inertia или Axios)
    closeModals();
}

// Сохранение изменений контрагента
function saveContractor() {
    console.log('Сохраняем изменения:', selectedContractor.value);
    // Здесь отправьте изменения на сервер
    closeModals();
}
</script>

<template>
    <Head title="Контрагенты"></Head>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-[60rem] overflow-x-auto rounded-md pb-5 pt-5">
            <!-- Таблица -->
            <table
                class="min-w-full divide-y divide-gray-200 rounded-md border border-gray-200 shadow"
            >
                <thead class="bg-gray-100">
                    <tr>
                        <th
                            class="flex items-center justify-between px-4 py-2 text-start text-sm font-medium text-gray-700"
                        >
                            <span>Контрагенты</span>
                            <button
                                class="rounded bg-green-500 px-3 py-1 text-sm text-white hover:bg-green-600"
                                @click="openCreateModal"
                            >
                                Добавить
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="contractor in contractors" :key="contractor.id">
                        <td
                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-900"
                        >
                            <span>{{ contractor.name }}</span>
                            <button
                                class="rounded bg-blue-500 px-3 py-1 text-sm text-white hover:bg-blue-600"
                                @click="openEditModal(contractor)"
                            >
                                Редактировать
                            </button>
                        </td>
                    </tr>
                    <tr v-if="contractors.length === 0">
                        <td class="px-4 py-2 text-center text-sm text-gray-500">
                            Нет контрагентов
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Модальное окно редактирования -->
        <div
            v-if="isEditModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <div class="w-[30rem] rounded-md bg-white p-5 shadow-lg">
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
                        type="text"
                    />
                </div>
                <div class="flex justify-end gap-2">
                    <button
                        class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                        @click="closeModals"
                    >
                        Отмена
                    </button>
                    <button
                        class="rounded bg-blue-500 px-4 py-2 text-sm text-white hover:bg-blue-600"
                        @click="saveContractor"
                    >
                        Сохранить
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания -->
        <div
            v-if="isCreateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <div class="w-[30rem] rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-4 text-lg font-medium text-gray-800">
                    Добавить контрагента
                </h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Имя
                    </label>
                    <input
                        v-model="newContractor.name"
                        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        type="text"
                    />
                </div>
                <div class="flex justify-end gap-2">
                    <button
                        class="rounded bg-gray-300 px-4 py-2 text-sm hover:bg-gray-400"
                        @click="closeModals"
                    >
                        Отмена
                    </button>
                    <button
                        class="rounded bg-green-500 px-4 py-2 text-sm text-white hover:bg-green-600"
                        @click="saveNewContractor"
                    >
                        Добавить
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
