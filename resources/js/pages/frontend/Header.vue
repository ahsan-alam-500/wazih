<script setup>
import useCart from '@/composables/useCart';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const { cart } = useCart();
const isDrawerOpen = ref(false);

// Lock scroll when drawer is open
watch(isDrawerOpen, (open) => {
    if (open) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

// Close drawer on ESC key press
function onKeydown(e) {
    if (e.key === 'Escape' && isDrawerOpen.value) {
        isDrawerOpen.value = false;
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div class="sticky top-0 bg-gray-800 text-white">
        <nav class="container mx-auto flex items-center justify-between px-5 py-3">
            <div class="text-2xl font-bold">
                <Link href="/">
                    <img
                        class="h-10 w-15"
                        src="https://bponi.sgp1.cdn.digitaloceanspaces.com/bponi/file/1b48f6b3-67d3-4679-a511-fbd47fadb24d.png"
                        alt=""
                    />
                </Link>
            </div>

            <!-- Desktop nav -->
            <div class="hidden items-center space-x-6 md:flex">
                <Link href="/" class="hover:underline">Home</Link>
                <!-- Cart icon desktop -->
                <Link href="/cart" class="relative mt-1">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.3h11.1a1 1 0 00.9-1.3L17 13M7 13L5.4 5M17 13l1.6 3.2"
                        />
                    </svg>
                    <span v-if="cart.length" class="absolute -top-2 -right-2 rounded-full bg-red-600 px-2 text-xs text-white">
                        {{ cart.length }}
                    </span>
                </Link>
            </div>

            <!-- Mobile hamburger button -->
            <button @click="isDrawerOpen = true" class="focus:outline-none md:hidden">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </nav>

        <!-- Drawer + overlay -->
        <transition name="fade">
            <div v-if="isDrawerOpen" class="bg-opacity-50 fixed inset-0 z-40 bg-black" @click.self="isDrawerOpen = false">
                <transition name="slide">
                    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gray-900 p-6" @click.stop>
                        <div class="mb-8 flex items-center justify-between">
                            <h2 class="text-xl font-bold">Menu</h2>
                            <button @click="isDrawerOpen = false" aria-label="Close Menu" class="text-white hover:text-gray-400 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <nav class="flex flex-col space-y-4">
                            <Link href="/" class="hover:underline" @click="isDrawerOpen = false">Home</Link>
                            <Link href="/categories" class="hover:underline" @click="isDrawerOpen = false">Categories</Link>
                            <Link href="/cart" class="relative flex items-center gap-2 hover:underline" @click="isDrawerOpen = false">
                                Cart
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.3h11.1a1 1 0 00.9-1.3L17 13M7 13L5.4 5M17 13l1.6 3.2"
                                    />
                                </svg>
                                <span v-if="cart.length" class="rounded-full bg-red-600 px-2 text-xs text-white">
                                    {{ cart.length }}
                                </span>
                            </Link>
                        </nav>
                    </aside>
                </transition>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Fade transition for overlay */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Slide transition for drawer */
.slide-enter-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(-100%);
}
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
