<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});
const form = ref({
    category_id: '',
    name: '',
    slug: '',
    description: '',
    price: '',
    discount_price: '',
    quantity: '',
    image: '',
    status: 'active',
});

import { watch } from 'vue';

// Slug generator utility
function slugify(text: string) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[\s\W-]+/g, '-') // Replace spaces and non-word characters with dashes
        .replace(/^-+|-+$/g, ''); // Remove leading/trailing dashes
}

// Auto-generate slug when name changes
watch(
    () => form.value.name,
    (newName) => {
        if (!form.value.slug || form.value.slug === '' || form.value.slug === slugify(form.value.slug)) {
            form.value.slug = slugify(newName);
        }
    },
);

function submit() {
    const payload = new FormData();
    for (const key in form.value) {
        payload.append(key, form.value[key] as any);
    }

    router.post(route('products.store'), payload, {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Create Product" />
    <AppLayout>
        <div class="bg-gray-500 px-4 py-10">
            <!-- Page Heading -->
            <div class="mb-8 rounded-xl bg-gradient-to-br from-indigo-700 via-purple-700 to-blue-600 p-6 shadow-lg">
                <h1 class="flex items-center gap-2 text-3xl font-bold text-white">🛍️ Add New Product</h1>
                <p class="mt-1 text-sm text-indigo-100">Easily manage your inventory by adding new items.</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8 rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900">
                <!-- Section: Basic Info -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">📦 Basic Information</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select
                                v-model="form.category_id"
                                class="w-full rounded-lg border px-3 py-2 transition focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Choose Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border px-3 py-2 transition focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section: Slug & Description -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">📝 Details</h2>
                    <div class="grid gap-6">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="w-full rounded-lg border px-3 py-2 transition focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-lg border px-3 py-2 transition focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Pricing & Stock -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">💰 Pricing & Inventory</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Price</label>
                            <input
                                v-model="form.discount_price"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                            <input
                                v-model="form.quantity"
                                type="number"
                                class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section: Status & Image -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">📸 Media & Status</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Image</label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="(e) => (form.image = e.target.files[0])"
                                class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Preview -->
                    <div v-if="form.image" class="mt-4 flex items-center gap-4">
                        <div v-if="form.image && typeof form.image === 'object'" class="mt-2">
                            <img
                                :src="URL.createObjectURL(form.image)"
                                alt="Preview"
                                class="h-24 rounded border object-contain dark:border-gray-700"
                            />
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ form.name || 'Product Name' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ form.description || 'Description will appear here' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-5 py-3 font-bold text-white transition hover:scale-105 hover:bg-indigo-700"
                    >
                        🚀 Submit Product
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
<style scoped>
input {
    margin: 0;
    outline: 2px solid #ccc;
}
textarea {
    margin: 0;
    border: 2px solid #ccc;
}
</style>
