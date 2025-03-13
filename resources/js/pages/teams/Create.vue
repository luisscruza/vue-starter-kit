<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Create Team',
        href: route('teams.create'),
    },
];

const nameInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    name: '',
});

const createTeam = () => {
    form.post(route('teams.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.name) {
                form.reset('name');
                nameInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Team" />

        <div class="max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
            <HeadingSmall 
                title="Create a New Team" 
                description="Create a new team to collaborate with others" 
            />

            <form @submit.prevent="createTeam" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Team Name</Label>
                    <Input
                        id="name"
                        ref="nameInput"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="Enter team name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">
                        Create Team
                    </Button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                            Team created.
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 