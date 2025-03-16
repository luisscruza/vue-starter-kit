<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { TeamMember, TeamRole } from '@/types';
import { useForm } from '@inertiajs/vue3';

interface Props {
    open: boolean;
    member: TeamMember;
    teamRoles: TeamRole[];
    onOpenChange: (open: boolean) => void;
}

const props = defineProps<Props>();

const form = useForm({
    role_id: props.member.membership.role_id,
});

const updateRole = () => {
    form.put(route('teams.members.update', { 
        team: props.member.membership.team_id, 
        user: props.member.id 
    }), {
        preserveScroll: true,
        onSuccess: () => {
            props.onOpenChange(false);
        },
    });
};

const getRoleName = (roleId: number) => {
    return props.teamRoles.find(role => role.id === roleId)?.name ?? '';
};
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit Member Role</DialogTitle>
                <DialogDescription>
                    Change the role for {{ member.name }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="updateRole" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Role </label>
                    <Select v-model="form.role_id">
                        <SelectTrigger>
                            <SelectValue class="capitalize" :placeholder="getRoleName(form.role_id)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="role in teamRoles" 
                                :key="role.id" 
                                :value="role.id"
                                class="capitalize"
                            >
                                {{ getRoleName(role.id) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DialogFooter>
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="onOpenChange(false)"
                    >
                        Cancel
                    </Button>
                    <Button 
                        type="submit"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template> 