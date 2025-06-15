<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps<{ customers: any[] }>();
const searchTerm = ref('');

const filteredCustomers = computed(() => {
    if (!searchTerm.value) return props.customers;
    const term = searchTerm.value.toLowerCase();
    return props.customers.filter((c) => [c.name, c.email, c.mobile].some((field) => field?.toLowerCase().includes(term)));
});
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-purple-100 via-white to-indigo-100 p-6">
            <div class="max-w-8xl mx-auto">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h1 class="text-3xl font-bold text-indigo-700">🧑‍💼 Customer Directory</h1>
                    <p class="text-sm text-gray-500">Search your customers by name, email or mobile number</p>
                </div>

                <!-- Search Box -->
                <div class="mb-6 text-center">
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="🔍 Search by name, email, or mobile"
                        class="w-full max-w-md rounded-xl border border-gray-300 bg-white/80 px-4 py-2 text-sm shadow focus:border-indigo-500 focus:ring focus:outline-none"
                    />
                </div>

                <!-- Table -->
                <div class="overflow-auto rounded-2xl bg-white/60 shadow-xl backdrop-blur-md">
                    <table class="min-w-full text-sm text-gray-700">
                        <thead class="bg-indigo-100 text-left tracking-wide text-gray-700 uppercase">
                            <tr>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Mobile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="customer in filteredCustomers"
                                :key="customer.id"
                                class="border-t border-gray-200 transition hover:bg-indigo-50"
                            >
                                <td class="px-6 py-3 font-semibold">{{ customer.name }}</td>
                                <td class="px-6 py-3">{{ customer.email }}</td>
                                <td class="px-6 py-3">{{ customer.mobile || 'Not available' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty state -->
                    <div v-if="filteredCustomers.length === 0" class="p-6 text-center text-gray-500">😕 No customers matched your search.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
