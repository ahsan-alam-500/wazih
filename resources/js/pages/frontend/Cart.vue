<script setup>
import useCart from '@/composables/useCart';
import Header from '@/pages/frontend/Header.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const { cart, removeFromCart, clearCart, increaseQuantity, decreaseQuantity } = useCart();

const totalPrice = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));
</script>

<template>
    <Head title="Your Cart" />
    <Header />

    <main class="mx-auto max-w-5xl px-4 py-10 dark:bg-neutral-900 dark:text-white">
        <h1 class="mb-6 text-center text-3xl font-bold">🛒 আপনার কার্ট</h1>

        <section v-if="cart.length" class="space-y-6">
            <article
                v-for="item in cart"
                :key="item.id"
                class="flex flex-col items-center justify-between rounded-xl bg-white p-4 shadow sm:flex-row dark:bg-neutral-800"
            >
                <div class="flex w-full items-center gap-4 sm:w-auto">
                    <img :src="item.image || '/placeholder.png'" alt="Product Image" class="h-20 w-20 rounded-lg border object-cover" />
                    <div>
                        <h2 class="text-lg font-semibold">{{ item.name }}</h2>
                        <p class="font-medium text-green-600">৳{{ item.price }}</p>

                        <div class="mt-2 flex items-center gap-2">
                            <button
                                @click="decreaseQuantity(item.id)"
                                aria-label="Decrease"
                                class="rounded bg-gray-300 px-2 py-1 text-xl font-bold hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                −
                            </button>
                            <span class="font-semibold">{{ item.quantity }}</span>
                            <button
                                @click="increaseQuantity(item.id)"
                                aria-label="Increase"
                                class="rounded bg-gray-300 px-2 py-1 text-xl font-bold hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-right sm:mt-0">
                    <p class="text-lg font-bold text-gray-800 dark:text-white">৳{{ item.price * item.quantity }}</p>
                    <button @click="removeFromCart(item.id)" class="mt-2 text-sm text-red-500 hover:underline">Remove</button>
                </div>
            </article>

            <footer class="mt-10 flex flex-col items-center justify-between gap-6 border-t pt-6 md:flex-row">
                <div class="text-lg">
                    <p class="text-xl font-bold">
                        মোট: <span class="text-green-600">৳{{ totalPrice }}</span>
                    </p>
                    <button @click="clearCart" class="mt-3 rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">কার্ট খালি করুন</button>
                </div>

                <div class="text-right">
                    <p class="mb-2 text-xl font-bold">অর্ডার করতে প্রস্তুত?</p>
                    <Link :href="route('checkout')" class="mt-3 inline-block rounded bg-indigo-600 px-6 py-2 text-lg text-white hover:bg-indigo-700">
                        Checkout করুন
                    </Link>
                </div>
            </footer>
        </section>

        <div v-else class="mt-10 text-center text-gray-500 dark:text-gray-300">
            <p class="text-xl">আপনার কার্ট এখনো খালি।</p>
            <Link
                href="/"
                class="mt-6 inline-block rounded border border-gray-400 px-5 py-2 hover:bg-blue-100 hover:text-blue-800 dark:border-gray-600 dark:hover:bg-neutral-800"
            >
                🏠 হোম পেজে ফিরে যান
            </Link>
        </div>
    </main>
</template>
