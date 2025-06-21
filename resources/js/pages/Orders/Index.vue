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

// Bulk selection state
const selectedOrders = ref<Set<string>>(new Set());
const showBulkActions = ref(false);
const bulkActionType = ref<'status' | 'payment' | 'delete'>('status');
const bulkStatus = ref('');
const bulkPaymentStatus = ref('');
const isBulkUpdating = ref(false);

const statusOptions = ['pending', 'processing', 'completed', 'cancelled'];
const sourceOptions = ['facebook', 'website', 'whatsapp'];
const datePresets = ['Today', 'Yesterday', 'Last 7 Days', 'Last 30 Days', 'This Month', 'Last Month'];

const selectedStatus = ref('');
const selectedSource = ref('');
const selectedDateRange = ref('');
const customRange = ref<[Date | null, Date | null]>([null, null]);

const startOfDay = (date: Date) => new Date(date.setHours(0, 0, 0, 0));
const endOfDay = (date: Date) => new Date(date.setHours(23, 59, 59, 999));

const today = new Date();
const yesterday = new Date();
yesterday.setDate(today.getDate() - 1);

const customRanges = {
    Today: [startOfDay(new Date()), endOfDay(new Date())],
    Yesterday: [startOfDay(new Date(yesterday)), endOfDay(new Date(yesterday))],
    'Last 7 Days': [startOfDay(new Date(Date.now() - 6 * 86400000)), endOfDay(new Date())],
    'Last 30 Days': [startOfDay(new Date(Date.now() - 29 * 86400000)), endOfDay(new Date())],
    'This Month': [
        new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        endOfDay(new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)),
    ],
    'Last Month': [
        new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1),
        endOfDay(new Date(new Date().getFullYear(), new Date().getMonth(), 0)),
    ],
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
const selectedOrderCount = computed(() => selectedOrders.value.size);
const isAllSelected = computed(() => filteredOrders.value.length > 0 && selectedOrders.value.size === filteredOrders.value.length);
const isPartiallySelected = computed(() => selectedOrders.value.size > 0 && selectedOrders.value.size < filteredOrders.value.length);

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
            return 'text-emerald-400 font-semibold';
        case 'VIP':
            return 'text-amber-400 font-semibold';
        case 'Fraud':
            return 'text-rose-500 font-semibold';
        case 'Neutral':
            return 'text-slate-300 font-medium';
        default:
            return 'text-gray-400';
    }
}

