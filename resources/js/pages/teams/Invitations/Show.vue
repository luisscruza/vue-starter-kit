<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    team: {
        id: number;
        name: string;
    };
    invitation: {
        uuid: string;
        email: string;
    };
}>();

const form = useForm({
    name: '',
    email: props.invitation.email,
    password: '',
    password_confirmation: '',
});

const page = usePage();

const submit = () => {
    if (!page.props.auth) {
        form.post(route('teams.invitations.accept.register', { team: props.team.id, invitation: props.invitation.uuid }), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    } else {
       router.put(route('teams.invitations.accept.auth', { team: props.team.id, invitation: props.invitation.uuid }));
    }
};
</script>

<template>

    <AuthBase 
        v-if="!$page.props.auth"
        :title="`Join ${team.name}`" 
        :description="`You've been invited to join ${team.name}. Create an account to accept the invitation.`"
    >
        <Head :title="`Join ${team.name}`" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input 
                        id="name" 
                        type="text" 
                        required 
                        autofocus 
                        :tabindex="1" 
                        autocomplete="name" 
                        v-model="form.name" 
                        placeholder="Full name" 
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input 
                        id="email" 
                        type="email" 
                        required 
                        :tabindex="2" 
                        autocomplete="email" 
                        v-model="form.email" 
                        :value="invitation.email"
                        readonly
                        disabled
                        class="bg-muted"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Accept invitation and create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">
                    Log in
                </TextLink>
            </div>
        </form>
    </AuthBase>

    <AuthBase 
        v-else
        :title="`Join ${team.name}`" 
        :description="`You've been invited to join ${team.name} as ${$page.props.auth.email}.`"
    >
        <Head :title="`Join ${team.name}`" />

        <div v-if="$page.props.auth.user.email !== invitation.email" class="text-center text-sm text-destructive">
            You are currently logged in as {{ $page.props.auth.email }}. This invitation was sent to {{ invitation.email }}. 
            Please <TextLink :href="route('logout')" method="post" as="button">log out</TextLink> and sign in with the correct account.
        </div>

        <div v-else class="flex flex-col gap-6">
            <div class="rounded-lg border border-border bg-card p-4 text-card-foreground">
                <div class="flex flex-col gap-2">
                    <div class="text-sm text-muted-foreground">Team</div>
                    <div class="font-medium">{{ team.name }}</div>
                </div>
                <div class="mt-4 flex flex-col gap-2">
                    <div class="text-sm text-muted-foreground">Your Email</div>
                    <div class="font-medium">{{ invitation.email }}</div>
                </div>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <Button type="submit" class="w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Accept Invitation
                </Button>
            </form>

            <div class="text-center text-sm text-muted-foreground">
                Want to use a different account?
                <TextLink :href="route('logout')" method="post" as="button" class="underline underline-offset-4">
                    Log out
                </TextLink>
            </div>
        </div>
    </AuthBase>
</template>