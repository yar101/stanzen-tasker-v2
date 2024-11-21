<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps(['contractors']);

function editContractor(contractorId) {
    console.log('Редактировать контрагента с ID:', contractorId);
}

function addContractor() {
    console.log('Добавить нового контрагента');
    // Здесь можно реализовать логику для добавления нового контрагента
}
</script>

<template>
    <Head title="Контрагенты"></Head>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-[60rem] overflow-x-auto rounded-md pb-5 pt-5">
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
                                class="rounded bg-green-500 px-3 py-1 text-sm text-white transition-colors hover:bg-green-600"
                                @click="addContractor"
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
                                class="rounded bg-blue-400 px-3 py-1 text-sm text-white transition-colors hover:bg-blue-500"
                                @click="editContractor(contractor.id)"
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
    </AuthenticatedLayout>
</template>
