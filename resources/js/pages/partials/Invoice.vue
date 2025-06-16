<script setup lang="ts">
import JsBarcode from 'jsbarcode';
import { computed, defineEmits, nextTick, ref, watch } from 'vue';

const props = defineProps({
    order: {
        type: Object as () => {
            id: string;
            user: { name: string; avatar?: string | null; mobile: string } | null;
            total_amount: string | number;
            status: string | null;
            payment_status: string | null;
            created_at: string;
            items: {
                id: number;
                quantity: number;
                price: string | number;
                product: { name: string; image?: string | null } | null;
            }[];
        } | null,
        default: null,
    },
    show: Boolean,
});

const emit = defineEmits(['close', 'update-status']);

function closeModal() {
    emit('close');
}

const selectedStatus = ref<string | null>(null);
const selectedPaymentStatus = ref<string | null>(null);

watch(
    () => props.show,
    (visible) => {
        if (visible && props.order) {
            selectedStatus.value = props.order.status || 'Pending';
            selectedPaymentStatus.value = props.order.payment_status || 'unpaid';
            nextTick(() => generateBarcode());
        }
    },
    { immediate: true },
);

const totalAmountComputed = computed(() => {
    if (!props.order || !props.order.items) return '0.00';
    const total = props.order.items.reduce((sum, item) => {
        const price = Number(item.price ?? 0);
        const qty = item.quantity ?? 1;
        return sum + price * qty;
    }, 0);
    return total.toFixed(2);
});

function updateStatus() {
    if (props.order) {
        emit('update-status', {
            orderId: props.order.id,
            newStatus: selectedStatus.value,
            newPaymentStatus: selectedPaymentStatus.value,
        });
    }
}

function generateBarcode() {
    if (props.order?.id) {
        JsBarcode('#barcode', props.order.id, {
            format: 'CODE128',
            displayValue: false,
            height: 40,
            lineColor: '#fff',
        });
    }
}
</script>

<template>
    <div v-if="show" class="bg-opacity-70 fixed inset-0 z-50 flex justify-center bg-black backdrop-blur-sm">
        <div
            class="relative top-1/2 max-h-[90vh] w-full max-w-4xl -translate-y-1/2 transform overflow-y-auto rounded-xl border border-white/20 bg-white/10 p-6 shadow-lg shadow-black/40 backdrop-blur-md"
        >
            <!-- Header -->
            <div class="mb-4 flex flex-col items-center justify-between border-b border-white/30 pb-4 md:flex-row">
                <div>
                    <h2 class="text-3xl font-bold text-white">Invoice: Order #{{ order?.id }}</h2>
                    <svg id="barcode"></svg>
                </div>
                <div class="mt-4 md:mt-0">
                    <label class="mr-2 font-semibold text-white">Status:</label>
                    <select
                        v-model="selectedStatus"
                        @change="updateStatus"
                        class="rounded border border-white/30 bg-white/20 px-3 py-1 text-white focus:outline-none"
                    >
                        <option class="text-gray-800">Pending</option>
                        <option class="text-gray-800">Processing</option>
                        <option class="text-gray-800">completed</option>
                        <option class="text-gray-800">in courier</option>
                        <option class="text-gray-800">Shipped</option>
                        <option class="text-gray-800">Delivered</option>
                        <option class="text-gray-800">Abandoned</option>
                        <option class="text-gray-800">Returened</option>
                        <option class="text-gray-800">Cancelled</option>
                    </select>
                </div>
                <div class="mt-4 md:mt-0">
                    <label class="mr-2 font-semibold text-white">Payment Status:</label>
                    <select
                        v-model="selectedPaymentStatus"
                        @change="updateStatus"
                        class="rounded border border-white/30 bg-white/20 px-3 py-1 text-white focus:outline-none"
                    >
                        <option class="text-gray-800">paid</option>
                        <option class="text-gray-800">unpaid</option>
                    </select>
                </div>
            </div>

            <!-- Customer Info -->
            <section class="mb-6 text-white">
                <h3 class="mb-2 text-lg font-semibold">Customer Information</h3>
                <p><strong>Name:</strong> {{ order?.user?.name || 'Unknown' }}</p>
                <p><strong>Mobile:</strong> {{ order?.user?.mobile || 'Unknown' }}</p>
                <p><strong>Order Date:</strong> {{ order ? new Date(order.created_at).toLocaleDateString() : '-' }}</p>
            </section>

            <!-- Items -->
            <section class="mb-6 text-white">
                <h3 class="mb-2 text-lg font-semibold">Items</h3>
                <table class="w-full rounded-lg border border-white/30 text-left text-sm text-white/90">
                    <thead>
                        <tr class="bg-white/20">
                            <th class="border-b border-white/30 p-2">Product</th>
                            <th class="border-b border-white/30 p-2">Quantity</th>
                            <th class="border-b border-white/30 p-2">Price (৳)</th>
                            <th class="border-b border-white/30 p-2">Subtotal (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order?.items || []" :key="item.id" class="border-b border-white/20 last:border-b-0">
                            <td class="p-2">{{ item.product?.name || 'Unknown Product' }}</td>
                            <td class="p-2">{{ item.quantity }}</td>
                            <td class="p-2">{{ Number(item.price ?? 0).toFixed(2) }}</td>
                            <td class="p-2">
                                {{ (Number(item.price ?? 0) * (item.quantity ?? 1)).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Total -->
            <section class="text-right text-xl font-bold text-white">Total Amount: ৳{{ totalAmountComputed }}</section>

            <!-- Close Button -->
            <div class="mt-6 flex justify-end">
                <button
                    @click="closeModal"
                    class="rounded bg-blue-500 px-5 py-2 text-white transition hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                >
                    Close
                </button>
            </div>
        </div>
        {{ Credential }}
    </div>
</template>

<style scoped>
#barcode {
    background-color: transparent;
    margin-top: 10px;
}
</style>
