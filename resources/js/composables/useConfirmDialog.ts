import { useConfirmDialog as useVueUseConfirmDialog } from '@vueuse/core';
import { ref } from 'vue';

interface CustomReveal {
    title?: string;
    description?: string;
    confirmText?: string;
    variant?: Variant;
    formSubmit?: FormSubmit;
}

interface FormSubmit {
    url: string;
    method: Method;
}
type Method = 'delete';
type Variant = 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link' | undefined;

const dialogTitle = ref();
const dialogdescription = ref();
const dialogConfirmText = ref();
const dialogVariant = ref<Variant>();
const dialogFormSubmit = ref<FormSubmit>();

const { reveal, confirm, isRevealed } = useVueUseConfirmDialog();

function customReveal({
    title = 'Are you sure you want to confirm?',
    description = 'Once confirmed, this action will be completed. Make sure you want to continue.',
    confirmText = 'Confirm',
    variant = 'default',
    formSubmit,
}: CustomReveal) {
    dialogTitle.value = title;
    dialogdescription.value = description;
    dialogConfirmText.value = confirmText;
    dialogVariant.value = variant;
    dialogFormSubmit.value = formSubmit;
    return reveal();
}

export function useConfirmDialog() {
    return {
        isRevealed,
        confirm,
        reveal: customReveal,
        title: dialogTitle,
        description: dialogdescription,
        confirmText: dialogConfirmText,
        variant: dialogVariant,
        formSubmit: dialogFormSubmit,
    };
}
