<script setup lang="ts">
// Import Sidebar components from your custom UI library
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

// Inertia imports for routing and active page detection
import { Link, usePage } from '@inertiajs/vue3';

// Import icons from lucide
import {
    BarChart,
    Brain,
    CheckCircle2,
    Clock,
    LayoutDashboard,
    Loader2,
    PackageCheck,
    ShoppingCart,
    Trash2,
    Truck,
    Undo2,
    Users,
    XCircle,
} from 'lucide-vue-next';

// Get current page URL from Inertia
const page = usePage();

// Define all sidebar items, including orders with status-based color hints
const items = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },

    // Orders (status-based)
    { title: 'All Orders', href: '/admin/orders', icon: ShoppingCart },
    { title: 'Pending', href: '/admin/orders?status=pending', icon: Clock, class: 'text-yellow-500' },
    { title: 'Processing', href: '/admin/orders?status=processing', icon: Loader2, class: 'text-blue-500' },
    { title: 'In Courier', href: '/admin/orders?status=in_courier', icon: Truck, class: 'text-indigo-500' },
    { title: 'Shipped', href: '/admin/orders?status=shipped', icon: PackageCheck, class: 'text-purple-500' },
    { title: 'Delivered', href: '/admin/orders?status=delivered', icon: CheckCircle2, class: 'text-green-600' },
    { title: 'Completed', href: '/admin/orders?status=completed', icon: CheckCircle2, class: 'text-green-500' },
    { title: 'Cancelled', href: '/admin/orders?status=cancelled', icon: XCircle, class: 'text-red-500' },
    { title: 'Returned', href: '/admin/orders?status=returned', icon: Undo2, class: 'text-orange-500' },
    { title: 'Abandoned', href: '/admin/orders?status=abandoned', icon: Trash2, class: 'text-gray-500' },

    // Other administrative links
    { title: 'Customers', href: '/admin/customers', icon: Users },
    { title: 'Employees', href: '/admin/employees', icon: Brain },
    { title: 'Reports', href: '/admin/reports', icon: BarChart },
];
</script>

<template>
    <!-- Sidebar group container -->
    <SidebarGroup class="px-2 py-0">
        <!-- Group title -->
        <SidebarGroupLabel>Admin Panel</SidebarGroupLabel>

        <!-- Sidebar menu container -->
        <SidebarMenu>
            <!-- Loop through each sidebar item -->
            <SidebarMenuItem v-for="item in items" :key="item.title" class="rounded-lg transition hover:bg-gray-100 dark:hover:bg-gray-800">
                <!-- Handle active link styling -->
                <SidebarMenuButton
                    as-child
                    :is-active="page.url === item.href || page.url.startsWith(item.href)"
                    :tooltip="item.title"
                    class="w-full"
                >
                    <!-- Actual clickable link -->
                    <Link
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 transition-colors"
                        :class="{
                            // Highlight if base path matches or query string contains status
                            'bg-gray-500 text-gray-900 dark:bg-gray-700 dark:text-white':
                                page.url.startsWith(item.href.split('?')[0]) &&
                                (!item.href.includes('?') || page.url.includes(item.href.split('=')[1])),
                            // Default dim style
                            'text-gray-600 dark:text-gray-300': !(
                                page.url.startsWith(item.href.split('?')[0]) &&
                                (!item.href.includes('?') || page.url.includes(item.href.split('=')[1]))
                            ),
                        }"
                    >
                        <!-- Icon component with optional color class -->
                        <component :is="item.icon" class="h-5 w-5" :class="item.class" />
                        <!-- Link text -->
                        <span class="text-sm font-medium">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
