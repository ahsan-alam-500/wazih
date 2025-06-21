<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    product: {
        category_id: number | string;
        name: string;
        slug: string;
        description: string;
        price: number | string;
        discount_price?: number | string | null;
        quantity: number;
        image: string;
        status: string;
    };
    categories: Array<{ id: number; name: string }>;
}>();

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
    image_preview: '',
});

onMounted(() => {
    Object.assign(form.value, props.product);
});

// Auto-generate slug
function slugify(text: string) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[\s\W-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
watch(
    () => form.value.name,
    (newName) => {
        if (!form.value.slug || form.value.slug === slugify(form.value.slug)) {
            form.value.slug = slugify(newName);
        }
    },
);

// File input change
function handleImageUpload(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.value.image = file;
        form.value.image_preview = URL.createObjectURL(file);
    }
}

// Submit
function submit() {
    const data = new FormData();
    Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null) data.append(key, value);
    });

    router.post(route('products.update', props.product.id), data, {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit Product - ${props.product.name}`" />
    <AppLayout>
        <div class="mx-auto max-w-4xl space-y-8 px-4 py-8">
            <div class="rounded-xl bg-gradient-to-r from-amber-600 to-yellow-500 p-6 shadow-lg">
                <h1 class="text-3xl font-extrabold text-white">✏️ Edit Product</h1>
                <p class="mt-1 text-sm text-yellow-100">Update the product information below.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <!-- Category + Name -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <select
                            v-model="form.category_id"
                            class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Select category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Slug -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                    <input
                        v-model="form.slug"
                        type="text"
                        class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-lg border px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    ></textarea>
                </div>

                <!-- Price, Discount, Quantity, Status -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                </div>

                <!-- Image -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product Image</label>
                    <input
                        type="file"
                        @change="handleImageUpload"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-white hover:file:bg-indigo-700"
                    />
                </div>

                <!-- Preview -->
                <div v-if="form.image_preview" class="flex items-center gap-4">
                    <img :src="form.image_preview" alt="Preview" class="h-24 w-24 rounded-lg border object-cover dark:border-gray-600" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white">{{ form.name || 'Product Name' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ form.description || 'Product description' }}</p>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="mt-4 w-full rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white transition hover:bg-amber-700">
                    💾 Update Product
                </button>
            </form>
        </div>
    </AppLayout>
</template>
