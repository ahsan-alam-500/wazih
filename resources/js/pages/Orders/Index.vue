<script setup lang="ts">
import CDateRangePicker from '@/components/ui/CDateRangePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import '@vuepic/vue-datepicker/dist/main.css';
import axios from 'axios';
import { computed, ref } from 'vue';
import InvoiceModal from '../partials/Invoice.vue';
import TrustModal from '../partials/TrustModel.vue';

interface Order {
    id: string;
    user: { name: string; avatar?: string | null; mobile: string } | null;
    total_amount: number;
    source?: string | null;
    status: string | null;
    payment_status: string | null;
    created_at: string;
    items: {
        id: number;
        quantity: number;
        price: string;
        product: { name: string; image?: string | null } | null;
    }[];
}

// Configuration constants
const TRUST_LEVEL_THRESHOLDS = {
    FRAUD_CANCELLED: 5,
    VIP_COMPLETED: 3,
} as const;

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';

const props = defineProps<{
    orders: {
        data: Order[];
    };
    statusFilter: string | null;
}>();

const selectedOrder = ref<Order | null>(null);
const showInvoice = ref(false);
const trustModalVisible = ref(false);
const trustSummary = ref<any>(null);
const isLoading = ref(false);
const isUpdating = ref(false);
const errorMessage = ref<string | null>(null);

const statusOptions = ['pending', 'processing', 'completed', 'cancelled'];
const sourceOptions = ['facebook', 'website', 'whatsapp'];
const datePresets = ['Today', 'Yesterday', 'Last 7 Days', 'Last 30 Days', 'This Month', 'Last Month'];

const selectedStatus = ref('');
const selectedSource = ref('');
const selectedDateRange = ref('');
const customRange = ref<[Date | null, Date | null]>([null, null]);

const customRanges = {
    Today: [new Date(), new Date()],
    Yesterday: [new Date(new Date().setDate(new Date().getDate() - 1)), new Date(new Date().setDate(new Date().getDate() - 1))],
    'Last 7 Days': [new Date(new Date().setDate(new Date().getDate() - 6)), new Date()],
    'Last 30 Days': [new Date(new Date().setDate(new Date().getDate() - 29)), new Date()],
    'This Month': [new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)],
    'Last Month': [new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1), new Date(new Date().getFullYear(), new Date().getMonth(), 0)],
};

const filteredOrders = computed(() => {
    return props.orders.data.filter((order) => {
        const matchStatus = selectedStatus.value ? order.status === selectedStatus.value : true;
        const matchSource = selectedSource.value ? order.source === selectedSource.value : true;
        const matchDatePreset = selectedDateRange.value ? filterByDatePreset(order.created_at) : true;
        const matchCustomRange = filterByCustomRange(order.created_at);
        return matchStatus && matchSource && matchDatePreset && matchCustomRange;
    });
});

function filterByDatePreset(dateStr: string): boolean {
    if (!selectedDateRange.value) return true;

    const orderDate = new Date(dateStr);
    const range = customRanges[selectedDateRange.value as keyof typeof customRanges];

    if (range) {
        const [start, end] = range;
        return orderDate >= start && orderDate <= end;
    }

    return true;
}

function filterByCustomRange(dateStr: string): boolean {
    if (!customRange.value[0] || !customRange.value[1]) return true;

    const orderDate = new Date(dateStr);
    return orderDate >= customRange.value[0] && orderDate <= customRange.value[1];
}

const filteredOrderCount = computed(() => filteredOrders.value.length);

function viewInvoice(orderId: string) {
    const order = props.orders.data.find((o) => o.id === orderId);
    if (order) {
        selectedOrder.value = order;
        showInvoice.value = true;
    }
}

function closeInvoice() {
    showInvoice.value = false;
    selectedOrder.value = null;
}

async function handleStatusUpdate({
    orderId,
    newStatus,
    newPaymentStatus,
}: {
    orderId: string;
    newStatus?: string | null;
    newPaymentStatus?: string | null;
}) {
    isUpdating.value = true;
    errorMessage.value = null;

    try {
        await axios.post(`${API_BASE_URL}/order/update`, {
            order_id: orderId,
            status: newStatus,
            payment_status: newPaymentStatus,
        });

        // Show success message
        showSuccessMessage('Order updated successfully!');

        // Optionally refresh the orders data
        // You might want to emit an event to the parent component
        // or use a store to update the orders list
    } catch (error) {
        console.error('Order update failed:', error);
        errorMessage.value = 'Failed to update order. Please try again.';
    } finally {
        isUpdating.value = false;
    }
}

