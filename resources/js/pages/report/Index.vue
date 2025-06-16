<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { ref } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    orders: Array,
    filters: Object,
    stats: {
        type: Object,
        default: () => ({
            completed_orders: 0,
            total_sales: 0,
        }),
    },
});

const status = ref(props.filters.status || '');
const payment_status = ref(props.filters.payment_status || '');
const from_date = ref(props.filters.from_date || '');
const to_date = ref(props.filters.to_date || '');

function applyFilter() {
    router.get(
        route('reports'),
        {
            status: status.value,
            payment_status: payment_status.value,
            from_date: from_date.value,
            to_date: to_date.value,
        },
        { preserveState: true },
    );
}

function clearFilters() {
    status.value = '';
    payment_status.value = '';
    from_date.value = '';
    to_date.value = '';
    applyFilter();
}

const chartData = ref({
    labels: ['Completed Orders', 'Total Sales'],
    datasets: [
        {
            label: 'Overview',
            backgroundColor: '#2563eb',
            data: [props.stats.completed_orders ?? 0, props.stats.total_sales ?? 0],
        },
    ],
});

const chartOptions = ref({
    responsive: true,
    plugins: {
        legend: {
            labels: {
                color: '#000',
            },
        },
    },
    scales: {
        x: {
            ticks: {
                color: '#000',
            },
        },
        y: {
            ticks: {
                color: '#000',
            },
        },
    },
});
</script>

<template>
    <AppLayout title="Order Report">
        <div class="space-y-6 p-4">
            <!-- Filters -->
            <div class="flex flex-wrap items-end gap-4 rounded-lg bg-gray-100 p-4 shadow dark:bg-gray-800">
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Status</label>
                    <select v-model="status" class="rounded border p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Payment</label>
                    <select v-model="payment_status" class="rounded border p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">From</label>
                    <input type="date" v-model="from_date" class="rounded border p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">To</label>
                    <input type="date" v-model="to_date" class="rounded border p-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex gap-2">
                    <button @click="applyFilter" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Filter</button>
                    <button @click="clearFilters" class="rounded bg-gray-500 px-4 py-2 text-white hover:bg-gray-600">Clear</button>
                </div>
            </div>

            <!-- Order Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300 rounded-lg border text-sm shadow dark:divide-gray-700">
                    <thead class="bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Payment</th>
                            <th class="px-4 py-2 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        <tr v-for="order in props.orders" :key="order.id">
                            <td class="px-4 py-2">{{ order.order_number }}</td>
                            <td class="px-4 py-2">{{ order.user?.name }}</td>
                            <td class="px-4 py-2">৳{{ order.total_amount }}</td>
                            <td class="px-4 py-2 capitalize">{{ order.status }}</td>
                            <td class="px-4 py-2 capitalize">{{ order.payment_status }}</td>
                            <td class="px-4 py-2">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Chart Section -->
            <div class="mt-8 rounded bg-gray-100 p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Overview</h2>
                <Bar :data="chartData" :options="chartOptions" class="mx-auto w-full max-w-xl" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
canvas {
    max-height: 300px;
}
</style>
