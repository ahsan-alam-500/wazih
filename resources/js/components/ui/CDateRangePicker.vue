<!-- resources/js/components/ui/CDateRangePicker.vue -->
<script setup lang="ts">
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { defineModel, ref, watch } from 'vue';

const modelValue = defineModel<[Date | null, Date | null]>({ required: true });
</script>

<template>
  <VueDatePicker
    v-model="modelValue"
    range
    :dark="true"
    :enable-time-picker="false"
    input-class-name="w-full border px-4 py-2 rounded dark:bg-neutral-900 dark:text-white"
    placeholder="Select custom date range"
  />
</template>
