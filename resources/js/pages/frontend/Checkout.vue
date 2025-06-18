<script setup>
import districts from '@/composables/district.js';
import useCart from '@/composables/useCart';
import Header from '@/pages/frontend/Header.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const { cart, increaseQuantity, decreaseQuantity, removeFromCart, clearCart } = useCart();

const name = ref('');
const mobile = ref('');
const email = ref('');
const address = ref('');
const selectedDistrict = ref('');
const isSubmitting = ref(false);

function getDeliveryCharge() {
    return selectedDistrict.value === 'Dhaka' ? 70 : selectedDistrict.value ? 130 : 0;
}

const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));
const total = computed(() => cartTotal.value + getDeliveryCharge());

const validateForm = () => {
    if (!name.value || !mobile.value || !email.value || !selectedDistrict.value) {
        toast.error('সব ফিল্ড পূরণ করুন');
        return false;
    }
    if (!/^[0-9]{10,15}$/.test(mobile.value)) {
        toast.warning('সঠিক মোবাইল নম্বর দিন');
        return false;
    }
    if (!/\S+@\S+\.\S+/.test(email.value)) {
        toast.warning('সঠিক ইমেইল দিন');
        return false;
    }
    return true;
};

const placeOrder = async () => {
    if (!validateForm()) return;
    if (cart.value.length === 0) {
        toast.warning('কার্ট খালি!');
        return;
    }

    isSubmitting.value = true;

    const firstProduct = cart.value[0];

    try {
        console.log({
            name: name.value,
            mobile: mobile.value,
            email: email.value,
            shipping_address: address.value + ', ' + selectedDistrict.value,
            payment_status: 'unpaid',
            source: 'website',
            cart: cart.value,
            total_amount: cartTotal.value,
            status: 'pending',
            delivery_charge: getDeliveryCharge(),
        });
        const res = await axios
            .post('http://127.0.0.1:8000/api/v1/order', {
                name: name.value,
                mobile: mobile.value,
                email: email.value,
                shipping_address: address.value + ', ' + selectedDistrict.value,
                payment_status: 'unpaid',
                source: 'website',
                product_id: firstProduct.id,
                quantity: firstProduct.quantity,
                price: firstProduct.price,
                total_price: firstProduct.price * firstProduct.quantity,
                total_amount: cartTotal.value,
                status: 'pending',
                delivery_charge: getDeliveryCharge(),
            })
            .then((res) => res.data)
            .catch((err) => {
                toast.error(err.response?.data?.message || 'কিছু সমস্যা হয়েছে, আবার চেষ্টা করুন');
                throw err;
                console.error(err);
            });
        toast.success(res.data.message || 'অর্ডার সফলভাবে প্লেস হয়েছে!');
        clearCart();
        name.value = '';
        mobile.value = '';
        email.value = '';
        address.value = '';
        selectedDistrict.value = '';
    } catch (err) {
        toast.error(err.response?.data?.message || 'কিছু সমস্যা হয়েছে, আবার চেষ্টা করুন');
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head title="Checkout" />
    <Header />
    <div class="mx-auto max-w-6xl px-4 py-10 dark:bg-neutral-900 dark:text-white">
        <h2 class="mb-8 text-center text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">🛍️ Checkout Page</h2>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <!-- Cart Section -->
            <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-neutral-800">
                <h3 class="mb-4 border-b pb-2 text-2xl font-semibold">Your Cart</h3>

                <div v-if="cart.length === 0" class="text-center text-gray-400 italic dark:text-gray-300">আপনার কার্ট খালি 🛒</div>

                <div v-else>
                    <div v-for="item in cart" :key="item.id" class="flex items-center gap-4 border-b py-4">
                        <img :src="item.image || '/placeholder.png'" class="h-16 w-16 rounded-lg object-cover" />
                        <div class="flex-1">
                            <h4 class="font-semibold">{{ item.title || item.name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-300">৳{{ item.price }} × {{ item.quantity }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="decreaseQuantity(item.id)"
                                :disabled="item.quantity === 1"
                                class="rounded bg-gray-200 px-2 py-1 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                −
                            </button>
                            <span class="px-2 font-semibold">{{ item.quantity }}</span>
                            <button
                                @click="increaseQuantity(item.id)"
                                class="rounded bg-gray-200 px-2 py-1 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                +
                            </button>
                            <button @click="removeFromCart(item.id)" class="ml-2 text-sm text-red-500 hover:underline">Remove</button>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2 border-t pt-4">
                        <div class="flex justify-between text-lg">
                            <span>Delivery Charge:</span>
                            <span class="font-semibold text-red-600">৳{{ getDeliveryCharge() }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span class="text-green-600">৳{{ total.toFixed(2) }}</span>
                        </div>
                        <button
                            @click="clearCart"
                            class="mt-4 w-full rounded-lg bg-red-100 py-2 font-semibold text-red-600 hover:bg-red-200 dark:bg-red-800 dark:text-white dark:hover:bg-red-700"
                        >
                            Clear Cart
                        </button>
                    </div>
                </div>
            </section>

            <!-- Info Form Section -->
            <section class="rounded-xl bg-white p-6 shadow-lg dark:bg-neutral-800">
                <h3 class="mb-4 border-b pb-2 text-2xl font-semibold">Your Info</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block font-medium">Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="name"
                            type="text"
                            placeholder="আপনার নাম"
                            class="w-full rounded-md border px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block font-medium">Mobile <span class="text-red-500">*</span></label>
                        <input
                            v-model="mobile"
                            type="tel"
                            placeholder="017xxxxxxxx"
                            class="w-full rounded-md border px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block font-medium">Email <span class="text-red-500">*</span></label>
                        <input
                            v-model="email"
                            type="email"
                            placeholder="you@email.com"
                            class="w-full rounded-md border px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block font-medium">District <span class="text-red-500">*</span></label>
                        <select
                            v-model="selectedDistrict"
                            class="w-full rounded-md border px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        >
                            <option value="" disabled>জেলা নির্বাচন করুন</option>
                            <option v-for="district in districts" :key="district" :value="district">{{ district }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium">Address <span class="text-red-500">*</span></label>
                        <textarea
                            v-model="address"
                            placeholder="আপনার ঠিকানা"
                            class="w-full rounded-md border px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        ></textarea>
                    </div>
                    <button
                        @click="placeOrder"
                        :disabled="isSubmitting"
                        class="mt-6 w-full rounded-lg bg-indigo-600 py-3 text-lg font-semibold text-white hover:bg-indigo-700"
                    >
                        {{ isSubmitting ? 'Placing Order...' : 'Place Order Now' }}
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
