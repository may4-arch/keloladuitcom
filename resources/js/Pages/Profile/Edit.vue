<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-white">
                Pengaturan Profil
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Update Profile Info Card -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-6 shadow-sm sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Update Password Card -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-6 shadow-sm sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account Card -->
                <div class="rounded-xl border border-rose-950/60 bg-slate-900/80 p-6 shadow-sm sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
