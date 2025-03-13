<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Team } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import TeamInformation from '@/components/teams/TeamInformation.vue';
import TeamMembersList from '@/components/teams/TeamMembersList.vue';
import type { BreadcrumbItem } from '@/types';

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

const canManageTeam = props.team.currentUserRole === 'owner' || props.team.currentUserRole === 'admin';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="team.name" />

        <div class="max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <HeadingSmall 
                :title="team.name" 
                description="Team Settings" 
            />

            <TeamInformation 
                :team="team"
                :can-manage-team="canManageTeam"
            />

            <TeamMembersList
                :team="team"
                :can-manage-team="canManageTeam"
            />
        </div>
    </AppLayout>
</template> 