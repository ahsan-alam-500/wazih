<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    orders: any[];
    employees: any[];
    customers: any[];
    activities: any[];
}>();

const recentOrders = props.orders;
const employees = props.employees;
const customers = props.customers;
const activities = props.activities;

const pendingCount = computed(() => recentOrders.filter((o) => o.status === 'pending').length);
const processingCount = computed(() => recentOrders.filter((o) => o.status === 'processing').length);
const shippedCount = computed(() => recentOrders.filter((o) => o.status === 'shipped').length);
const completedCount = computed(() => recentOrders.filter((o) => o.status === 'completed').length);
const cancelledCount = computed(() => recentOrders.filter((o) => o.status === 'cancelled').length);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div
            class="min-h-screen space-y-10 bg-gray-100 p-6 text-gray-900 transition-colors duration-300 dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800 dark:text-white"
        >
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
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
                <div class="rounded-xl bg-gray-300 p-4 shadow transition hover:scale-105 dark:bg-gray-700">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-white/80">📦 Total Orders</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ recentOrders.length }}</p>
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
                        <tr
                            v-for="order in recentOrders"
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
            </div>

            <!-- Recent Activities -->
            <div class="rounded-xl bg-white p-6 shadow-md dark:bg-white/5">
                <h2 class="mb-4 text-xl font-semibold">🕒 Recent Activities</h2>
                <ul class="space-y-3 text-sm">
                    <li
                        v-for="(activity, index) in activities"
                        :key="index"
                        class="flex justify-between border-b border-gray-200 pb-2 last:border-0 dark:border-white/10"
                    >
                        <span>{{ activity.action }}</span>
                        <span class="text-gray-500 dark:text-white/60">
                            {{ new Date(activity.created_at).toLocaleString() }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
