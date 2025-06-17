<script setup lang="ts">
// Import required components and utilities
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Define props from parent component or server-side data
const props = defineProps<{
    orders: any[];
    employees: any[];
    customers: any[];
    activities: any[];
}>();

// Assign props to local constants
const recentOrders = props.orders;
const employees = props.employees;
const customers = props.customers;
const activities = props.activities;

// Define pagination logic for recent orders
const ordersPerPage = 10; // Show 10 orders per page
const currentPage = ref(1); // Current page
const totalPages = computed(() => Math.ceil(recentOrders.length / ordersPerPage));

// Paginated orders based on current page
const paginatedOrders = computed(() => {
    const start = (currentPage.value - 1) * ordersPerPage;
    const end = start + ordersPerPage;
    return recentOrders.slice(start, end);
});

// Define pagination logic for activities
const activitiesPerPage = 5; // Show 5 activities per page
const activityPage = ref(1); // Current page for activities
const totalActivityPages = computed(() => Math.ceil(activities.length / activitiesPerPage));

// Paginated activities based on current page
const paginatedActivities = computed(() => {
    const start = (activityPage.value - 1) * activitiesPerPage;
    const end = start + activitiesPerPage;
    return activities.slice(start, end);
});

// Define counts using computed properties
const pendingCount = computed(() => recentOrders.filter((o) => o.status === 'pending').length);
const processingCount = computed(() => recentOrders.filter((o) => o.status === 'processing').length);
const shippedCount = computed(() => recentOrders.filter((o) => o.status === 'shipped').length);
const completedCount = computed(() => recentOrders.filter((o) => o.status === 'completed').length);
const abandonedCount = computed(() => recentOrders.filter((o) => o.status === 'abandoned').length);
const cancelledCount = computed(() => recentOrders.filter((o) => o.status === 'cancelled').length);
</script>

<template>
    <!-- Set browser tab title -->
    <Head title="Dashboard" />

    <!-- Wrap content inside AppLayout -->
    <AppLayout>
        <div
            class="min-h-screen space-y-10 bg-gray-100 p-6 text-gray-900 transition-colors duration-300 dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800 dark:text-white"
        >
            <!-- Status Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
                <!-- Card per status -->
                <div class="rounded-xl bg-yellow-500 p-4 shadow transition hover:scale-105 dark:bg-yellow-600">
                    <h3 class="text-sm font-medium text-white/80">🕐 Pending</h3>
                    <p class="mt-1 text-2xl font-bold">{{ pendingCount }}</p>
                </div>
                <div class="rounded-xl bg-blue-400 p-4 shadow transition hover:scale-105 dark:bg-blue-500">
                    <h3 class="text-sm font-medium text-white/80">🔄 Processing</h3>
                    <p class="mt-1 text-2xl font-bold">{{ processingCount }}</p>
                </div>
                <div class="rounded-xl bg-indigo-400 p-4 shadow transition hover:scale-105 dark:bg-indigo-500">
                    <h3 class="text-sm font-medium text-white/80">🚚 Shipped</h3>
                    <p class="mt-1 text-2xl font-bold">{{ shippedCount }}</p>
                </div>
                <div class="rounded-xl bg-green-500 p-4 shadow transition hover:scale-105 dark:bg-green-600">
                    <h3 class="text-sm font-medium text-white/80">✅ Completed</h3>
                    <p class="mt-1 text-2xl font-bold">{{ completedCount }}</p>
                </div>
                <div class="rounded-xl bg-red-500 p-4 shadow transition hover:scale-105 dark:bg-red-600">
                    <h3 class="text-sm font-medium text-white/80">❌ Cancelled</h3>
                    <p class="mt-1 text-2xl font-bold">{{ cancelledCount }}</p>
                </div>
                <div class="rounded-xl bg-gray-500 p-4 shadow transition hover:scale-105 dark:bg-gray-600">
                    <h3 class="text-sm font-medium text-white/80">🚫 Abandoned</h3>
                    <p class="mt-1 text-2xl font-bold">{{ abandonedCount }}</p>
                </div>
                <div class="rounded-xl bg-gray-300 p-4 shadow transition hover:scale-105 dark:bg-gray-700">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-white/80">📦 Total Orders</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ recentOrders.length }}</p>
                </div>
                <div class="rounded-xl bg-gray-300 p-4 shadow transition hover:scale-105 dark:bg-gray-700">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-white/80">👥 Total Employees</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ employees.length }}</p>
                </div>
                <div class="rounded-xl bg-gray-300 p-4 shadow transition hover:scale-105 dark:bg-gray-700">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-white/80">👤 Total Customers</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ customers.length }}</p>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="rounded-xl bg-white p-6 shadow-md transition-colors dark:bg-white/5">
                <h2 class="mb-4 text-xl font-semibold">📦 Recent Orders</h2>
                <table class="w-full text-sm text-gray-800 dark:text-white">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="p-2 text-left">Order ID</th>
                            <th class="p-2 text-left">Customer</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through paginated orders -->
                        <tr
                            v-for="order in paginatedOrders"
                            :key="order.id"
                            class="border-b border-gray-200 hover:bg-gray-100 dark:border-white/10 dark:hover:bg-white/10"
                        >
                            <td class="p-2 font-semibold">{{ order.id }}</td>
                            <td class="p-2">{{ order.user?.name ?? 'Guest' }}</td>
                            <td class="p-2">৳{{ order.total_amount }}</td>
                            <td
                                class="p-2 font-semibold"
                                :class="{
                                    'text-yellow-500': order.status === 'pending',
                                    'text-blue-400': order.status === 'processing',
                                    'text-indigo-400': order.status === 'shipped',
                                    'text-green-400': order.status === 'completed',
                                    'text-red-400': order.status === 'cancelled',
                                }"
                            >
                                {{ order.status }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination Controls -->
                <div class="mt-4 flex justify-center space-x-2">
                    <button
                        @click="currentPage--"
                        :disabled="currentPage <= 1"
                        class="rounded bg-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:bg-gray-700"
                    >
                        Prev
                    </button>
                    <span class="px-3 py-1 text-sm">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button
                        @click="currentPage++"
                        :disabled="currentPage >= totalPages"
                        class="rounded bg-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:bg-gray-700"
                    >
                        Next
                    </button>
                </div>
            </div>

            <!-- Recent Activities List with Pagination -->
            <div class="rounded-xl bg-white p-6 shadow-md dark:bg-white/5">
                <h2 class="mb-4 text-xl font-semibold">🕒 Recent Activities</h2>
                <ul class="space-y-3 text-sm">
                    <li
                        v-for="(activity, index) in paginatedActivities"
                        :key="index"
                        class="flex justify-between border-b border-gray-200 pb-2 last:border-0 dark:border-white/10"
                    >
                        <span>{{ activity.description }}</span>
                        <span class="text-gray-500 dark:text-white/60">
                            {{ new Date(activity.created_at).toLocaleString() }}
                        </span>
                    </li>
                </ul>
                <!-- Pagination Controls for Activities -->
                <div class="mt-4 flex justify-center space-x-2">
                    <button
                        @click="activityPage--"
                        :disabled="activityPage <= 1"
                        class="rounded bg-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:bg-gray-700"
                    >
                        Prev
                    </button>
                    <span class="px-3 py-1 text-sm">Page {{ activityPage }} of {{ totalActivityPages }}</span>
                    <button
                        @click="activityPage++"
                        :disabled="activityPage >= totalActivityPages"
                        class="rounded bg-gray-300 px-3 py-1 text-sm disabled:opacity-50 dark:bg-gray-700"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
