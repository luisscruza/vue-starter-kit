<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type Team } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    team: Team;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.team.name,
        href: route('teams.settings', { team: props.team.id }),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="team.name" />

        <div class="max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
            <HeadingSmall 
                :title="team.name" 
                description="Team Details" 
            />

            <div class="rounded-lg border bg-card p-6">
                <dl class="divide-y divide-border">
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6">Team Name</dt>
                        <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                            {{ team.name }}
                        </dd>
                    </div>
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6">Created</dt>
                        <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                            {{ new Date(team.created_at).toLocaleDateString() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </AppLayout>
</template> 