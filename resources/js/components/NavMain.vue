<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  ShoppingCart,
  Package,
  Users,
  BarChart,
  Settings,
} from 'lucide-vue-next';

const page = usePage();

// 🛒 eCommerce Dashboard Menu Items
const items = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Orders',
    href: '/admin/orders',
    icon: ShoppingCart,
  },
  {
    title: 'Products',
    href: '/admin/products',
    icon: Package,
  },
  {
    title: 'Customers',
    href: '/admin/customers',
    icon: Users,
  },
  {
    title: 'Reports',
    href: '/admin/reports',
    icon: BarChart,
  },
  {
    title: 'Settings',
    href: '/admin/settings',
    icon: Settings,
  },
];
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Admin Panel</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem v-for="item in items" :key="item.title">
        <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
          <Link :href="item.href">
            <component :is="item.icon" class="w-5 h-5 mr-2" />
            <span>{{ item.title }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>
