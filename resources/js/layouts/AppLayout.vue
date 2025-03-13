<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import Toaster from '@/components/ui/toast/Toaster.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

interface PageProps {
    flash: {
        success?: string;
        error?: string;
    };
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { toast } = useToast();
const page = usePage<PageProps>();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            toast({
                title: flash.success,
                variant: "default",
            });
        }
        if (flash.error) {
            toast({
                title: flash.error,
                variant: "destructive",
            });
        }
    },
    { immediate: true }
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <Toaster />
</template>
