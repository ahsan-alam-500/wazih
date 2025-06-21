<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    products: {
        id: number;
        name: string;
        description: string;
        price: string;
        image: string;
    }[];
}>();

// Delete
function destroy(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', productId));
    }
}
</script>

<template>
    <Head title="Products" />

    <AppLayout>
        <div class="min-h-screen p-6 dark:bg-gray-900">
            <div class="flex items-center justify-around pb-6 md:pb-10">
                <h1 class="mb-6 text-2xl font-bold text-gray-800 dark:text-white">🛍️ All Products</h1>
                <Link :href="route('products.create')" class="btn btn-primary text-gray-800 dark:text-white">Add Product</Link>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="overflow-hidden rounded-lg border shadow transition-all hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <img :src="product.image" :alt="product.name" class="h-48 w-full object-cover" />
                    <div class="p-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                            {{ product.name }}
                        </h2>
                        <p class="line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ product.description }}
                        </p>
                        <div class="mt-2 text-right">
                            <span class="text-md font-bold text-green-600 dark:text-green-400"> ৳{{ product.price }} </span>
                        </div>

                        <Link :href="route('products.edit', product.id)" class="btn btn-primary text-gray-800 dark:text-white">Edit</Link>
                        <Button @click="destroy(product.id)" class="btn btn-danger text-gray-800 dark:text-white">Delete</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
