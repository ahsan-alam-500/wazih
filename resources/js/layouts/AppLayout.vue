<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import Swal from 'sweetalert2';
import { onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

onMounted(async () => {
    try {
        const Pusher = (await import('pusher-js')).default;
        const Echo = (await import('laravel-echo')).default;

        window.Pusher = Pusher;

        // Echo instance
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        // echo using properly
        window.Echo.channel('orders').listen('.order.placed', (e: any) => {
            const orderNumber = e?.order?.order_number || 'N/A';

            Swal.fire({
                icon: 'success',
                title: 'New Order Placed',
                html: `<b>Order Number:</b> ${orderNumber}`,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        });
    } catch (error) {
        console.error('🔴 Echo Initialization Error:', error);
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
