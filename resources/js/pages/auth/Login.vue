<template>
    <AuthBase title="Welcome Back 👋" description="Login to continue">
        <Head title="Login" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="rounded-lg bg-white/10 p-6 shadow-lg backdrop-blur-md">
            <div class="mb-6 text-center text-2xl font-bold text-white">Log in</div>

            <div class="grid gap-5">
                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="you@example.com"
                        class="text-white placeholder:text-gray-400"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm">
                            Forgot?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="••••••••"
                        class="text-white placeholder:text-gray-400"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Remember -->
                <div class="flex items-center space-x-2">
                    <Checkbox id="remember" v-model="form.remember" />
                    <Label for="remember">Remember me</Label>
                </div>

                <!-- Submit Button -->
                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Log in</span>
                </Button>
            </div>

            <!-- Register link -->
            <div class="mt-6 text-center text-sm text-muted-foreground">
                New here?
                <TextLink :href="route('register')">Create account</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
