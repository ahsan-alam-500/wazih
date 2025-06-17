<script setup lang="ts">
import { useClickOutside } from '@/composables/useClickOutside';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({
    products: Array,
});

const form = useForm({
    title: '',
    description: '',
    banner: null,
    images: [],
    product_id: '',
});

const dropdownRef = ref(null);
const showDropdown = ref(false);

useClickOutside(dropdownRef, () => {
    showDropdown.value = false;
});
function selectProduct(id: number) {
    form.product_id = id;
    showDropdown.value = false;
}

function selectedProduct() {
    return props.products.find((p) => p.id === form.product_id);
}

function handleImageUpload(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        form.images = Array.from(target.files);
    }
}

function handleBannerUpload(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.banner = target.files[0];
    }
}

function submit() {
    form.post('/admin/landingPages', {
        forceFormData: true, // image বা file থাকলে এটি আবশ্যক
        onSuccess: () => {
            showDropdown.value = false;
            form.reset(); // সকল ফর্ম ফিল্ড reset হবে
            form.banner = null; // image field reset
            form.images = []; // multiple image field reset
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        },
    });
}
</script>
<template>
    <Head title="Create Landing Page" />
    <AppLayout>
        <div class="min-h-screen bg-neutral-900 p-6 text-white">
            <h1 class="mb-6 text-2xl font-bold">📄 Create Landing Page</h1>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-neutral-800 p-6 shadow-xl">
                <!-- Title -->
                <div>
                    <label class="mb-1 block font-semibold">Title</label>
                    <input v-model="form.title" type="text" class="w-full rounded border border-neutral-600 bg-neutral-900 p-2 text-white" />
                    <span class="text-sm text-red-400" v-if="form.errors.title">{{ form.errors.title }}</span>
                </div>

                <!-- Description -->
                <div>
                    <label class="mb-1 block font-semibold">Description</label>
                    <textarea v-model="form.description" rows="4" class="w-full rounded border border-neutral-600 bg-neutral-900 p-2 text-white" />
                    <span class="text-sm text-red-400" v-if="form.errors.description">{{ form.errors.description }}</span>
                </div>

                <!-- Banner Upload -->
                <div>
                    <label class="mb-1 block font-semibold">Banner Image</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="handleBannerUpload"
                        class="w-full rounded border border-neutral-600 bg-neutral-900 p-2 text-white"
                    />
                </div>

                <!-- Multiple Image Upload -->
                <div>
                    <label class="mb-1 block font-semibold">Gallery Images</label>
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleImageUpload"
                        class="w-full rounded border border-neutral-600 bg-neutral-900 p-2 text-white"
                    />
                </div>
                <!-- Product Selector with Image -->
                <div>
                    <label class="mb-1 block font-semibold">Product</label>
                    <div class="relative">
                        <div
                            class="flex items-center justify-between rounded border border-neutral-600 bg-neutral-900 p-2 hover:cursor-pointer"
                            @click="showDropdown = !showDropdown"
                        >
                            <div class="flex items-center gap-2">
                                <img
                                    v-if="selectedProduct()?.image"
                                    :src="selectedProduct()?.image"
                                    class="h-8 w-8 rounded object-cover"
                                    alt="Product"
                                />
                                <span>
                                    {{ selectedProduct() ? selectedProduct().name : 'Select Product' }}
                                </span>
                            </div>
                            <span>▾</span>
                        </div>

                        <div
                            v-show="showDropdown"
                            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded border border-neutral-600 bg-neutral-900 shadow-lg"
                        >
                            <div
                                v-for="product in props.products"
                                :key="product.id"
                                class="flex cursor-pointer items-center gap-3 p-2 hover:bg-neutral-700"
                                @click="selectProduct(product.id)"
                            >
                                <img :src="product.image" class="h-8 w-8 rounded object-cover" alt="Product" />
                                <span>{{ product.name }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-sm text-red-400" v-if="form.errors.product_id">{{ form.errors.product_id }}</span>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Creating...' : 'Create Page' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