function getUserTrustLevel(mobile: string): string {
    if (!mobile) return 'Unknown';

    const relatedOrders = props.orders.data.filter((o) => o.user?.mobile === mobile);
    const completed = relatedOrders.filter((o) => o.status?.toLowerCase() === 'completed').length;
    const cancelled = relatedOrders.filter((o) => o.status?.toLowerCase() === 'cancelled').length;
    const newOrders = relatedOrders.filter((o) => ['pending', 'processing'].includes(o.status?.toLowerCase() || '')).length;

    if (cancelled > TRUST_LEVEL_THRESHOLDS.FRAUD_CANCELLED) return 'Fraud';
    if (completed > TRUST_LEVEL_THRESHOLDS.VIP_COMPLETED) return 'VIP';
    if (newOrders > 0) return 'Fresh';
    return 'Neutral';
}

function getTrustLevelColor(level: string): string {
    switch (level) {
        case 'Fresh':
            return 'text-green-400';
        case 'VIP':
            return 'text-yellow-300';
        case 'Fraud':
            return 'text-red-500';
        case 'Neutral':
            return 'text-gray-400';
        default:
            return 'text-gray-500';
    }
}

async function toggleTrustCheck(orderId: string, mobile: string) {
    if (!mobile) {
        errorMessage.value = 'No mobile number available for trust check';
        return;
    }

    trustModalVisible.value = true;
    trustSummary.value = null;
    isLoading.value = true;

    try {
        const response = await axios.post(`${API_BASE_URL}/fraudcheck/check`, { phone: mobile });
        const overall = response.data.overall;
        const couriers = response.data.couriers.reduce((acc: any, courier: any) => {
            acc[courier.courier_name] = {
                total: courier.total_parcels,
                success: courier.delivered,
                cancel: courier.returned,
                successRate: Math.round((courier.delivered / (courier.total_parcels || 1)) * 100),
                cancelRate: Math.round((courier.returned / (courier.total_parcels || 1)) * 100),
            };
            return acc;
        }, {});

        trustSummary.value = {
            total: overall.total_parcels,
            success: overall.delivered,
            cancel: overall.returned,
            successRate: Math.round((overall.delivered / (overall.total_parcels || 1)) * 100),
            cancelRate: Math.round((overall.returned / (overall.total_parcels || 1)) * 100),
            couriers,
        };
    } catch (error) {
        console.error('Trust check failed:', error);
        errorMessage.value = 'Failed to check trust level. Please try again.';
        trustSummary.value = null;
    } finally {
        isLoading.value = false;
    }
}

function showSuccessMessage(message: string) {
    // You can implement a toast notification system here
    // For now, using alert as fallback
    alert(message);
}

