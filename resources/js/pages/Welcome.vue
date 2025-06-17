<script setup lang="ts">
// 🛒 Import composable for cart actions
import useCart from '@/composables/useCart';

// 🧱 Layout components
import Header from '@/pages/frontend/Header.vue'; // Make sure file exists at given path
import { Head, Link, router } from '@inertiajs/vue3';

// 📡 For fetching data
import axios from 'axios';
import { onMounted, ref } from 'vue';

// 🛍️ Cart & product data
const { cart, addToCart } = useCart();
const products = ref([]); // Holds product list
const loading = ref(true); // Controls skeleton loader state

// 🚀 Fetch products on component mount
onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/products');
        products.value = res.data.products;
    } catch (e) {
        alert('❌ Error loading products. Please try again.');
    } finally {
        loading.value = false;
    }
});

// ➕ Add product to cart
function handleAddToCart(product: any) {
    const added = addToCart(product);
    alert(added ? `${product.name} added to cart!` : `${product.name} is already in the cart.`);
}

// ⚡ Directly go to checkout after adding
function directCheckout(product: any) {
    addToCart(product);
    router.visit('/checkout');
}
</script>

<template>
    <Head title="Home" />
    <Header />

    <div class="container mx-auto px-4 py-8">
        <h1 class="mb-6 text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">🛍️ New Arrival Products</h1>

        <!-- 🔄 Loading skeleton UI -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <template v-if="loading">
                <div v-for="n in 8" :key="n" class="animate-pulse rounded-xl bg-neutral-700 p-4 shadow dark:bg-neutral-800">
                    <div class="mb-4 h-48 w-full rounded bg-gray-600"></div>
                    <div class="mb-2 h-5 w-3/4 rounded bg-gray-500"></div>
                    <div class="mb-4 h-4 w-1/2 rounded bg-gray-600"></div>
                    <div class="h-10 w-full rounded bg-gray-600"></div>
                </div>
            </template>

            <!-- 🛒 Product Cards -->
            <template v-else>
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex flex-col rounded-xl bg-white p-4 shadow transition-all hover:shadow-lg dark:bg-neutral-800 dark:text-white"
                >
                    <img :src="product.image || '/placeholder.png'" class="mb-4 h-48 w-full rounded object-contain" alt="Product Image" />

                    <Link
                        :href="route('product.details', [product.id, product.name])"
                        class="mb-1 line-clamp-2 text-lg font-bold text-gray-900 hover:underline dark:text-white"
                    >
                        {{ product.name }}
                    </Link>

                    <p class="mb-3 font-semibold text-green-600 dark:text-green-400">৳{{ product.price }}</p>

                    <div class="mt-auto flex gap-2 pt-4">
                        <button
                            @click="directCheckout(product)"
                            class="flex-1 rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Buy Now
                        </button>
                        <button
                            @click="handleAddToCart(product)"
                            class="flex-1 rounded bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700"
                        >
                            Add to Cart
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
