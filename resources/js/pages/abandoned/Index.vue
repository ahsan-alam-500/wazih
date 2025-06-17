<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

interface AbandonedOrder {
    id: number;
    name: string;
    mobile: string;
    email: string;
    product_name: string;
    product_image: string;
    product_price: number;
}

const props = defineProps<{
    abandonedOrders: AbandonedOrder[];
}>();

const processingId = ref<number | null>(null);

async function handleAddToOrder(order: AbandonedOrder) {
    try {
        processingId.value = order.id;

        // const response = await axios.post('http://127.0.0.1:8000/api/v1/abandoned/to/order', {
        //     name: order.name,
        //     mobile: order.mobile,
        //     email: order.email,
        //     product_name: order.product_name,
        //     product_image: order.product_image,
        //     product_price: order.product_price,
        // });

        const response = await axios.post('http://127.0.0.1:8000/api/v1/abandoned/to/order', {
            name: order.name,
            mobile: order.mobile,
            email: order.email,
            product_name: order.product_name,
            product_image: order.product_image,
            product_price: order.product_price,
            price: order.product_price, // ✅
            quantity: 1, // ✅ defaulting to 1
            total_price: order.product_price, // ✅
            product_id: order.id, // ✅ just using abandoned ID if no actual ID
        });

        alert('Order added successfully!');
    } catch (err) {
        console.error(err);
        alert('Failed to add order.');
    } finally {
        processingId.value = null;
    }
}
</script>

<template>
    <Head title="Abandoned Orders" />

    <AppLayout>
        <div class="min-h-screen bg-neutral-900 p-6 text-white">
            <h1 class="mb-6 text-2xl font-bold">🛑 Abandoned Orders</h1>

            <div class="overflow-x-auto rounded-xl border border-neutral-700 bg-neutral-800 p-6 shadow">
                <table class="w-full min-w-[700px] text-left text-sm md:text-base lg:text-lg">
                    <thead>
                        <tr class="border-b border-neutral-600">
                            <th class="p-3">Name</th>
                            <th class="p-3">Mobile</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Product</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in props.abandonedOrders" :key="order.id" class="border-b border-neutral-700 hover:bg-neutral-700/40">
                            <td class="p-3">{{ order.name }}</td>
                            <td class="p-3">{{ order.mobile }}</td>
                            <td class="p-3">{{ order.email }}</td>
                            <td class="flex items-center gap-2 p-3">
                                <img
                                    :src="order.product_image || 'https://via.placeholder.com/40'"
                                    alt="Product"
                                    class="h-10 w-10 rounded object-cover"
                                />
                                <span>{{ order.product_name }}</span>
                            </td>
                            <td class="p-3">৳{{ order.product_price }}</td>
                            <td class="p-3">
                                <button
                                    :disabled="processingId === order.id"
                                    @click.prevent="handleAddToOrder(order)"
                                    class="border border-neutral-600 px-4 py-2 text-green-400 hover:bg-neutral-600/40 disabled:opacity-50"
                                >
                                    {{ processingId === order.id ? 'Processing...' : 'Add To Order' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
