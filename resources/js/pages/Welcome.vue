<script setup lang="ts">
import useCart from '@/composables/useCart';
import Header from '@/pages/frontend/Header.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const { cart, addToCart } = useCart();
const products = ref([]);
const loading = ref(true);

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

function handleAddToCart(product: any) {
    const added = addToCart(product);
    alert(added ? `${product.name} added to cart!` : `${product.name} is already in the cart.`);
}

function directCheckout(product: any) {
    addToCart(product);
    router.visit('/checkout');
}

const props = defineProps<{
    landingPages: {
        id: number;
        title: string;
        description: string;
        banner: string;
        images: string[];
        product_id: number;
        product: any;
    }[];
}>();
</script>

<template>
    <Head title="Home" />
    <Header />

    <div class="container mx-auto px-4 py-8 text-gray-900 dark:text-white">
        <!-- <h1 class="mb-6 text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">🌟 Top Selling Products</h1>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="page in props.landingPages"
                :key="page.id"
                class="rounded-lg border bg-white p-4 shadow-xl dark:border-neutral-700 dark:bg-neutral-800"
            >
                <img :src="`/storage/${page.banner}` || '/placeholder.png'" class="mb-4 h-40 w-full rounded object-cover" />
                <h2 class="mb-2 text-xl font-bold">{{ page.title }}</h2>
                <p class="mb-4 text-gray-600 dark:text-gray-400">{{ products.find((p) => p.id === page.product_id)?.price || 'Not Defined' }}</p>
            </div>
        </div> -->
        <div class="bannerSection">
            <img src="http://127.0.0.1:8000/storage/banners/1.png" alt="Banner 1" class="banner" />
            <img src="/storage/banners/2.png" alt="Banner 2" class="banner" />
            <img src="/storage/banners/3.png" alt="Banner 3" class="banner" />
        </div>
        <h1 class="mt-10 mb-6 text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">🛍️ New Arrival Products</h1>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <template v-if="loading">
                <div v-for="n in 8" :key="n" class="animate-pulse rounded-xl bg-neutral-700 p-4 shadow">
                    <div class="mb-4 h-48 w-full rounded bg-gray-600"></div>
                    <div class="mb-2 h-5 w-3/4 rounded bg-gray-500"></div>
                    <div class="mb-4 h-4 w-1/2 rounded bg-gray-600"></div>
                    <div class="h-10 w-full rounded bg-gray-600"></div>
                </div>
            </template>

            <template v-else>
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex flex-col rounded-xl bg-white p-4 shadow transition hover:shadow-lg dark:bg-neutral-800"
                >
                    <img :src="product.image || '/placeholder.png'" class="mb-4 h-48 w-full rounded object-contain" />

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
