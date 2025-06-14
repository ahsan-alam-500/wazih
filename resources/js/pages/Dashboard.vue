<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const recentOrders = [
    { id: '#ORD001', customer: 'Sajib Khan', amount: 999, status: 'Pending' },
    { id: '#ORD002', customer: 'Rehana Akter', amount: 1450, status: 'Delivered' },
    { id: '#ORD003', customer: 'Raju Ahmed', amount: 570, status: 'Cancelled' },
];

const activities = [
    { time: '5 mins ago', action: 'New order placed (#ORD001)' },
    { time: '30 mins ago', action: 'Product added to stock' },
    { time: 'Today', action: 'New user registered' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="min-h-screen space-y-8 bg-gray-900 p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4">
                <div class="rounded-xl border border-white/20 bg-black/30 p-6 shadow-md backdrop-blur-md transition duration-300 hover:shadow-lg">
                    <h3 class="text-sm font-medium text-gray-300">Total Orders</h3>
                    <p class="mt-2 text-3xl font-extrabold text-white">৳12,500</p>
                </div>

                <div class="rounded-xl border border-white/20 bg-black/30 p-6 shadow-md backdrop-blur-md transition duration-300 hover:shadow-lg">
                    <h3 class="text-sm font-medium text-gray-300">Total Products</h3>
                    <p class="mt-2 text-3xl font-extrabold text-white">320</p>
                </div>

                <div class="rounded-xl border border-white/20 bg-black/30 p-6 shadow-md backdrop-blur-md transition duration-300 hover:shadow-lg">
                    <h3 class="text-sm font-medium text-gray-300">Today’s Visitors</h3>
                    <p class="mt-2 text-3xl font-extrabold text-white">890</p>
                </div>

                <div class="rounded-xl border border-white/20 bg-black/30 p-6 shadow-md backdrop-blur-md transition duration-300 hover:shadow-lg">
                    <h3 class="text-sm font-medium text-gray-300">New Customers</h3>
                    <p class="mt-2 text-3xl font-extrabold text-white">17</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="rounded-xl border border-white/20 bg-black/30 p-6 text-gray-200 shadow-md backdrop-blur-md">
                <h2 class="mb-4 text-xl font-semibold text-white">Recent Orders</h2>
                <table class="w-full text-sm text-gray-300">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="p-2 text-left">Order ID</th>
                            <th class="p-2 text-left">Customer</th>
                            <th class="p-2 text-left">Amount</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in recentOrders" :key="order.id" class="border-b border-white/10 transition hover:bg-white/5">
                            <td class="p-2 font-semibold">{{ order.id }}</td>
                            <td class="p-2">{{ order.customer }}</td>
                            <td class="p-2">৳{{ order.amount }}</td>
                            <td
                                class="p-2 font-semibold"
                                :class="{
                                    'text-yellow-400': order.status === 'Pending',
                                    'text-green-400': order.status === 'Delivered',
                                    'text-red-400': order.status === 'Cancelled',
                                }"
                            >
                                {{ order.status }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Recent Activities -->
            <div class="rounded-xl border border-white/20 bg-black/30 p-6 text-gray-200 shadow-md backdrop-blur-md">
                <h2 class="mb-4 text-xl font-semibold text-white">Recent Activities</h2>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li v-for="(activity, index) in activities" :key="index" class="flex justify-between border-b border-white/10 pb-2 last:border-0">
                        <span>{{ activity.action }}</span>
                        <span class="text-gray-400">{{ activity.time }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
