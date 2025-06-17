<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
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

const props = defineProps<{
    orders: {
        data: Order[];
    };
    statusFilter: string | null;
}>();

const selectedOrder = ref<Order | null>(null);
const showInvoice = ref(false);

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
    try {
        await axios.post('http://127.0.0.1:8000/api/v1/order/update', {
            order_id: orderId,
            status: newStatus,
            payment_status: newPaymentStatus,
        });

        alert('Order updated successfully!');
    } catch (error) {
        console.error('Order update failed:', error.response?.data || error.message || error);
    }
}

function getUserTrustLevel(mobile: string): string {
    const relatedOrders = props.orders.data.filter((o) => o.user?.mobile === mobile);
    const completed = relatedOrders.filter((o) => o.status?.toLowerCase() === 'completed').length;
    const cancelled = relatedOrders.filter((o) => o.status?.toLowerCase() === 'cancelled').length;
    const newOrders = relatedOrders.filter((o) => ['pending', 'processing'].includes(o.status?.toLowerCase() || '')).length;

    if (cancelled > 5) return 'Fraud';
    if (completed > 3) return 'VIP';
    if (newOrders > 0) return 'Fresh';
    return 'Neutral';
}

const trustModalVisible = ref(false);
const trustSummary = ref<any>(null);

async function toggleTrustCheck(orderId: string, mobile: string) {
    trustModalVisible.value = true;
    trustSummary.value = null;

    try {
        const response = await axios.post(`http://127.0.0.1:8000/api/v1/fraudcheck/check`, {
            phone: mobile,
        });

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
        console.error('API error:', error.response?.data || error.message);
        trustSummary.value = null;
    }
}
</script>

<template>
    <Head title="Orders" />
    <AppLayout>
        <div class="min-h-screen space-y-6 bg-neutral-900 p-6 text-white">
            <h1 class="mb-4 text-2xl font-bold capitalize">{{ statusFilter }} Orders List 📦</h1>

            <div class="overflow-x-auto rounded-xl border border-neutral-700 bg-neutral-800 p-6 shadow-md backdrop-blur-md">
                <table class="w-full min-w-[700px] text-left text-sm md:text-base lg:text-lg">
                    <thead>
                        <tr class="border-b border-neutral-600">
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Mobile</th>
                            <th class="p-3">Products</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Source</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Payment Status</th>
                            <th class="p-3">Trust Level</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in props.orders.data" :key="order.id" class="border-b border-neutral-700 transition hover:bg-neutral-700/40">
                            <td class="p-3 font-semibold">{{ order.id }}</td>
                            <td class="p-3">
                                {{ order.user?.name ?? 'Guest' }}
                                <sup
                                    class="text-xs font-semibold"
                                    :class="{
                                        'text-green-400': getUserTrustLevel(order.user?.mobile || '') === 'Fresh',
                                        'text-yellow-300': getUserTrustLevel(order.user?.mobile || '') === 'VIP',
                                        'text-red-500': getUserTrustLevel(order.user?.mobile || '') === 'Fraud',
                                    }"
                                >
                                    {{ getUserTrustLevel(order.user?.mobile || '') }}
                                </sup>
                            </td>
                            <td class="p-3">{{ order.user?.mobile ?? 'N/A' }}</td>
                            <td class="p-3">
                                <ul class="space-y-2">
                                    <li v-for="item in order.items" :key="item.id" class="flex items-center space-x-2">
                                        <img :src="item.product?.image || 'https://via.placeholder.com/40'" class="h-10 w-10 rounded object-cover" />
                                        <span>{{ item.product?.name || 'Unknown Product' }} x {{ item.quantity }}</span>
                                    </li>
                                </ul>
                            </td>
                            <td class="p-3">৳{{ order.total_amount }}</td>
                            <td class="p-3">{{ order.source ?? 'N/A' }}</td>
                            <td
                                class="p-3 font-semibold"
                                :class="{
                                    'text-yellow-400': order.status?.toLowerCase() === 'pending',
                                    'text-green-400': order.status?.toLowerCase() === 'completed',
                                    'text-red-400': order.status?.toLowerCase() === 'cancelled',
                                }"
                            >
                                {{ order.status }}
                            </td>
                            <td class="p-3">
                                <span
                                    :class="{
                                        'text-green-400': order.payment_status?.toLowerCase() === 'paid',
                                        'text-red-400': order.payment_status?.toLowerCase() === 'unpaid',
                                    }"
                                >
                                    {{ order.payment_status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <button
                                    @click="toggleTrustCheck(order.id, order.user?.mobile || '')"
                                    class="border border-neutral-600 px-4 py-2 text-green-400 hover:cursor-pointer hover:bg-neutral-600/40"
                                >
                                    Check
                                </button>
                            </td>
                            <td class="p-3">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                            <td class="space-x-2 p-3">
                                <button
                                    @click="viewInvoice(order.id)"
                                    class="border border-neutral-600 px-4 py-2 text-blue-400 hover:cursor-pointer hover:bg-neutral-600/40"
                                >
                                    Invoice
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>

    <InvoiceModal :order="selectedOrder" :show="showInvoice" @close="closeInvoice" @update-status="handleStatusUpdate" />
    <TrustModal :show="trustModalVisible" :summary="trustSummary" @close="trustModalVisible = false" />
</template>
