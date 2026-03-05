import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSubmissionTracking } from '@/composables/useSubmissionTracking';
import type { TrackingShowPageProps } from '@/types/registration';

export const useTrackingShow = (props: TrackingShowPageProps) => {
    const form = useForm({});
    const requestEditForm = useForm({});
    const { formatDate, sectionEditUrl } = useSubmissionTracking(props.editUrl);

    const canEditSection = (sectionName: string) =>
        props.canEdit && props.editableSections.includes(sectionName);

    const summarySections = computed(() => {
        return (props.summary?.sections ?? [])
            .map((section) => {
                if (section.name === 'treasurer_details') {
                    return {
                        ...section,
                        fields: section.fields.map((field) => ({
                            ...field,
                            value: String(field.value ?? '').trim() === '' ? 'NA' : field.value,
                        })),
                    };
                }

                return {
                    ...section,
                    fields: section.fields.filter((field) => String(field.value ?? '').trim() !== ''),
                };
            })
            .filter((section) => section.name === 'treasurer_details' || section.fields.length > 0);
    });

    const logout = () => {
        form.post(props.logoutUrl);
    };

    const requestEditPermission = () => {
        requestEditForm.post(props.requestEditPermissionUrl, {
            preserveScroll: true,
        });
    };

    return {
        form,
        requestEditForm,
        formatDate,
        sectionEditUrl,
        canEditSection,
        summarySections,
        logout,
        requestEditPermission,
    };
};
