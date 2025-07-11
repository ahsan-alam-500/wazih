<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md">
        <div
            class="max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700"
        >
            <!-- Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">🔍 Search Orders by Product</h2>
                <button @click="$emit('close')" class="text-xl text-gray-500 hover:text-red-500 dark:text-gray-300">✕</button>
            </div>

            <div class="h-[80vh] overflow-y-auto px-6 pt-4 pb-6">
                <!-- Search -->
                <input
                    v-model="searchText"
                    type="text"
                    placeholder="Search product..."
                    class="mb-4 w-full rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />

                <!-- Filters -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        v-for="option in filterOptions"
                        :key="option.label"
                        @click="setDateFilter(option.value)"
                        class="rounded-full px-4 py-1.5 text-sm font-medium shadow-sm transition-all"
                        :class="{
                            'bg-blue-600 text-white': dateFilter === option.value,
                            'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600':
                                dateFilter !== option.value,
                        }"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <!-- Product List -->
                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                    <div
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                        :class="{ 'bg-blue-100 dark:bg-gray-700': selectedProduct?.id === product.id }"
                        @click="selectProduct(product)"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-white"
                        >
                            {{ product.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">{{ product.name }}</span>
                    </div>
                </div>

                <!-- Orders Table -->
                <div v-if="selectedProduct">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800 dark:text-white">
                        Orders for: {{ selectedProduct.name }} ({{ filteredOrders.length }})
                    </h3>

                    <div v-if="filteredOrders.length" class="overflow-x-auto rounded-lg border dark:border-gray-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-left dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2">Order ID</th>
                                    <th class="px-4 py-2">Image</th>
                                    <th class="px-4 py-2">Customer</th>
                                    <th class="px-4 py-2">Amount</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in filteredOrders"
                                    :key="order.id"
                                    class="border-t hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    <td class="px-4 py-2">{{ order.id }}</td>
                                    <td class="px-4 py-2">
                                        <img
                                            :src="order.items[0]?.product?.image ?? '/placeholder.jpg'"
                                            :alt="order.items[0]?.product?.name ?? 'Product'"
                                            class="h-10 w-10 rounded object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                                        />
                                    </td>
                                    <td class="px-4 py-2">{{ order.user?.name }}</td>
                                    <td class="px-4 py-2">৳{{ order.total_amount }}</td>
                                    <td class="px-4 py-2">{{ order.status }}</td>
                                    <td class="px-4 py-2">{{ formatDate(order.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">No orders found for this filter.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, defineEmits, onMounted, ref } from 'vue';

const emit = defineEmits(['close']);

interface Product {
    id: number;
    name: string;
}

interface Order {
    id: number;
    user: { name: string } | null;
    total_amount: number;
    status: string;
    created_at: string;
    items: {
        product_id: number;
        product?: {
            image?: string;
            name?: string;
        };
    }[];
}

const props = defineProps<{ show: boolean }>();

const products = ref<Product[]>([]);
const orders = ref<Order[]>([]);
const selectedProduct = ref<Product | null>(null);
const selectedProductOrders = ref<Order[]>([]);
const searchText = ref('');
const dateFilter = ref('all');
const loading = ref(true);

const filterOptions = [
    { label: 'Today', value: 'today' },
    { label: 'Yesterday', value: 'yesterday' },
    { label: 'This Week', value: 'week' },
    { label: 'This Month', value: 'month' },
    { label: 'Last Month', value: 'last_month' },
    { label: 'This Year', value: 'year' },
    { label: 'All', value: 'all' },
];

onMounted(async () => {
    try {
        const [productRes, orderRes] = await Promise.all([
            axios.get('http://127.0.0.1:8000/api/v1/products'),
            axios.get('http://127.0.0.1:8000/api/v1/orders'),
        ]);
        products.value = productRes.data.products;
        orders.value = orderRes.data.orders;
    } catch (error) {
        console.error('Failed to fetch data:', error);
    } finally {
        loading.value = false;
    }
});

const filteredProducts = computed(() => {
    return products.value.filter((p) => p.name.toLowerCase().includes(searchText.value.toLowerCase()));
});

function selectProduct(product: Product) {
    selectedProduct.value = product;

    selectedProductOrders.value = orders.value.filter((order) => {
        if (!Array.isArray(order.items)) return false;

        return order.items.some((item) => item.product_id === product.id);
    });
}

const filteredOrders = computed(() => {
    if (!selectedProduct) return [];

    if (dateFilter.value === 'all') return selectedProductOrders.value;

    const now = new Date();

    return selectedProductOrders.value.filter((order) => {
        const date = new Date(order.created_at);
        switch (dateFilter.value) {
            case 'today':
                return date.toDateString() === now.toDateString();
            case 'yesterday':
                const yesterday = new Date(now);
                yesterday.setDate(now.getDate() - 1);
                return date.toDateString() === yesterday.toDateString();
            case 'week':
                const startOfWeek = new Date(now);
                startOfWeek.setDate(now.getDate() - now.getDay());
                return date >= startOfWeek;
            case 'month':
                return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
            case 'last_month':
                const lastMonth = new Date(now);
                lastMonth.setMonth(now.getMonth() - 1);
                return date.getMonth() === lastMonth.getMonth() && date.getFullYear() === lastMonth.getFullYear();
            case 'year':
                return date.getFullYear() === now.getFullYear();
            default:
                return true;
        }
    });
});

function setDateFilter(value: string) {
    dateFilter.value = value;
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString();
}
</script>

<style scoped>
button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 24 6, 0.5);
}
</style>
