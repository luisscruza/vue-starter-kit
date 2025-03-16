<script setup lang="ts">
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { Team } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings, Plus, Check } from 'lucide-vue-next';
import TeamInfo from './TeamInfo.vue';
import { router } from '@inertiajs/vue3';
interface Props {
    teams: Team[] | null;
    currentTeam: Team | null;
}

defineProps<Props>();

const switchTeam = (teamId: number) => {
    router.put(route('teams.switch', teamId), {}, {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <TeamInfo :team="currentTeam" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />

    <!-- Teams List -->
    <DropdownMenuGroup v-if="teams?.length">
        <DropdownMenuLabel class="px-2 py-1.5 text-xs font-medium">Your Teams</DropdownMenuLabel>
        <DropdownMenuItem v-for="team in teams" :key="team.id" :as-child="true">
            <button 
                class="flex w-full items-center" 
                @click="switchTeam(team.id)">
                <TeamInfo :team="team" />
                <Check 
                    v-if="currentTeam?.id === team.id" 
                    class="ml-auto h-4 w-4" 
                />
            </button>
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator v-if="teams?.length" />
    
    <!-- Create Team -->
    <DropdownMenuItem :as-child="true">
        <Link class="flex w-full items-center" :href="route('teams.create')" as="button">
            <Plus class="mr-2 h-4 w-4" />
            Create New Team
        </Link>
    </DropdownMenuItem>

    <!-- Team Settings -->
    <DropdownMenuItem :as-child="true" v-if="currentTeam">
        <Link class="flex w-full items-center" :href="route('teams.settings', currentTeam.id)" as="button">
            <Settings class="mr-2 h-4 w-4" />
            Team Settings
        </Link>
    </DropdownMenuItem>
</template>
