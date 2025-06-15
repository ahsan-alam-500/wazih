<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 px-4">
    <div class="w-full max-w-md rounded-xl bg-white/10 p-8 shadow-xl backdrop-blur-lg text-white">
      <h2 class="text-center text-3xl font-bold mb-6">Welcome Back 👋</h2>
      <p class="text-center text-sm text-gray-300 mb-6">Login to access your account</p>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium mb-1">Email address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="you@example.com"
            class="w-full rounded bg-white/10 border border-gray-500 px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-green-500"
          />
          <p v-if="form.errors.email" class="text-sm text-red-400 mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium mb-1">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full rounded bg-white/10 border border-gray-500 px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-green-500"
          />
          <p v-if="form.errors.password" class="text-sm text-red-400 mt-1">{{ form.errors.password }}</p>
        </div>

        <!-- Remember checkbox -->
        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2 text-sm">
            <input
              type="checkbox"
              v-model="form.remember"
              class="rounded border-gray-300 text-green-500 focus:ring-0"
            />
            <span>Remember me</span>
          </label>
          <a v-if="canResetPassword" :href="route('password.request')" class="text-sm text-green-400 hover:underline">Forgot password?</a>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full rounded bg-green-500 hover:bg-green-600 px-4 py-2 font-medium text-white transition disabled:opacity-50"
        >
          <span v-if="form.processing">Logging in...</span>
          <span v-else>Log In</span>
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-300">
        Don't have an account?
        <a :href="route('register')" class="text-green-400 hover:underline">Sign up</a>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