function getTrustBadgeColor(level: string): string {
    switch (level) {
        case 'Fresh':
            return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
        case 'VIP':
            return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
        case 'Fraud':
            return 'bg-rose-500/20 text-rose-300 border-rose-500/30';
        case 'Neutral':
            return 'bg-slate-500/20 text-slate-300 border-slate-500/30';
        default:
            return 'bg-gray-500/20 text-gray-300 border-gray-500/30';
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
// Bulk selection functions
function toggleSelectAll() {
    if (isAllSelected.value) {
        selectedOrders.value.clear();
    } else {
        filteredOrders.value.forEach((order) => {
            selectedOrders.value.add(order.id);
        });
    }
}

function toggleSelectOrder(orderId: string) {
    if (selectedOrders.value.has(orderId)) {
        selectedOrders.value.delete(orderId);
    } else {
        selectedOrders.value.add(orderId);
    }
}

function clearSelection() {
    selectedOrders.value.clear();
    showBulkActions.value = false;
}

function openBulkActions() {
    if (selectedOrders.value.size > 0) {
        showBulkActions.value = true;
    }
}

async function executeBulkAction() {
    if (selectedOrders.value.size === 0) return;

    isBulkUpdating.value = true;
    errorMessage.value = null;
    let successCount = 0;
    let failCount = 0;

    try {
        const promises = Array.from(selectedOrders.value).map(async (orderId) => {
            try {
                if (bulkActionType.value === 'status' && bulkStatus.value) {
                    await axios.post(`${API_BASE_URL}/order/update`, {
                        order_id: orderId,
                        status: bulkStatus.value,
                    });
                } else if (bulkActionType.value === 'payment' && bulkPaymentStatus.value) {
                    await axios.post(`${API_BASE_URL}/order/update`, {
                        order_id: orderId,
                        payment_status: bulkPaymentStatus.value,
                    });
                } else if (bulkActionType.value === 'delete') {
                    await axios.delete(`${API_BASE_URL}/order/${orderId}`);
                }
                successCount++;
            } catch (error) {
                console.error(`Failed to update order ${orderId}:`, error);
                failCount++;
            }
        });

        await Promise.allSettled(promises);

        if (successCount > 0) {
            showSuccessMessage(`Successfully updated ${successCount} order(s).${failCount > 0 ? ` ${failCount} failed.` : ''}`);
        }

        if (failCount > 0 && successCount === 0) {
            errorMessage.value = `Failed to update ${failCount} order(s). Please try again.`;
        }

        // Clear selection and close modal
        clearSelection();
    } catch (error) {
        console.error('Bulk update failed:', error);
        errorMessage.value = 'Bulk update failed. Please try again.';
    } finally {
        isBulkUpdating.value = false;
    }
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
        <div class="min-h-screen space-y-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white">
            <!-- Header Section -->

            <!-- Error Message -->
            <div
                v-if="errorMessage"
                class="rounded-xl border border-rose-500/30 bg-gradient-to-r from-rose-500/10 to-red-500/10 p-4 backdrop-blur-sm"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <p class="font-medium text-rose-300">{{ errorMessage }}</p>
                    </div>
                    <button @click="clearError" class="text-rose-400 transition-colors hover:text-rose-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="rounded-xl border border-slate-600/30 bg-gradient-to-r from-slate-800/50 to-slate-700/50 shadow-xl backdrop-blur-sm">
                <div class="p-6">
                    <h3 class="mb-4 flex items-center text-lg font-semibold text-slate-200">
                        <svg class="mr-2 h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"
                            ></path>
                        </svg>
                        Filters
                    </h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-slate-600/50 bg-slate-800/80 px-4 py-3 text-white backdrop-blur-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                            >
                                <option value="">All Status</option>
                                <option v-for="status in statusOptions" :key="status" :value="status" class="bg-slate-800 capitalize">
                                    {{ status }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Source</label>
                            <select
                                v-model="selectedSource"
                                class="w-full rounded-lg border border-slate-600/50 bg-slate-800/80 px-4 py-3 text-white backdrop-blur-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                            >
                                <option value="">All Sources</option>
                                <option v-for="source in sourceOptions" :key="source" :value="source" class="bg-slate-800 capitalize">
                                    {{ source }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Date Range</label>
                            <select
                                v-model="selectedDateRange"
                                class="z-999 w-full rounded-lg border border-slate-600/50 bg-slate-800/80 px-4 py-3 text-white backdrop-blur-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                            >
                                <option value="">All Dates</option>
                                <option v-for="preset in datePresets" :key="preset" :value="preset" class="bg-slate-800">
                                    {{ preset }}
                                </option>
                            </select>
                        </div>

                        <div class="z-999 space-y-2">
                            <label class="text-sm font-medium text-slate-300">Custom Range</label>
                            <CDateRangePicker
                                :ranges="customRanges"
                                class="z-999"
                                v-model="customRange"
                                @update:modelValue="selectedDateRange = ''"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Header -->
            <div
                class="flex items-center justify-between rounded-xl border border-indigo-500/20 bg-gradient-to-r from-indigo-600/20 to-blue-600/20 p-4 backdrop-blur-sm"
            >
                <div class="flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20">
                        <svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            ></path>
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-indigo-200">
                        Showing <span class="font-bold text-white">{{ filteredOrderCount }}</span>
                        {{ filteredOrderCount === 1 ? 'order' : 'orders' }}
                    </p>
                    <div v-if="selectedOrderCount > 0" class="flex items-center space-x-2">
                        <span class="text-sm text-slate-400">|</span>
                        <span class="text-sm font-medium text-amber-300"> {{ selectedOrderCount }} selected </span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div v-if="selectedOrderCount > 0" class="flex items-center space-x-2">
                        <button
                            @click="openBulkActions"
                            class="rounded-lg border border-purple-500/40 bg-gradient-to-r from-purple-500/20 to-pink-500/20 px-4 py-2 text-sm font-medium text-purple-300 transition-all duration-200 hover:from-purple-500/30 hover:to-pink-500/30"
                        >
                            Bulk Actions
                        </button>
                        <button
                            @click="clearSelection"
                            class="rounded-lg border border-slate-500/40 bg-slate-600/50 px-3 py-2 text-sm text-slate-300 transition-all duration-200 hover:bg-slate-600/70"
                        >
                            Clear
                        </button>
                    </div>
                    <div v-if="isLoading" class="flex items-center space-x-3 rounded-lg border border-blue-500/30 bg-blue-500/20 px-3 py-2">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></div>
                        <span class="text-sm font-medium text-blue-300">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div
                class="overflow-hidden rounded-xl border border-slate-600/30 bg-gradient-to-br from-slate-800/80 to-slate-900/80 shadow-2xl backdrop-blur-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-600/50 bg-gradient-to-r from-slate-700/80 to-slate-800/80">
                                <th class="p-4 text-center font-semibold text-slate-200">
                                    <div class="flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            :indeterminate="isPartiallySelected"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 rounded border-slate-500 bg-slate-700 text-blue-500 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </th>
                                <th class="p-4 text-center font-semibold text-slate-200">SL</th>
                                <th class="p-4 font-semibold text-slate-200">Order Details</th>
                                <th class="p-4 font-semibold text-slate-200">Status</th>
                                <th class="p-4 font-semibold text-slate-200">Name</th>
                                <th class="p-4 font-semibold text-slate-200">Update</th>
                                <th class="p-4 font-semibold text-slate-200">Address</th>
                                <th class="p-4 font-semibold text-slate-200">Products</th>
                                <th class="p-4 font-semibold text-slate-200">Trust Check</th>
                                <th class="p-4 font-semibold text-slate-200">Logistics</th>
                                <th class="p-4 font-semibold text-slate-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(order, index) in filteredOrders"
                                :key="order.id"
                                class="border-b border-slate-700/50 transition-all duration-200 hover:bg-gradient-to-r hover:from-slate-700/30 hover:to-slate-600/30 hover:shadow-lg"
                                :class="{ 'border-blue-500/30 bg-blue-500/10': selectedOrders.has(order.id) }"
                            >
                                <td class="p-4 text-center">
                                    <input
                                        type="checkbox"
                                        :checked="selectedOrders.has(order.id)"
                                        @change="toggleSelectOrder(order.id)"
                                        class="h-4 w-4 rounded border-slate-500 bg-slate-700 text-blue-500 focus:ring-2 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="p-4 text-center">
                                    <div
                                        class="mx-auto flex h-8 w-8 items-center justify-center rounded-lg border border-blue-500/30 bg-gradient-to-r from-blue-500/20 to-purple-500/20"
                                    >
                                        <span class="font-mono text-sm font-semibold text-blue-300">{{ index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="space-y-2">
                                        <div class="rounded-md border border-slate-600/30 bg-slate-700/50 px-2 py-1 font-mono text-sm">
                                            <span class="text-slate-300">ID:</span>
                                            <span class="flex items-center align-middle text-sm font-semibold text-blue-300"
                                                >{{ order.id }} {{ order.source ? `${order.source}` : '' }}</span
                                            >
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-slate-400">{{ formatDate(order.created_at) }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="mr-2 rounded bg-slate-600/50 px-2 py-1 font-semibold text-slate-200 dark:text-slate-300">
                                                {{ order.total_amount }}
                                            </span>
                                            <span
                                                class="rounded-full border px-2 py-1 text-xs font-semibold"
                                                :class="getTrustBadgeColor(getUserTrustLevel(order.user?.mobile || ''))"
                                            >
                                                {{ getUserTrustLevel(order.user?.mobile || '') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold capitalize"
                                        :class="{
                                            'border-amber-500/40 bg-amber-500/20 text-amber-300': order.status?.toLowerCase() === 'pending',
                                            'border-blue-500/40 bg-blue-500/20 text-blue-300': order.status?.toLowerCase() === 'processing',
                                            'border-emerald-500/40 bg-emerald-500/20 text-emerald-300': order.status?.toLowerCase() === 'completed',
                                            'border-rose-500/40 bg-rose-500/20 text-rose-300': order.status?.toLowerCase() === 'cancelled',
                                        }"
                                    >
                                        {{ order.status || 'Unknown' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col space-y-2">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full border border-purple-500/30 bg-gradient-to-r from-purple-500/20 to-pink-500/20"
                                            >
                                                <span class="text-xs font-semibold text-purple-300">
                                                    {{ (order.user?.name ?? 'Guest').charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-slate-200">
                                                <span
                                                    class="rounded bg-slate-600/50 px-2 py-1 text-xs font-semibold text-slate-400 dark:text-slate-300"
                                                >
                                                    {{ getUserTrustLevel(order.user?.mobile || '') }}</span
                                                >
                                                <span class="text-xs font-semibold">
                                                    {{ order.user?.name ?? 'Guest' }}
                                                </span>
                                                <br />
                                                {{ order.user?.mobile ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <!-- update  -->
                                <td class="p-4">
                                    {{ order.updated_at ? formatDate(order.updated_at) : 'N/A' }}
                                </td>
                                <!-- address  -->
                                <td class="p-4">
                                    <span class="truncate text-sm font-medium text-slate-200">
                                        {{ order.shipping_address || 'N/A' }}
                                    </span>
                                </td>
                                <!-- products  -->
                                <td class="p-4">
                                    <div class="max-w-xs space-y-2">
                                        <div
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="flex items-center space-x-3 rounded-lg border border-slate-600/20 bg-slate-700/30 p-2"
                                        >
                                            <img
                                                :src="item.product?.image || 'https://via.placeholder.com/40'"
                                                :alt="item.product?.name || 'Product'"
                                                class="h-10 w-10 flex-shrink-0 rounded-lg border border-slate-600/30 object-cover"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium text-slate-200">
                                                    {{ item.product?.name || 'Unknown Product' }}
                                                </p>
                                                <span
                                                    class="inline-flex items-center rounded-full border border-blue-500/30 bg-blue-500/20 px-2 py-0.5 text-xs font-medium text-blue-300"
                                                >
                                                    ×{{ item.quantity }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4">
                                    <button
                                        @click="toggleTrustCheck(order.id, order.user?.mobile || '')"
                                        :disabled="!order.user?.mobile || isLoading"
                                        class="rounded-lg border px-4 py-2 text-sm font-medium transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            order.user?.mobile
                                                ? 'border-emerald-500/40 bg-emerald-500/20 text-emerald-300 hover:border-emerald-500/60 hover:bg-emerald-500/30'
                                                : 'border-gray-500/40 bg-gray-500/20 text-gray-400'
                                        "
                                    >
                                        {{ isLoading ? 'Loading...' : 'Check' }}
                                    </button>
                                </td>
                                <td class="p-4">
                                    <span
                                        :class="
                                            order.status?.toLowerCase() === 'pathao'
                                                ? 'border-emerald-500/40 bg-emerald-500/20 text-emerald-300'
                                                : 'border-rose-500/40 bg-rose-500/20 text-rose-300'
                                        "
                                        class="rounded-lg border px-4 py-2 text-sm font-medium"
                                    >
                                        {{ order.logistics?.toLowerCase() || 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <button
                                        @click="viewInvoice(order.id)"
                                        class="rounded-lg border border-blue-500/40 bg-blue-500/20 px-4 py-2 text-sm font-medium text-blue-300 transition-all duration-200 hover:border-blue-500/60 hover:bg-blue-500/30"
                                    >
                                        📄 Invoice
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="12" class="p-12 text-center">
                                    <div class="flex flex-col items-center space-y-4">
                                        <div
                                            class="flex h-16 w-16 items-center justify-center rounded-full border border-slate-600/30 bg-slate-700/50"
                                        >
                                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                                ></path>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-lg font-medium text-slate-400">No orders found</p>
                                            <p class="mt-1 text-sm text-slate-500">Try adjusting your filter criteria</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <InvoiceModal :order="selectedOrder" :show="showInvoice" @close="closeInvoice" @update-status="handleStatusUpdate" />
    <TrustModal :show="trustModalVisible" :summary="trustSummary" :loading="isLoading" @close="trustModalVisible = false" />

    <!-- Bulk Actions Modal -->
    <div v-if="showBulkActions" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl border border-slate-600/30 bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="flex items-center text-xl font-semibold text-white">
                    <svg class="mr-2 h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Bulk Actions
                </h3>
                <button @click="showBulkActions = false" class="text-slate-400 transition-colors hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3">
                    <p class="text-sm text-blue-300">
                        <strong>{{ selectedOrderCount }}</strong> order{{ selectedOrderCount > 1 ? 's' : '' }} selected
                    </p>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-medium text-slate-300">Action Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="bulkActionType = 'status'"
                            :class="
                                bulkActionType === 'status'
                                    ? 'border-blue-500/40 bg-blue-500/20 text-blue-300'
                                    : 'border-slate-600/30 bg-slate-700/50 text-slate-400'
                            "
                            class="rounded-lg border px-3 py-2 text-xs font-medium transition-all"
                        >
                            Status
                        </button>
                        <button
                            @click="bulkActionType = 'payment'"
                            :class="
                                bulkActionType === 'payment'
                                    ? 'border-green-500/40 bg-green-500/20 text-green-300'
                                    : 'border-slate-600/30 bg-slate-700/50 text-slate-400'
                            "
                            class="rounded-lg border px-3 py-2 text-xs font-medium transition-all"
                        >
                            Payment
                        </button>
                        <button
                            @click="bulkActionType = 'delete'"
                            :class="
                                bulkActionType === 'delete'
                                    ? 'border-red-500/40 bg-red-500/20 text-red-300'
                                    : 'border-slate-600/30 bg-slate-700/50 text-slate-400'
                            "
                            class="rounded-lg border px-3 py-2 text-xs font-medium transition-all"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div v-if="bulkActionType === 'status'" class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">New Status</label>
                    <select
                        v-model="bulkStatus"
                        class="w-full rounded-lg border border-slate-600/50 bg-slate-800/80 px-4 py-3 text-white backdrop-blur-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                    >
                        <option value="">Select Status</option>
                        <option v-for="status in statusOptions" :key="status" :value="status" class="bg-slate-800 capitalize">
                            {{ status }}
                        </option>
                    </select>
                </div>

                <div v-if="bulkActionType === 'payment'" class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">New Payment Status</label>
                    <select
                        v-model="bulkPaymentStatus"
                        class="w-full rounded-lg border border-slate-600/50 bg-slate-800/80 px-4 py-3 text-white backdrop-blur-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                    >
                        <option value="">Select Payment Status</option>
                        <option value="paid" class="bg-slate-800">Paid</option>
                        <option value="unpaid" class="bg-slate-800">Unpaid</option>
                        <option value="pending" class="bg-slate-800">Pending</option>
                    </select>
                </div>

                <div v-if="bulkActionType === 'delete'" class="rounded-lg border border-red-500/20 bg-red-500/10 p-3">
                    <p class="text-sm text-red-300">⚠️ This action cannot be undone. Selected orders will be permanently deleted.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    @click="showBulkActions = false"
                    class="rounded-lg border border-slate-500/40 bg-slate-600/50 px-4 py-2 text-slate-300 transition-all duration-200 hover:bg-slate-600/70"
                >
                    Cancel
                </button>
                <button
                    @click="executeBulkAction"
                    :disabled="isBulkUpdating || (bulkActionType === 'status' && !bulkStatus) || (bulkActionType === 'payment' && !bulkPaymentStatus)"
                    class="flex items-center space-x-2 rounded-lg px-4 py-2 font-medium transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50"
                    :class="
                        bulkActionType === 'delete'
                            ? 'border border-red-500/40 bg-red-500/20 text-red-300 hover:bg-red-500/30'
                            : 'border border-blue-500/40 bg-blue-500/20 text-blue-300 hover:bg-blue-500/30'
                    "
                >
                    <div v-if="isBulkUpdating" class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                    <span>{{ isBulkUpdating ? 'Processing...' : 'Apply Changes' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