function clearError() {
    errorMessage.value = null;
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('bn-BD', {
        style: 'currency',
        currency: 'BDT',
        currencyDisplay: 'symbol',
    })
        .format(amount)
        .replace('BDT', '৳');
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Orders" />
    <AppLayout>
        <div class="min-h-screen space-y-6 bg-neutral-900 p-6 text-white">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold capitalize">Orders List 📦</h1>
                <div v-if="isUpdating" class="flex items-center space-x-2">
                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 border-white"></div>
                    <span class="text-sm">Updating...</span>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="rounded-lg border border-red-600 bg-red-800/20 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-red-400">{{ errorMessage }}</p>
                    <button @click="clearError" class="text-red-400 hover:text-red-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg bg-white shadow-md dark:bg-neutral-800">
                <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-3 lg:grid-cols-4">
                    <select
                        v-model="selectedStatus"
                        class="rounded border border-neutral-600 bg-neutral-800 px-4 py-2 focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All Status</option>
                        <option v-for="status in statusOptions" :key="status" :value="status" class="capitalize">
                            {{ status }}
                        </option>
                    </select>

                    <select
                        v-model="selectedSource"
                        class="rounded border border-neutral-600 bg-neutral-800 px-4 py-2 focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All Sources</option>
                        <option v-for="source in sourceOptions" :key="source" :value="source" class="capitalize">
                            {{ source }}
                        </option>
                    </select>

                    <select
                        v-model="selectedDateRange"
                        class="rounded border border-neutral-600 bg-neutral-800 px-4 py-2 focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All Dates</option>
                        <option v-for="preset in datePresets" :key="preset" :value="preset">
                            {{ preset }}
                        </option>
                    </select>

                    <CDateRangePicker :ranges="customRanges" v-model="customRange" @update:modelValue="selectedDateRange = ''" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">Showing {{ filteredOrderCount }} {{ filteredOrderCount === 1 ? 'order' : 'orders' }}</p>
                <div v-if="isLoading" class="flex items-center space-x-2">
                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 border-white"></div>
                    <span class="text-sm">Loading...</span>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto rounded-xl border border-neutral-700 bg-neutral-800 shadow-md backdrop-blur-md">
                <table class="w-full min-w-[700px] text-left text-sm md:text-base lg:text-lg">
                    <thead>
                        <tr class="bg-neutral-750 border-b border-neutral-600">
                            <th class="p-3 font-semibold">Order ID</th>
                            <th class="p-3 font-semibold">Customer</th>
                            <th class="p-3 font-semibold">Mobile</th>
                            <th class="p-3 font-semibold">Products</th>
                            <th class="p-3 font-semibold">Amount</th>
                            <th class="p-3 font-semibold">Source</th>
                            <th class="p-3 font-semibold">Status</th>
                            <th class="p-3 font-semibold">Payment</th>
                            <th class="p-3 font-semibold">Trust Check</th>
                            <th class="p-3 font-semibold">Date</th>
                            <th class="p-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in filteredOrders" :key="order.id" class="border-b border-neutral-700 transition hover:bg-neutral-700/40">
                            <td class="p-3 font-mono text-sm">{{ order.id }}</td>
                            <td class="p-3">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ order.user?.name ?? 'Guest' }}</span>
                                    <span class="text-xs font-semibold" :class="getTrustLevelColor(getUserTrustLevel(order.user?.mobile || ''))">
                                        {{ getUserTrustLevel(order.user?.mobile || '') }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3 font-mono text-sm">{{ order.user?.mobile ?? 'N/A' }}</td>
                            <td class="p-3">
                                <ul class="max-w-xs space-y-1">
                                    <li v-for="item in order.items" :key="item.id" class="flex items-center space-x-2">
                                        <img
                                            :src="item.product?.image || 'https://via.placeholder.com/40'"
                                            :alt="item.product?.name || 'Product'"
                                            class="h-8 w-8 flex-shrink-0 rounded object-cover"
                                        />
                                        <span class="truncate text-sm">
                                            {{ item.product?.name || 'Unknown Product' }}
                                            <span class="text-blue-400">×{{ item.quantity }}</span>
                                        </span>
                                    </li>
                                </ul>
                            </td>
                            <td class="p-3 font-semibold">{{ formatCurrency(order.total_amount) }}</td>
                            <td class="p-3">
                                <span class="rounded bg-neutral-700 px-2 py-1 text-xs capitalize">
                                    {{ order.source ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span
                                    class="rounded px-2 py-1 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-yellow-400/20 text-yellow-400': order.status?.toLowerCase() === 'pending',
                                        'bg-blue-400/20 text-blue-400': order.status?.toLowerCase() === 'processing',
                                        'bg-green-400/20 text-green-400': order.status?.toLowerCase() === 'completed',
                                        'bg-red-400/20 text-red-400': order.status?.toLowerCase() === 'cancelled',
                                    }"
                                >
                                    {{ order.status || 'Unknown' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span
                                    class="rounded px-2 py-1 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-green-400/20 text-green-400': order.payment_status?.toLowerCase() === 'paid',
                                        'bg-red-400/20 text-red-400': order.payment_status?.toLowerCase() === 'unpaid',
                                        'bg-yellow-400/20 text-yellow-400': order.payment_status?.toLowerCase() === 'pending',
                                    }"
                                >
                                    {{ order.payment_status || 'Unknown' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <button
                                    @click="toggleTrustCheck(order.id, order.user?.mobile || '')"
                                    :disabled="!order.user?.mobile || isLoading"
                                    class="rounded border border-neutral-600 px-3 py-1 text-sm transition-colors hover:bg-neutral-600/40 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="order.user?.mobile ? 'text-green-400' : 'text-gray-500'"
                                >
                                    {{ isLoading ? 'Loading...' : 'Check' }}
                                </button>
                            </td>
                            <td class="p-3 text-sm">{{ formatDate(order.created_at) }}</td>
                            <td class="p-3">
                                <button
                                    @click="viewInvoice(order.id)"
                                    class="rounded border border-neutral-600 px-3 py-1 text-sm text-blue-400 transition-colors hover:bg-neutral-600/40"
                                >
                                    Invoice
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredOrders.length === 0">
                            <td colspan="11" class="p-8 text-center text-gray-500">No orders found matching your criteria</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>

    <InvoiceModal :order="selectedOrder" :show="showInvoice" @close="closeInvoice" @update-status="handleStatusUpdate" />
    <TrustModal :show="trustModalVisible" :summary="trustSummary" :loading="isLoading" @close="trustModalVisible = false" />
</template>
