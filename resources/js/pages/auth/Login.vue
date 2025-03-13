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
import { inject, ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const googleLoading = ref(false);

const handleGoogleLogin = () => {
    googleLoading.value = true;
    window.location.href = route('auth.google');
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const hasGoogleAuth = inject('hasGoogleAuth');
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="flex flex-col gap-6">
            <!-- Google Sign In Button -->
            <div v-if="hasGoogleAuth" class="grid gap-6">
                <Button 
                    variant="outline" 
                    class="w-full" 
                    @click="handleGoogleLogin"
                    :disabled="googleLoading"
                >
                    <LoaderCircle v-if="googleLoading" class="mr-2 h-4 w-4 animate-spin" />
                    <svg v-else class="mr-2 h-4 w-4" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Sign in with Google
                </Button>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-background px-2 text-muted-foreground">Or continue with</span>
                    </div>
                </div>
            </div>

            <!-- Existing login form -->
            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            v-model="form.email"
                            placeholder="email@example.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Password</Label>
                            <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                                Forgot password?
                            </TextLink>
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between" :tabindex="3">
                        <Label for="remember" class="flex items-center space-x-3">
                            <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Log in
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
                </div>
            </form>
        </div>
    </AuthBase>
</template>
