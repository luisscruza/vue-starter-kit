<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const { getInitials } = useInitials();

const previewUrl = ref<string | null>(null);

const form = useForm({
    name: user.name,
    email: user.email,
    avatar_url: null as File | null,
    _method: 'put',
});

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const handleAvatarChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        // Revoke previous preview URL if exists
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
        }
        
        form.avatar_url = input.files[0];
        // Create new preview URL
        previewUrl.value = URL.createObjectURL(input.files[0]);
    }
};

// Cleanup preview URL when component is unmounted
onUnmounted(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name, email address and profile picture" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <Avatar class="h-24 w-24">
                            <AvatarImage 
                                v-if="previewUrl" 
                                :src="previewUrl" 
                                :alt="user.name" 
                            />
                            <AvatarImage 
                                v-else-if="user.avatar" 
                                :src="user.avatar" 
                                :alt="user.name" 
                            />
                            <AvatarFallback class="text-lg">
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>

                        <div class="grid gap-1.5">
                            <Label for="avatar">Profile picture</Label>
                            <Input 
                                id="avatar" 
                                type="file" 
                                accept="image/*"
                                @change="handleAvatarChange"
                            />
                            <InputError class="mt-2" :message="form.errors.avatar_url" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
