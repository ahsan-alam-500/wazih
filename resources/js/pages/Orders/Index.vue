<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ref } from 'vue';
import InvoiceModal from '../partials/Invoice.vue';

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
</script>

<template>
    <Head title="Orders" />

    <AppLayout>
        <div class="min-h-screen space-y-6 bg-gray-900 p-6">
            <h1 class="mb-4 text-2xl font-bold text-white">Orders List</h1>

            <div class="overflow-x-auto rounded-xl border border-white/20 bg-black/30 p-6 text-gray-200 shadow-md backdrop-blur-md">
                <table class="w-full min-w-[700px] text-center text-left text-sm text-gray-300 md:text-base lg:text-lg">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Mobile</th>
                            <th class="p-3">Products</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Source</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Payment Status</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in props.orders.data" :key="order.id" class="border-b border-white/10 transition hover:bg-white/5">
                            <td class="p-3 font-semibold">{{ order.id }}</td>
                            <!-- <td class="p-3">{{ order.user ? order.user.name : 'Guest' }}</td> -->
                            <td class="p-3">{{ order.user?.name ?? 'Guest' }}</td>
                            <td class="p-3">{{ order.user?.mobile ?? 'N/A' }}</td>
                            <td class="p-3">
                                <ul class="space-y-2">
                                    <li v-for="item in order.items" :key="item.id" class="flex items-center space-x-2">
                                        <img
                                            :src="item.product?.image || 'https://via.placeholder.com/40'"
                                            alt="Product"
                                            class="h-10 w-10 rounded object-cover"
                                        />
                                        <span>{{ item.product ? item.product.name : 'Unknown Product' }} x {{ item.quantity }}</span>
                                    </li>
                                </ul>
                            </td>
                            <td class="p-3">৳{{ order.total_amount }}</td>
                            <td class="p-3">{{ order.source ?? 'N/A' }}</td>
                            <td
                                class="p-3 font-semibold"
                                :class="{
                                    'text-yellow-400': order.status && order.status.toLowerCase() === 'pending',
                                    'text-green-400': order.status && order.status.toLowerCase() === 'completed',
                                    'text-red-400': order.status && order.status.toLowerCase() === 'cancelled',
                                }"
                            >
                                {{ order.status }}
                            </td>
                            <td class="p-3">
                                <span
                                    :class="{
                                        'text-green-400': order.status && order.status.toLowerCase() == 'paid',
                                        'text-red-400': order.status && order.status.toLowerCase() == 'unpaid',
                                    }"
                                >
                                    {{ order.payment_status }}
                                </span>
                            </td>
                            <td class="p-3">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                            <td class="space-x-2 p-3">
                                <button
                                    @click="viewInvoice(order.id)"
                                    class="mr-2 border px-4 py-2 text-blue-400 hover:cursor-pointer hover:bg-gray-100"
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
</template>
