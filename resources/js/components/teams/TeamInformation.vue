<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Team } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    team: Team;
    canManageTeam: boolean;
}

const props = defineProps<Props>();
const nameInput = ref<HTMLInputElement | null>(null);

const updateForm = useForm({
    name: props.team.name,
});

const updateTeam = () => {
    updateForm.put(route('teams.update', { team: props.team.id }), {
        preserveScroll: true,
        onSuccess: () => {
            updateForm.reset();
            nameInput.value?.blur();
        },
    });
};
</script>

<template>
    <div class="rounded-lg border bg-card p-6">
        <form @submit.prevent="updateTeam" class="space-y-6">
            <div class="grid gap-2">
                <Label for="name">Team Name</Label>
                <Input
                    id="name"
                    ref="nameInput"
                    v-model="updateForm.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    :disabled="!canManageTeam"
                />
            </div>

            <div class="flex items-center gap-4" v-if="canManageTeam">
                <Button :disabled="updateForm.processing">
                    Update Team
                </Button>
            </div>
        </form>
    </div>
</template> 