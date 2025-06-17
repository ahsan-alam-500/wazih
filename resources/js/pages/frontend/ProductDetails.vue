<script setup>
import useCart from '@/composables/useCart';
import Header from '@/pages/frontend/Header.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const { cart, addToCart } = useCart();

const props = defineProps({
    product: Object,
});

function directCheckout(product) {
    const added = addToCart(product);
    if (added) {
        toast.success(`${product.title || product.name} added to cart!`);
    }
    router.visit('/checkout');
}

// Main image source reactive state
const mainImage = ref(props.product.images?.[0] || props.product.thumbnail || '');

function handleAddToCart(product) {
    const added = addToCart(product);
    if (added) {
        toast.success(`${product.title || product.name} added to cart!`);
    } else {
        toast.info(`${product.title || product.name} is already in the cart. Increase quantity from cart.`);
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function selectImage(img) {
    mainImage.value = img;
}
</script>

<template>
    <Head :title="product.title" />
    <Header />
    <div class="container mx-auto mt-8 rounded bg-white px-4 py-6 shadow-md">
        <!-- Title and Price -->
        <div class="flex flex-col gap-8 md:flex-row">
            <!-- Images -->
            <div class="flex flex-col items-center md:w-1/3">
                <img :src="mainImage" :alt="product.title" class="max-h-96 w-full rounded object-contain shadow-md" />
                <div class="no-scrollbar mt-4 flex w-full space-x-3 overflow-x-auto py-2">
                    <img :src="product.image" :alt="`${product.title}`" class="cursor-pointer rounded border-2 object-contain" />
                </div>
            </div>

            <!-- Info -->
            <div class="space-y-5 md:w-2/3">
                <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
                <p class="text-2xl font-semibold text-green-600">৳{{ product.price }}</p>
                <p class="text-gray-600 italic">
                    Brand: <span class="font-medium">{{ product.brand }}</span>
                </p>
                <p class="leading-relaxed text-gray-700">{{ product.description }}</p>

                <!-- Specs -->
                <div class="rounded-lg bg-gray-100 p-5 shadow-inner">
                    <h3 class="mb-3 text-lg font-semibold">Product Details</h3>
                    <ul class="list-inside list-disc space-y-1 text-sm text-gray-700">
                        <li><strong>Category:</strong> {{ product.category.name || 'N/A' }}</li>
                        <li><strong>Stock:</strong> {{ product.quantity > 1 ? 'In Stock' : ('Out of Stock' ?? 'N/A') }}</li>
                        <li><strong>Discount:</strong> {{ product.price - product.discount_price ?? '0' }}</li>
                    </ul>
                </div>

                <button
                    @click="handleAddToCart(product)"
                    class="rounded-lg bg-green-600 px-8 py-3 font-semibold text-white shadow-md transition hover:bg-green-700"
                >
                    Add to Cart
                </button>

                <button
                    @click="directCheckout(product)"
                    class="mr-2 w-full cursor-pointer rounded bg-blue-600 py-2 text-white transition hover:bg-blue-700"
                >
                    Buy Now
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
</style>
