<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { Team } from '@/types';
import { computed } from 'vue';

interface Props {
    team?: Team | null;
}

const props = defineProps<Props>();

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = false;

// Add computed property for safe team name access
const teamName = computed(() => props.team?.name ?? 'No Team');

const fallbackInitials = computed(() => props.team ? getInitials(teamName.value) : 'NT');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage src="https://github.com/shadcn.png" v-if="showAvatar" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ fallbackInitials }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ teamName }}</span>
    </div>
</template>
