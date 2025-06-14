<script setup lang="ts">
const appName = import.meta.env.VITE_APP_NAME;
</script>

<template>
  <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
    <img src="https://bponi.sgp1.cdn.digitaloceanspaces.com/bponi/file/1b48f6b3-67d3-4679-a511-fbd47fadb24d.png" alt="">
  </div>
  <div class="ml-1 grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{ appName }}</span>
  </div>
</template>
