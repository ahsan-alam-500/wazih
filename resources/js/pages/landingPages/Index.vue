<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    landingPages: {
        id: number;
        title: string;
        content: string;
        banner: string;
    }[];
}>();
</script>

<template>
    <Head title="Landing Pages" />
    <AppLayout>
        <div class="min-h-screen bg-neutral-900 p-6 text-white">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold">📄 Landing Pages</h1>
                <Link
                    href="/admin/landingPages/create"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-700"
                >
                    + Create New
                </Link>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="page in props.landingPages"
                    :key="page.id"
                    class="group relative overflow-hidden rounded-2xl border border-neutral-700 bg-neutral-800 shadow-lg transition hover:border-blue-500"
                >
                    <!-- Banner Image -->
                    <img
                        :src="`/storage/${page.banner}`"
                        alt="Banner"
                        class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                    />

                    <!-- Content -->
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="truncate text-lg font-semibold">{{ page.title }}</h2>
                            <span class="text-xs text-gray-400">#{{ page.id }}</span>
                        </div>

                        <p class="line-clamp-3 text-sm text-gray-400">
                            {{ page.content }}
                        </p>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-3">
                            <Link
                                :href="`/admin/landingPages/${page.id}/edit`"
                                class="rounded border border-blue-400 px-3 py-1 text-sm text-blue-400 transition hover:bg-blue-600 hover:text-white"
                            >
                                Edit
                            </Link>
                            <Link
                                :href="`/admin/landing-page/${page.id}`"
                                method="delete"
                                as="button"
                                class="rounded border border-red-400 px-3 py-1 text-sm text-red-400 transition hover:bg-red-600 hover:text-white"
                            >
                                Delete
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
