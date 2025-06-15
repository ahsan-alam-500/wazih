<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    employees: {
        id: number;
        name: string;
        email: string;
        mobile?: string;
    }[];
}>();

const search = ref('');

const filteredEmployees = computed(() =>
    props.employees.filter(
        (emp) =>
            emp.name.toLowerCase().includes(search.value.toLowerCase()) ||
            emp.email.toLowerCase().includes(search.value.toLowerCase()) ||
            emp.mobile?.toLowerCase().includes(search.value.toLowerCase()),
    ),
);
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-white to-purple-100 p-6">
            <div class="max-w-8xl mx-auto">
                <!-- Title -->
                <div class="mb-6 text-center">
                    <h1 class="text-3xl font-bold text-indigo-700">👥 Employee Directory</h1>
                    <p class="text-sm text-gray-500">Search and manage your employees below</p>
                </div>

                <!-- Search Bar -->
                <div class="mb-6 text-center">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="🔍 Search employees by name, email or mobile..."
                        class="w-full max-w-md rounded-xl border border-gray-300 bg-white/80 px-4 py-2 text-sm shadow focus:border-indigo-500 focus:ring focus:outline-none"
                    />
                </div>

                <!-- Table -->
                <div class="overflow-auto rounded-2xl bg-white/60 shadow-xl backdrop-blur-md">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead class="bg-indigo-100 text-left tracking-wide text-gray-700 uppercase">
                            <tr>
                                <th class="px-6 py-4">#</th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Mobile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(emp, index) in filteredEmployees"
                                :key="emp.id"
                                class="border-t border-gray-200 transition hover:bg-indigo-50"
                            >
                                <td class="px-6 py-3 font-medium text-gray-600">{{ index + 1 }}</td>
                                <td class="px-6 py-3 font-semibold">{{ emp.name }}</td>
                                <td class="px-6 py-3">{{ emp.email }}</td>
                                <td class="px-6 py-3">{{ emp.mobile || 'Not found' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="filteredEmployees.length === 0" class="mt-6 text-center text-gray-500">No employees matched your search.</div>
            </div>
        </div>
    </AppLayout>
</template>
