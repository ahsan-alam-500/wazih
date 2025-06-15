<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-2xl rounded bg-white p-6 shadow-lg">
            <h2 class="mb-4 text-xl font-bold text-gray-800">Trust Summary</h2>

            <div v-if="summary">
                <div class="mb-4">
                    <p><strong>Total Orders:</strong> {{ summary.total }}</p>
                    <p><strong>Success:</strong> {{ summary.success }}</p>
                    <p><strong>Cancelled:</strong> {{ summary.cancel }}</p>
                    <p><strong>Success Rate:</strong> {{ summary.successRate }}%</p>
                    <p><strong>Cancel Rate:</strong> {{ summary.cancelRate }}%</p>
                </div>

                <!-- Courier-wise breakdown -->
                <div>
                    <h3 class="mb-2 text-lg font-semibold">Courier-wise Breakdown:</h3>
                    <table class="w-full border text-sm text-gray-800">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-2 text-left">Courier</th>
                                <th class="p-2 text-right text-gray-800">Total</th>
                                <th class="p-2 text-right text-gray-800">Success</th>
                                <th class="p-2 text-right text-gray-800">Cancel</th>
                                <th class="p-2 text-right text-gray-800">Success %</th>
                                <th class="p-2 text-right text-gray-800">Cancel %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(data, courier) in summary.couriers" :key="courier">
                                <td class="p-2">{{ courier }}</td>
                                <td class="p-2 text-right">{{ data.total }}</td>
                                <td class="p-2 text-right text-green-600">{{ data.success }}</td>
                                <td class="p-2 text-right text-red-600">{{ data.cancel }}</td>
                                <td class="p-2 text-right">{{ data.successRate }}%</td>
                                <td class="p-2 text-right">{{ data.cancelRate }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Condition block only renders after summary is loaded -->
            <div v-if="summary" class="mt-6 flex items-center justify-between">
                <p class="mr-4 font-semibold text-green-600">
                    <span v-if="summary.successRate > 80">Cash On Delivery secured</span>
                    <span v-else-if="summary.successRate > 60">Neutral Delivery</span>
                    <span v-else>Not Cash On Delivery</span>
                </p>
                <button @click="$emit('close')" class="rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-700">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    show: boolean;
    summary: {
        total: number;
        success: number;
        cancel: number;
        successRate: number;
        cancelRate: number;
        couriers: {
            [courierName: string]: {
                total: number;
                success: number;
                cancel: number;
                successRate: number;
                cancelRate: number;
            };
        };
    } | null;
}>();
</script>
