import { useForm } from '@inertiajs/vue3';
import type { TrackingLookupPageProps } from '@/types/registration';

export const useTrackingLookup = (props: TrackingLookupPageProps) => {
    const form = useForm({
        email: '',
    });

    const submit = () => {
        form.post(props.requestLinkUrl, {
            preserveScroll: true,
        });
    };

    return {
        form,
        submit,
    };
};
